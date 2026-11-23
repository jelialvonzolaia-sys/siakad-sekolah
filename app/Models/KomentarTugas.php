<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KomentarTugas extends Model
{
    use HasFactory;

    protected $table = 'komentar_tugas';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pengumpulan_tugas_id',
        'user_id',
        'komentar',
    ];

    /**
     * Atribut tambahan yang diserialkan ke JSON.
     *
     * @var list<string>
     */
    protected $appends = ['formatted_time'];

    /**
     * Format waktu komentar dalam zona waktu WIB (Asia/Jakarta).
     */
    public function getFormattedTimeAttribute(): string
    {
        if (!$this->created_at) {
            return '';
        }

        return $this->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB';
    }

    /**
     * Relasi ke pengumpulan tugas induk.
     */
    public function pengumpulanTugas(): BelongsTo
    {
        return $this->belongsTo(PengumpulanTugas::class, 'pengumpulan_tugas_id');
    }

    /**
     * Relasi ke penulis komentar (User: Guru atau Siswa).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
