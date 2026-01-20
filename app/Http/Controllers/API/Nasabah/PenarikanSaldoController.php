<?php

namespace App\Http\Controllers\API\Nasabah;

use App\Http\Controllers\Controller;
use App\Models\PenarikanSaldo;
use App\Models\MemberBankSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PenarikanSaldoController extends Controller
{
    /**
     * Create a new withdrawal request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_sampah_id' => 'required|exists:bank_sampah,id',
            'jumlah_penarikan' => 'required|numeric|min:0',
            'foto_buku_tabungan' => 'required|image|max:2048',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = $request->user();
        
        // Get membership
        $member = MemberBankSampah::where('user_id', $user->id)
            ->where('bank_sampah_id', $request->bank_sampah_id)
            ->where('status_keanggotaan', 'aktif')
            ->first();
        
        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Anda bukan nasabah aktif bank sampah ini'
            ], 403);
        }
        
        // Check sufficient balance
        if ($member->saldo < $request->jumlah_penarikan) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo tidak mencukupi'
            ], 422);
        }
        
        // Upload photo
        $fotoPath = $request->file('foto_buku_tabungan')
            ->store('penarikan_saldo', 'public');
        
        // Create withdrawal
        $penarikan = PenarikanSaldo::create([
            'user_id' => $user->id,
            'bank_sampah_id' => $request->bank_sampah_id,
            'member_bank_sampah_id' => $member->id,
            'jumlah_penarikan' => $request->jumlah_penarikan,
            'foto_buku_tabungan' => $fotoPath,
            'kode_verifikasi' => PenarikanSaldo::generateKodeVerifikasi(),
            'status' => 'pending',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Pengajuan penarikan berhasil dibuat',
            'data' => [
                'id' => $penarikan->id,
                'jumlah_penarikan' => (float) $penarikan->jumlah_penarikan,
                'kode_verifikasi' => $penarikan->kode_verifikasi,
                'status' => $penarikan->status,
                'created_at' => $penarikan->created_at->format('Y-m-d H:i:s'),
            ]
        ], 201);
    }
    
    /**
     * Approve a withdrawal request (for petugas).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(Request $request, $id)
    {
        $penarikan = PenarikanSaldo::find($id);
        
        if (!$penarikan) {
            return response()->json([
                'success' => false,
                'message' => 'Penarikan tidak ditemukan'
            ], 404);
        }
        
        // Check if already approved or completed
        if ($penarikan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Penarikan sudah diproses sebelumnya'
            ], 422);
        }
        
        // Update status to approved
        $penarikan->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Penarikan berhasil disetujui',
            'data' => $penarikan
        ]);
    }
    
    /**
     * Complete a withdrawal with verification code (for nasabah).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function complete(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'kode_verifikasi' => 'required|string|size:6',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $penarikan = PenarikanSaldo::find($id);
        
        if (!$penarikan) {
            return response()->json([
                'success' => false,
                'message' => 'Penarikan tidak ditemukan'
            ], 404);
        }
        
        // Verify code
        if ($penarikan->kode_verifikasi !== strtoupper($request->kode_verifikasi)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode verifikasi salah'
            ], 422);
        }
        
        // Check status
        if ($penarikan->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Penarikan belum disetujui petugas'
            ], 422);
        }
        
        // Update status and deduct balance
        DB::transaction(function() use ($penarikan) {
            $penarikan->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            
            $member = $penarikan->memberBankSampah;
            $member->saldo -= $penarikan->jumlah_penarikan;
            $member->save();
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Penarikan berhasil diselesaikan',
            'data' => [
                'id' => $penarikan->id,
                'status' => $penarikan->status,
                'completed_at' => $penarikan->completed_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }
    
    /**
     * Get withdrawal history for authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $penarikan = PenarikanSaldo::where('user_id', $user->id)
            ->with(['bankSampah', 'memberBankSampah'])
            ->orderByDesc('created_at')
            ->paginate(20);
        
        return response()->json([
            'success' => true,
            'message' => 'Riwayat penarikan berhasil diambil',
            'data' => $penarikan->items(),
            'meta' => [
                'current_page' => $penarikan->currentPage(),
                'per_page' => $penarikan->perPage(),
                'total' => $penarikan->total(),
                'last_page' => $penarikan->lastPage(),
            ]
        ]);
    }
    
    /**
     * Get withdrawal detail.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $penarikan = PenarikanSaldo::with(['bankSampah', 'memberBankSampah', 'user', 'approvedBy'])
            ->find($id);
        
        if (!$penarikan) {
            return response()->json([
                'success' => false,
                'message' => 'Penarikan tidak ditemukan'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Detail penarikan berhasil diambil',
            'data' => $penarikan
        ]);
    }
}
