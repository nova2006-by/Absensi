<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('kategori_pelanggaran_id')->constrained('kategori_pelanggaran')->cascadeOnDelete();
            $table->unsignedInteger('poin');
            $table->date('tanggal');
            $table->string('keterangan')->nullable();
            $table->foreignId('ditambahkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggaran');
    }
};
