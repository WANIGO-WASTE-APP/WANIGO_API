<?php

namespace App\Http\Controllers\API\Nasabah;

use App\Http\Controllers\Controller;
use App\Models\MemberBankSampah;
use App\Models\SetoranSampah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics for authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        $user = $request->user();
        
        // Get all active memberships
        $memberships = MemberBankSampah::where('user_id', $user->id)
            ->where('status_keanggotaan', 'aktif')
            ->get();
        
        // Calculate total saldo from all memberships
        $totalSaldo = $memberships->sum('saldo') ?? 0.00;
        
        // Calculate total tonase from completed setoran
        $totalTonase = SetoranSampah::where('user_id', $user->id)
            ->where('status_setoran', 'Selesai')
            ->sum('total_berat') ?? 0.00;
        
        // Count total setoran
        $totalSetoran = SetoranSampah::where('user_id', $user->id)->count() ?? 0;
        
        // Count total bank sampah memberships
        $totalBankSampah = $memberships->count() ?? 0;
        
        return response()->json([
            'success' => true,
            'message' => 'Statistik dashboard berhasil diambil',
            'data' => [
                'total_saldo' => (float) $totalSaldo,
                'total_tonase_sampah' => (float) $totalTonase,
                'total_setoran' => (int) $totalSetoran,
                'total_bank_sampah' => (int) $totalBankSampah,
            ]
        ]);
    }
}
