<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kelurahan_desa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->constrained('kecamatan')->onDelete('cascade');
            $table->string('nama_kelurahan_desa');
            $table->string('kode_kelurahan_desa');
            $table->enum('tipe', ['kelurahan', 'desa']);
            $table->timestamps();

            // Kode kelurahan/desa harus unik per kecamatan
            $table->unique(['kecamatan_id', 'kode_kelurahan_desa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelurahan_desa');
    }
};