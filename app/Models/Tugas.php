<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tugas extends Model
{
    use HasFactory;

    protected $table = 'tugas';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kelas_id',
        'mapel_id',
        'guru_id',
        'judul',
        'deskripsi_html',
        'lampiran_guru',
        'waktu_buka',
        'batas_waktu',
        'tipe_file_diizinkan',
        'max_ukuran_mb',
        'max_jumlah_file',
        'izinkan_terlambat',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu_buka' => 'datetime',
            'batas_waktu' => 'datetime',
            'tipe_file_diizinkan' => 'array',
            'izinkan_terlambat' => 'boolean',
            'max_ukuran_mb' => 'integer',
            'max_jumlah_file' => 'integer',
        ];
    }

    /**
     * Relasi ke rombel kelas.
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
     * Relasi ke guru pembuat tugas (User).
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    /**
     * Relasi ke seluruh pengumpulan tugas oleh siswa.
     */
    public function pengumpulanTugas(): HasMany
    {
        return $this->hasMany(PengumpulanTugas::class, 'tugas_id');
    }

    /**
     * Cek apakah tugas sudah mulai dibuka.
     */
    public function isBuka(): bool
    {
        return Carbon::now()->greaterThanOrEqualTo($this->waktu_buka);
    }

    /**
     * Cek apakah tugas telah melewati jatuh tempo (batas waktu).
     */
    public function isLewatBatas(): bool
    {
        return Carbon::now()->greaterThan($this->batas_waktu);
    }

    /**
     * Cek apakah siswa masih diizinkan mengumpulkan tugas ini.
     */
    public function bisaKumpul(): bool
    {
        if (!$this->isBuka()) {
            return false;
        }

        if ($this->isLewatBatas() && !$this->izinkan_terlambat) {
            return false;
        }

        return true;
    }
}
