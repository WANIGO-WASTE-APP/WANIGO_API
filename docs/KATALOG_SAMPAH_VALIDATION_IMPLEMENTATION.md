# KatalogSampah Validation Implementation Guide

## Overview

This document explains the implementation of validation rules for KatalogSampah (Waste Catalog) creation and updates, specifically focusing on the `KatalogKategoriConsistency` validation rule and `kategori_sampah` validation.

## Requirements Addressed

- **Requirement 1.4**: When creating new sub-categories, the system shall validate kategori_sampah is either 0 or 1
- **Requirement 5.5**: When a katalog item is created, the system shall validate that katalog_sampah.kategori_sampah matches sub_kategori_sampah.kategori_sampah
- **Requirement 6.1**: When creating a katalog_sampah record, the system shall verify kategori_sampah matches the linked sub_kategori_sampah.kategori_sampah
- **Requirement 6.2**: When updating a katalog_sampah record, the system shall re-validate category consistency

## Implementation Components

### 1. Validation Rule: `KatalogKategoriConsistency`

**Location**: `app/Rules/KatalogKategoriConsistency.php`

This custom validation rule ensures that a katalog item's `kategori_sampah` matches the `kategori_sampah` of its linked `sub_kategori_sampah`.

**Key Features**:
- Accepts `kategori_sampah` value (0 or 1) in constructor
- Allows `null` values for backward compatibility
- Validates that the sub-kategori exists
- Verifies kategori_sampah consistency
- Provides clear error messages in Indonesian

**Usage**:
```php
use App\Rules\KatalogKategoriConsistency;

$rules = [
    'sub_kategori_sampah_id' => [
        'nullable',
        'exists:sub_kategori_sampah,id',
        new KatalogKategoriConsistency($request->kategori_sampah)
    ],
    'kategori_sampah' => 'required|in:0,1',
];
```

### 2. Form Request Classes

#### StoreKatalogSampahRequest

**Location**: `app/Http/Requests/StoreKatalogSampahRequest.php`

Form Request for creating new KatalogSampah items.

**Validation Rules**:
- `bank_sampah_id`: Required, must exist in bank_sampah table
- `sub_kategori_sampah_id`: Nullable, must exist, must match kategori_sampah
- `kategori_sampah`: Required, must be 0 or 1
- `nama_item_sampah`: Required, max 100 characters
- `harga_per_kg`: Required, numeric, minimum 0
- Other fields: Optional

**Custom Messages**: Provides Indonesian error messages for better UX

#### UpdateKatalogSampahRequest

**Location**: `app/Http/Requests/UpdateKatalogSampahRequest.php`

Form Request for updating existing KatalogSampah items.

**Key Features**:
- Uses existing `kategori_sampah` if not provided in request
- Re-validates category consistency on update (Requirement 6.2)
- All fields are optional (uses 'sometimes' rule)

**Usage**:
```php
public function update(UpdateKatalogSampahRequest $request, $id)
{
    $katalog = KatalogSampah::findOrFail($id);
    $katalog->update($request->validated());
    return response()->json(['success' => true, 'data' => $katalog]);
}
```

### 3. Controller Implementations

Two controller implementations are provided as examples:

#### Option A: Inline Validation

**Location**: `app/Http/Controllers/API/Admin/KatalogSampahAdminController.php`

Uses `Validator::make()` for inline validation in controller methods.

**Pros**:
- All validation logic visible in controller
- Easy to customize per endpoint
- Good for simple cases

**Cons**:
- More verbose
- Validation logic mixed with business logic

**Example**:
```php
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'bank_sampah_id' => 'required|exists:bank_sampah,id',
        'sub_kategori_sampah_id' => [
            'nullable',
            'exists:sub_kategori_sampah,id',
            new KatalogKategoriConsistency($request->kategori_sampah)
        ],
        'kategori_sampah' => 'required|in:0,1',
        'nama_item_sampah' => 'required|string|max:100',
        'harga_per_kg' => 'required|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $validator->errors()
        ], 422);
    }

    $katalog = KatalogSampah::create($validator->validated());
    return response()->json(['success' => true, 'data' => $katalog], 201);
}
```

