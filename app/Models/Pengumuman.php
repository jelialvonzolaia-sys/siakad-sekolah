<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumumans';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'judul',
        'isi',
        'gambar_path',
        'target_role',
        'is_published',
        'penulis_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    /**
     * Relasi ke penulis pengumuman (User).
     */
    public function penulis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penulis_id');
    }
}
