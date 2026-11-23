<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'wali_kelas_id',
    ];

    /**
     * Relasi ke wali kelas (User).
     */
    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    /**
     * Relasi ke daftar siswa dalam kelas ini.
     */
    public function siswas(): HasMany
    {
        return $this->hasMany(User::class, 'kelas_id');
    }

    /**
     * Relasi ke jadwal pelajaran kelas ini.
     */
    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'kelas_id');
    }

    /**
     * Relasi ke jadwal ujian kelas ini.
     */
    public function jadwalUjians(): HasMany
    {
        return $this->hasMany(JadwalUjian::class, 'kelas_id');
    }

    /**
     * Relasi ke materi pelajaran kelas ini.
     */
    public function materis(): HasMany
    {
        return $this->hasMany(Materi::class, 'kelas_id');
    }
}
