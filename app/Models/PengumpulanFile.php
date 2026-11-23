<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengumpulanFile extends Model
{
    use HasFactory;

    protected $table = 'pengumpulan_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pengumpulan_tugas_id',
        'nama_file_asli',
        'file_path',
        'ukuran_kb',
        'ekstensi',
    ];

    /**
     * Relasi ke pengumpulan tugas induk.
     */
    public function pengumpulanTugas(): BelongsTo
    {
        return $this->belongsTo(PengumpulanTugas::class, 'pengumpulan_tugas_id');
    }

    /**
     * Format ukuran berkas yang mudah dibaca (KB / MB).
     */
    public function getUkuranFormattedAttribute(): string
    {
        if ($this->ukuran_kb >= 1024) {
            return round($this->ukuran_kb / 1024, 2) . ' MB';
        }

        return $this->ukuran_kb . ' KB';
    }
}
