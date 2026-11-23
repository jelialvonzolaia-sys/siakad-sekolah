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
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mapel_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->string('judul');
            $table->longText('deskripsi_html');
            $table->string('lampiran_guru')->nullable();
            $table->dateTime('waktu_buka');
            $table->dateTime('batas_waktu');
            $table->json('tipe_file_diizinkan');
            $table->integer('max_ukuran_mb')->default(10);
            $table->integer('max_jumlah_file')->default(3);
            $table->boolean('izinkan_terlambat')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas');
    }
};
