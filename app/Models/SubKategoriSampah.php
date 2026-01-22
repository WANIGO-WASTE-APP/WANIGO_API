<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubKategoriSampah extends Model
{
    use HasFactory;

    protected $table = 'sub_kategori_sampah';

    protected $fillable = [
        'bank_sampah_id',
        'kategori_sampah_id', // Keep for backward compatibility during migration
        'kategori_sampah', // New field
        'nama_sub_kategori',
        'kode_sub_kategori',
        'slug', // New field
        'deskripsi',
        'icon',
        'warna',
        'urutan',
        'status_aktif', // Keep for backward compatibility during migration
        'is_active', // New field
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'is_active' => 'boolean',
        'kategori_sampah' => 'integer',
        'urutan' => 'integer',
    ];

    /**
     * Mendapatkan bank sampah yang memiliki sub-kategori ini.
     */
    public function bankSampah(): BelongsTo
    {
        return $this->belongsTo(BankSampah::class, 'bank_sampah_id');
    }

    /**
     * Mendapatkan kategori sampah utama dari sub-kategori ini.
     */
    public function kategoriSampah(): BelongsTo
    {
        return $this->belongsTo(KategoriSampah::class, 'kategori_sampah_id');
    }

    /**
     * Mendapatkan item sampah dalam sub-kategori ini.
     */
    public function katalogSampah(): HasMany
    {
        return $this->hasMany(KatalogSampah::class, 'sub_kategori_sampah_id');
    }

    /**
     * Scope untuk sub-kategori aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    /**
     * Scope untuk sub-kategori aktif (new field).
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk sub-kategori berdasarkan kategori utama.
     */
    public function scopeKategori($query, $kategoriId)
    {
        return $query->where('kategori_sampah_id', $kategoriId);
    }

    /**
     * Scope untuk mendapatkan sub-kategori sampah kering.
     */
    public function scopeKering($query)
    {
        // New implementation using kategori_sampah field
        return $query->where('kategori_sampah', 0);
    }

    /**
     * Scope untuk mendapatkan sub-kategori sampah basah.
     */
    public function scopeBasah($query)
    {
        // New implementation using kategori_sampah field
        return $query->where('kategori_sampah', 1);
    }

    /**
     * Scope untuk filter berdasarkan kategori (kering/basah/semua).
     */
    public function scopeByKategori($query, $kategori)
    {
        if ($kategori === 'kering') {
            return $query->kering();
        } elseif ($kategori === 'basah') {
            return $query->basah();
        }
        return $query; // 'semua' or invalid
    }

    /**
     * Scope untuk filter berdasarkan bank sampah.
     */
    public function scopeForBank($query, $bankSampahId)
    {
        return $query->where('bank_sampah_id', $bankSampahId);
    }

    /**
     * Scope untuk mengurutkan sub-kategori.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan', 'asc')->orderBy('nama_sub_kategori', 'asc');
    }

    /**
     * Accessor untuk mendapatkan teks kategori sampah.
     */
    public function getKategoriSampahTextAttribute()
    {
        return $this->kategori_sampah === 0 ? 'kering' : 'basah';
    }

    /**
     * Mutator untuk nama sub kategori dengan auto-slug generation.
     */
    public function setNamaSubKategoriAttribute($value)
    {
        $this->attributes['nama_sub_kategori'] = $value;
        
        // Auto-generate slug if not set
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = $this->generateUniqueSlug($value);
        }
    }

    /**
     * Helper method untuk generate unique slug.
     */
    protected function generateUniqueSlug($name)
    {
        $slug = \Illuminate\Support\Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;
        
        while (static::where('bank_sampah_id', $this->bank_sampah_id)
            ->where('kategori_sampah', $this->kategori_sampah)
            ->where('slug', $slug)
            ->where('id', '!=', $this->id ?? 0)
            ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Periksa apakah sub-kategori ini adalah untuk sampah kering.
     */
    public function isKering(): bool
    {
        return $this->kategoriSampah->isKering();
    }

    /**
     * Periksa apakah sub-kategori ini adalah untuk sampah basah.
     */
    public function isBasah(): bool
    {
        return $this->kategoriSampah->isBasah();
    }
}