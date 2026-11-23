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
        Schema::table('presensis', function (Blueprint $table) {
            $table->foreignId('sesi_presensi_id')->nullable()->after('jadwal_id')->constrained('sesi_presensis')->cascadeOnDelete();
            $table->time('waktu_presensi')->nullable()->after('tanggal');
            $table->string('latitude')->nullable()->after('keterangan');
            $table->string('longitude')->nullable()->after('latitude');
            $table->text('alamat_lokasi')->nullable()->after('longitude');
            $table->string('metode_absen')->nullable()->after('alamat_lokasi');

            // Unique composite (sesi_presensi_id, siswa_id)
            $table->unique(['sesi_presensi_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropUnique(['sesi_presensi_id', 'siswa_id']);
            $table->dropForeign(['sesi_presensi_id']);
            $table->dropColumn([
                'sesi_presensi_id',
                'waktu_presensi',
                'latitude',
                'longitude',
                'alamat_lokasi',
                'metode_absen',
            ]);
        });
    }
};
