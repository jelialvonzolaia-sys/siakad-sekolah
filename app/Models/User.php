<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nis_nip',
        'kelas_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke kelas (jika user adalah siswa).
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Relasi ke kelas yang diwalikan (jika user adalah wali kelas).
     */
    public function kelasWali(): HasMany
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    /**
     * Relasi ke mata pelajaran yang diampu (jika user adalah guru).
     */
    public function mataPelajarans(): HasMany
    {
        return $this->hasMany(MataPelajaran::class, 'guru_id');
    }

    /**
     * Relasi ke jadwal mengajar (jika user adalah guru).
     */
    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'guru_id');
    }

    /**
     * Relasi ke presensi (jika user adalah siswa).
     */
    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'siswa_id');
    }

    /**
     * Relasi ke materi pelajaran yang diunggah (jika user adalah guru).
     */
    public function materis(): HasMany
    {
        return $this->hasMany(Materi::class, 'guru_id');
    }

    /**
     * Relasi ke pengumuman yang dibuat oleh user.
     */
    public function pengumumans(): HasMany
    {
        return $this->hasMany(Pengumuman::class, 'penulis_id');
    }

    /**
     * Relasi ke notifikasi sistem milik pengguna.
     */
    public function notifikasis(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'user_id');
    }
}
