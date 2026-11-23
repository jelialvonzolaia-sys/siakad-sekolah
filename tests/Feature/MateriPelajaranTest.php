<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MateriPelajaranTest extends TestCase
{
    use RefreshDatabase;

    private User $guru1;
    private User $guru2;
    private User $siswa1;
    private User $siswa2;
    private Kelas $kelas1;
    private Kelas $kelas2;
    private MataPelajaran $mapelMat;
    private MataPelajaran $mapelFis;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->guru1 = User::factory()->create([
            'name' => 'Dra. Rosmina Zebua, M.Pd',
            'role' => 'guru',
            'nis_nip' => '197903122005012003',
        ]);

        $this->guru2 = User::factory()->create([
            'name' => 'Berkat Daeli, S.Pd',
            'role' => 'guru',
            'nis_nip' => '198805152011011004',
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
            'nis_nip' => '260034',
            'kelas_id' => $this->kelas2->id,
        ]);

        $this->mapelMat = MataPelajaran::create([
            'kode_mapel' => 'MAT',
            'nama_mapel' => 'Matematika Peminatan',
        ]);

        $this->mapelFis = MataPelajaran::create([
            'kode_mapel' => 'FIS',
            'nama_mapel' => 'Fisika Terapan',
        ]);

        // Jadwal
        Jadwal::create([
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelMat->id,
            'guru_id' => $this->guru1->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:30:00',
            'jam_selesai' => '09:00:00',
        ]);

        Jadwal::create([
            'kelas_id' => $this->kelas2->id,
            'mapel_id' => $this->mapelFis->id,
            'guru_id' => $this->guru2->id,
            'hari' => 'Selasa',
            'jam_mulai' => '09:15:00',
            'jam_selesai' => '10:45:00',
        ]);
    }

    public function test_guru_can_view_materi_list_with_only_their_own_materials(): void
    {
        // Materi guru 1
        $materi1 = Materi::create([
            'mapel_id' => $this->mapelMat->id,
            'guru_id' => $this->guru1->id,
            'kelas_id' => $this->kelas1->id,
            'judul' => 'Persamaan Nilai Mutlak Dasar',
            'deskripsi' => 'Modul konsep nilai mutlak',
            'file_path' => 'materi/nilai_mutlak.pdf',
        ]);

        // Materi guru 2
        $materi2 = Materi::create([
            'mapel_id' => $this->mapelFis->id,
            'guru_id' => $this->guru2->id,
            'kelas_id' => $this->kelas2->id,
            'judul' => 'Kinematika Gerak Lurus',
            'deskripsi' => 'Panduan GLB dan GLBB',
            'file_path' => 'materi/kinematika.pdf',
        ]);

        $response = $this->actingAs($this->guru1)->get(route('guru.materi.index'));

        $response->assertStatus(200);
        $response->assertSee('Persamaan Nilai Mutlak Dasar');
        $response->assertDontSee('Kinematika Gerak Lurus');
    }

    public function test_guru_can_access_create_materi_page(): void
    {
        $response = $this->actingAs($this->guru1)->get(route('guru.materi.create'));

        $response->assertStatus(200);
        $response->assertSee('Unggah Materi Pembelajaran Baru');
        $response->assertSee('X MIPA 1');
        $response->assertSee('Matematika Peminatan');
    }

    public function test_guru_can_upload_new_materi_file_and_it_is_stored_in_storage(): void
    {
        $file = UploadedFile::fake()->create('modul_trigonometri.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->guru1)->post(route('guru.materi.store'), [
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelMat->id,
            'judul' => 'Trigonometri dan Sudut Berelasi',
            'deskripsi' => 'Rangkuman lengkap sudut istimewa kuadran I-IV',
            'file_materi' => $file,
        ]);

        $response->assertRedirect(route('guru.materi.index'));
        $response->assertSessionHas('status');

        $materi = Materi::where('judul', 'Trigonometri dan Sudut Berelasi')->first();
        $this->assertNotNull($materi);
        $this->assertEquals($this->guru1->id, $materi->guru_id);
        $this->assertEquals($this->kelas1->id, $materi->kelas_id);
        $this->assertEquals($this->mapelMat->id, $materi->mapel_id);

        Storage::disk('public')->assertExists($materi->file_path);
    }

    public function test_guru_upload_validation_rejects_invalid_file_type(): void
    {
        $invalidFile = UploadedFile::fake()->create('virus.exe', 500, 'application/octet-stream');

        $response = $this->actingAs($this->guru1)->post(route('guru.materi.store'), [
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelMat->id,
            'judul' => 'Judul Uji',
            'file_materi' => $invalidFile,
        ]);

        $response->assertSessionHasErrors('file_materi');
    }

    public function test_guru_can_edit_and_update_materi_with_optional_file_replacement(): void
    {
        $oldFile = UploadedFile::fake()->create('modul_lama.pdf', 500, 'application/pdf');
        $oldPath = $oldFile->store('materi', 'public');

        $materi = Materi::create([
            'mapel_id' => $this->mapelMat->id,
            'guru_id' => $this->guru1->id,
            'kelas_id' => $this->kelas1->id,
            'judul' => 'Judul Versi Awal',
            'deskripsi' => 'Deskripsi versi awal',
            'file_path' => $oldPath,
        ]);

        Storage::disk('public')->assertExists($oldPath);

        // Edit dengan unggah file baru
        $newFile = UploadedFile::fake()->create('modul_revisi.docx', 800, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($this->guru1)->put(route('guru.materi.update', $materi->id), [
            'kelas_id' => $this->kelas1->id,
            'mapel_id' => $this->mapelMat->id,
            'judul' => 'Judul Revisi Final',
            'deskripsi' => 'Deskripsi materi yang telah direvisi',
            'file_materi' => $newFile,
        ]);

        $response->assertRedirect(route('guru.materi.index'));

        $materi->refresh();
        $this->assertEquals('Judul Revisi Final', $materi->judul);
        $this->assertEquals('Deskripsi materi yang telah direvisi', $materi->deskripsi);

        // File lama terhapus dan file baru tersimpan
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($materi->file_path);
    }

    public function test_guru_can_delete_materi_and_physical_file_is_deleted_from_storage(): void
    {
        $file = UploadedFile::fake()->create('modul_hapus.pptx', 600);
        $filePath = $file->store('materi', 'public');

        $materi = Materi::create([
            'mapel_id' => $this->mapelMat->id,
            'guru_id' => $this->guru1->id,
            'kelas_id' => $this->kelas1->id,
            'judul' => 'Materi yang Akan Dihapus',
            'file_path' => $filePath,
        ]);

        Storage::disk('public')->assertExists($filePath);

        $response = $this->actingAs($this->guru1)->delete(route('guru.materi.destroy', $materi->id));

        $response->assertRedirect(route('guru.materi.index'));
        $this->assertDatabaseMissing('materis', ['id' => $materi->id]);
        Storage::disk('public')->assertMissing($filePath);
    }

    public function test_guru_cannot_edit_or_delete_materi_belonging_to_another_guru(): void
    {
        $materi = Materi::create([
            'mapel_id' => $this->mapelFis->id,
            'guru_id' => $this->guru2->id,
            'kelas_id' => $this->kelas2->id,
            'judul' => 'Materi Milik Guru 2',
            'file_path' => 'materi/guru2.pdf',
        ]);

        // Guru 1 mencoba edit materi Guru 2 -> 403 Forbidden
        $responseEdit = $this->actingAs($this->guru1)->get(route('guru.materi.edit', $materi->id));
        $responseEdit->assertStatus(403);

        $responseDelete = $this->actingAs($this->guru1)->delete(route('guru.materi.destroy', $materi->id));
        $responseDelete->assertStatus(403);
    }

    public function test_siswa_can_view_materi_filtered_by_their_class(): void
    {
        // Materi untuk Kelas X MIPA 1 (Siswa 1)
        Materi::create([
            'mapel_id' => $this->mapelMat->id,
            'guru_id' => $this->guru1->id,
            'kelas_id' => $this->kelas1->id,
            'judul' => 'Materi Khusus Kelas X MIPA 1',
            'deskripsi' => 'Rangkuman Aljabar',
            'file_path' => 'materi/aljabar.pdf',
        ]);

        // Materi untuk Kelas XI MIPA 1 (Siswa 2)
        Materi::create([
            'mapel_id' => $this->mapelFis->id,
            'guru_id' => $this->guru2->id,
            'kelas_id' => $this->kelas2->id,
            'judul' => 'Materi Khusus Kelas XI MIPA 1',
            'deskripsi' => 'Termodinamika',
            'file_path' => 'materi/termo.pdf',
        ]);

        $response = $this->actingAs($this->siswa1)->get(route('siswa.materi.index'));

        $response->assertStatus(200);
        $response->assertSee('Materi Khusus Kelas X MIPA 1');
        $response->assertDontSee('Materi Khusus Kelas XI MIPA 1');
    }

    public function test_siswa_cannot_download_materi_for_another_class(): void
    {
        $file = UploadedFile::fake()->create('rahasia_xi.pdf', 300);
        $filePath = $file->store('materi', 'public');

        $materiKelas2 = Materi::create([
            'mapel_id' => $this->mapelFis->id,
            'guru_id' => $this->guru2->id,
            'kelas_id' => $this->kelas2->id,
            'judul' => 'Materi Khusus Kelas XI',
            'file_path' => $filePath,
        ]);

        // Siswa 1 (Kelas X) mencoba mengunduh materi Kelas XI -> 403 Forbidden
        $response = $this->actingAs($this->siswa1)->get(route('siswa.materi.download', $materiKelas2->id));
        $response->assertStatus(403);
    }
}
