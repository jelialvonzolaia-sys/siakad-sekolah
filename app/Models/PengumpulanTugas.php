<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengumpulanTugas extends Model
{
    use HasFactory;

    protected $table = 'pengumpulan_tugas';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tugas_id',
        'siswa_id',
        'status_pengajuan',
        'status_penilaian',
        'nilai',
        'feedback_guru',
        'submitted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'nilai' => 'float',
        ];
    }

    /**
     * Relasi ke tugas utama.
     */
    public function tugas(): BelongsTo
    {
        return $this->belongsTo(Tugas::class, 'tugas_id');
    }

    /**
     * Relasi ke siswa (User).
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    /**
     * Relasi ke berkas-berkas yang diunggah siswa.
     */
    public function files(): HasMany
    {
        return $this->hasMany(PengumpulanFile::class, 'pengumpulan_tugas_id');
    }

    /**
     * Relasi ke diskusi komentar pada pengumpulan ini.
     */
    public function komentars(): HasMany
    {
        return $this->hasMany(KomentarTugas::class, 'pengumpulan_tugas_id');
    }

    /**
     * Cek apakah pengajuan dikumpulkan terlambat.
     */
    public function isTerlambat(): bool
    {
        if (!$this->submitted_at || !$this->tugas) {
            return false;
        }

        return $this->submitted_at->greaterThan($this->tugas->batas_waktu);
    }

    /**
     * Menghitung deskripsi selisih waktu pengajuan relatif terhadap batas waktu (format formal).
     */
    public function getSelisihWaktuLabelAttribute(): string
    {
        if (!$this->submitted_at || !$this->tugas) {
            return '-';
        }

        $submitted = $this->submitted_at;
        $deadline = $this->tugas->batas_waktu;

        if ($submitted->lessThanOrEqualTo($deadline)) {
            $diffDays = (int) $submitted->diffInDays($deadline);
            $diffHours = (int) $submitted->copy()->addDays($diffDays)->diffInHours($deadline);
            $diffMins = (int) $submitted->copy()->addDays($diffDays)->addHours($diffHours)->diffInMinutes($deadline);

            $parts = [];
            if ($diffDays > 0) $parts[] = $diffDays . ' hari';
            if ($diffHours > 0) $parts[] = $diffHours . ' jam';
            $parts[] = $diffMins . ' min';

            return 'Penugasan diajukan ' . implode(' ', $parts) . ' lebih awal';
        } else {
            $diffDays = (int) $deadline->diffInDays($submitted);
            $diffHours = (int) $deadline->copy()->addDays($diffDays)->diffInHours($submitted);
            $diffMins = (int) $deadline->copy()->addDays($diffDays)->addHours($diffHours)->diffInMinutes($submitted);

            $parts = [];
            if ($diffDays > 0) $parts[] = $diffDays . ' hari';
            if ($diffHours > 0) $parts[] = $diffHours . ' jam';
            $parts[] = $diffMins . ' min';

            return 'Terlambat ' . implode(' ', $parts);
        }
    }
}
