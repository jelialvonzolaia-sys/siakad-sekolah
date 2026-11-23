<!-- HEADER GELOMBANG ANIMASI EVERGREEN (LOKAL SVG - ANTI BROKEN IMAGE) -->
<div align="center">
  <img src="./docs/header.svg" width="100%" alt="Header SIAKAD SMANSA"/>
</div>

<!-- ANIMASI TEKS MENGETIK (TYPING SVG - KONTRAS DI DARK & LIGHT MODE) -->
<div align="center">
  <a href="https://github.com/jelialvonzolaia-sys/siakad-sekolah">
    <img src="https://readme-typing-svg.demolab.com?font=Plus+Jakarta+Sans&weight=600&size=18&duration=3200&pause=1000&color=76B947&center=true&vCenter=true&multiline=false&width=780&lines=Portal+Akademik+Multi-Role+(Admin+TU%2C+Guru%2C+dan+Siswa);Presensi+Cerdas+dengan+Validasi+GPS+%26+QR+Code+Dinamis;Manajemen+Materi%2C+Tugas+Rich-Text%2C+Jadwal+Ujian+%26+Help+Desk;Real-Time+Notifikasi+%26+Sinkronisasi+Waktu+WIB+(Asia%2FJakarta)" alt="Typing SVG" />
  </a>
</div>

<br/>

<!-- BADGES TEKNOLOGI -->
<div align="center">
  <img src="https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11"/>
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+"/>
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL"/>
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS"/>
  <img src="https://img.shields.io/badge/Alpine.js-Interactive-8BC0D0?style=for-the-badge&logo=alpinedotjs&logoColor=black" alt="Alpine.js"/>
</div>

<br/>

<!-- GARIS PEMISAH ANIMASI -->
<img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif" width="100%" alt="Animated Divider"/>

## 🏫 Tentang Sistem

**SIAKAD & LMS SMANSA** adalah aplikasi berbasis web terintegrasi yang dirancang khusus untuk **SMA Negeri 1 Gunungsitoli** *(NPSN: 10258462 — Jl. Pendidikan No. 3, Gunungsitoli, Sumatera Utara)*. Sistem ini menggabungkan administrasi akademik sekolah dan *Learning Management System* (LMS) dalam satu antarmuka **Evergreen** yang bersih, formal, responsif, dan *real-time*.

<table>
  <tr>
    <td width="50%" valign="top">
      <h3>🎯 Keunggulan Utama</h3>
      <ul>
        <li><b>Multi-Role Terpadu:</b> Hak akses terpisah untuk <b>Admin TU</b>, <b>Guru</b>, dan <b>Siswa</b>.</li>
        <li><b>Presensi Anti-Titip Absen:</b> Wajib GPS Geolocation & <i>reverse-geocoding</i> alamat + QR Code dinamis berganti tiap 20 detik.</li>
        <li><b>Sinkronisasi Waktu Real-Time:</b> Jam digital aktif <code>HH:MM:SS WIB</code> (<code>Asia/Jakarta</code>) di semua role & tab.</li>
        <li><b>Modern UI/UX:</b> Tanpa dialog <code>alert()</code> bawaan browser—diganti dengan <i>Toast Notification Card</i> & <i>Custom Confirmation Modal</i>.</li>
      </ul>
    </td>
    <td width="50%" valign="top">
      <h3>📊 Struktur Data Awal (Seeder)</h3>
      <ul>
        <li><b>Identitas Sekolah:</b> SMA Negeri 1 Gunungsitoli (10258462)</li>
        <li><b>Akun Administrator:</b> 1 Admin TU Terpusat</li>
        <li><b>Tenaga Pendidik:</b> 6 Guru Pengampu & Wali Kelas</li>
        <li><b>Rombongan Belajar:</b> 6 Kelas (<code>X MIPA 1</code> s/d <code>XII IPS 1</code>)</li>
        <li><b>Peserta Didik:</b> 100 Siswa Aktif Terdaftar</li>
      </ul>
    </td>
  </tr>
</table>

<img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif" width="100%" alt="Animated Divider"/>

