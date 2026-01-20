<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PenarikanSaldo extends Model
{
    use HasFactory;

    protected $table = 'penarikan_saldo';

    protected $fillable = [
        'user_id',
        'bank_sampah_id',
        'member_bank_sampah_id',
        'jumlah_penarikan',
        'foto_buku_tabungan',
        'kode_verifikasi',
        'status',
        'approved_at',
        'completed_at',
        'approved_by',
        'catatan',
    ];

    protected $casts = [
        'jumlah_penarikan' => 'decimal:2',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Generate a unique 6-character verification code.
     *
     * @return string
     */
    public static function generateKodeVerifikasi(): string
    {
        return strtoupper(Str::random(6));
    }

    /**
     * Get the user that owns this withdrawal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bank sampah for this withdrawal.
     */
    public function bankSampah(): BelongsTo
    {
        return $this->belongsTo(BankSampah::class);
    }

    /**
     * Get the member bank sampah for this withdrawal.
     */
    public function memberBankSampah(): BelongsTo
    {
        return $this->belongsTo(MemberBankSampah::class);
    }

    /**
     * Get the user who approved this withdrawal.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending withdrawals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved withdrawals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include completed withdrawals.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get formatted jumlah penarikan in Rupiah.
     *
     * @return string
     */
    public function getJumlahPenarikanFormatAttribute()
    {
        return 'Rp ' . number_format($this->jumlah_penarikan, 0, ',', '.');
    }

    /**
     * Get the full URL for foto_buku_tabungan.
     *
     * @return string|null
     */
    public function getFotoBukuTabunganUrlAttribute()
    {
        if (!$this->foto_buku_tabungan) {
            return null;
        }

        // If already a full URL
        if (filter_var($this->foto_buku_tabungan, FILTER_VALIDATE_URL)) {
            return $this->foto_buku_tabungan;
        }

        // If it's a file path in storage
        return url('storage/' . $this->foto_buku_tabungan);
    }
}
