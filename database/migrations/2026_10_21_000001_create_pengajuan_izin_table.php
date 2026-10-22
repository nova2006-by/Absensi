<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_izin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->string('jenis', 10); // izin | sakit
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->text('alasan');
            $table->string('bukti')->nullable();
            $table->string('status', 20)->default('menunggu'); // menunggu | disetujui | ditolak
            $table->foreignId('ditinjau_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditinjau_at')->nullable();
            $table->string('catatan_penolakan')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_izin');
    }
};
