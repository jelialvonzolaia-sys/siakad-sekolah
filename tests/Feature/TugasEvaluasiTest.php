<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\KomentarTugas;
use App\Models\MataPelajaran;
use App\Models\PengumpulanFile;
use App\Models\PengumpulanTugas;
use App\Models\Tugas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TugasEvaluasiTest extends TestCase
{
    use RefreshDatabase;

    private User $guru1;
    private User $guru2;
    private User $siswa1;
    private User $siswa2;
    private Kelas $kelas1;
    private Kelas $kelas2;
    private MataPelajaran $mapelInformatika;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->guru1 = User::factory()->create([
            'name' => 'Febriani Hulu, S.Kom',
            'role' => 'guru',
            'nis_nip' => '198902142014022001',
        ]);

        $this->guru2 = User::factory()->create([
            'name' => 'Dra. Rosmina Zebua, M.Pd',
            'role' => 'guru',
            'nis_nip' => '197903122005012003',
        ]);

        $this->kelas1 = Kelas::create([
            'nama_kelas' => 'X MIPA 1',
            'tingkat' => 'X',
            'wali_kelas_id' => $this->guru1->id,
        ]);

        $this->kelas2 = Kelas::create([
            'nama_kelas' => 'XI MIPA 1',
            'tingkat' => 'XI',
            'wali_kelas_id' => $this->guru2->id,
        ]);

        $this->siswa1 = User::factory()->create([
            'name' => 'Alvino Putra Harefa',
            'role' => 'siswa',
            'nis_nip' => '260001',
            'kelas_id' => $this->kelas1->id,
        ]);

        $this->siswa2 = User::factory()->create([
            'name' => 'Berkat Emanuel Lase',
            'role' => 'siswa',
            'nis_nip' => '260002',
            'kelas_id' => $this->kelas1->id,
        ]);

        $this->mapelInformatika = MataPelajaran::create([
            'kode_mapel' => 'INF',
            'nama_mapel' => 'Informatika & Pemrograman',
        ]);

        Jadwal::create([
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelInformatika->id,
            'guru_id' => $this->guru1->id,
            'hari' => 'Kamis',
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:00:00',
        ]);
    }

    public function test_guru_can_view_tugas_index_and_create_pages(): void
    {
        $responseIndex = $this->actingAs($this->guru1)->get(route('guru.tugas.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Manajemen Tugas &amp; Evaluasi', false);

        $responseCreate = $this->actingAs($this->guru1)->get(route('guru.tugas.create'));
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('Buat Penugasan Pembelajaran Baru');
    }

    public function test_guru_can_create_tugas_with_rich_text_and_rules(): void
    {
        $soalFile = UploadedFile::fake()->create('panduan_praktikum.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->guru1)->post(route('guru.tugas.store'), [
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelInformatika->id,
            'judul' => 'Tugas Praktikum 1: Struktur Percabangan C++',
            'deskripsi_html' => '<p>Buatlah program C++ untuk menghitung diskon belanja menggunakan if-else.</p>',
            'lampiran_guru' => $soalFile,
            'waktu_buka' => now()->format('Y-m-d\TH:i'),
            'batas_waktu' => now()->addDays(7)->format('Y-m-d\TH:i'),
            'tipe_file_diizinkan' => ['cpp', 'pdf'],
            'max_ukuran_mb' => 10,
            'max_jumlah_file' => 3,
            'izinkan_terlambat' => 1,
        ]);

        $response->assertRedirect(route('guru.tugas.index'));
        $response->assertSessionHas('status');

        $tugas = Tugas::where('judul', 'Tugas Praktikum 1: Struktur Percabangan C++')->first();
        $this->assertNotNull($tugas);
        $this->assertEquals($this->guru1->id, $tugas->guru_id);
        $this->assertEquals(['cpp', 'pdf'], $tugas->tipe_file_diizinkan);
        $this->assertTrue($tugas->izinkan_terlambat);
        $this->assertNotNull($tugas->lampiran_guru);

        Storage::disk('public')->assertExists($tugas->lampiran_guru);
    }

    public function test_guru_can_edit_and_update_tugas(): void
    {
        $tugas = Tugas::create([
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelInformatika->id,
            'guru_id' => $this->guru1->id,
            'judul' => 'Tugas Versi Awal',
            'deskripsi_html' => '<p>Deskripsi Awal</p>',
            'waktu_buka' => now(),
            'batas_waktu' => now()->addDays(5),
            'tipe_file_diizinkan' => ['pdf'],
            'max_ukuran_mb' => 5,
            'max_jumlah_file' => 2,
            'izinkan_terlambat' => false,
        ]);

        $response = $this->actingAs($this->guru1)->put(route('guru.tugas.update', $tugas->id), [
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelInformatika->id,
            'judul' => 'Tugas Versi Diperbarui',
            'deskripsi_html' => '<p>Deskripsi Diperbarui</p>',
            'waktu_buka' => now()->format('Y-m-d\TH:i'),
            'batas_waktu' => now()->addDays(6)->format('Y-m-d\TH:i'),
            'tipe_file_diizinkan' => ['pdf', 'docx'],
            'max_ukuran_mb' => 10,
            'max_jumlah_file' => 3,
            'izinkan_terlambat' => 1,
        ]);

        $response->assertRedirect(route('guru.tugas.index'));

        $tugas->refresh();
        $this->assertEquals('Tugas Versi Diperbarui', $tugas->judul);
        $this->assertEquals(['pdf', 'docx'], $tugas->tipe_file_diizinkan);
        $this->assertTrue($tugas->izinkan_terlambat);
    }

    public function test_guru_can_delete_tugas_and_files_are_deleted(): void
    {
        $lampiran = UploadedFile::fake()->create('soal.pdf', 200)->store('tugas', 'public');

        $tugas = Tugas::create([
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelInformatika->id,
            'guru_id' => $this->guru1->id,
            'judul' => 'Tugas Untuk Dihapus',
            'deskripsi_html' => '<p>Deskripsi</p>',
            'lampiran_guru' => $lampiran,
            'waktu_buka' => now(),
            'batas_waktu' => now()->addDays(2),
            'tipe_file_diizinkan' => ['pdf'],
        ]);

        $pengumpulan = PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $this->siswa1->id,
            'status_pengajuan' => 'Terkirim',
            'submitted_at' => now(),
        ]);

        $fileSiswa = UploadedFile::fake()->create('jawaban.pdf', 300)->store('tugas_submissions', 'public');
        PengumpulanFile::create([
            'pengumpulan_tugas_id' => $pengumpulan->id,
            'nama_file_asli' => 'jawaban.pdf',
            'file_path' => $fileSiswa,
            'ukuran_kb' => 300,
            'ekstensi' => 'pdf',
        ]);

        Storage::disk('public')->assertExists($lampiran);
        Storage::disk('public')->assertExists($fileSiswa);

        $response = $this->actingAs($this->guru1)->delete(route('guru.tugas.destroy', $tugas->id));

        $response->assertRedirect(route('guru.tugas.index'));
        $this->assertDatabaseMissing('tugas', ['id' => $tugas->id]);
        $this->assertDatabaseMissing('pengumpulan_tugas', ['id' => $pengumpulan->id]);

        Storage::disk('public')->assertMissing($lampiran);
        Storage::disk('public')->assertMissing($fileSiswa);
    }

    public function test_guru_cannot_access_or_grade_tugas_belonging_to_another_guru(): void
    {
        $tugasGuru2 = Tugas::create([
            'kelas_id' => $this->kelas2->id,
            'mapel_id' => $this->mapelInformatika->id,
            'guru_id' => $this->guru2->id,
            'judul' => 'Tugas Guru 2',
            'deskripsi_html' => '<p>Deskripsi</p>',
            'waktu_buka' => now(),
            'batas_waktu' => now()->addDays(3),
            'tipe_file_diizinkan' => ['pdf'],
        ]);

        $response = $this->actingAs($this->guru1)->get(route('guru.tugas.edit', $tugasGuru2->id));
        $response->assertStatus(403);

        $responseShow = $this->actingAs($this->guru1)->get(route('guru.tugas.show', $tugasGuru2->id));
        $responseShow->assertStatus(403);
    }

    public function test_siswa_can_view_tugas_filtered_by_their_class(): void
    {
        // Tugas untuk kelas 1 (kelas siswa 1)
        Tugas::create([
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelInformatika->id,
            'guru_id' => $this->guru1->id,
            'judul' => 'Tugas Khusus Kelas X MIPA 1',
            'deskripsi_html' => '<p>Deskripsi Tugas</p>',
            'waktu_buka' => now(),
            'batas_waktu' => now()->addDays(4),
            'tipe_file_diizinkan' => ['pdf'],
        ]);

        // Tugas untuk kelas 2
        Tugas::create([
            'kelas_id' => $this->kelas2->id,
            'mapel_id' => $this->mapelInformatika->id,
            'guru_id' => $this->guru2->id,
            'judul' => 'Tugas Khusus Kelas XI MIPA 1',
            'deskripsi_html' => '<p>Deskripsi</p>',
            'waktu_buka' => now(),
            'batas_waktu' => now()->addDays(4),
            'tipe_file_diizinkan' => ['pdf'],
        ]);

        $response = $this->actingAs($this->siswa1)->get(route('siswa.tugas.index'));

        $response->assertStatus(200);
        $response->assertSee('Tugas Khusus Kelas X MIPA 1');
        $response->assertDontSee('Tugas Khusus Kelas XI MIPA 1');
    }

    public function test_siswa_can_submit_multi_files_and_view_status_table(): void
    {
        $tugas = Tugas::create([
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelInformatika->id,
            'guru_id' => $this->guru1->id,
            'judul' => 'Tugas C++ Multi-File',
            'deskripsi_html' => '<p>Instruksi C++</p>',
            'waktu_buka' => now()->subDay(),
            'batas_waktu' => now()->addDays(3),
            'tipe_file_diizinkan' => ['cpp', 'pdf'],
            'max_ukuran_mb' => 5,
            'max_jumlah_file' => 3,
        ]);

        $file1 = UploadedFile::fake()->create('kelompok12_01a.cpp', 50, 'text/plain');
        $file2 = UploadedFile::fake()->create('laporan.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->siswa1)->post(route('siswa.tugas.submit', $tugas->id), [
            'files' => [$file1, $file2],
        ]);

        $response->assertRedirect(route('siswa.tugas.show', $tugas->id));
        $response->assertSessionHas('status');

        $pengumpulan = PengumpulanTugas::where('tugas_id', $tugas->id)
            ->where('siswa_id', $this->siswa1->id)
            ->first();

        $this->assertNotNull($pengumpulan);
        $this->assertEquals('Terkirim', $pengumpulan->status_pengajuan);
        $this->assertCount(2, $pengumpulan->files);

        // Akses halaman detail siswa dan pastikan tabel status akademik tampil rapi
        $showResponse = $this->actingAs($this->siswa1)->get(route('siswa.tugas.show', $tugas->id));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Terkirim dan siap dinilai');
        $showResponse->assertSee('kelompok12_01a.cpp');
        $showResponse->assertSee('laporan.pdf');
        $showResponse->assertSee('Penugasan diajukan');
    }

    public function test_siswa_cannot_submit_disallowed_extension_or_exceed_deadline(): void
    {
        // Tugas yang tidak mengizinkan pengumpulan terlambat dan sudah lewat deadline
        $tugas = Tugas::create([
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelInformatika->id,
            'guru_id' => $this->guru1->id,
            'judul' => 'Tugas Kedaluwarsa',
            'deskripsi_html' => '<p>Sudah lewat</p>',
            'waktu_buka' => now()->subDays(5),
            'batas_waktu' => now()->subDay(),
            'tipe_file_diizinkan' => ['pdf'],
            'izinkan_terlambat' => false,
        ]);

        $file = UploadedFile::fake()->create('jawaban.pdf', 100);

        $response = $this->actingAs($this->siswa1)->post(route('siswa.tugas.submit', $tugas->id), [
            'files' => [$file],
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('pengumpulan_tugas', ['tugas_id' => $tugas->id]);
    }

    public function test_guru_can_grade_submission_with_score_and_feedback(): void
    {
        $tugas = Tugas::create([
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelInformatika->id,
            'guru_id' => $this->guru1->id,
            'judul' => 'Tugas Uji Penilaian',
            'deskripsi_html' => '<p>Uji Skor</p>',
            'waktu_buka' => now()->subDay(),
            'batas_waktu' => now()->addDays(2),
            'tipe_file_diizinkan' => ['pdf'],
        ]);

        $pengumpulan = PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $this->siswa1->id,
            'status_pengajuan' => 'Terkirim',
            'status_penilaian' => 'Belum dinilai',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->guru1)->postJson(route('guru.tugas.grade', $pengumpulan->id), [
            'nilai' => 96.50,
            'feedback_guru' => 'Analisis algoritma sangat rapi dan efisien. Pertahankan!',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $pengumpulan->refresh();
        $this->assertEquals('Sudah dinilai', $pengumpulan->status_penilaian);
        $this->assertEquals(96.50, $pengumpulan->nilai);
        $this->assertStringContainsString('Analisis algoritma', $pengumpulan->feedback_guru);
    }

    public function test_guru_and_siswa_can_exchange_comments(): void
    {
        $tugas = Tugas::create([
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelInformatika->id,
            'guru_id' => $this->guru1->id,
            'judul' => 'Tugas Diskusi Komentar',
            'deskripsi_html' => '<p>Diskusi</p>',
            'waktu_buka' => now()->subDay(),
            'batas_waktu' => now()->addDays(2),
            'tipe_file_diizinkan' => ['pdf'],
        ]);

        $pengumpulan = PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $this->siswa1->id,
            'status_pengajuan' => 'Terkirim',
            'submitted_at' => now(),
        ]);

        // Siswa mengirim komentar
        $siswaComment = $this->actingAs($this->siswa1)->postJson(route('siswa.tugas.comment', $pengumpulan->id), [
            'komentar' => 'Ibu Guru, saya menambahkan penjelasan variabel di halaman 3.',
        ]);
        $siswaComment->assertStatus(200);

        // Guru membalas komentar
        $guruComment = $this->actingAs($this->guru1)->postJson(route('guru.tugas.comment', $pengumpulan->id), [
            'komentar' => 'Baik Alvino, penjelasan sudah ibu periksa dan sangat jelas.',
        ]);
        $guruComment->assertStatus(200);

        $komentars = KomentarTugas::where('pengumpulan_tugas_id', $pengumpulan->id)->get();
        $this->assertCount(2, $komentars);
    }

    public function test_guru_magic_features_download_zip_and_export_csv(): void
    {
        $tugas = Tugas::create([
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelInformatika->id,
            'guru_id' => $this->guru1->id,
            'judul' => 'Tugas Uji Magic Download',
            'deskripsi_html' => '<p>Magic</p>',
            'waktu_buka' => now()->subDay(),
            'batas_waktu' => now()->addDays(3),
            'tipe_file_diizinkan' => ['pdf'],
        ]);

        $pengumpulan = PengumpulanTugas::create([
            'tugas_id' => $tugas->id,
            'siswa_id' => $this->siswa1->id,
            'status_pengajuan' => 'Terkirim',
            'status_penilaian' => 'Sudah dinilai',
            'nilai' => 90.0,
            'submitted_at' => now(),
        ]);

        $fileDummy = UploadedFile::fake()->create('tugas_alvino.pdf', 100);
        $storedPath = $fileDummy->store('tugas_submissions', 'public');

        PengumpulanFile::create([
            'pengumpulan_tugas_id' => $pengumpulan->id,
            'nama_file_asli' => 'tugas_alvino.pdf',
            'file_path' => $storedPath,
            'ukuran_kb' => 100,
            'ekstensi' => 'pdf',
        ]);

        // 1. Uji Magic Feature: Download Semua Berkas (ZIP)
        $responseZip = $this->actingAs($this->guru1)->get(route('guru.tugas.download-zip', $tugas->id));
        $responseZip->assertStatus(200);
        $this->assertEquals('application/zip', $responseZip->headers->get('content-type'));

        // 2. Uji Magic Feature: Export Rekap Nilai (CSV)
        $responseCsv = $this->actingAs($this->guru1)->get(route('guru.tugas.export-csv', $tugas->id));
        $responseCsv->assertStatus(200);
        $this->assertStringContainsString('text/csv', $responseCsv->headers->get('content-type'));
    }
}
