# Phase 4 Migration: Add Foreign Key Constraint to Katalog Sampah

## Overview

This migration adds a foreign key constraint from `katalog_sampah.sub_kategori_sampah_id` to `sub_kategori_sampah.id` to enforce referential integrity between the two tables.

## Migration File

`2026_01_22_090000_add_foreign_key_to_katalog_sampah_phase4.php`

## Requirements

This migration validates:
- **Requirement 5.1**: Add foreign key constraint on katalog_sampah.sub_kategori_sampah_id
- **Requirement 5.4**: Ensure all existing katalog_sampah records have valid sub_kategori_sampah_id
- **Requirement 10.6**: Add FK constraint after validating all references

## Prerequisites

Before running this migration, ensure:
1. Phase 1 migration has been run (adds new columns to sub_kategori_sampah)
2. Phase 2 migration has been run (backfills data)
3. Phase 3 migration has been run (adds constraints and indexes)
4. All `katalog_sampah` records with non-null `sub_kategori_sampah_id` reference valid `sub_kategori_sampah.id` values

## What This Migration Does

### Up Migration

1. **Validates References**: Checks for orphaned `katalog_sampah` records
   - Finds records where `sub_kategori_sampah_id` is not null but doesn't exist in `sub_kategori_sampah` table
   - If orphaned records are found, throws a detailed exception with:
     - Count of orphaned records
     - List of orphaned records (up to 10 shown)
     - IDs of all orphaned records
     - SQL commands to fix the issue

2. **Adds Foreign Key Constraint**: If validation passes
   - Creates FK constraint: `katalog_sampah.sub_kategori_sampah_id` → `sub_kategori_sampah.id`
   - Uses `ON DELETE RESTRICT` to prevent deletion of sub-categories that have katalog items
   - Allows null values (for backward compatibility)

### Down Migration

- Drops the foreign key constraint
- Allows the system to return to the state before Phase 4

## Running the Migration

### Standard Migration

```bash
php artisan migrate
```

### Run Specific Migration

```bash
php artisan migrate --path=database/migrations/2026_01_22_090000_add_foreign_key_to_katalog_sampah_phase4.php
```

### Rollback

```bash
php artisan migrate:rollback --path=database/migrations/2026_01_22_090000_add_foreign_key_to_katalog_sampah_phase4.php
```

## Handling Orphaned Records

If the migration fails with an orphaned records error, you have three options:

### Option 1: Set to NULL (Recommended for backward compatibility)

```sql
-- Set orphaned references to NULL
UPDATE katalog_sampah 
SET sub_kategori_sampah_id = NULL 
WHERE id IN (1, 2, 3, ...); -- Replace with actual orphaned IDs
```

### Option 2: Update to Valid References

```sql
-- Update to a valid sub_kategori_sampah_id
UPDATE katalog_sampah 
SET sub_kategori_sampah_id = <valid_id> 
WHERE id IN (1, 2, 3, ...); -- Replace with actual orphaned IDs
```

### Option 3: Delete Orphaned Records

```sql
-- Delete orphaned katalog records (use with caution!)
DELETE FROM katalog_sampah 
WHERE id IN (1, 2, 3, ...); -- Replace with actual orphaned IDs
```

## Expected Behavior After Migration

### Foreign Key Enforcement

1. **Prevents Invalid References**
   ```php
   // This will fail if sub_kategori_sampah_id doesn't exist
   KatalogSampah::create([
       'sub_kategori_sampah_id' => 99999, // Non-existent ID
       // ... other fields
   ]);
   // Throws: Integrity constraint violation
   ```

2. **Prevents Deletion of Referenced Sub-Categories**
   ```php
   // This will fail if katalog items reference this sub-category
   SubKategoriSampah::find($id)->delete();
   // Throws: Cannot delete or update a parent row
   ```

3. **Allows NULL Values**
   ```php
   // This is allowed (backward compatibility)
   KatalogSampah::create([
       'sub_kategori_sampah_id' => null,
       // ... other fields
   ]);
   ```

4. **Allows Deletion When No References**
   ```php
   // This succeeds if no katalog items reference this sub-category
   SubKategoriSampah::find($id)->delete();
   ```

## Testing

A comprehensive test suite has been created: `tests/Feature/SubKategoriSampahForeignKeyMigrationTest.php`

### Test Coverage

1. ✅ Migration succeeds with valid references
2. ✅ Migration succeeds with null references
3. ✅ Migration fails with orphaned records
4. ✅ Migration provides detailed error messages
5. ✅ Foreign key prevents deletion of referenced sub-categories
6. ✅ Foreign key allows deletion when no references exist
7. ✅ Migration rollback removes foreign key
8. ✅ Migration handles mixed valid and null references

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=SubKategoriSampahForeignKeyMigrationTest

# Or using PHPUnit directly
./vendor/bin/phpunit --filter=SubKategoriSampahForeignKeyMigrationTest
```

## Troubleshooting

### Issue: Migration fails with "orphaned records" error

**Solution**: Follow the error message instructions to fix orphaned records before re-running the migration.

### Issue: Cannot delete sub-category

**Error**: `SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row`

**Solution**: This is expected behavior. Delete or update the katalog items first, then delete the sub-category.

### Issue: Cannot insert katalog with invalid sub_kategori_sampah_id

**Error**: `SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row`

**Solution**: Ensure the `sub_kategori_sampah_id` exists in the `sub_kategori_sampah` table, or set it to NULL.

## Database Schema After Migration

```sql
-- katalog_sampah table with FK constraint
CREATE TABLE katalog_sampah (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    bank_sampah_id BIGINT UNSIGNED NOT NULL,
    sub_kategori_sampah_id BIGINT UNSIGNED NULL,  -- Now has FK constraint
    kategori_sampah TINYINT(1) NOT NULL,
    nama_item_sampah VARCHAR(100) NOT NULL,
    harga_per_kg DECIMAL(10,2) NOT NULL,
    -- other fields...
    FOREIGN KEY (bank_sampah_id) REFERENCES bank_sampah(id),
    FOREIGN KEY (sub_kategori_sampah_id) REFERENCES sub_kategori_sampah(id) ON DELETE RESTRICT
);
```

## Rollback Plan

If issues occur after running this migration:

1. **Immediate Rollback**
   ```bash
   php artisan migrate:rollback --step=1
   ```

2. **Verify Rollback**
   ```sql
   -- Check that FK constraint is removed
   SHOW CREATE TABLE katalog_sampah;
   ```

3. **Fix Issues**
   - Identify and fix orphaned records
   - Verify data integrity

4. **Re-run Migration**
   ```bash
   php artisan migrate
   ```

## Production Deployment Checklist

- [ ] Backup database before migration
- [ ] Run migration in staging environment first
- [ ] Verify no orphaned records exist
- [ ] Test katalog creation/update with validation
- [ ] Test sub-category deletion prevention
- [ ] Monitor error logs after deployment
- [ ] Have rollback plan ready
- [ ] Schedule during low-traffic window

## Related Files

- Migration: `database/migrations/2026_01_22_090000_add_foreign_key_to_katalog_sampah_phase4.php`
- Tests: `tests/Feature/SubKategoriSampahForeignKeyMigrationTest.php`
- Design Doc: `.kiro/specs/sub-kategori-sampah-refactoring/design.md`
- Requirements: `.kiro/specs/sub-kategori-sampah-refactoring/requirements.md`

## Support

For issues or questions, refer to:
- Design document section: "Phase 4: Add Foreign Key to Katalog Sampah"
- Requirements: 5.1, 5.4, 10.6
- Task: 9.1 in tasks.md
