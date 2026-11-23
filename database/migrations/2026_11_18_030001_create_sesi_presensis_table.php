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
        Schema::create('sesi_presensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('jadwals')->cascadeOnDelete();
            $table->integer('pertemuan_ke');
            $table->string('materi');
            $table->enum('metode', ['mandiri', 'qr'])->default('mandiri');
            $table->string('qr_token')->nullable();
            $table->timestamp('qr_expires_at')->nullable();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->enum('status_sesi', ['dibuka', 'ditutup'])->default('dibuka');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_presensis');
    }
};
