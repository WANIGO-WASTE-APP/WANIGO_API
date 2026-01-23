<?php
/**
 * Script untuk test endpoint katalog API
 * 
 * Cara pakai:
 * 1. Pastikan Laravel server sudah running (php artisan serve)
 * 2. Jalankan: php test-endpoint.php
 */

$baseUrl = 'http://localhost:8000';

echo "=== TEST ENDPOINT KATALOG API ===\n\n";

// Test 1: Debug endpoint
echo "Test 1: Debug Endpoint\n";
echo "URL: {$baseUrl}/test-katalog\n";
$response1 = @file_get_contents($baseUrl . '/test-katalog');
if ($response1 === false) {
    echo "❌ GAGAL: Server tidak berjalan atau endpoint tidak ditemukan\n";
    echo "   Pastikan server Laravel sudah running: php artisan serve\n\n";
} else {
    $data1 = json_decode($response1, true);
    echo "✅ BERHASIL\n";
    echo "   Jumlah katalog: " . ($data1['katalog_count'] ?? 0) . "\n";
    echo "   Katalog ditemukan: " . ($data1['katalog_found'] ? 'Ya' : 'Tidak') . "\n";
    echo "   Sub-kategori loaded: " . ($data1['sub_kategori_loaded'] ? 'Ya' : 'Tidak') . "\n\n";
    
    if (isset($data1['error'])) {
        echo "   ⚠️  Error: " . $data1['error'] . "\n\n";
    }
}

// Test 2: Endpoint asli tanpa auth (akan error 401, tapi kita cek apakah route ada)
echo "Test 2: Endpoint Asli (tanpa auth)\n";
echo "URL: {$baseUrl}/api/bank-sampah/1/katalog?kategori=kering&per_page=20&page=1\n";
$response2 = @file_get_contents($baseUrl . '/api/bank-sampah/1/katalog?kategori=kering&per_page=20&page=1');
if ($response2 === false) {
    // Cek apakah error 401 (unauthorized) atau error lain
    $headers = $http_response_header ?? [];
    $statusLine = $headers[0] ?? '';
    
    if (strpos($statusLine, '401') !== false) {
        echo "✅ Route ditemukan (401 Unauthorized - perlu token auth)\n";
        echo "   Gunakan Postman dengan token Bearer untuk test lengkap\n\n";
    } else {
        echo "❌ GAGAL: " . $statusLine . "\n\n";
    }
} else {
    $data2 = json_decode($response2, true);
    if (isset($data2['success']) && $data2['success']) {
        echo "✅ BERHASIL\n";
        echo "   Total data: " . ($data2['meta']['total'] ?? 0) . "\n\n";
    } else {
        echo "⚠️  Response: " . json_encode($data2, JSON_PRETTY_PRINT) . "\n\n";
    }
}

echo "=== SELESAI ===\n";
echo "\nCatatan:\n";
echo "- Jika Test 1 gagal: Jalankan 'php artisan serve' terlebih dahulu\n";
echo "- Jika Test 2 error 401: Normal, endpoint memerlukan authentication\n";
echo "- Untuk test lengkap: Gunakan Postman dengan Bearer token\n";
