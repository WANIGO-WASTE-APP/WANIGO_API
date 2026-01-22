<?php

namespace App\Rules;

use App\Models\SubKategoriSampah;
use Illuminate\Contracts\Validation\Rule;

class KatalogKategoriConsistency implements Rule
{
    /**
     * The kategori_sampah value to validate against.
     *
     * @var int
     */
    protected $kategoriSampah;

    /**
     * Create a new rule instance.
     *
     * @param int $kategoriSampah The kategori_sampah value (0=kering, 1=basah)
     * @return void
     */
    public function __construct($kategoriSampah)
    {
        $this->kategoriSampah = $kategoriSampah;
    }

    /**
     * Determine if the validation rule passes.
     *
     * This rule validates that a katalog item's kategori_sampah matches
     * the kategori_sampah of its linked sub_kategori_sampah.
     *
     * @param string $attribute The attribute name being validated
     * @param mixed $value The sub_kategori_sampah_id value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Allow null sub_kategori_sampah_id for backward compatibility
        if (is_null($value)) {
            return true;
        }

        // Find the sub kategori sampah
        $subKategori = SubKategoriSampah::find($value);

        // If sub kategori doesn't exist, fail validation
        if (!$subKategori) {
            return false;
        }

        // Verify kategori_sampah matches
        return $subKategori->kategori_sampah == $this->kategoriSampah;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $kategoriText = $this->kategoriSampah == 0 ? 'kering' : 'basah';
        return "Sub kategori sampah harus sesuai dengan kategori sampah ({$kategoriText}).";
    }
}
