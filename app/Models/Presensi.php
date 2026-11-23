<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensis';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'jadwal_id',
        'sesi_presensi_id',
        'siswa_id',
        'tanggal',
        'waktu_presensi',
        'status',
        'keterangan',
        'latitude',
        'longitude',
        'alamat_lokasi',
        'metode_absen',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    /**
     * Relasi ke jadwal pelajaran.
     */
    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

    /**
     * Relasi ke sesi presensi.
     */
    public function sesiPresensi(): BelongsTo
    {
        return $this->belongsTo(SesiPresensi::class, 'sesi_presensi_id');
    }

    /**
     * Relasi ke siswa (User).
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}
