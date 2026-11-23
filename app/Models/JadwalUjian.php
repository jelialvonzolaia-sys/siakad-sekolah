<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalUjian extends Model
{
    use HasFactory;

    protected $table = 'jadwal_ujians';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kelas_id',
        'mapel_id',
        'jenis_ujian',
        'tanggal',
        'waktu_mulai',
        'ruangan',
    ];

    /**
     * Relasi ke kelas peserta ujian.
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Relasi ke mata pelajaran yang diujikan.
     */
    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }
}
