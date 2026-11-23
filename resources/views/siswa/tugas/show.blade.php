@extends('layouts.siakad')

@php
    $pageTitle = 'Detail Tugas';
    $pageSubtitle = $tugas->judul;
@endphp

@section('content')
@php
    $isSubmitted = $pengumpulan && $pengumpulan->status_pengajuan === 'Terkirim';
    $isGraded = $pengumpulan && $pengumpulan->status_penilaian === 'Sudah dinilai';
    $bisaKumpul = $tugas->bisaKumpul();
    $bisaEdit = $bisaKumpul && !$isGraded;
    $allowedExts = array_map('strtolower', $tugas->tipe_file_diizinkan ?? []);
    $maxSizeMb = $tugas->max_ukuran_mb;
    $maxFiles = $tugas->max_jumlah_file;
    $existingFilesCount = $pengumpulan ? $pengumpulan->files->count() : 0;
    $slotSisa = max(0, $maxFiles - $existingFilesCount);
@endphp

<div class="space-y-5" x-data="{
    isEditing: {{ $isSubmitted ? 'false' : 'true' }},
    showCommentsAccordion: false,
    selectedFiles: [],
    dragOver: false,
    allowedExts: {{ json_encode($allowedExts) }},
    maxSizeMb: {{ $maxSizeMb }},
    maxFiles: {{ $slotSisa }},
    errorMessage: '',
    commentInput: '',
    commentsList: {{ json_encode($pengumpulan ? $pengumpulan->komentars->map(fn($c) => ['id' => $c->id, 'user_name' => $c->user->name, 'user_role' => $c->user->role, 'text' => $c->komentar, 'time' => $c->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB']) : []) }},

    handleFiles(fileList) {
        this.errorMessage = '';
        const incoming = Array.from(fileList);

        if (this.selectedFiles.length + incoming.length > this.maxFiles) {
            this.errorMessage = `Maksimal hanya dapat memilih ${this.maxFiles} berkas lagi.`;
            return;
        }

        for (let file of incoming) {
            const ext = file.name.split('.').pop().toLowerCase();
            if (!this.allowedExts.includes(ext)) {
                this.errorMessage = `Ekstensi berkas .${ext} tidak diizinkan. Ekstensi yang diperbolehkan: ${this.allowedExts.join(', ')}`;
                return;
            }
            if (file.size > this.maxSizeMb * 1024 * 1024) {
                this.errorMessage = `Ukuran berkas ${file.name} melebihi batas maksimal ${this.maxSizeMb} MB.`;
                return;
            }
            this.selectedFiles.push(file);
        }
    },

    removeSelectedFile(index) {
        this.selectedFiles.splice(index, 1);
    },

    submitComment() {
        if (!this.commentInput.trim()) return;
        @if ($pengumpulan)
        fetch('{{ route('siswa.tugas.comment', $pengumpulan->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ komentar: this.commentInput })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                this.commentsList.push(data.komentar);
                this.commentInput = '';
            }
        });
        @endif
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

    <!-- Tombol Navigasi Kembali -->
    <a href="{{ route('siswa.tugas.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-[#6B7280] hover:text-[#1F2937] transition-colors">
        <span class="material-symbols-outlined text-[17px]">arrow_back</span>
        <span>Kembali ke Daftar Tugas Siswa</span>
    </a>

    <!-- 1. KARTU INFORMASI UTAMA & INSTRUKSI RICH TEXT TUGAS -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-6 space-y-5">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#F9FAFB] text-[#374151] border border-[#E5E7EB]">
                    {{ $tugas->mataPelajaran->nama_mapel }}
                </span>
                <span class="text-xs text-[#9CA3AF]">•</span>
                <span class="text-xs text-[#6B7280] font-medium">Kelas {{ $tugas->kelas->nama_kelas }}</span>
            </div>
            <h1 class="text-lg sm:text-xl font-heading font-bold text-[#1F2937] tracking-tight mt-1.5">
                {{ $tugas->judul }}
            </h1>
            <p class="text-xs text-[#6B7280] mt-1">
                Guru Pengampu: <strong class="text-[#1F2937]">{{ $tugas->guru->name }}</strong> (NIP: {{ $tugas->guru->nis_nip ?? '-' }}) • SMA Negeri 1 Gunungsitoli
            </p>
        </div>

        <!-- Wadah Teks Instruksi Rich Text -->
        <div class="pt-4 border-t border-[#E5E7EB]">
            <h3 class="text-xs font-semibold text-[#374151] uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-[#1E9E63]">subject</span>
                <span>Petunjuk &amp; Instruksi Pengerjaan Tugas</span>
            </h3>
            <div class="prose prose-sm max-w-none text-[#1F2937] leading-relaxed p-4 rounded-lg bg-[#F9FAFB] border border-[#E5E7EB]">
                {!! $tugas->deskripsi_html !!}
            </div>
        </div>

        <!-- Lampiran Berkas dari Guru (Jika Ada) -->
        @if ($tugas->lampiran_guru)
            <div class="p-4 rounded-lg bg-[#E8F5EE]/50 border border-[#BBF7D0] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-md bg-white border border-[#BBF7D0] text-[#1E9E63] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[19px]">attachment</span>
                    </div>
                    <div>
                        <p class="font-semibold text-[#1F2937]">Berkas Lampiran Panduan / Soal dari Guru</p>
                        <p class="text-[11px] text-[#6B7280] ">{{ basename($tugas->lampiran_guru) }}</p>
                    </div>
                </div>
                <a href="{{ route('siswa.tugas.download-lampiran', $tugas->id) }}" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white font-semibold text-xs transition-colors flex-shrink-0">
                    <span class="material-symbols-outlined text-[16px]">download</span>
                    <span>Unduh Berkas Panduan</span>
                </a>
            </div>
        @endif
    </div>

    <!-- 2. TABEL STATUS PENGAJUAN TUGAS (STANDAR AKADEMIK FORMAL) -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E5E7EB] bg-[#F9FAFB] flex items-center justify-between">
            <h3 class="font-heading font-semibold text-base text-[#1F2937]">
                Status Pengajuan Tugas
            </h3>
            @if ($isSubmitted && $bisaEdit)
                <button type="button" @click="isEditing = !isEditing" class="px-3.5 py-1.5 rounded-md border border-[#D1D5DB] bg-white hover:bg-[#F3F4F6] text-[#374151] text-xs font-medium transition-colors cursor-pointer">
                    <span x-text="isEditing ? 'Tutup Formulir Unggah' : 'Edit Pengajuan'"></span>
                </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm text-[#374151] border-collapse">
                <tbody class="divide-y divide-[#E5E7EB]">
                    <!-- 1. Dibuka -->
                    <tr>
                        <th class="py-3.5 px-5 font-semibold text-[#6B7280] bg-[#F9FAFB] w-1/3 sm:w-1/4">Dibuka:</th>
                        <td class="py-3.5 px-5 font-medium text-[#1F2937]">
                            {{ $tugas->waktu_buka->translatedFormat('l, d F Y, H:i') }} WIB
                        </td>
                    </tr>

                    <!-- 2. Jatuh Tempo -->
                    <tr>
                        <th class="py-3.5 px-5 font-semibold text-[#6B7280] bg-[#F9FAFB]">Jatuh tempo:</th>
                        <td class="py-3.5 px-5 font-medium text-[#1F2937]">
                            {{ $tugas->batas_waktu->translatedFormat('l, d F Y, H:i') }} WIB
                        </td>
                    </tr>

                    <!-- 3. Status Pengajuan -->
                    <tr>
                        <th class="py-3.5 px-5 font-semibold text-[#6B7280] bg-[#F9FAFB]">Status pengajuan:</th>
                        <td class="py-3.5 px-5">
                            @if ($isSubmitted)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#1E9E63]"></span>
                                    <span>Terkirim dan siap dinilai</span>
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-[#F3F4F6] text-[#4B5563] border border-[#E5E7EB]">
                                    Belum mengumpulkan tugas
                                </span>
                            @endif
                        </td>
                    </tr>

                    <!-- 4. Status Penilaian -->
                    <tr>
                        <th class="py-3.5 px-5 font-semibold text-[#6B7280] bg-[#F9FAFB]">Status penilaian:</th>
                        <td class="py-3.5 px-5">
                            @if ($isGraded)
                                <div class="space-y-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                                        <span class="material-symbols-outlined text-[15px]">grade</span>
                                        <span>Sudah dinilai — Nilai: {{ $pengumpulan->nilai }} / 100</span>
                                    </span>
                                    @if ($pengumpulan->feedback_guru)
                                        <div class="p-3 rounded-md bg-[#F9FAFB] border border-[#E5E7EB] text-xs">
                                            <p class="font-semibold text-[#1F2937]">Catatan / Feedback Guru:</p>
                                            <p class="text-[#374151] mt-1 leading-relaxed">{{ $pengumpulan->feedback_guru }}</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-[#6B7280] font-medium">Belum dinilai</span>
                            @endif
                        </td>
                    </tr>

                    <!-- 5. Waktu Tersisa / Keterangan Selisih Pengajuan -->
                    <tr>
                        <th class="py-3.5 px-5 font-semibold text-[#6B7280] bg-[#F9FAFB]">Waktu tersisa:</th>
                        <td class="py-3.5 px-5">
                            @if ($isSubmitted)
                                @if ($pengumpulan->isTerlambat())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                        {{ $pengumpulan->selisih_waktu_label }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                                        {{ $pengumpulan->selisih_waktu_label }}
                                    </span>
                                @endif
                            @else
                                @if ($tugas->isLewatBatas())
                                    <span class="text-xs font-semibold text-red-700 bg-red-50 px-2.5 py-0.5 rounded-md border border-red-200">
                                        Penugasan telah berakhir {{ $tugas->batas_waktu->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-xs font-semibold text-[#146C43]">
                                        Tersisa {{ Carbon\Carbon::now()->diffForHumans($tugas->batas_waktu, ['syntax' => Carbon\CarbonInterface::DIFF_ABSOLUTE, 'parts' => 3]) }} lagi
                                    </span>
                                @endif
                            @endif
                        </td>
                    </tr>

                    <!-- 6. Terakhir Diubah -->
                    <tr>
                        <th class="py-3.5 px-5 font-semibold text-[#6B7280] bg-[#F9FAFB]">Terakhir diubah:</th>
                        <td class="py-3.5 px-5 text-[#1F2937]">
                            @if ($isSubmitted && $pengumpulan->submitted_at)
                                {{ $pengumpulan->submitted_at->translatedFormat('l, d F Y, H:i') }} WIB
                            @else
                                <span class="text-[#9CA3AF] italic">-</span>
                            @endif
                        </td>
                    </tr>

                    <!-- 7. Pengajuan Berkas -->
                    <tr>
                        <th class="py-3.5 px-5 font-semibold text-[#6B7280] bg-[#F9FAFB]">Pengajuan berkas:</th>
                        <td class="py-3.5 px-5">
                            @if ($pengumpulan && $pengumpulan->files->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach ($pengumpulan->files as $f)
                                        <div class="flex items-center justify-between p-2.5 rounded-md bg-[#F9FAFB] border border-[#E5E7EB] text-xs">
                                            <div class="flex items-center gap-2 overflow-hidden">
                                                <span class="material-symbols-outlined text-[17px] text-[#1E9E63]">description</span>
                                                <a href="{{ Storage::url($f->file_path) }}" target="_blank" class="font-semibold text-[#1F2937] hover:underline break-words">
                                                    {{ $f->nama_file_asli }}
                                                </a>
                                                <span class="text-[11px] text-[#6B7280] ">({{ $f->ukuran_formatted }})</span>
                                                <span class="text-[11px] text-[#9CA3AF]">• {{ $f->created_at->translatedFormat('d M Y, H:i') }}</span>
                                            </div>
                                            @if ($bisaEdit)
                                                <form method="POST" action="{{ route('siswa.tugas.delete-file', $f->id) }}" onsubmit="return handleConfirmForm(event, this, 'Konfirmasi Hapus Berkas', 'Apakah Anda yakin ingin menghapus berkas \'{{ addslashes($f->nama_file_asli) }}\'?', 'Ya, Hapus Data');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-[11px] font-medium px-2 py-0.5 hover:bg-red-50 rounded cursor-pointer" title="Hapus Berkas Ini">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-[#9CA3AF] italic text-xs">Belum ada berkas yang diunggah.</span>
                            @endif
                        </td>
                    </tr>

                    <!-- 8. Komentar Pengajuan (Accordion) -->
                    <tr>
                        <th class="py-3.5 px-5 font-semibold text-[#6B7280] bg-[#F9FAFB]">Komentar pengajuan:</th>
                        <td class="py-3.5 px-5">
                            @if ($pengumpulan)
                                <div>
                                    <button type="button" @click="showCommentsAccordion = !showCommentsAccordion" class="text-xs font-semibold text-[#1E9E63] hover:underline flex items-center gap-1 cursor-pointer">
                                        <span class="material-symbols-outlined text-[16px]">chat_bubble_outline</span>
                                        <span>Komentar (<span x-text="commentsList.length"></span>)</span>
                                        <span class="material-symbols-outlined text-[16px]" x-text="showCommentsAccordion ? 'expand_less' : 'expand_more'"></span>
                                    </button>

                                    <!-- Bagian Konten Komentar Accordion -->
                                    <div x-show="showCommentsAccordion" x-collapse class="mt-3 space-y-3 pt-2 border-t border-[#E5E7EB]">
                                        <div class="max-h-48 overflow-y-auto space-y-2 p-3 rounded-lg bg-[#F9FAFB] border border-[#E5E7EB] text-xs">
                                            <template x-for="c in commentsList" :key="c.id">
                                                <div class="p-2.5 rounded-md bg-white border border-[#E5E7EB] space-y-0.5">
                                                    <div class="flex items-center justify-between text-[11px]">
                                                        <span class="font-semibold text-[#1F2937]" x-text="c.user_name + (c.user_role === 'guru' ? ' (Guru)' : '')"></span>
                                                        <span class="text-[#6B7280] text-[10px]" x-text="c.time"></span>
                                                    </div>
                                                    <p class="text-[#374151] text-xs" x-text="c.text"></p>
                                                </div>
                                            </template>
                                            <template x-if="commentsList.length === 0">
                                                <p class="text-[#9CA3AF] text-xs italic text-center py-2">Belum ada komentar.</p>
                                            </template>
                                        </div>

                                        <div class="flex gap-2">
                                            <input type="text" x-model="commentInput" @keydown.enter.prevent="submitComment()" placeholder="Ketik catatan / pertanyaan pribadi untuk Guru..." class="w-full text-xs px-3.5 py-2 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63]">
                                            <button type="button" @click="submitComment()" class="px-4 py-2 bg-[#1E9E63] hover:bg-[#168251] text-white rounded-md text-xs font-semibold cursor-pointer">
                                                Kirim
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-[#9CA3AF] text-xs italic">Komentar tersedia setelah Anda mengirimkan penugasan.</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. AREA DRAG-AND-DROP MULTI-FILE UPLOAD (ALPINE.JS) -->
    @if ($bisaKumpul && (!$isSubmitted || $bisaEdit))
        <div x-show="isEditing" x-collapse class="bg-white rounded-lg border border-[#E5E7EB] p-6 space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-[#E5E7EB]">
                <div>
                    <h3 class="font-heading font-semibold text-base text-[#1F2937] flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#1E9E63] text-[19px]">cloud_upload</span>
                        <span>Unggah Berkas Tugas (Drag &amp; Drop Multi-File)</span>
                    </h3>
                    <p class="text-xs text-[#6B7280] mt-0.5">
                        Format yang diizinkan: <strong>.{{ implode(', .', $allowedExts) }}</strong> • Maksimal {{ $maxSizeMb }} MB per file • Sisa slot: <span x-text="maxFiles - selectedFiles.length"></span> berkas lagi.
                    </p>
                </div>
            </div>

            <!-- Error Message Box -->
            <div x-show="errorMessage" x-cloak class="p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-xs font-semibold flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">error</span>
                <span x-text="errorMessage"></span>
            </div>

            <form method="POST" action="{{ route('siswa.tugas.submit', $tugas->id) }}" enctype="multipart/form-data" id="form-upload-tugas">
                @csrf

                <!-- Dropzone Box -->
                <div @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false" @drop.prevent="dragOver = false; handleFiles($event.dataTransfer.files)" :class="dragOver ? 'border-[#1E9E63] bg-[#E8F5EE]/50' : 'border-[#D1D5DB] bg-[#F9FAFB]'" class="border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer hover:border-[#1E9E63]">
                    <input type="file" id="multi-file-input" name="files[]" multiple @change="handleFiles($event.target.files)" class="sr-only">
                    <label for="multi-file-input" class="cursor-pointer space-y-2 block">
                        <span class="material-symbols-outlined text-[42px] text-[#9CA3AF]">upload_file</span>
                        <div>
                            <p class="text-sm font-heading font-semibold text-[#1F2937]">
                                Tarik &amp; Lepaskan berkas Anda ke sini, atau <span class="text-[#1E9E63] underline">Pilih Berkas</span>
                            </p>
                            <p class="text-xs text-[#6B7280] mt-1">Dapat memilih banyak file sekaligus (Multi-file upload)</p>
                        </div>
                    </label>
                </div>

                <!-- Preview Berkas yang Baru Dipilih Sebelum Dikirim -->
                <template x-if="selectedFiles.length > 0">
                    <div class="mt-4 space-y-2">
                        <p class="text-xs font-semibold text-[#374151]">Berkas yang Siap Dikirim (<span x-text="selectedFiles.length"></span>):</p>
                        <div class="space-y-1.5">
                            <template x-for="(file, index) in selectedFiles" :key="index">
                                <div class="flex items-center justify-between p-2.5 rounded-md bg-[#F9FAFB] border border-[#E5E7EB] text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[16px] text-[#1E9E63]">check_circle</span>
                                        <span class="font-semibold text-[#1F2937]" x-text="file.name"></span>
                                        <span class="text-[11px] text-[#6B7280]" x-text="'(' + (file.size / 1024 / 1024).toFixed(2) + ' MB)'"></span>
                                    </div>
                                    <button type="button" @click="removeSelectedFile(index)" class="text-red-600 hover:text-red-800 text-[11px] font-medium cursor-pointer">
                                        Batal
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Tombol Submit Pengajuan -->
                <div class="pt-4 border-t border-[#E5E7EB] flex items-center justify-end gap-3 mt-4">
                    <button type="submit" :disabled="selectedFiles.length === 0" :class="selectedFiles.length > 0 ? 'bg-[#1E9E63] hover:bg-[#168251] text-white cursor-pointer' : 'bg-[#D1D5DB] text-[#6B7280] cursor-not-allowed'" class="px-5 py-2 rounded-md text-xs font-semibold transition-colors flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[17px]">send</span>
                        <span>Simpan / Kirim Pengajuan Tugas</span>
                    </button>
                </div>
            </form>
        </div>
    @elseif (!$bisaKumpul && !$isSubmitted)
        <div class="p-5 rounded-lg bg-[#FFF7ED] border border-[#FED7AA] text-[#9A3412] text-xs space-y-1">
            <p class="font-semibold text-sm flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px] text-[#D97706]">lock_clock</span>
                <span>Waktu Pengumpulan Tugas Telah Ditutup</span>
            </p>
            <p class="leading-relaxed">
                Penugasan ini telah melewati batas waktu pengerjaan dan guru pengampu tidak mengizinkan pengumpulan terlambat.
            </p>
        </div>
    @endif

</div>
@endsection