#### Option B: Form Request Classes (Recommended)

**Location**: `app/Http/Controllers/API/Admin/KatalogSampahAdminControllerWithFormRequests.php`

Uses dedicated Form Request classes for validation.

**Pros**:
- Clean, readable controller methods
- Reusable validation logic
- Follows Laravel best practices
- Easier to test
- Better separation of concerns

**Cons**:
- Requires additional files

**Example**:
```php
public function store(StoreKatalogSampahRequest $request)
{
    $katalog = KatalogSampah::create($request->validated());
    return response()->json(['success' => true, 'data' => $katalog], 201);
}

public function update(UpdateKatalogSampahRequest $request, $id)
{
    $katalog = KatalogSampah::findOrFail($id);
    $katalog->update($request->validated());
    return response()->json(['success' => true, 'data' => $katalog]);
}
```

## Validation Scenarios

### Scenario 1: Valid - Matching Categories

```php
// Sub-kategori with kategori_sampah = 0 (kering)
$subKategori = SubKategoriSampah::find(1); // kategori_sampah = 0

// Katalog with kategori_sampah = 0 (kering)
$request = [
    'bank_sampah_id' => 1,
    'kategori_sampah' => 0,
    'sub_kategori_sampah_id' => 1,
    'nama_item_sampah' => 'Botol Plastik',
    'harga_per_kg' => 3000,
];

// ✅ Validation passes - both are kering
```

### Scenario 2: Invalid - Mismatched Categories

```php
// Sub-kategori with kategori_sampah = 1 (basah)
$subKategori = SubKategoriSampah::find(2); // kategori_sampah = 1

// Katalog with kategori_sampah = 0 (kering)
$request = [
    'bank_sampah_id' => 1,
    'kategori_sampah' => 0,
    'sub_kategori_sampah_id' => 2,
    'nama_item_sampah' => 'Sisa Makanan',
    'harga_per_kg' => 500,
];

// ❌ Validation fails with error:
// "Sub kategori sampah harus sesuai dengan kategori sampah (kering)."
```

### Scenario 3: Valid - Null Sub-Kategori (Backward Compatibility)

```php
// Katalog without sub-kategori
$request = [
    'bank_sampah_id' => 1,
    'kategori_sampah' => 0,
    'sub_kategori_sampah_id' => null,
    'nama_item_sampah' => 'Item Lama',
    'harga_per_kg' => 1000,
];

// ✅ Validation passes - null is allowed for backward compatibility
```

### Scenario 4: Invalid - Invalid kategori_sampah Value

```php
$request = [
    'bank_sampah_id' => 1,
    'kategori_sampah' => 2, // Invalid! Must be 0 or 1
    'sub_kategori_sampah_id' => 1,
    'nama_item_sampah' => 'Test Item',
    'harga_per_kg' => 1000,
];

// ❌ Validation fails with error:
// "Kategori sampah harus 0 (kering) atau 1 (basah)"
```

### Scenario 5: Update with Category Change

```php
// Existing katalog with kategori_sampah = 0
$katalog = KatalogSampah::find(1); // kategori_sampah = 0, sub_kategori_sampah_id = 1

// Update request changing kategori_sampah
$request = [
    'kategori_sampah' => 1, // Changing to basah
    // sub_kategori_sampah_id not changed (still 1, which is kering)
];

// ❌ Validation fails - sub_kategori (kering) doesn't match new kategori (basah)
// Must also update sub_kategori_sampah_id to a basah sub-kategori
```

## Error Response Format

When validation fails, the API returns a 422 status code with error details:

```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "sub_kategori_sampah_id": [
            "Sub kategori sampah harus sesuai dengan kategori sampah (kering)."
        ],
        "kategori_sampah": [
            "Kategori sampah harus 0 (kering) atau 1 (basah)"
        ]
    }
}
```

