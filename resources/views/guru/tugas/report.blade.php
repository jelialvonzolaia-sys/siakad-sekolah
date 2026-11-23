@extends('layouts.siakad')

@php
    $pageTitle = 'Rekap & Penilaian Tugas';
    $pageSubtitle = $tugas->judul;
@endphp

@section('content')
<div class="space-y-5" x-data="{
    showGradeModal: false,
    selectedStudent: null,
    currentSubmissionId: null,
    currentNilai: '',
    currentFeedback: '',
    currentFiles: [],
    currentComments: [],
    activePreviewFile: null,
    newCommentText: '',

    openGradeModal(student, submission) {
        this.selectedStudent = student;
        this.currentSubmissionId = submission ? submission.id : null;
        this.currentNilai = submission && submission.nilai !== null ? submission.nilai : '';
        this.currentFeedback = submission && submission.feedback_guru ? submission.feedback_guru : '';
        this.currentFiles = submission && submission.files ? submission.files : [];
        this.currentComments = submission && submission.komentars ? submission.komentars : [];
        this.activePreviewFile = this.currentFiles.length > 0 ? this.currentFiles[0] : null;
        this.newCommentText = '';
        this.showGradeModal = true;
    },

    submitGrade() {
        if (!this.currentSubmissionId) return;
        fetch(`/guru/tugas/pengumpulan/${this.currentSubmissionId}/grade`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                nilai: this.currentNilai,
                feedback_guru: this.currentFeedback
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.showToast('success', 'Nilai dan catatan evaluasi berhasil disimpan.', 'Penilaian Tersimpan');
                setTimeout(() => window.location.reload(), 900);
            } else {
                window.showToast('error', 'Gagal menyimpan nilai.', 'Gagal');
            }
        })
        .catch(err => {
            console.error(err);
            window.showToast('error', 'Terjadi kesalahan jaringan.', 'Kesalahan Jaringan');
        });
    },

    sendComment() {
        if (!this.newCommentText.trim() || !this.currentSubmissionId) return;
        fetch(`/guru/tugas/pengumpulan/${this.currentSubmissionId}/comment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                komentar: this.newCommentText
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.currentComments.push(data.komentar);
                this.newCommentText = '';
            }
        });
    }
}">

    <!-- Flash Status Notification -->
    @if (session('status'))
        <div class="p-3.5 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] text-[#146C43] text-xs font-medium flex items-center gap-2.5">
            <span class="material-symbols-outlined text-[#1E9E63] text-[19px]">check_circle</span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="p-3.5 rounded-lg bg-red-50 border border-red-200 text-red-800 text-xs font-medium flex items-center gap-2.5">
            <span class="material-symbols-outlined text-red-600 text-[19px]">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header Section & Tombol Magic -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-5 rounded-lg bg-white border border-[#E5E7EB]">
        <div class="flex items-start gap-3.5">
            <a href="{{ route('guru.tugas.index') }}" class="p-2 rounded-md bg-[#F9FAFB] border border-[#E5E7EB] hover:bg-[#F3F4F6] text-[#4B5563] transition-colors mt-0.5">
                <span class="material-symbols-outlined text-[19px]">arrow_back</span>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#F9FAFB] border border-[#D1D5DB] text-[#1F2937]">
                        {{ $tugas->kelas->nama_kelas }}
                    </span>
                    <span class="text-xs text-[#9CA3AF]">•</span>
                    <span class="text-xs font-medium text-[#4B5563]">{{ $tugas->mataPelajaran->nama_mapel }}</span>
                    <span class="text-xs text-[#9CA3AF]">•</span>
                    @if ($tugas->isLewatBatas())
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-[#F3F4F6] text-[#6B7280] border border-[#E5E7EB]">
                            Jatuh Tempo Berakhir
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#1E9E63]"></span>
                            <span>Sedang Berjalan</span>
                        </span>
                    @endif
                </div>
                <h2 class="text-lg font-heading font-bold text-[#1F2937] mt-1">
                    {{ $tugas->judul }}
                </h2>
                <p class="text-xs text-[#6B7280] mt-0.5">
                    Batas Waktu: <strong class="text-[#1F2937]">{{ $tugas->batas_waktu->translatedFormat('l, d F Y, H:i') }} WIB</strong>
                    @if ($tugas->lampiran_guru)
                        • <a href="{{ Storage::url($tugas->lampiran_guru) }}" target="_blank" class="text-[#1E9E63] font-semibold hover:underline inline-flex items-center gap-0.5"><span class="material-symbols-outlined text-[14px]">attachment</span> Berkas Soal Guru</a>
                    @endif
                </p>
            </div>
        </div>

        <!-- 2 Tombol Magic: Download ZIP & Export CSV -->
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('guru.tugas.download-zip', $tugas->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer" title="Unduh seluruh file siswa dalam format ZIP teratur">
                <span class="material-symbols-outlined text-[17px]">folder_zip</span>
                <span>Download Semua Berkas (ZIP)</span>
            </a>

            <a href="{{ route('guru.tugas.export-csv', $tugas->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md bg-white border border-[#D1D5DB] hover:bg-[#F9FAFB] text-[#1F2937] text-xs font-semibold transition-colors cursor-pointer" title="Ekspor daftar nilai ke format spreadsheet CSV">
                <span class="material-symbols-outlined text-[17px] text-[#1E9E63]">table_view</span>
                <span>Export Rekap Nilai (CSV)</span>
            </a>
        </div>
    </div>

    <!-- 4 Kartu Statistik Ringkas -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg p-4 border border-[#E5E7EB] flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Total Siswa Kelas</p>
                <p class="text-2xl font-heading font-bold text-[#1F2937] mt-0.5">{{ $totalSiswa }}</p>
                <p class="text-[11px] text-[#6B7280] mt-0.5">Rombel {{ $tugas->kelas->nama_kelas }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#F9FAFB] border border-[#E5E7EB] text-[#4B5563] flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">group</span>
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 border border-[#E5E7EB] flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Sudah Mengumpulkan</p>
                <p class="text-2xl font-heading font-bold text-[#146C43] mt-0.5">{{ $totalKumpul }}</p>
                <p class="text-[11px] text-[#1E9E63] mt-0.5">{{ $totalSiswa > 0 ? round(($totalKumpul/$totalSiswa)*100) : 0 }}% partisipasi</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] text-[#1E9E63] flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">task_alt</span>
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 border border-[#E5E7EB] flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Belum Mengumpulkan</p>
                <p class="text-2xl font-heading font-bold text-[#B91C1C] mt-0.5">{{ $totalSiswa - $totalKumpul }}</p>
                <p class="text-[11px] text-[#B91C1C] mt-0.5">Perlu tindak lanjut</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#FEF2F2] border border-[#FECACA] text-[#B91C1C] flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">pending_actions</span>
            </div>
        </div>

        <div class="bg-white rounded-lg p-4 border border-[#E5E7EB] flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Lewat Jatuh Tempo</p>
                <p class="text-2xl font-heading font-bold text-[#B45309] mt-0.5">{{ $totalTerlambat }}</p>
                <p class="text-[11px] text-[#B45309] mt-0.5">{{ $totalDinilai }} berkas dinilai</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#FFFBEB] border border-[#FDE68A] text-[#B45309] flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">history_toggle_off</span>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Seluruh Siswa & Status Pengumpulan -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between bg-[#F9FAFB]">
            <div>
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Daftar Pengajuan Siswa</h3>
                <p class="text-xs text-[#6B7280] mt-0.5">Periksa berkas jawaban siswa, berikan nilai, feedback, dan diskusikan pengumpulan.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#374151] border-collapse">
                <thead class="bg-[#F9FAFB] text-[#6B7280] font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4 w-28">NIS</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4 w-36">Status Pengajuan</th>
                        <th class="py-3 px-4 min-w-[200px]">Waktu Kumpul &amp; Selisih</th>
                        <th class="py-3 px-4">Berkas Siswa</th>
                        <th class="py-3 px-4 w-24 text-center">Nilai</th>
                        <th class="py-3 px-4 w-32 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse ($daftarSiswa as $index => $siswa)
                        @php
                            $sub = $pengumpulanMap->get($siswa->id);
                            $isSubmitted = $sub && $sub->status_pengajuan === 'Terkirim';
                            $isGraded = $sub && $sub->status_penilaian === 'Sudah dinilai';
                            $isTerlambat = $sub && $sub->isTerlambat();
                        @endphp
                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                            <td class="py-3 px-4 text-center text-[#6B7280]">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-3 px-4 text-[#6B7280]">
                                {{ $siswa->nis_nip ?? '-' }}
                            </td>
                            <td class="py-3 px-4 font-medium text-[#1F2937]">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-[#F4F6F8] border border-[#E5E7EB] text-[#4B5563] flex items-center justify-center font-semibold text-[10px]">
                                        {{ strtoupper(substr($siswa->name, 0, 2)) }}
                                    </div>
                                    <span>{{ $siswa->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($isSubmitted)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#1E9E63]"></span>
                                        <span>Terkirim</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#F3F4F6] text-[#6B7280]">
                                        Belum Kumpul
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-xs">
                                @if ($isSubmitted && $sub->submitted_at)
                                    <p class="font-medium text-[#1F2937]">{{ $sub->submitted_at->translatedFormat('d M Y, H:i') }}</p>
                                    <p class="text-[11px] font-semibold {{ $isTerlambat ? 'text-red-600' : 'text-[#146C43]' }}">
                                        {{ $sub->selisih_waktu_label }}
                                    </p>
                                @else
                                    <span class="text-[#9CA3AF] italic text-[11px]">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if ($isSubmitted && $sub->files->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5 max-w-xs">
                                        @foreach ($sub->files as $f)
                                            <a href="{{ Storage::url($f->file_path) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-[#F9FAFB] hover:bg-[#E8F5EE] text-[#374151] border border-[#E5E7EB] text-[10px] font-medium transition-colors" title="{{ $f->nama_file_asli }} ({{ $f->ukuran_formatted }})">
                                                <span class="material-symbols-outlined text-[12px] text-[#1E9E63]">description</span>
                                                <span class="break-words max-w-[120px]">{{ $f->nama_file_asli }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-[#9CA3AF] text-[11px] italic">Tidak ada berkas</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if ($isGraded)
                                    <span class="inline-flex items-center justify-center font-heading font-bold text-xs px-2.5 py-1 rounded bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                                        {{ $sub->nilai }}
                                    </span>
                                @else
                                    <span class="text-[#9CA3AF] text-[11px] ">--</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                @if ($isSubmitted)
                                    <button type="button" @click="openGradeModal({{ json_encode($siswa) }}, {{ json_encode($sub) }})" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                                        <span class="material-symbols-outlined text-[15px]">rate_review</span>
                                        <span>Periksa &amp; Nilai</span>
                                    </button>
                                @else
                                    <button type="button" disabled class="px-2.5 py-1.5 rounded-md bg-[#F3F4F6] text-[#9CA3AF] text-xs font-medium cursor-not-allowed">
                                        Belum Ada
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-[#6B7280]">
                                Belum ada siswa yang terdaftar pada rombel kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL / PANEL QUICK GRADING (PENILAIAN CEPAT) -->
    <div x-show="showGradeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-900/50 transition-opacity duration-200" role="dialog" aria-modal="true">
        <div @click.away="showGradeModal = false" class="bg-white w-full max-w-4xl rounded-lg shadow-xl border border-[#E5E7EB] overflow-hidden max-h-[90vh] flex flex-col">
            <!-- Header Modal -->
            <div class="bg-[#F9FAFB] px-6 py-4 flex items-center justify-between border-b border-[#E5E7EB]">
                <div class="flex items-center gap-3">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#1E9E63]"></span>
                    <div>
                        <h3 class="font-heading font-semibold text-sm sm:text-base text-[#1F2937] leading-tight">
                            Evaluasi Jawaban: <span x-text="selectedStudent ? selectedStudent.name : ''"></span>
                        </h3>
                        <p class="text-[11px] text-[#6B7280] ">
                            NIS: <span x-text="selectedStudent ? selectedStudent.nis_nip : ''"></span> • SMAN 1 Gunungsitoli
                        </p>
                    </div>
                </div>
                <button @click="showGradeModal = false" class="text-[#6B7280] hover:text-[#1F2937] p-1.5 rounded-md hover:bg-[#E5E7EB]/60 cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <!-- Konten Dua Kolom: Kiri Preview Berkas, Kanan Form Nilai & Komentar -->
            <div class="flex-1 grid grid-cols-1 md:grid-cols-12 overflow-y-auto divide-y md:divide-y-0 md:divide-x divide-[#E5E7EB]">
                <!-- Kolom Kiri (7 Kolom): Berkas Siswa & Preview -->
                <div class="md:col-span-7 p-6 space-y-4">
                    <h4 class="font-heading font-semibold text-xs text-[#1F2937] uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-[#1E9E63]">attach_file</span>
                        <span>Daftar Berkas Jawaban Siswa</span>
                    </h4>

                    <!-- Tab Berkas Jawaban Siswa -->
                    <div class="flex flex-wrap gap-2">
                        <template x-for="file in currentFiles" :key="file.id">
                            <button type="button" @click="activePreviewFile = file" :class="activePreviewFile && activePreviewFile.id === file.id ? 'bg-[#1E9E63] text-white border-[#1E9E63] font-semibold' : 'bg-[#F9FAFB] text-[#374151] hover:bg-[#F3F4F6] border-[#E5E7EB]'" class="px-3 py-1.5 rounded-md text-xs border flex items-center gap-1.5 transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-[15px]">description</span>
                                <span x-text="file.nama_file_asli" class="break-words max-w-[140px]"></span>
                            </button>
                        </template>
                    </div>

                    <!-- Area Preview Berkas -->
                    <div class="rounded-lg border border-[#E5E7EB] bg-[#F9FAFB] p-4 min-h-[300px] flex flex-col justify-between">
                        <template x-if="activePreviewFile">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between pb-2.5 border-b border-[#E5E7EB] text-xs">
                                    <div>
                                        <p class="font-semibold text-[#1F2937]" x-text="activePreviewFile.nama_file_asli"></p>
                                        <p class="text-[11px] text-[#6B7280] " x-text="'Ekstensi: .' + activePreviewFile.ekstensi + ' • Ukuran: ' + activePreviewFile.ukuran_kb + ' KB'"></p>
                                    </div>
                                    <a :href="'/storage/' + activePreviewFile.file_path" target="_blank" class="px-3 py-1.5 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white font-semibold text-xs inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                        <span>Buka / Unduh</span>
                                    </a>
                                </div>

                                <!-- Preview Frame jika PDF atau Gambar -->
                                <template x-if="activePreviewFile.ekstensi === 'pdf'">
                                    <iframe :src="'/storage/' + activePreviewFile.file_path" class="w-full h-72 rounded-md border border-[#E5E7EB] bg-white"></iframe>
                                </template>
                                <template x-if="['jpg', 'jpeg', 'png'].includes(activePreviewFile.ekstensi)">
                                    <div class="max-h-72 flex items-center justify-center overflow-hidden rounded-md bg-white border border-[#E5E7EB] p-2">
                                        <img :src="'/storage/' + activePreviewFile.file_path" alt="Preview Gambar" class="max-h-72 object-contain">
                                    </div>
                                </template>
                                <template x-if="!['pdf', 'jpg', 'jpeg', 'png'].includes(activePreviewFile.ekstensi)">
                                    <div class="p-8 text-center space-y-2">
                                        <span class="material-symbols-outlined text-[40px] text-[#9CA3AF]">file_download</span>
                                        <p class="text-xs text-[#4B5563] font-medium">Pratinjau otomatis langsung di halaman ini tidak didukung untuk tipe file ini.</p>
                                        <a :href="'/storage/' + activePreviewFile.file_path" target="_blank" class="text-xs font-semibold text-[#1E9E63] hover:underline">
                                            Klik untuk mengunduh dan memeriksa berkas secara manual
                                        </a>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!activePreviewFile">
                            <div class="p-12 text-center text-[#6B7280] text-xs">
                                Tidak ada berkas yang dipilih.
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Kolom Kanan (5 Kolom): Input Skor, Feedback Guru & Komentar Diskusi -->
                <div class="md:col-span-5 p-6 flex flex-col justify-between space-y-5 bg-white">
                    <div class="space-y-4">
                        <h4 class="font-heading font-semibold text-xs text-[#1F2937] uppercase tracking-wider flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-[#1E9E63]">grade</span>
                            <span>Penilaian Guru</span>
                        </h4>

                        <!-- Input Nilai 0 - 100 -->
                        <div>
                            <label class="block text-xs font-semibold text-[#374151] mb-1">
                                Nilai Akhir (Skala 0 - 100) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" min="0" max="100" x-model="currentNilai" placeholder="Contoh: 95.5" class="w-full text-sm font-heading font-bold px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-[#1F2937]">
                        </div>

                        <!-- Feedback Guru -->
                        <div>
                            <label class="block text-xs font-semibold text-[#374151] mb-1">
                                Catatan / Feedback Evaluasi
                            </label>
                            <textarea rows="3" x-model="currentFeedback" placeholder="Tuliskan evaluasi pemahaman, catatan perbaikan, atau apresiasi..." class="w-full text-xs px-3.5 py-2 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-[#1F2937]"></textarea>
                        </div>

                        <!-- Diskusi Komentar Tugas -->
                        <div class="border-t border-[#E5E7EB] pt-3 space-y-2">
                            <label class="block text-xs font-semibold text-[#374151]">
                                Diskusi Komentar Pengajuan (<span x-text="currentComments.length"></span>)
                            </label>
                            <div class="max-h-36 overflow-y-auto space-y-2 p-2.5 rounded-lg bg-[#F9FAFB] border border-[#E5E7EB] text-xs">
                                <template x-for="c in currentComments" :key="c.id">
                                    <div class="p-2 rounded bg-white border border-[#E5E7EB] space-y-0.5">
                                        <div class="flex items-center justify-between text-[10px]">
                                            <span class="font-semibold text-[#1F2937]" x-text="c.user ? c.user.name : c.user_name"></span>
                                            <span class="text-[#6B7280]" x-text="c.time || c.formatted_time || ''"></span>
                                        </div>
                                        <p class="text-[#374151] text-[11px]" x-text="c.komentar || c.text"></p>
                                    </div>
                                </template>
                                <template x-if="currentComments.length === 0">
                                    <p class="text-[11px] text-[#9CA3AF] italic text-center py-2">Belum ada komentar.</p>
                                </template>
                            </div>

                            <div class="flex gap-2">
                                <input type="text" x-model="newCommentText" @keydown.enter.prevent="sendComment()" placeholder="Tulis komentar/balasan..." class="w-full text-xs px-3 py-1.5 rounded-md border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63]">
                                <button type="button" @click="sendComment()" class="px-3 py-1.5 bg-[#1E9E63] hover:bg-[#168251] text-white rounded-md text-xs font-semibold cursor-pointer">
                                    Kirim
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Simpan Nilai -->
                    <div class="pt-4 border-t border-[#E5E7EB] flex items-center justify-end gap-2">
                        <button type="button" @click="showGradeModal = false" class="px-4 py-2 rounded-md border border-[#D1D5DB] text-[#374151] text-xs font-medium hover:bg-[#F9FAFB] cursor-pointer">
                            Batal
                        </button>
                        <button type="button" @click="submitGrade()" class="px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold flex items-center gap-1.5 cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">save</span>
                            <span>Simpan Nilai &amp; Feedback</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
