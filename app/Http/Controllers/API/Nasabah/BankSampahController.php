<?php

namespace App\Http\Controllers\API\Nasabah;

use App\Http\Controllers\Controller;
use App\Http\Resources\BankSampahListResource;
use App\Http\Resources\BankSampahDetailResource;
use App\Models\BankSampah;
use App\Models\MemberBankSampah;
use App\Models\JamOperasionalBankSampah;
use App\Models\KatalogSampah;
use App\Models\SetoranSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BankSampahController extends Controller
{
    /**
     * Mendapatkan daftar bank sampah.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = BankSampah::with(['jamOperasional', 'katalogSampah']);

        // Filter berdasarkan keyword (nama bank sampah)
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where('nama_bank_sampah', 'like', "%{$keyword}%");
        }

        // Filter berdasarkan status operasional
        if ($request->has('status_operasional')) {
            $query->where('status_operasional', $request->status_operasional);
        }

        // Filter berdasarkan kategori sampah
        if ($request->has('kategori_sampah')) {
            $kategoriSampah = $request->kategori_sampah;

            $query->whereHas('katalogSampah', function($q) use ($kategoriSampah) {
                $q->where('kategori_sampah', $kategoriSampah);
            });
        }

        // Filter berdasarkan jarak (jika ada latitude dan longitude)
        if ($request->has('latitude') && $request->has('longitude')) {
            $latitude = $request->latitude;
            $longitude = $request->longitude;
            $radius = $request->radius ?? 10; // Default 10 km

            // Haversine formula untuk menghitung jarak
            $query->selectRaw("bank_sampah.*,
                (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance_km",
                [$latitude, $longitude, $latitude])
                ->having('distance_km', '<=', $radius)
                ->orderBy('distance_km');
        }

        $bankSampah = $query->get();

        // Tambahkan status keanggotaan untuk setiap bank sampah
        $userId = Auth::id();
        foreach ($bankSampah as $bank) {
            $member = MemberBankSampah::where('user_id', $userId)
                ->where('bank_sampah_id', $bank->id)
                ->first();

            $bank->member_status = $member ? $member->status_keanggotaan : 'bukan_nasabah';
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar bank sampah berhasil diambil',
            'data' => BankSampahListResource::collection($bankSampah)
        ]);
    }

    /**
     * Mendapatkan daftar bank sampah yang terhubung dengan nasabah.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBankSampahList()
    {
        $userId = Auth::id();

        $bankSampah = BankSampah::with(['jamOperasional', 'katalogSampah'])
            ->whereIn('id', function($query) use ($userId) {
            $query->select('bank_sampah_id')
                  ->from('member_bank_sampah')
                  ->where('user_id', $userId)
                  ->where('status_keanggotaan', 'aktif');
        })->get();

        // Add member status
        foreach ($bankSampah as $bank) {
            $bank->member_status = 'aktif';
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar bank sampah nasabah berhasil diambil',
            'data' => BankSampahListResource::collection($bankSampah)
        ]);
    }

    /**
     * Mendapatkan detail bank sampah.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $bankSampah = BankSampah::with(['jamOperasional', 'katalogSampah', 'provinsi', 'kabupatenKota', 'kecamatan', 'kelurahanDesa'])->find($id);

        if (!$bankSampah) {
            return response()->json([
                'success' => false,
                'message' => 'Bank sampah tidak ditemukan'
            ], 404);
        }

        // Cek status keanggotaan nasabah di bank sampah ini
        $userId = Auth::id();
        $memberStatus = 'bukan_nasabah';
        $memberData = null;

        $memberBankSampah = MemberBankSampah::where('user_id', $userId)
            ->where('bank_sampah_id', $id)
            ->first();

        if ($memberBankSampah) {
            $memberStatus = $memberBankSampah->status_keanggotaan;
            $memberData = [
                'kode_nasabah' => $memberBankSampah->kode_nasabah,
                'tanggal_bergabung' => $memberBankSampah->created_at->format('Y-m-d'),
                'saldo' => (float) $memberBankSampah->saldo,
            ];
        }

        // Add member status and data to bank sampah object
        $bankSampah->member_status = $memberStatus;
        $bankSampah->member_data = $memberData;

        return response()->json([
            'success' => true,
            'message' => 'Detail bank sampah berhasil diambil',
            'data' => new BankSampahDetailResource($bankSampah)
        ]);
    }

    /**
     * Mencari bank sampah terdekat.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function findNearby(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'sometimes|numeric|min:1|max:50',
            'kategori_sampah' => 'sometimes|in:0,1' // 0=kering, 1=basah
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius ?? 10; // Default 10 km

        $query = BankSampah::with(['jamOperasional', 'katalogSampah'])
            ->selectRaw("bank_sampah.*,
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance_km",
            [$latitude, $longitude, $latitude])
            ->where('status_operasional', true)
            ->having('distance_km', '<=', $radius);

        // Filter berdasarkan kategori sampah
        if ($request->has('kategori_sampah')) {
            $kategoriSampah = $request->kategori_sampah;

            $query->whereHas('katalogSampah', function($q) use ($kategoriSampah) {
                $q->where('kategori_sampah', $kategoriSampah);
            });
        }

        $bankSampah = $query->orderBy('distance_km')->get();

        // Tambahkan status keanggotaan untuk setiap bank sampah
        $userId = Auth::id();
        foreach ($bankSampah as $bank) {
            $member = MemberBankSampah::where('user_id', $userId)
                ->where('bank_sampah_id', $bank->id)
                ->first();

            $bank->member_status = $member ? $member->status_keanggotaan : 'bukan_nasabah';
        }

        return response()->json([
            'success' => true,
            'message' => 'Bank sampah terdekat berhasil diambil',
            'data' => BankSampahListResource::collection($bankSampah)
        ]);
    }

    /**
     * Mendapatkan jam operasional bank sampah.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getJamOperasional($id)
    {
        $bankSampah = BankSampah::find($id);

        if (!$bankSampah) {
            return response()->json([
                'success' => false,
                'message' => 'Bank sampah tidak ditemukan'
            ], 404);
        }

        $jamOperasional = JamOperasionalBankSampah::where('bank_sampah_id', $id)
            ->orderBy('day_of_week')
            ->get();

        // Format jam operasional untuk tampilan
        $formatted = [];
        foreach ($jamOperasional as $jam) {
            $formatted[] = [
                'day_of_week' => $jam->day_of_week,
                'day_name' => $jam->getDayNameAttribute(),
                'open_time' => Carbon::parse($jam->open_time)->format('H:i'),
                'close_time' => Carbon::parse($jam->close_time)->format('H:i'),
                'jam_operasional_format' => $jam->getJamOperasionalFormatAttribute()
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }

    /**
     * Mendapatkan katalog sampah bank sampah.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getKatalogSampah($id, Request $request)
    {
        $bankSampah = BankSampah::find($id);

        if (!$bankSampah) {
            return response()->json([
                'success' => false,
                'message' => 'Bank sampah tidak ditemukan'
            ], 404);
        }

        $query = KatalogSampah::where('bank_sampah_id', $id)
                  ->where('status', 'aktif');

        // Filter berdasarkan kategori sampah
        if ($request->has('kategori_sampah')) {
            $query->where('kategori_sampah', $request->kategori_sampah);
        }

        $katalogSampah = $query->orderBy('nama_sampah')->get();

        return response()->json([
            'success' => true,
            'data' => $katalogSampah
        ]);
    }

    /**
     * Filter bank sampah berdasarkan peta.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function mapFilter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'sometimes|numeric|min:1|max:50',
            'kategori_sampah' => 'sometimes|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius ?? 10; // Default 10 km

        $query = BankSampah::with(['jamOperasional', 'katalogSampah'])
            ->selectRaw("bank_sampah.*,
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance_km",
            [$latitude, $longitude, $latitude])
            ->having('distance_km', '<=', $radius);

        // Filter berdasarkan kategori sampah
        if ($request->has('kategori_sampah')) {
            $kategoriSampah = $request->kategori_sampah;

            $query->whereHas('katalogSampah', function($q) use ($kategoriSampah) {
                $q->where('kategori_sampah', $kategoriSampah);
            });
        }

        $bankSampah = $query->orderBy('distance_km')->get();

        // Tambahkan status keanggotaan dan status operasional realtime
        $userId = Auth::id();
        $hariIni = Carbon::now()->dayOfWeek;

        foreach ($bankSampah as $bank) {
            // Status keanggotaan
            $member = MemberBankSampah::where('user_id', $userId)
                ->where('bank_sampah_id', $bank->id)
                ->first();

            $bank->member_status = $member ? $member->status_keanggotaan : 'bukan_nasabah';

            // Status operasional realtime
            $jamOperasional = JamOperasionalBankSampah::where('bank_sampah_id', $bank->id)
                ->where('day_of_week', $hariIni)
                ->first();

            $bank->sedang_buka = $jamOperasional ? $jamOperasional->isBuka() : false;
        }

        return response()->json([
            'success' => true,
            'message' => 'Bank sampah berhasil difilter',
            'data' => BankSampahListResource::collection($bankSampah)
        ]);
    }

    /**
     * Get top frequency bank sampah based on user's transaction history.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopFrequency(Request $request)
    {
        $userId = Auth::id();
        
        // Aggregate setoran_sampah by bank_sampah_id and count
        $topBanks = SetoranSampah::select('bank_sampah_id', DB::raw('COUNT(*) as visit_count'))
            ->where('user_id', $userId)
            ->groupBy('bank_sampah_id')
            ->orderByDesc('visit_count')
            ->limit(5)
            ->with(['bankSampah' => function($query) {
                $query->with(['jamOperasional', 'katalogSampah']);
            }])
            ->get();
        
        // Transform to include bank details
        $result = $topBanks->map(function($item) {
            return [
                'bank_sampah' => $item->bankSampah,
                'visit_count' => $item->visit_count,
            ];
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Top frekuensi bank sampah berhasil diambil',
            'data' => $result
        ]);
    }

    /**
     * Get all bank sampah with comprehensive filtering and sorting.
     * Public endpoint for listing all bank sampah.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllBankSampah(Request $request)
    {
        // Validate request parameters
        $validator = Validator::make($request->all(), [
            'q' => 'sometimes|string|max:255',
            'lat' => 'sometimes|numeric|between:-90,90',
            'lng' => 'sometimes|numeric|between:-180,180',
            'radius_km' => 'sometimes|integer|min:1|max:100',
            'kategori' => 'sometimes|in:kering,basah,semua',
            'provinsi_id' => 'sometimes|exists:provinsi,id',
            'kabupaten_id' => 'sometimes|exists:kabupaten_kota,id',
            'kecamatan_id' => 'sometimes|exists:kecamatan,id',
            'sort' => 'sometimes|in:distance,name',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $query = BankSampah::with(['jamOperasional', 'provinsi', 'kabupatenKota', 'kecamatan', 'kelurahanDesa', 'katalogSampah'])
            ->where('status_operasional', true);
        
        // Keyword search
        if ($request->filled('q')) {
            $query->where('nama_bank_sampah', 'like', "%{$request->q}%");
        }
        
        // Administrative region filters
        if ($request->filled('provinsi_id')) {
            $query->where('provinsi_id', $request->provinsi_id);
        }
        
        if ($request->filled('kabupaten_id')) {
            $query->where('kabupaten_kota_id', $request->kabupaten_id);
        }
        
        if ($request->filled('kecamatan_id')) {
            $query->where('kecamatan_id', $request->kecamatan_id);
        }
        
        // Category filter
        if ($request->filled('kategori') && $request->kategori !== 'semua') {
            $kategoriValue = $request->kategori === 'kering' ? 0 : 1;
            $query->whereHas('katalogSampah', function($q) use ($kategoriValue) {
                $q->where('kategori_sampah', $kategoriValue)
                  ->where('status_aktif', true);
            });
        }
        
        // Location-based filtering with distance calculation
        $hasLocation = $request->filled('lat') && $request->filled('lng');
        if ($hasLocation) {
            $lat = $request->lat;
            $lng = $request->lng;
            $radius = $request->radius_km ?? 10;
            
            // Haversine formula for distance calculation
            $query->selectRaw("
                bank_sampah.*,
                (6371 * acos(
                    cos(radians(?)) * 
                    cos(radians(latitude)) * 
                    cos(radians(longitude) - radians(?)) + 
                    sin(radians(?)) * 
                    sin(radians(latitude))
                )) AS distance_km
            ", [$lat, $lng, $lat])
            ->having('distance_km', '<=', $radius);
        }
        
        // Sorting
        $sort = $request->sort ?? ($hasLocation ? 'distance' : 'name');
        if ($sort === 'distance' && $hasLocation) {
            $query->orderBy('distance_km', 'asc');
        } else {
            $query->orderBy('nama_bank_sampah', 'asc');
        }
        
        // Pagination
        $perPage = $request->per_page ?? 20;
        $bankSampah = $query->paginate($perPage);
        
        // Add member status for authenticated users
        $userId = Auth::id();
        if ($userId) {
            $bankSampah->getCollection()->transform(function($bank) use ($userId) {
                $member = MemberBankSampah::where('user_id', $userId)
                    ->where('bank_sampah_id', $bank->id)
                    ->first();
                
                $bank->member_status = $member ? $member->status_keanggotaan : 'bukan_nasabah';
                return $bank;
            });
        }
        
        // Transform to resource
        return response()->json([
            'success' => true,
            'message' => 'Daftar bank sampah berhasil diambil',
            'data' => BankSampahListResource::collection($bankSampah),
            'meta' => [
                'current_page' => $bankSampah->currentPage(),
                'per_page' => $bankSampah->perPage(),
                'total' => $bankSampah->total(),
                'last_page' => $bankSampah->lastPage(),
                'from' => $bankSampah->firstItem(),
                'to' => $bankSampah->lastItem(),
            ]
        ]);
    }
}