## Integration with Existing Code

### Updating Existing Controllers

If you have existing controllers that create or update KatalogSampah items, add the validation:

```php
// Before (no validation)
public function store(Request $request)
{
    $katalog = KatalogSampah::create($request->all());
    return response()->json(['data' => $katalog]);
}

// After (with validation)
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
    ]);

    $katalog = KatalogSampah::create($validated);
    return response()->json(['data' => $katalog]);
}
```

### Updating Seeders

Seeders should also validate data to ensure consistency:

```php
// In KatalogSampahSeeder.php
foreach ($katalogData as $data) {
    // Validate before creating
    $validator = Validator::make($data, [
        'kategori_sampah' => 'required|in:0,1',
        'sub_kategori_sampah_id' => [
            'nullable',
            'exists:sub_kategori_sampah,id',
            new KatalogKategoriConsistency($data['kategori_sampah'])
        ],
    ]);

    if ($validator->fails()) {
        $this->command->error("Invalid data: " . json_encode($validator->errors()));
        continue;
    }

    KatalogSampah::create($data);
}
```

## Testing

### Unit Tests

Test the validation rule directly:

```php
public function test_validation_passes_when_categories_match()
{
    $subKategori = SubKategoriSampah::factory()->create(['kategori_sampah' => 0]);
    
    $rule = new KatalogKategoriConsistency(0);
    $this->assertTrue($rule->passes('sub_kategori_sampah_id', $subKategori->id));
}

public function test_validation_fails_when_categories_mismatch()
{
    $subKategori = SubKategoriSampah::factory()->create(['kategori_sampah' => 1]);
    
    $rule = new KatalogKategoriConsistency(0);
    $this->assertFalse($rule->passes('sub_kategori_sampah_id', $subKategori->id));
}
```

### Integration Tests

Test the full request/response cycle:

```php
public function test_store_fails_with_mismatched_categories()
{
    $subKategori = SubKategoriSampah::factory()->create(['kategori_sampah' => 1]);
    
    $response = $this->postJson('/api/admin/katalog-sampah', [
        'bank_sampah_id' => 1,
        'kategori_sampah' => 0,
        'sub_kategori_sampah_id' => $subKategori->id,
        'nama_item_sampah' => 'Test Item',
        'harga_per_kg' => 1000,
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['sub_kategori_sampah_id']);
}
```

## Best Practices

1. **Always validate kategori_sampah**: Use `'kategori_sampah' => 'required|in:0,1'` for creation and `'kategori_sampah' => 'sometimes|in:0,1'` for updates

2. **Use Form Requests for complex validation**: They provide better organization and reusability

3. **Provide clear error messages**: Use Indonesian messages for better user experience

4. **Allow null for backward compatibility**: The `sub_kategori_sampah_id` field should be nullable to support legacy data

5. **Re-validate on updates**: Always apply the KatalogKategoriConsistency rule on updates, not just creation

6. **Test edge cases**: Test null values, non-existent IDs, and category mismatches

7. **Document validation rules**: Keep this documentation updated as validation rules evolve

## Migration Considerations

When migrating existing data:

1. **Verify existing data**: Check for any kategori_sampah mismatches before adding validation
2. **Fix inconsistencies**: Update any katalog items with mismatched categories
3. **Add validation gradually**: Start with warnings, then enforce strict validation
4. **Provide migration scripts**: Create scripts to fix data issues automatically

## Conclusion

The KatalogKategoriConsistency validation rule ensures data integrity by preventing katalog items from being assigned to sub-categories of a different waste type. This implementation satisfies requirements 1.4, 5.5, 6.1, and 6.2, providing robust validation for both creation and update operations.

For questions or issues, refer to:
- `docs/VALIDATION_RULE_USAGE_EXAMPLE.md` - Basic usage examples
- `tests/Unit/KatalogKategoriConsistencyTest.php` - Unit tests
- `.kiro/specs/sub-kategori-sampah-refactoring/design.md` - Design specifications
