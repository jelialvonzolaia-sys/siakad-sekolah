<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajarans';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'guru_id',
        'kkm',
    ];

    /**
     * Relasi ke guru pengampu utama mata pelajaran (User).
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    /**
     * Relasi ke jadwal pelajaran.
     */
    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'mapel_id');
    }

    /**
     * Relasi ke jadwal ujian.
     */
    public function jadwalUjians(): HasMany
    {
        return $this->hasMany(JadwalUjian::class, 'mapel_id');
    }

    /**
     * Relasi ke materi pelajaran.
     */
    public function materis(): HasMany
    {
        return $this->hasMany(Materi::class, 'mapel_id');
    }
}
