<?php

namespace Database\Seeders;

use App\Models\Jadwal;
use App\Models\JadwalUjian;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\KomentarTugas;
use App\Models\Materi;
use App\Models\PengumpulanFile;
use App\Models\PengumpulanTugas;
use App\Models\Pengumuman;
use App\Models\Presensi;
use App\Models\SesiPresensi;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');

        // 1. Akun Admin SMAN 1 Gunungsitoli
        $admin = User::create([
            'name' => 'Administrator SIAKAD SMAN 1 Gunungsitoli',
            'email' => 'admin@sman1gst.sch.id',
            'password' => $defaultPassword,
            'role' => 'admin',
            'nis_nip' => '198001012005011001',
            'kelas_id' => null,
        ]);

        // 2. Akun 6 Guru SMAN 1 Gunungsitoli
        $guruData = [
            [
                'name' => 'Dra. Rosmina Zebua, M.Pd',
                'email' => 'rosmina.zebua@sman1gst.sch.id',
                'nis_nip' => '197103151998012001',
                'subject_code' => 'KIM',
                'subject_name' => 'Kimia',
            ],
            [
                'name' => 'Yosua Laia, S.Pd',
                'email' => 'yosua.laia@sman1gst.sch.id',
                'nis_nip' => '198205102008011002',
                'subject_code' => 'MAT',
                'subject_name' => 'Matematika',
            ],
            [
                'name' => 'Febriani Hulu, S.Pd, M.Si',
                'email' => 'febriani.hulu@sman1gst.sch.id',
                'nis_nip' => '198508202010012003',
                'subject_code' => 'FIS',
                'subject_name' => 'Fisika',
            ],
            [
                'name' => 'Berkat Daeli, S.Kom, M.Kom',
                'email' => 'berkat.daeli@sman1gst.sch.id',
                'nis_nip' => '198811052014021004',
                'subject_code' => 'INF',
                'subject_name' => 'Informatika',
            ],
            [
                'name' => 'Kristina Lase, S.Pd',
                'email' => 'kristina.lase@sman1gst.sch.id',
                'nis_nip' => '198302142009022005',
                'subject_code' => 'ING',
                'subject_name' => 'Bahasa Inggris',
            ],
            [
                'name' => 'Arofah Harefa, S.Pd',
                'email' => 'arofah.harefa@sman1gst.sch.id',
                'nis_nip' => '197907222006041006',
                'subject_code' => 'SEJ',
                'subject_name' => 'Sejarah Indonesia',
            ],
        ];

        $teachers = [];
        foreach ($guruData as $g) {
            $teacher = User::create([
                'name' => $g['name'],
                'email' => $g['email'],
                'password' => $defaultPassword,
                'role' => 'guru',
                'nis_nip' => $g['nis_nip'],
                'kelas_id' => null,
            ]);
            $teachers[$g['subject_code']] = $teacher;
        }

        // 3. 6 Kelas SMAN 1 Gunungsitoli (Wali kelas diisi oleh guru)
        $kelasConfig = [
            ['nama_kelas' => 'X MIPA 1', 'tingkat' => 'X', 'wali' => $teachers['KIM']->id],
            ['nama_kelas' => 'X IPS 1', 'tingkat' => 'X', 'wali' => $teachers['MAT']->id],
            ['nama_kelas' => 'XI MIPA 1', 'tingkat' => 'XI', 'wali' => $teachers['FIS']->id],
            ['nama_kelas' => 'XI IPS 1', 'tingkat' => 'XI', 'wali' => $teachers['INF']->id],
            ['nama_kelas' => 'XII MIPA 1', 'tingkat' => 'XII', 'wali' => $teachers['ING']->id],
            ['nama_kelas' => 'XII IPS 1', 'tingkat' => 'XII', 'wali' => $teachers['SEJ']->id],
        ];

        $kelasList = [];
        foreach ($kelasConfig as $k) {
            $kelasList[$k['nama_kelas']] = Kelas::create([
                'nama_kelas' => $k['nama_kelas'],
                'tingkat' => $k['tingkat'],
                'wali_kelas_id' => $k['wali'],
            ]);
        }
        $kelases = array_values($kelasList);

        // 4. 6 Mata Pelajaran
        $mapelList = [];
        foreach ($guruData as $g) {
            $mapelList[$g['subject_code']] = MataPelajaran::create([
                'kode_mapel' => $g['subject_code'],
                'nama_mapel' => $g['subject_name'],
                'guru_id' => $teachers[$g['subject_code']]->id,
                'kkm' => 75,
            ]);
        }

        // 5. 100 Siswa SMAN 1 Gunungsitoli (NIS 260001 - 260100 dibagi rata ke 6 kelas)
        $namaSiswa = [
            'Alvaro Jendri Laia', 'Nathaniel Faduhusi Harefa', 'Keanu Rafael Zai', 'Elvano Christian Gea', 'Davin Alexander Waruwu',
            'Gabriel Yosua Telaumbanua', 'Rayhan Febri Lase', 'Jonathan Elia Mendrofa', 'Nathaniel Samuel Gulo', 'Adrian Michael Bawamenewi',
            'Aurelia Natasha Harefa', 'Celine Marsha Laia', 'Gabriella Naomi Zai', 'Vanessa Clarissa Gea', 'Evelyn Anastasia Waruwu',
            'Olivia Michelle Lase', 'Felicia Nathania Gulo', 'Calista Febiola Mendrofa', 'Angelica Priscilla Telaumbanua', 'Kezia Aurellia Bawamenewi',
            'Arsenio Daffa Harefa', 'Kelvin Alvaro Laia', 'Rafael Jonathan Zai', 'Matthew Daniel Gea', 'Ethan Alexander Gulo',
            'Darren Nicholas Waruwu', 'Fabian Christian Lase', 'Leonel Andreas Mendrofa', 'Vincent Adrian Telaumbanua', 'Reyhan Althaf Bawamenewi',
            'Amanda Clarissa Harefa', 'Jessica Aurelia Laia', 'Natasha Evelyn Zai', 'Stephanie Michelle Gea', 'Gabriella Felicia Gulo',
            'Kimberly Anastasia Waruwu', 'Vanessa Angelica Lase', 'Aurelia Nathania Mendrofa', 'Clarissa Giovanna Telaumbanua', 'Melody Cassandra Bawamenewi',
            'Elbert Nathaniel Harefa', 'Alexander Raffael Laia', 'Jeremia Christian Zai', 'Samuel Jonathan Gea', 'Daniel Matthew Gulo',
            'Nicholas Adrian Waruwu', 'Jonathan Rafael Lase', 'Andreas Vincent Mendrofa', 'Kevin Alexander Telaumbanua', 'Timothy Gabriel Bawamenewi',
            'Michelle Olivia Harefa', 'Grace Nathania Laia', 'Jessica Naomi Zai', 'Natalia Aurelia Gea', 'Monica Gabriella Gulo',
            'Elizabeth Clarissa Waruwu', 'Febriana Celine Lase', 'Anastasia Putri Mendrofa', 'Yohana Gabriella Telaumbanua', 'Priscilla Angelica Bawamenewi',
            'Alvaro Dwi Pratama', 'Farel Jonathan Harefa', 'Christian Elia Laia', 'Matthew Rian Zai', 'David Nathanael Gea',
            'Rafael Dimas Gulo', 'Jonathan Farel Waruwu', 'Kevin Samuel Lase', 'Elvano Raffael Mendrofa', 'Daniel Arsenio Telaumbanua',
            'Celine Aurelia Bawamenewi', 'Grace Michelle Harefa', 'Vanessa Nathania Laia', 'Olivia Clarissa Zai', 'Felicia Anastasia Gea',
            'Gabriella Natasha Gulo', 'Kimberly Aurellia Waruwu', 'Evelyn Priscilla Lase', 'Angelica Naomi Mendrofa', 'Jessica Marsha Telaumbanua',
            'Aditya Pratama Saputra', 'Muhammad Fikri Ramadhan', 'Putri Maharani Siregar', 'Rafi Akbar Firmansyah', 'Nabila Zahra Putri',
            'Andre Fernando Simanjuntak', 'Rizky Alif Maulana', 'Sarah Amanda Situmorang', 'Dinda Ayu Lestari', 'Farhan Ilham Nasution',
            'Natasha Kirana Wijaya', 'Bagas Arya Prakoso', 'Tiara Putri Anggraini', 'Reza Mahendra Lubis', 'Aulia Safira Ramadhani',
            'Kevin Jonathan Sihombing', 'Salsa Nadhira Putri', 'Daffa Rizqullah Hakim', 'Michelle Grace Hutagalung', 'Alisha Zahra Permata',
        ];

        $studentsByClass = [];
        foreach ($namaSiswa as $index => $nama) {
            $studentNumber = $index + 1;
            $email = sprintf('siswa%03d@sman1gst.sch.id', $studentNumber);
            $nis = (string) (260001 + $index);
            $kelasTarget = $kelases[$index % count($kelases)];

            $student = User::create([
                'name' => $nama,
                'email' => $email,
                'password' => $defaultPassword,
                'role' => 'siswa',
                'nis_nip' => $nis,
                'kelas_id' => $kelasTarget->id,
            ]);

            $studentsByClass[$kelasTarget->id][] = $student;
        }

        // 6. Sampel Jadwal Pelajaran Mingguan
        $jadwalDefinitions = [
            // Kelas X MIPA 1
            ['kelas' => 'X MIPA 1', 'mapel' => 'KIM', 'guru' => 'KIM', 'hari' => 'Senin', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
            ['kelas' => 'X MIPA 1', 'mapel' => 'MAT', 'guru' => 'MAT', 'hari' => 'Senin', 'jam_mulai' => '09:15:00', 'jam_selesai' => '10:45:00'],
            ['kelas' => 'X MIPA 1', 'mapel' => 'FIS', 'guru' => 'FIS', 'hari' => 'Selasa', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
            ['kelas' => 'X MIPA 1', 'mapel' => 'ING', 'guru' => 'ING', 'hari' => 'Rabu', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
            ['kelas' => 'X MIPA 1', 'mapel' => 'INF', 'guru' => 'INF', 'hari' => 'Kamis', 'jam_mulai' => '09:15:00', 'jam_selesai' => '10:45:00'],
            ['kelas' => 'X MIPA 1', 'mapel' => 'SEJ', 'guru' => 'SEJ', 'hari' => 'Jumat', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],

            // Kelas X IPS 1
            ['kelas' => 'X IPS 1', 'mapel' => 'SEJ', 'guru' => 'SEJ', 'hari' => 'Senin', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
            ['kelas' => 'X IPS 1', 'mapel' => 'ING', 'guru' => 'ING', 'hari' => 'Senin', 'jam_mulai' => '09:15:00', 'jam_selesai' => '10:45:00'],
            ['kelas' => 'X IPS 1', 'mapel' => 'MAT', 'guru' => 'MAT', 'hari' => 'Selasa', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
            ['kelas' => 'X IPS 1', 'mapel' => 'INF', 'guru' => 'INF', 'hari' => 'Rabu', 'jam_mulai' => '09:15:00', 'jam_selesai' => '10:45:00'],
            ['kelas' => 'X IPS 1', 'mapel' => 'FIS', 'guru' => 'FIS', 'hari' => 'Kamis', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
            ['kelas' => 'X IPS 1', 'mapel' => 'KIM', 'guru' => 'KIM', 'hari' => 'Jumat', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],

            // Kelas XI MIPA 1
            ['kelas' => 'XI MIPA 1', 'mapel' => 'FIS', 'guru' => 'FIS', 'hari' => 'Senin', 'jam_mulai' => '09:15:00', 'jam_selesai' => '10:45:00'],
            ['kelas' => 'XI MIPA 1', 'mapel' => 'KIM', 'guru' => 'KIM', 'hari' => 'Selasa', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
            ['kelas' => 'XI MIPA 1', 'mapel' => 'MAT', 'guru' => 'MAT', 'hari' => 'Rabu', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
            ['kelas' => 'XI MIPA 1', 'mapel' => 'INF', 'guru' => 'INF', 'hari' => 'Kamis', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
            ['kelas' => 'XI MIPA 1', 'mapel' => 'ING', 'guru' => 'ING', 'hari' => 'Jumat', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],

            // Kelas XI IPS 1
            ['kelas' => 'XI IPS 1', 'mapel' => 'SEJ', 'guru' => 'SEJ', 'hari' => 'Senin', 'jam_mulai' => '11:00:00', 'jam_selesai' => '12:30:00'],
            ['kelas' => 'XI IPS 1', 'mapel' => 'INF', 'guru' => 'INF', 'hari' => 'Selasa', 'jam_mulai' => '09:15:00', 'jam_selesai' => '10:45:00'],
            ['kelas' => 'XI IPS 1', 'mapel' => 'ING', 'guru' => 'ING', 'hari' => 'Rabu', 'jam_mulai' => '09:15:00', 'jam_selesai' => '10:45:00'],
            ['kelas' => 'XI IPS 1', 'mapel' => 'MAT', 'guru' => 'MAT', 'hari' => 'Kamis', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
            ['kelas' => 'XI IPS 1', 'mapel' => 'KIM', 'guru' => 'KIM', 'hari' => 'Jumat', 'jam_mulai' => '09:15:00', 'jam_selesai' => '10:45:00'],

            // Kelas XII MIPA 1
            ['kelas' => 'XII MIPA 1', 'mapel' => 'MAT', 'guru' => 'MAT', 'hari' => 'Senin', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
            ['kelas' => 'XII MIPA 1', 'mapel' => 'KIM', 'guru' => 'KIM', 'hari' => 'Selasa', 'jam_mulai' => '11:00:00', 'jam_selesai' => '12:30:00'],
            ['kelas' => 'XII MIPA 1', 'mapel' => 'FIS', 'guru' => 'FIS', 'hari' => 'Rabu', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
            ['kelas' => 'XII MIPA 1', 'mapel' => 'ING', 'guru' => 'ING', 'hari' => 'Kamis', 'jam_mulai' => '09:15:00', 'jam_selesai' => '10:45:00'],
            ['kelas' => 'XII MIPA 1', 'mapel' => 'INF', 'guru' => 'INF', 'hari' => 'Jumat', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],

            // Kelas XII IPS 1
            ['kelas' => 'XII IPS 1', 'mapel' => 'SEJ', 'guru' => 'SEJ', 'hari' => 'Senin', 'jam_mulai' => '09:15:00', 'jam_selesai' => '10:45:00'],
            ['kelas' => 'XII IPS 1', 'mapel' => 'ING', 'guru' => 'ING', 'hari' => 'Selasa', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
            ['kelas' => 'XII IPS 1', 'mapel' => 'MAT', 'guru' => 'MAT', 'hari' => 'Rabu', 'jam_mulai' => '11:00:00', 'jam_selesai' => '12:30:00'],
            ['kelas' => 'XII IPS 1', 'mapel' => 'INF', 'guru' => 'INF', 'hari' => 'Kamis', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
            ['kelas' => 'XII IPS 1', 'mapel' => 'FIS', 'guru' => 'FIS', 'hari' => 'Jumat', 'jam_mulai' => '07:30:00', 'jam_selesai' => '09:00:00'],
        ];

        $jadwals = [];
        foreach ($jadwalDefinitions as $jd) {
            $jadwals[] = Jadwal::create([
                'kelas_id' => $kelasList[$jd['kelas']]->id,
                'mapel_id' => $mapelList[$jd['mapel']]->id,
                'guru_id' => $teachers[$jd['guru']]->id,
                'hari' => $jd['hari'],
                'jam_mulai' => $jd['jam_mulai'],
                'jam_selesai' => $jd['jam_selesai'],
            ]);
        }

        // 7. Sampel Jadwal Ujian PAS Ganjil 2026/2027
        $examScheduleConfig = [
            ['mapel' => 'MAT', 'tanggal' => '2026-12-01', 'waktu_mulai' => '08:00:00'],
            ['mapel' => 'ING', 'tanggal' => '2026-12-02', 'waktu_mulai' => '08:00:00'],
            ['mapel' => 'FIS', 'tanggal' => '2026-12-03', 'waktu_mulai' => '08:00:00'],
            ['mapel' => 'KIM', 'tanggal' => '2026-12-04', 'waktu_mulai' => '08:00:00'],
            ['mapel' => 'INF', 'tanggal' => '2026-12-05', 'waktu_mulai' => '08:00:00'],
            ['mapel' => 'SEJ', 'tanggal' => '2026-12-07', 'waktu_mulai' => '08:00:00'],
        ];

        $ruanganPerKelas = [
            'X MIPA 1' => 'Ruang 01 (Gedung A Lantai 1)',
            'X IPS 1' => 'Ruang 02 (Gedung A Lantai 1)',
            'XI MIPA 1' => 'Ruang 03 (Gedung B Lantai 1)',
            'XI IPS 1' => 'Ruang 04 (Gedung B Lantai 1)',
            'XII MIPA 1' => 'Ruang 05 (Gedung B Lantai 2)',
            'XII IPS 1' => 'Ruang 06 (Gedung B Lantai 2)',
        ];

        foreach ($kelasList as $namaKelas => $kelasObj) {
            foreach ($examScheduleConfig as $exam) {
                JadwalUjian::create([
                    'kelas_id' => $kelasObj->id,
                    'mapel_id' => $mapelList[$exam['mapel']]->id,
                    'jenis_ujian' => 'PAS Ganjil 2026/2027',
                    'tanggal' => $exam['tanggal'],
                    'waktu_mulai' => $exam['waktu_mulai'],
                    'ruangan' => $ruanganPerKelas[$namaKelas],
                ]);
            }
        }

        // 8. Sampel Materi Pembelajaran
        $materiData = [
            [
                'mapel' => 'KIM',
                'guru' => 'KIM',
                'kelas' => 'X MIPA 1',
                'judul' => 'Struktur Atom dan Konfigurasi Elektron',
                'deskripsi' => 'Modul pembelajaran mengenai perkembangan teori atom, bilangan kuantum, dan sistem periodik unsur modern.',
                'file_path' => 'materi/kimia_struktur_atom.pdf',
            ],
            [
                'mapel' => 'KIM',
                'guru' => 'KIM',
                'kelas' => 'XI MIPA 1',
                'judul' => 'Termokimia dan Penentuan Perubahan Entalpi',
                'deskripsi' => 'Bahan kajian reaksi eksoterm, endoterm, kalorimetri, dan penerapan Hukum Hess dalam reaksi kimia.',
                'file_path' => 'materi/kimia_termokimia.pdf',
            ],
            [
                'mapel' => 'MAT',
                'guru' => 'MAT',
                'kelas' => 'X MIPA 1',
                'judul' => 'Persamaan dan Pertidaksamaan Nilai Mutlak Satu Variabel',
                'deskripsi' => 'Rangkuman rumus konsep nilai mutlak beserta contoh penyelesaian soal cerita aplikatif.',
                'file_path' => 'materi/matematika_nilai_mutlak.pdf',
            ],
            [
                'mapel' => 'MAT',
                'guru' => 'MAT',
                'kelas' => 'XII MIPA 1',
                'judul' => 'Kalkulus Lanjut: Limit Fungsi Trigonometri & Turunan',
                'deskripsi' => 'Catatan kuliah pengantar kalkulus, dalil L\'Hopital, dan aplikasi turunan pada laju perubahan.',
                'file_path' => 'materi/matematika_turunan_trigonometri.pdf',
            ],
            [
                'mapel' => 'FIS',
                'guru' => 'FIS',
                'kelas' => 'X MIPA 1',
                'judul' => 'Kinematika Gerak Lurus Beraturan dan Berubah Beraturan',
                'deskripsi' => 'Slide presentasi GLB dan GLBB serta panduan praktikum gerak jatuh bebas di laboratorium fisika.',
                'file_path' => 'materi/fisika_kinematika_gerak.pdf',
            ],
            [
                'mapel' => 'FIS',
                'guru' => 'FIS',
                'kelas' => 'XII MIPA 1',
                'judul' => 'Rangkaian Arus Bolak-Balik (AC) & Induksi Elektromagnetik',
                'deskripsi' => 'Materi diagram fasor, impedansi rangkaian R-L-C seri, serta konsep daya semu dan daya aktif.',
                'file_path' => 'materi/fisika_arus_bolak_balik.pdf',
            ],
            [
                'mapel' => 'INF',
                'guru' => 'INF',
                'kelas' => 'X IPS 1',
                'judul' => 'Pengenalan Algoritma, Flowchart, dan Logika Pemrograman',
                'deskripsi' => 'Panduan dasar struktur logika percabangan, perulangan, dan penerapannya dalam pseudocode.',
                'file_path' => 'materi/informatika_algoritma_dasar.pdf',
            ],
            [
                'mapel' => 'INF',
                'guru' => 'INF',
                'kelas' => 'XI MIPA 1',
                'judul' => 'Arsitektur Jaringan Komputer dan Subnetting IPv4',
                'deskripsi' => 'Bahan praktikum konfigurasi IP address, subnet mask CIDR, dan topologi jaringan lokal LAN.',
                'file_path' => 'materi/informatika_jaringan_ipv4.pdf',
            ],
            [
                'mapel' => 'ING',
                'guru' => 'ING',
                'kelas' => 'X MIPA 1',
                'judul' => 'Mastering Descriptive & Report Texts in English Context',
                'deskripsi' => 'Comprehensive grammar guidelines, vocabulary builders, and reading comprehension exercises.',
                'file_path' => 'materi/bahasa_inggris_descriptive_text.pdf',
            ],
            [
                'mapel' => 'ING',
                'guru' => 'ING',
                'kelas' => 'XII IPS 1',
                'judul' => 'Writing Professional Job Application Letters & Curriculum Vitae',
                'deskripsi' => 'Format penulisan surat lamaran kerja resmi standar internasional beserta format resume efektif.',
                'file_path' => 'materi/bahasa_inggris_application_letter.pdf',
            ],
            [
                'mapel' => 'SEJ',
                'guru' => 'SEJ',
                'kelas' => 'X IPS 1',
                'judul' => 'Pengantar Ilmu Sejarah: Cara Berpikir Sinkronik dan Diakronik',
                'deskripsi' => 'Ulasan metodologi penelitian sejarah, kritik sumber, heuristik, dan historiografi Indonesia.',
                'file_path' => 'materi/sejarah_pengantar_metodologi.pdf',
            ],
            [
                'mapel' => 'SEJ',
                'guru' => 'SEJ',
                'kelas' => 'XI IPS 1',
                'judul' => 'Jejak Pergerakan Nasional Menuju Kemerdekaan Indonesia',
                'deskripsi' => 'Analisis kronologis berdirinya Budi Utomo, Sarekat Islam, Indische Partij hingga peristiwa Sumpah Pemuda.',
                'file_path' => 'materi/sejarah_pergerakan_nasional.pdf',
            ],
        ];

        foreach ($materiData as $md) {
            Materi::create([
                'mapel_id' => $mapelList[$md['mapel']]->id,
                'guru_id' => $teachers[$md['guru']]->id,
                'kelas_id' => $kelasList[$md['kelas']]->id,
                'judul' => $md['judul'],
                'deskripsi' => $md['deskripsi'],
                'file_path' => $md['file_path'],
            ]);
        }

        // 9. Sampel Pengumuman
        $pengumumanData = [
            [
                'judul' => 'Pelaksanaan Penilaian Akhir Semester (PAS) Ganjil T.A. 2026/2027',
                'isi' => 'Diberitahukan kepada seluruh civitas akademika SMAN 1 Gunungsitoli bahwa Penilaian Akhir Semester (PAS) Ganjil Tahun Ajaran 2026/2027 akan dilaksanakan serentak mulai tanggal 1 s/d 8 Desember 2026. Seluruh siswa diharapkan menjaga kesehatan dan mempersiapkan perlengkapan ujian.',
                'target_role' => 'semua',
                'penulis_id' => $admin->id,
            ],
            [
                'judul' => 'Rapat Pleno Dewan Guru: Finalisasi Kisi-Kisi dan Soal Asesmen Semester',
                'isi' => 'Seluruh bapak/ibu dewan guru SMAN 1 Gunungsitoli diundang menghadiri rapat koordinasi pleno pada hari Jumat pukul 13.30 WIB di Ruang Rapat Utama untuk verifikasi soal PAS Ganjil serta penyusunan rubrik asesmen kurikulum.',
                'target_role' => 'guru',
                'penulis_id' => $admin->id,
            ],
            [
                'judul' => 'Tata Tertib Ketat Peserta Ujian PAS dan Kedisiplinan Seragam',
                'isi' => 'Peserta didik wajib memasuki ruang ujian 15 menit sebelum bel masuk berbunyi dan mengenakan seragam putih abu-abu lengkap dengan dasi serta tanda lokasi SMAN 1 Gunungsitoli. Dilarang keras membawa smartphone dan catatan ke dalam meja ujian.',
                'target_role' => 'siswa',
                'penulis_id' => $teachers['KIM']->id,
            ],
            [
                'judul' => 'Sosialisasi Pendaftaran SNBP dan Pembinaan Jalur Prestasi Perguruan Tinggi',
                'isi' => 'Bagi seluruh siswa kelas XII MIPA 1 dan XII IPS 1, tim Bimbingan Konseling membuka sesi konsultasi pemilihan program studi dan verifikasi nilai rapor untuk persiapan eligible SNBP 2027 mulai tanggal 20 November 2026 di Aula Sekolah.',
                'target_role' => 'siswa',
                'penulis_id' => $teachers['MAT']->id,
            ],
            [
                'judul' => 'Jadwal Libur Semester Ganjil dan Penyerahan Laporan Hasil Belajar (Rapor)',
                'isi' => 'Penyerahan Laporan Hasil Belajar (Rapor) Semester Ganjil akan dilaksanakan secara tatap muka dengan orang tua/wali murid pada hari Sabtu, 19 Desember 2026. Libur akhir semester berlangsung mulai 21 Desember 2026 sampai dengan 3 Januari 2027.',
                'target_role' => 'semua',
                'penulis_id' => $admin->id,
            ],
        ];

        foreach ($pengumumanData as $pd) {
            Pengumuman::create($pd);
        }

        // 10. Sampel Data Sesi Presensi & Rekap Kehadiran Siswa
        $tanggalPertemuan = ['2026-11-02', '2026-11-09', '2026-11-16'];
        $statusOptions = ['Hadir', 'Hadir', 'Hadir', 'Hadir', 'Hadir', 'Hadir', 'Hadir', 'Sakit', 'Izin', 'Alpha'];

        foreach ($jadwals as $jadwalItem) {
            $siswaKelas = $studentsByClass[$jadwalItem->kelas_id] ?? [];

            foreach ($tanggalPertemuan as $tglIndex => $tgl) {
                $pertemuanKe = $tglIndex + 1;
                $metode = ($tglIndex % 2 === 0) ? 'mandiri' : 'qr';

                $sesi = SesiPresensi::create([
                    'jadwal_id' => $jadwalItem->id,
                    'pertemuan_ke' => $pertemuanKe,
                    'materi' => 'Pokok Bahasan Materi Pertemuan Ke-' . $pertemuanKe,
                    'metode' => $metode,
                    'qr_token' => $metode === 'qr' ? 'SMAN1GST-' . strtoupper(\Illuminate\Support\Str::random(8)) : null,
                    'qr_expires_at' => $metode === 'qr' ? now()->subMinutes(60) : null,
                    'tanggal' => $tgl,
                    'jam_mulai' => $jadwalItem->jam_mulai,
                    'jam_selesai' => $jadwalItem->jam_selesai,
                    'status_sesi' => 'ditutup',
                ]);

                foreach ($siswaKelas as $sIndex => $siswaItem) {
                    $randKey = ($sIndex + $tglIndex + $jadwalItem->id) % count($statusOptions);
                    $status = $statusOptions[$randKey];

                    $keterangan = match ($status) {
                        'Sakit' => 'Surat keterangan sakit dari dokter terlampir',
                        'Izin' => 'Izin keperluan keluarga mendesak',
                        'Alpha' => 'Tanpa keterangan',
                        default => null,
                    };

                    Presensi::create([
                        'jadwal_id' => $jadwalItem->id,
                        'sesi_presensi_id' => $sesi->id,
                        'siswa_id' => $siswaItem->id,
                        'tanggal' => $tgl,
                        'waktu_presensi' => $status === 'Hadir' ? '07:45:00' : null,
                        'status' => $status,
                        'keterangan' => $keterangan,
                        'latitude' => $status === 'Hadir' ? '1.2829120' : null,
                        'longitude' => $status === 'Hadir' ? '97.6186450' : null,
                        'alamat_lokasi' => $status === 'Hadir' ? 'Jl. Pendidikan No. 3, Ilir, Kec. Gunungsitoli, Kota Gunungsitoli, Sumatera Utara 22815' : null,
                        'metode_absen' => $status === 'Hadir' ? $metode : null,
                    ]);
                }
            }
        }

        // Buat 1 Sesi Presensi Aktif ('dibuka') Hari Ini untuk Demo & Testing Langsung
        // Pada Jadwal Pertama (Kelas X MIPA 1, Matematika bersama Dra. Rosmina Zebua)
        $jadwalDemo = $jadwals[0];
        $siswaDemoKelas = $studentsByClass[$jadwalDemo->kelas_id] ?? [];

        $sesiAktif = SesiPresensi::create([
            'jadwal_id' => $jadwalDemo->id,
            'pertemuan_ke' => 4,
            'materi' => 'Matematika Terapan: Pertidaksamaan Rasional dan Kuadrat',
            'metode' => 'mandiri',
            'qr_token' => 'SMAN1GST-DEMO1234',
            'qr_expires_at' => now()->addMinutes(60),
            'tanggal' => now()->format('Y-m-d'),
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '23:59:00',
            'status_sesi' => 'dibuka',
        ]);

        foreach ($siswaDemoKelas as $sIndex => $siswaItem) {
            // Sebagian sudah hadir, sisanya default Alpha menunggu presensi
            $isHadir = $sIndex < 5;
            Presensi::create([
                'jadwal_id' => $jadwalDemo->id,
                'sesi_presensi_id' => $sesiAktif->id,
                'siswa_id' => $siswaItem->id,
                'tanggal' => now()->format('Y-m-d'),
                'waktu_presensi' => $isHadir ? '07:35:12' : null,
                'status' => $isHadir ? 'Hadir' : 'Alpha',
                'keterangan' => $isHadir ? 'Presensi mandiri via smartphone' : null,
                'latitude' => $isHadir ? '1.2829120' : null,
                'longitude' => $isHadir ? '97.6186450' : null,
                'alamat_lokasi' => $isHadir ? 'SMA Negeri 1 Gunungsitoli, Jl. Pendidikan No. 3' : null,
                'metode_absen' => $isHadir ? 'mandiri' : null,
            ]);
        }

        // 11. Sampel Data Penugasan & Pengumpulan Tugas (Assignment & Submission)
        $tugasMatematika = Tugas::create([
            'kelas_id' => $kelasList['X MIPA 1']->id,
            'mapel_id' => $mapelList['MAT']->id,
            'guru_id' => $teachers['MAT']->id,
            'judul' => 'Tugas Mandiri 1: Pemodelan Persamaan & Pertidaksamaan Nilai Mutlak',
            'deskripsi_html' => '<p><strong>Instruksi Pengerjaan Tugas:</strong></p><ol><li>Pelajari modul pembelajaran Bab 1 mengenai konsep nilai mutlak dan sifat-sifatnya.</li><li>Kerjakan soal latihan nomor 1 s/d 5 pada lembar kerja terlampir secara mandiri dan tulislah langkah penyelesaiannya secara rapi.</li><li>Pindai atau foto lembar jawaban Anda dalam format <strong>PDF</strong> atau <strong>Gambar (JPG/PNG)</strong>.</li><li>Pastikan berkas dapat terbaca dengan jelas sebelum menekan tombol Kirim Pengajuan.</li></ol><p><em>Nilai tugas ini akan dimasukkan sebagai komponen nilai formatif Semester Ganjil 2026/2027.</em></p>',
            'lampiran_guru' => 'tugas/panduan_soal_nilai_mutlak.pdf',
            'waktu_buka' => now()->subDays(2),
            'batas_waktu' => now()->addDays(5)->setTime(23, 59),
            'tipe_file_diizinkan' => ['pdf', 'docx', 'jpg', 'png'],
            'max_ukuran_mb' => 10,
            'max_jumlah_file' => 3,
            'izinkan_terlambat' => true,
        ]);

        // Sampel pengumpulan tugas oleh siswa pertama (Alvino Putra Harefa)
        if (!empty($siswaDemoKelas)) {
            $siswaSatu = $siswaDemoKelas[0];
            $pengumpulanSatu = PengumpulanTugas::create([
                'tugas_id' => $tugasMatematika->id,
                'siswa_id' => $siswaSatu->id,
                'status_pengajuan' => 'Terkirim',
                'status_penilaian' => 'Sudah dinilai',
                'nilai' => 95.00,
                'feedback_guru' => 'Langkah eliminasi dan grafik fungsi nilai mutlak sangat sistematis. Hasil akhir tepat 100%. Pertahankan!',
                'submitted_at' => now()->subDay()->setTime(14, 25),
            ]);

            PengumpulanFile::create([
                'pengumpulan_tugas_id' => $pengumpulanSatu->id,
                'nama_file_asli' => 'Jawaban_Tugas1_Alvino_Harefa.pdf',
                'file_path' => 'tugas_submissions/sample_alvino_jawaban.pdf',
                'ukuran_kb' => 450,
                'ekstensi' => 'pdf',
            ]);

            KomentarTugas::create([
                'pengumpulan_tugas_id' => $pengumpulanSatu->id,
                'user_id' => $siswaSatu->id,
                'komentar' => 'Selamat siang Ibu Rosmina, berkas jawaban tugas nilai mutlak sudah saya kirimkan. Terima kasih.',
            ]);

            KomentarTugas::create([
                'pengumpulan_tugas_id' => $pengumpulanSatu->id,
                'user_id' => $teachers['MAT']->id,
                'komentar' => 'Baik Alvino, jawaban sudah ibu periksa dan nilaimu sangat baik. Tetap semangat belajarnya!',
            ]);
        }
    }
}
