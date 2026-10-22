<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('jenis', 10);
            $table->timestamp('diminta_pada');
            $table->string('status', 20)->default('menunggu');
            $table->foreignId('ditinjau_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditinjau_pada')->nullable();
            $table->string('catatan_penolakan')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'tanggal', 'jenis']);
            $table->index(['status', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_pengajuan');
    }
};
