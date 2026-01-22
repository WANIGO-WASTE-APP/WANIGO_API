# KatalogKategoriConsistency Validation Rule Usage

## Overview

The `KatalogKategoriConsistency` validation rule ensures that a katalog item's `kategori_sampah` matches the `kategori_sampah` of its linked `sub_kategori_sampah`.

## Requirements Validated

- **Requirement 6.1**: When creating a katalog_sampah record, the system shall verify kategori_sampah matches the linked sub_kategori_sampah.kategori_sampah
- **Requirement 6.2**: When updating a katalog_sampah record, the system shall re-validate category consistency
- **Requirement 6.4**: The system shall provide a clear error message indicating the category mismatch
- **Requirement 6.5**: The system shall allow null sub_kategori_sampah_id for backward compatibility without validation

## Usage Example

### In a Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\KatalogSampah;
use App\Rules\KatalogKategoriConsistency;
use Illuminate\Http\Request;

class KatalogSampahController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_sampah_id' => 'required|exists:bank_sampah,id',
            'sub_kategori_sampah_id' => [
                'nullable',
                'exists:sub_kategori_sampah,id',
                new KatalogKategoriConsistency($request->kategori_sampah)
            ],
            'kategori_sampah' => 'required|in:0,1',
            'nama_item_sampah' => 'required|string|max:100',
            'harga_per_kg' => 'required|numeric|min:0',
            'deskripsi_item_sampah' => 'nullable|string',
            'gambar_item_sampah' => 'nullable|string',
            'status_aktif' => 'sometimes|boolean',
        ]);

        $katalog = KatalogSampah::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Katalog sampah berhasil dibuat',
            'data' => $katalog
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $katalog = KatalogSampah::findOrFail($id);

        $validated = $request->validate([
            'bank_sampah_id' => 'sometimes|exists:bank_sampah,id',
            'sub_kategori_sampah_id' => [
                'nullable',
                'exists:sub_kategori_sampah,id',
                new KatalogKategoriConsistency($request->kategori_sampah ?? $katalog->kategori_sampah)
            ],
            'kategori_sampah' => 'sometimes|in:0,1',
            'nama_item_sampah' => 'sometimes|string|max:100',
            'harga_per_kg' => 'sometimes|numeric|min:0',
            'deskripsi_item_sampah' => 'nullable|string',
            'gambar_item_sampah' => 'nullable|string',
            'status_aktif' => 'sometimes|boolean',
        ]);

        $katalog->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Katalog sampah berhasil diperbarui',
            'data' => $katalog
        ]);
    }
}
```

### In a Form Request

```php
<?php

namespace App\Http\Requests;

use App\Rules\KatalogKategoriConsistency;
use Illuminate\Foundation\Http\FormRequest;

class StoreKatalogSampahRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'bank_sampah_id' => 'required|exists:bank_sampah,id',
            'sub_kategori_sampah_id' => [
                'nullable',
                'exists:sub_kategori_sampah,id',
                new KatalogKategoriConsistency($this->kategori_sampah)
            ],
            'kategori_sampah' => 'required|in:0,1',
            'nama_item_sampah' => 'required|string|max:100',
            'harga_per_kg' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'kategori_sampah.required' => 'Kategori sampah wajib diisi',
            'kategori_sampah.in' => 'Kategori sampah harus 0 (kering) atau 1 (basah)',
            'sub_kategori_sampah_id.exists' => 'Sub kategori sampah tidak ditemukan',
        ];
    }
}
```

## Validation Behavior

### Case 1: Valid - Matching Categories
```php
// Sub-kategori with kategori_sampah = 0 (kering)
$subKategori = SubKategoriSampah::find(1); // kategori_sampah = 0

// Katalog with kategori_sampah = 0 (kering)
$request = [
    'kategori_sampah' => 0,
    'sub_kategori_sampah_id' => 1,
];

// ✅ Validation passes - both are kering
```

### Case 2: Invalid - Mismatched Categories
```php
// Sub-kategori with kategori_sampah = 1 (basah)
$subKategori = SubKategoriSampah::find(2); // kategori_sampah = 1

// Katalog with kategori_sampah = 0 (kering)
$request = [
    'kategori_sampah' => 0,
    'sub_kategori_sampah_id' => 2,
];

// ❌ Validation fails with error:
// "Sub kategori sampah harus sesuai dengan kategori sampah (kering)."
```

### Case 3: Valid - Null Sub-Kategori (Backward Compatibility)
```php
// Katalog without sub-kategori
$request = [
    'kategori_sampah' => 0,
    'sub_kategori_sampah_id' => null,
];

// ✅ Validation passes - null is allowed for backward compatibility
```

### Case 4: Invalid - Non-existent Sub-Kategori
```php
// Non-existent sub-kategori ID
$request = [
    'kategori_sampah' => 0,
    'sub_kategori_sampah_id' => 99999,
];

// ❌ Validation fails - sub-kategori not found
```

## Error Response Example

When validation fails, the API returns a 422 status code with error details:

```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "sub_kategori_sampah_id": [
            "Sub kategori sampah harus sesuai dengan kategori sampah (kering)."
        ]
    }
}
```

## Testing

The validation rule is tested in `tests/Unit/KatalogKategoriConsistencyTest.php` with the following test cases:

1. ✅ Validation passes when kategori_sampah matches
2. ❌ Validation fails when kategori_sampah does not match
3. ✅ Validation passes when sub_kategori_sampah_id is null
4. ❌ Validation fails when sub_kategori_sampah_id does not exist
5. ✅ Error message is clear for kering kategori
6. ✅ Error message is clear for basah kategori
7. ✅ Validation works correctly for basah kategori

## Implementation Details

### Rule Class: `app/Rules/KatalogKategoriConsistency.php`

```php
<?php

namespace App\Rules;

use App\Models\SubKategoriSampah;
use Illuminate\Contracts\Validation\Rule;

class KatalogKategoriConsistency implements Rule
{
    protected $kategoriSampah;

    public function __construct($kategoriSampah)
    {
        $this->kategoriSampah = $kategoriSampah;
    }

    public function passes($attribute, $value)
    {
        // Allow null sub_kategori_sampah_id for backward compatibility
        if (is_null($value)) {
            return true;
        }

        $subKategori = SubKategoriSampah::find($value);

        if (!$subKategori) {
            return false;
        }

        // Verify kategori_sampah matches
        return $subKategori->kategori_sampah == $this->kategoriSampah;
    }

    public function message()
    {
        $kategoriText = $this->kategoriSampah == 0 ? 'kering' : 'basah';
        return "Sub kategori sampah harus sesuai dengan kategori sampah ({$kategoriText}).";
    }
}
```

## Notes

- The rule accepts the `kategori_sampah` value (0 or 1) in the constructor
- The rule allows `null` values for backward compatibility
- The error message dynamically includes the kategori type (kering/basah)
- The rule validates that the sub-kategori exists before checking consistency
