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
        Schema::create('pengumpulan_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status_pengajuan', ['Draft', 'Terkirim'])->default('Terkirim');
            $table->enum('status_penilaian', ['Belum dinilai', 'Sudah dinilai'])->default('Belum dinilai');
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('feedback_guru')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['tugas_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumpulan_tugas');
    }
};
