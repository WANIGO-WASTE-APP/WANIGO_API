-- Script untuk cek data katalog sampah di database
-- Jalankan di MySQL client atau phpMyAdmin

-- 1. Cek jumlah katalog per bank sampah
SELECT 
    bank_sampah_id,
    COUNT(*) as total_katalog,
    SUM(CASE WHEN status_aktif = 1 THEN 1 ELSE 0 END) as aktif,
    SUM(CASE WHEN status_aktif = 0 THEN 1 ELSE 0 END) as non_aktif
FROM katalog_sampah
GROUP BY bank_sampah_id
ORDER BY bank_sampah_id;

-- 2. Cek katalog untuk bank_sampah_id = 1
SELECT 
    id,
    nama_item_sampah,
    harga_per_kg,
    kategori_sampah,
    sub_kategori_sampah_id,
    status_aktif
FROM katalog_sampah
WHERE bank_sampah_id = 1
LIMIT 10;

-- 3. Cek relasi katalog dengan sub_kategori
SELECT 
    ks.id,
    ks.nama_item_sampah,
    ks.kategori_sampah,
    ks.sub_kategori_sampah_id,
    sks.nama_sub_kategori,
    sks.kode_sub_kategori,
    sks.kategori_sampah_id
FROM katalog_sampah ks
LEFT JOIN sub_kategori_sampah sks ON ks.sub_kategori_sampah_id = sks.id
WHERE ks.bank_sampah_id = 1
LIMIT 10;

-- 4. Cek katalog yang tidak punya sub_kategori (orphaned)
SELECT 
    id,
    nama_item_sampah,
    sub_kategori_sampah_id
FROM katalog_sampah
WHERE bank_sampah_id = 1
  AND sub_kategori_sampah_id IS NULL;

-- 5. Cek katalog dengan sub_kategori invalid
SELECT 
    ks.id,
    ks.nama_item_sampah,
    ks.sub_kategori_sampah_id
FROM katalog_sampah ks
LEFT JOIN sub_kategori_sampah sks ON ks.sub_kategori_sampah_id = sks.id
WHERE ks.bank_sampah_id = 1
  AND ks.sub_kategori_sampah_id IS NOT NULL
  AND sks.id IS NULL;

-- 6. Cek sub_kategori untuk bank_sampah_id = 1
SELECT 
    id,
    nama_sub_kategori,
    kode_sub_kategori,
    kategori_sampah_id,
    status_aktif,
    urutan
FROM sub_kategori_sampah
WHERE bank_sampah_id = 1
ORDER BY urutan;

-- 7. Cek distribusi katalog per kategori (kering/basah)
SELECT 
    CASE 
        WHEN kategori_sampah = 0 THEN 'Kering'
        WHEN kategori_sampah = 1 THEN 'Basah'
        ELSE 'Unknown'
    END as kategori,
    COUNT(*) as jumlah
FROM katalog_sampah
WHERE bank_sampah_id = 1
  AND status_aktif = 1
GROUP BY kategori_sampah;

-- 8. Cek distribusi katalog per sub_kategori
SELECT 
    sks.nama_sub_kategori,
    sks.kode_sub_kategori,
    COUNT(ks.id) as jumlah_item
FROM sub_kategori_sampah sks
LEFT JOIN katalog_sampah ks ON sks.id = ks.sub_kategori_sampah_id 
    AND ks.bank_sampah_id = 1 
    AND ks.status_aktif = 1
WHERE sks.bank_sampah_id = 1
  AND sks.status_aktif = 1
GROUP BY sks.id, sks.nama_sub_kategori, sks.kode_sub_kategori
ORDER BY sks.urutan;
