<?php

// Script to update bank sampah with kode
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$banks = \App\Models\BankSampah::all();

foreach ($banks as $bank) {
    // Generate kode from first 3 letters of name
    $kode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $bank->nama_bank_sampah), 0, 3));
    $bank->kode_bank_sampah = $kode;
    $bank->save();
    
    echo "Updated: {$bank->nama_bank_sampah} -> {$kode}\n";
}

echo "\n✅ All bank sampah updated with kode!\n";