## ✨ Fitur Lengkap Berdasarkan Peran

<details open>
  <summary><b>🛡️ 1. Panel Administrator (Tata Usaha)</b></summary>
  <br/>
  <ul>
    <li><b>Dashboard Real-Time:</b> Statistik langsung total siswa, guru, rombel kelas, mata pelajaran, dan jadwal harian.</li>
    <li><b>Manajemen Data Kelas & Mapel:</b> CRUD kelas dengan integrasi wali kelas serta daftar rekap anggota kelas.</li>
    <li><b>Manajemen Akun Guru & Siswa:</b> Pengelompokan siswa per kelas, halaman detail satu layar, reset kata sandi, dan hapus akun.</li>
    <li><b>Penjadwalan Akademik:</b> Penyusunan jadwal pelajaran mingguan (dengan deteksi anti-bentrok) dan jadwal ujian sekolah.</li>
    <li><b>Pengumuman Sekolah Rich-Text:</b> Editor pengumuman lengkap dengan upload poster/gambar dan target penerima (Semua / Guru / Siswa).</li>
    <li><b>Pusat Help Desk Masuk:</b> Kotak masuk tiket bantuan dari siswa maupun guru dengan balasan percakapan langsung.</li>
  </ul>
</details>

<details>
  <summary><b>👨‍🏫 2. Portal Guru Pengampu</b></summary>
  <br/>
  <ul>
    <li><b>Sesi Presensi Cerdas:</b> Buka sesi presensi per pertemuan dengan pilihan metode <b>Mandiri (GPS)</b> atau <b>QR Code Dinamis</b>, disertai tabel rekap & override kehadiran langsung.</li>
    <li><b>Manajemen Materi Pelajaran:</b> Unggah dan kelola modul pembelajaran (<code>PDF</code>, <code>DOCX</code>, <code>PPTX</code>) per kelas.</li>
    <li><b>Tugas & Penilaian:</b> Buat instruksi tugas dengan <i>Rich Text Editor</i> ala MS Word, filter ekstensi file siswa, unduh seluruh tugas kelas dalam satu arsip <code>.zip</code>, dan berikan nilai beserta feedback.</li>
    <li><b>Notifikasi & Help Desk:</b> Notifikasi otomatis saat siswa mengumpulkan tugas atau membalas komentar.</li>
  </ul>
</details>

<details>
  <summary><b>🎓 3. Portal Siswa</b></summary>
  <br/>
  <ul>
    <li><b>Presensi Kelas 2-Tahap:</b> Daftar mata pelajaran kelas &rarr; tabel pertemuan &rarr; modal presensi dengan verifikasi lokasi GPS wajib & pemindai QR.</li>
    <li><b>Akses Materi & Pengumpulan Tugas:</b> Unduh materi guru dan unggah jawaban multi-file lewat area <i>Drag & Drop</i> lengkap dengan status waktu pengajuan & kolom diskusi.</li>
    <li><b>Jadwal Ujian & Pengumuman:</b> Pantau jadwal ujian kelas, pengumuman resmi sekolah, serta kirim tiket bantuan ke Admin TU lewat fitur Help Desk.</li>
  </ul>
</details>

<img src="https://user-images.githubusercontent.com/73097560/115834477-dbab4500-a447-11eb-908a-139a6edaec5c.gif" width="100%" alt="Animated Divider"/>

## 🚀 Panduan Instalasi Lokal (Laragon / XAMPP)

```bash
# 1. Clone repository ini
git clone [https://github.com/jelialvonzolaia-sys/siakad-sekolah.git](https://github.com/jelialvonzolaia-sys/siakad-sekolah.git)
cd siakad-sekolah

# 2. Instal dependensi PHP & Frontend
composer install
npm install && npm run build

# 3. Salin konfigurasi environment & buat application key
cp .env.example .env
php artisan key:generate

# 4. Atur nama database pada file .env (misal: DB_DATABASE=db_siakad_smansa)
# 5. Jalankan migrasi tabel, seeder data awal, dan storage link
php artisan migrate --seed
php artisan storage:link

# 6. Jalankan server pengembangan
php artisan serve
