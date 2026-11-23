<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notifikasi extends Model
{
    use HasFactory;

    protected $table = 'notifikasis';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'judul',
        'pesan',
        'kategori',
        'tautan',
        'is_read',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    /**
     * Relasi ke pengguna penerima notifikasi.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Kirim notifikasi ke banyak pengguna sekaligus dalam satu insert massal.
     *
     * @param  iterable<int, int>  $userIds
     */
    public static function kirimUntukBanyak(
        iterable $userIds,
        string $judul,
        string $pesan,
        string $kategori = 'sistem',
        ?string $tautan = null
    ): void {
        $now = Carbon::now('Asia/Jakarta');
        $rows = [];

        foreach ($userIds as $userId) {
            if (!$userId) {
                continue;
            }
            $rows[] = [
                'user_id' => (int) $userId,
                'judul' => $judul,
                'pesan' => $pesan,
                'kategori' => $kategori,
                'tautan' => $tautan,
                'is_read' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            static::insert($rows);
        }
    }
}
