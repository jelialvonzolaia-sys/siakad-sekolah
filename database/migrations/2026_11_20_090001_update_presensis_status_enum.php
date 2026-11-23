<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE presensis MODIFY COLUMN status ENUM('Belum Absen', 'Hadir', 'Sakit', 'Izin', 'Alpha') NOT NULL DEFAULT 'Belum Absen'");
        } else {
            Schema::table('presensis', function (Blueprint $table): void {
                $table->enum('status', ['Belum Absen', 'Hadir', 'Sakit', 'Izin', 'Alpha'])
                    ->default('Belum Absen')
                    ->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE presensis SET status = 'Alpha' WHERE status = 'Belum Absen'");
            DB::statement("ALTER TABLE presensis MODIFY COLUMN status ENUM('Hadir', 'Sakit', 'Izin', 'Alpha') NOT NULL");
        } else {
            DB::table('presensis')->where('status', 'Belum Absen')->update(['status' => 'Alpha']);
            Schema::table('presensis', function (Blueprint $table): void {
                $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alpha'])->change();
            });
        }
    }
};
