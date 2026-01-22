-- Fix Data Integrity Issues
-- Issue 1: Set orphaned sub_kategori_sampah_id to NULL
UPDATE katalog_sampah 
SET sub_kategori_sampah_id = NULL 
WHERE sub_kategori_sampah_id IS NOT NULL 
AND sub_kategori_sampah_id NOT IN (SELECT id FROM sub_kategori_sampah);

-- Issue 2: Set category mismatch records to NULL
UPDATE katalog_sampah k
INNER JOIN sub_kategori_sampah sk ON k.sub_kategori_sampah_id = sk.id
SET k.sub_kategori_sampah_id = NULL
WHERE k.kategori_sampah != sk.kategori_sampah;

-- Verify fixes
SELECT 'Orphaned records' as issue_type, COUNT(*) as count
FROM katalog_sampah k
LEFT JOIN sub_kategori_sampah sk ON k.sub_kategori_sampah_id = sk.id
WHERE k.sub_kategori_sampah_id IS NOT NULL AND sk.id IS NULL

UNION ALL

SELECT 'Category mismatches' as issue_type, COUNT(*) as count
FROM katalog_sampah k
INNER JOIN sub_kategori_sampah sk ON k.sub_kategori_sampah_id = sk.id
WHERE k.kategori_sampah != sk.kategori_sampah;
