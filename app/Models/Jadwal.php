<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwals';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kelas_id',
        'mapel_id',
        'guru_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
    ];

    /**
     * Relasi ke kelas.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Relasi ke mata pelajaran.
     */
    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }

    /**
     * Relasi ke guru pengajar (User).
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    /**
     * Relasi ke presensi jadwal ini.
     */
    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'jadwal_id');
    }

    /**
     * Relasi ke daftar sesi presensi pertemuan.
     */
    public function sesiPresensis(): HasMany
    {
        return $this->hasMany(SesiPresensi::class, 'jadwal_id');
    }
}
