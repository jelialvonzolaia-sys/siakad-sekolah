<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SesiPresensi extends Model
{
    use HasFactory;

    protected $table = 'sesi_presensis';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'jadwal_id',
        'pertemuan_ke',
        'materi',
        'metode',
        'qr_token',
        'qr_expires_at',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'status_sesi',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'qr_expires_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke jadwal pelajaran.
     */
    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

    /**
     * Relasi ke rekaman presensi seluruh siswa di sesi ini.
     */
    public function presensis(): HasMany
    {
        return $this->hasMany(Presensi::class, 'sesi_presensi_id');
    }

    /**
     * Cek apakah sesi saat ini sedang dibuka dan masih dalam rentang tanggal serta jam berlaku (WIB).
     */
    public function isAktif(): bool
    {
        if ($this->status_sesi !== 'dibuka') {
            return false;
        }

        $now = Carbon::now('Asia/Jakarta');
        $sessionDate = Carbon::parse($this->tanggal)->format('Y-m-d');
        $start = Carbon::parse($sessionDate . ' ' . $this->jam_mulai, 'Asia/Jakarta');
        $end = Carbon::parse($sessionDate . ' ' . $this->jam_selesai, 'Asia/Jakarta');

        return $now->betweenIncluded($start, $end);
    }

    /**
     * Ubah status siswa yang masih 'Belum Absen' menjadi 'Alpha' apabila sesi sudah ditutup atau lewat jam selesai.
     */
    public function syncExpiredStatus(): void
    {
        $now = Carbon::now('Asia/Jakarta');
        $sessionDate = Carbon::parse($this->tanggal)->format('Y-m-d');
        $end = Carbon::parse($sessionDate . ' ' . $this->jam_selesai, 'Asia/Jakarta');

        if ($this->status_sesi === 'ditutup' || $now->greaterThan($end)) {
            Presensi::where('sesi_presensi_id', $this->id)
                ->where('status', 'Belum Absen')
                ->update([
                    'status' => 'Alpha',
                    'keterangan' => 'Tidak melakukan presensi hingga batas waktu sesi berakhir',
                ]);
        }
    }

    /**
     * Generate token QR baru dan perbarui waktu kedaluwarsa (30 detik).
     */
    public function generateQrToken(): string
    {
        $token = 'SMAN1GST-' . strtoupper(Str::random(8)) . '-' . time();
        $this->update([
            'qr_token' => $token,
            'qr_expires_at' => Carbon::now('Asia/Jakarta')->addSeconds(30),
        ]);

        return $token;
    }

    /**
     * Cek validitas token QR yang dikirim siswa.
     */
    public function isQrValid(?string $token): bool
    {
        if (empty($token) || empty($this->qr_token)) {
            return false;
        }

        if ($this->qr_token !== $token) {
            return false;
        }

        if ($this->qr_expires_at && Carbon::now('Asia/Jakarta')->greaterThan($this->qr_expires_at)) {
            return false;
        }

        return true;
    }
}
