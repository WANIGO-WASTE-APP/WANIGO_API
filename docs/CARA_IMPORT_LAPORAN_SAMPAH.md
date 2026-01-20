# Cara Menambahkan Folder Laporan Sampah (TF 4) ke Postman Collection

## Opsi 1: Import File Terpisah (Paling Mudah)

1. Buka Postman
2. Klik tombol **Import** di pojok kiri atas
3. Pilih file `Laporan_Sampah_TF4.postman_collection.json`
4. Klik **Import**
5. Folder "Laporan Sampah (TF 4)" akan muncul sebagai collection terpisah
6. Anda bisa drag & drop folder ini ke dalam collection "WANIGO_API (Full Access)" di bagian "Nasabah"

## Opsi 2: Tambahkan Manual di Postman

1. Buka collection "WANIGO_API (Full Access)" di Postman
2. Klik kanan pada folder "Nasabah"
3. Pilih **Add Folder**
4. Beri nama: **Laporan Sampah (TF 4)**
5. Tambahkan request satu per satu dengan klik kanan pada folder baru → **Add Request**

### Daftar Endpoint yang Perlu Ditambahkan:

#### 1. Get Bank Sampah List (dengan Saldo)
- **Method**: GET
- **URL**: `{{base_url}}/api/nasabah/laporan-sampah/bank-sampah-list`
- **Auth**: Bearer Token `{{token}}`
- **Deskripsi**: Mendapatkan daftar bank sampah beserta saldo tabungan nasabah

#### 2. Check Nasabah Status
- **Method**: GET
- **URL**: `{{base_url}}/api/nasabah/laporan-sampah/check-nasabah-status`
- **Auth**: Bearer Token `{{token}}`
- **Deskripsi**: Mengecek apakah user terdaftar sebagai nasabah

#### 3. Get Laporan Sampah Summary
- **Method**: POST
- **URL**: `{{base_url}}/api/nasabah/laporan-sampah/summary`
- **Auth**: Bearer Token `{{token}}`
- **Body** (JSON):
```json
{
    "bank_sampah_id": 1
}
```
- **Deskripsi**: Mendapatkan total berat sampah terpilahkan dan total penjualan

#### 4. Get Tonase Sampah Per Kategori
- **Method**: POST
- **URL**: `{{base_url}}/api/nasabah/laporan-sampah/tonase-per-kategori`
- **Auth**: Bearer Token `{{token}}`
- **Body** (JSON):
```json
{
    "bank_sampah_id": 1,
    "jenis_sampah": "kering"
}
```

#### 5. Get Tren Tonase Sampah
- **Method**: POST
- **URL**: `{{base_url}}/api/nasabah/laporan-sampah/tren-tonase`
- **Auth**: Bearer Token `{{token}}`
- **Body** (JSON):
```json
{
    "bank_sampah_id": 1,
    "jenis_sampah": "kering",
    "periode": "mingguan"
}
```

#### 6. Get Riwayat Tonase Sampah
- **Method**: POST
- **URL**: `{{base_url}}/api/nasabah/laporan-sampah/riwayat-tonase`
- **Auth**: Bearer Token `{{token}}`
- **Body** (JSON):
```json
{
    "bank_sampah_id": 1,
    "per_page": 10
}
```

#### 7. Get Penjualan Sampah Per Kategori
- **Method**: POST
- **URL**: `{{base_url}}/api/nasabah/laporan-sampah/penjualan-per-kategori`
- **Auth**: Bearer Token `{{token}}`
- **Body** (JSON):
```json
{
    "bank_sampah_id": 1,
    "jenis_sampah": "kering"
}
```

#### 8. Get Tren Penjualan Sampah
- **Method**: POST
- **URL**: `{{base_url}}/api/nasabah/laporan-sampah/tren-penjualan`
- **Auth**: Bearer Token `{{token}}`
- **Body** (JSON):
```json
{
    "bank_sampah_id": 1,
    "jenis_sampah": "kering",
    "periode": "mingguan"
}
```

#### 9. Get Riwayat Penjualan Sampah
- **Method**: POST
- **URL**: `{{base_url}}/api/nasabah/laporan-sampah/riwayat-penjualan`
- **Auth**: Bearer Token `{{token}}`
- **Body** (JSON):
```json
{
    "bank_sampah_id": 1,
    "per_page": 10
}
```

#### 10. Get Dashboard (All Data)
- **Method**: POST
- **URL**: `{{base_url}}/api/nasabah/laporan-sampah/dashboard`
- **Auth**: Bearer Token `{{token}}`
- **Body** (JSON):
```json
{
    "bank_sampah_id": 1
}
```

## Endpoint Penting untuk Saldo & Total Sampah

### Untuk Mendapatkan SALDO TABUNGAN:
Gunakan endpoint **#1: Get Bank Sampah List**
- Response akan mengembalikan field `saldo` untuk setiap bank sampah

### Untuk Mendapatkan TOTAL SAMPAH TERPILAHKAN:
Gunakan endpoint **#3: Get Laporan Sampah Summary**
- Response akan mengembalikan `data.tonase.total_berat` (total berat sampah dalam kg)
- Response juga mengembalikan `data.penjualan.total_saldo` (total nilai penjualan)

## Catatan
- Semua endpoint memerlukan autentikasi dengan Bearer Token
- Pastikan environment variable `{{base_url}}` dan `{{token}}` sudah diset di Postman
- Endpoint ini hanya bisa diakses oleh user dengan role "nasabah"
