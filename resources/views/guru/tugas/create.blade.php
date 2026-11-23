@extends('layouts.siakad')

@php
    $pageTitle = 'Buat Tugas Baru';
    $pageSubtitle = 'Tambah Penugasan Pembelajaran';
@endphp

@push('scripts')
<!-- Quill Rich Text Editor CDN (No API Key Required) -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    <!-- Tombol Kembali -->
    <a href="{{ route('guru.tugas.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-[#6B7280] hover:text-[#1F2937] transition-colors">
        <span class="material-symbols-outlined text-[17px]">arrow_back</span>
        <span>Kembali ke Daftar Tugas</span>
    </a>

    <!-- Kartu Form Buat Tugas -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <!-- Header Kartu -->
        <div class="px-6 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] text-[#1E9E63] flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[20px]">post_add</span>
                </div>
                <div>
                    <h2 class="text-base font-heading font-semibold text-[#1F2937]">Buat Penugasan Pembelajaran Baru</h2>
                    <p class="text-xs text-[#6B7280]">Konfigurasikan instruksi terstruktur, berkas panduan, dan kriteria pengumpulan berkas siswa.</p>
                </div>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                Tugas Terstruktur
            </span>
        </div>

        <!-- Form Submission -->
        <form method="POST" action="{{ route('guru.tugas.store') }}" enctype="multipart/form-data" id="form-buat-tugas" class="p-6 space-y-5">
            @csrf

            <!-- Baris 1: Kelas & Mata Pelajaran -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="kelas_id" class="block text-xs font-semibold text-[#374151] mb-1.5">
                        Rombel Kelas Sasaran <span class="text-red-500">*</span>
                    </label>
                    <x-select-dropdown
                        id="kelas_id"
                        name="kelas_id"
                        :options="$kelasList->map(fn($k) => ['value' => $k->id, 'label' => 'Kelas ' . $k->nama_kelas . ' (Tingkat ' . $k->tingkat . ')'])"
                        :value="old('kelas_id')"
                        placeholder="-- Pilih Kelas --"
                        required
                    />
                    @error('kelas_id')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="mapel_id" class="block text-xs font-semibold text-[#374151] mb-1.5">
                        Mata Pelajaran <span class="text-red-500">*</span>
                    </label>
                    <x-select-dropdown
                        id="mapel_id"
                        name="mapel_id"
                        :options="$mapelList->map(fn($m) => ['value' => $m->id, 'label' => $m->nama_mapel . ' (' . $m->kode_mapel . ')'])"
                        :value="old('mapel_id')"
                        placeholder="-- Pilih Mata Pelajaran --"
                        required
                    />
                    @error('mapel_id')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Baris 2: Judul Tugas -->
            <div>
                <label for="judul" class="block text-xs font-semibold text-[#374151] mb-1.5">
                    Judul Penugasan <span class="text-red-500">*</span>
                </label>
                <input type="text" id="judul" name="judul" value="{{ old('judul') }}" placeholder="Contoh: Tugas Praktikum 3 - Algoritma Pemrograman &amp; Logika Percabangan" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937]">
                @error('judul')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Baris 3: Rich Text Editor (Ala MS Word) Deskripsi & Instruksi Tugas -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold text-[#374151]">
                        Instruksi &amp; Deskripsi Lengkap Tugas (Rich Text Editor) <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[11px] text-[#6B7280] font-medium">Mendukung format tabel, warna, highlight, font, dan perataan</span>
                </div>

                <!-- Wadah Kustom Toolbar Change Case Button & Quill Editor -->
                <div class="rounded-lg border border-[#D1D5DB] overflow-hidden focus-within:border-[#1E9E63]">
                    <div id="quill-toolbar" class="bg-[#F9FAFB] border-b border-[#E5E7EB]">
                        <span class="ql-formats">
                            <select class="ql-font"></select>
                            <select class="ql-size"></select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-bold"></button>
                            <button class="ql-italic"></button>
                            <button class="ql-underline"></button>
                            <button class="ql-strike"></button>
                        </span>
                        <span class="ql-formats">
                            <select class="ql-color"></select>
                            <select class="ql-background"></select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-script" value="sub"></button>
                            <button class="ql-script" value="super"></button>
                        </span>
                        <!-- Tombol Kustom Change Case (UPPERCASE / lowercase / TitleCase) -->
                        <span class="ql-formats">
                            <button type="button" id="btn-change-case" title="Ubah Huruf (UPPERCASE / lowercase / Capitalize)" class="text-xs font-bold px-1.5 py-0.5 rounded border border-[#D1D5DB] bg-white hover:bg-[#F3F4F6]">
                                Aa
                            </button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-list" value="ordered"></button>
                            <button class="ql-list" value="bullet"></button>
                            <button class="ql-indent" value="-1"></button>
                            <button class="ql-indent" value="+1"></button>
                        </span>
                        <span class="ql-formats">
                            <select class="ql-align"></select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-table"></button>
                            <button class="ql-image"></button>
                            <button class="ql-link"></button>
                            <button class="ql-clean"></button>
                        </span>
                    </div>
                    <div id="editor-container" class="min-h-[220px] bg-white text-[#1F2937] text-sm">
                        {!! old('deskripsi_html') !!}
                    </div>
                </div>
                <textarea id="deskripsi_html" name="deskripsi_html" class="hidden">{{ old('deskripsi_html') }}</textarea>
                @error('deskripsi_html')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Baris 4: Lampiran File Soal / Panduan dari Guru -->
            <div>
                <label for="lampiran_guru" class="block text-xs font-semibold text-[#374151] mb-1.5">
                    Lampiran Berkas Soal / Rubrik Penilaian <span class="text-[#6B7280] font-normal">(Opsional)</span>
                </label>
                <input type="file" id="lampiran_guru" name="lampiran_guru" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar" class="w-full text-xs text-[#4B5563] file:mr-4 file:py-2.5 file:px-4 file:rounded-l-lg file:border-0 file:border-r file:border-[#E5E7EB] file:text-xs file:font-semibold file:bg-[#F9FAFB] file:text-[#1F2937] hover:file:bg-[#E8F5EE] cursor-pointer border border-[#D1D5DB] rounded-lg">
                <p class="text-[11px] text-[#6B7280] mt-1">Format: PDF, Word, PowerPoint, Excel, atau ZIP (Maksimal 15 MB).</p>
                @error('lampiran_guru')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Baris 5: Waktu Buka & Batas Waktu (Jatuh Tempo) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-[#E5E7EB]">
                <div>
                    <label for="waktu_buka" class="block text-xs font-semibold text-[#374151] mb-1.5">
                        Waktu Tugas Dibuka <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" id="waktu_buka" name="waktu_buka" value="{{ old('waktu_buka', $defaultBuka) }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-xs font-medium text-[#1F2937] ">
                    @error('waktu_buka')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="batas_waktu" class="block text-xs font-semibold text-[#374151] mb-1.5">
                        Batas Waktu Pengumpulan (Jatuh Tempo) <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" id="batas_waktu" name="batas_waktu" value="{{ old('batas_waktu', $defaultBatas) }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-xs font-medium text-[#1F2937] ">
                    @error('batas_waktu')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Baris 6: Checkbox Tipe Berkas yang Diizinkan -->
            <div class="pt-3 border-t border-[#E5E7EB]">
                <label class="block text-xs font-semibold text-[#374151] mb-2">
                    Tipe Berkas yang Diizinkan untuk Siswa <span class="text-red-500">*</span>
                </label>
                @php
                    $oldTypes = old('tipe_file_diizinkan', ['pdf', 'docx']);
                @endphp
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                    <label class="flex items-center gap-2 p-2.5 rounded-md border border-[#E5E7EB] hover:border-[#1E9E63] cursor-pointer transition-colors bg-[#F9FAFB]">
                        <input type="checkbox" name="tipe_file_diizinkan[]" value="pdf" {{ in_array('pdf', $oldTypes) ? 'checked' : '' }} class="rounded text-[#1E9E63] focus:ring-[#1E9E63]">
                        <span class="font-medium text-[#374151]">Dokumen PDF (.pdf)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-md border border-[#E5E7EB] hover:border-[#1E9E63] cursor-pointer transition-colors bg-[#F9FAFB]">
                        <input type="checkbox" name="tipe_file_diizinkan[]" value="docx" {{ in_array('docx', $oldTypes) ? 'checked' : '' }} class="rounded text-[#1E9E63] focus:ring-[#1E9E63]">
                        <span class="font-medium text-[#374151]">Word (.doc, .docx)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-md border border-[#E5E7EB] hover:border-[#1E9E63] cursor-pointer transition-colors bg-[#F9FAFB]">
                        <input type="checkbox" name="tipe_file_diizinkan[]" value="pptx" {{ in_array('pptx', $oldTypes) ? 'checked' : '' }} class="rounded text-[#1E9E63] focus:ring-[#1E9E63]">
                        <span class="font-medium text-[#374151]">PowerPoint (.ppt, .pptx)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-md border border-[#E5E7EB] hover:border-[#1E9E63] cursor-pointer transition-colors bg-[#F9FAFB]">
                        <input type="checkbox" name="tipe_file_diizinkan[]" value="xlsx" {{ in_array('xlsx', $oldTypes) ? 'checked' : '' }} class="rounded text-[#1E9E63] focus:ring-[#1E9E63]">
                        <span class="font-medium text-[#374151]">Excel (.xls, .xlsx)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-md border border-[#E5E7EB] hover:border-[#1E9E63] cursor-pointer transition-colors bg-[#F9FAFB]">
                        <input type="checkbox" name="tipe_file_diizinkan[]" value="zip" {{ in_array('zip', $oldTypes) ? 'checked' : '' }} class="rounded text-[#1E9E63] focus:ring-[#1E9E63]">
                        <span class="font-medium text-[#374151]">Arsip ZIP / RAR</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-md border border-[#E5E7EB] hover:border-[#1E9E63] cursor-pointer transition-colors bg-[#F9FAFB]">
                        <input type="checkbox" name="tipe_file_diizinkan[]" value="cpp" {{ in_array('cpp', $oldTypes) ? 'checked' : '' }} class="rounded text-[#1E9E63] focus:ring-[#1E9E63]">
                        <span class="font-medium text-[#374151]">C++ (.cpp, .h)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-md border border-[#E5E7EB] hover:border-[#1E9E63] cursor-pointer transition-colors bg-[#F9FAFB]">
                        <input type="checkbox" name="tipe_file_diizinkan[]" value="py" {{ in_array('py', $oldTypes) ? 'checked' : '' }} class="rounded text-[#1E9E63] focus:ring-[#1E9E63]">
                        <span class="font-medium text-[#374151]">Python (.py)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-md border border-[#E5E7EB] hover:border-[#1E9E63] cursor-pointer transition-colors bg-[#F9FAFB]">
                        <input type="checkbox" name="tipe_file_diizinkan[]" value="jpg" {{ in_array('jpg', $oldTypes) ? 'checked' : '' }} class="rounded text-[#1E9E63] focus:ring-[#1E9E63]">
                        <span class="font-medium text-[#374151]">Gambar (.jpg, .png)</span>
                    </label>
                </div>
                @error('tipe_file_diizinkan')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Baris 7: Pengaturan Ukuran & Jumlah Berkas -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-3 border-t border-[#E5E7EB] items-center">
                <div>
                    <label for="max_ukuran_mb" class="block text-xs font-semibold text-[#374151] mb-1.5">
                        Maksimal Ukuran File (MB) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="max_ukuran_mb" name="max_ukuran_mb" value="{{ old('max_ukuran_mb', 10) }}" min="1" max="50" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-xs font-bold text-[#1F2937] ">
                    @error('max_ukuran_mb')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="max_jumlah_file" class="block text-xs font-semibold text-[#374151] mb-1.5">
                        Maksimal Jumlah Berkas <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="max_jumlah_file" name="max_jumlah_file" value="{{ old('max_jumlah_file', 3) }}" min="1" max="10" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-xs font-bold text-[#1F2937] ">
                    @error('max_jumlah_file')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="izinkan_terlambat" value="1" {{ old('izinkan_terlambat') ? 'checked' : '' }} class="rounded text-[#1E9E63] focus:ring-[#1E9E63]">
                        <span class="text-xs font-semibold text-[#1F2937]">Izinkan Pengumpulan Terlambat</span>
                    </label>
                    <p class="text-[11px] text-[#6B7280] mt-0.5">Siswa tetap dapat mengumpulkan setelah jatuh tempo dengan status bertanda terlambat.</p>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-[#E5E7EB] flex items-center justify-end gap-3">
                <a href="{{ route('guru.tugas.index') }}" class="px-4 py-2 rounded-md border border-[#D1D5DB] bg-white hover:bg-[#F9FAFB] text-[#374151] text-xs font-medium transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-[17px]">publish</span>
                    <span>Publikasikan Tugas</span>
                </button>
            </div>
        </form>
    </div>

</div>

<!-- Skrip Inisialisasi Quill Editor & Fitur Custom Change-Case -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const quill = new Quill('#editor-container', {
        theme: 'snow',
        modules: {
            toolbar: '#quill-toolbar'
        },
        placeholder: 'Tuliskan deskripsi lengkap instruksi tugas, panduan pengerjaan, soal latihan, atau kriteria penilaian di sini...'
    });

    const form = document.getElementById('form-buat-tugas');
    const hiddenInput = document.getElementById('deskripsi_html');

    form.addEventListener('submit', function (e) {
        hiddenInput.value = quill.root.innerHTML;
        if (quill.getText().trim().length === 0) {
            window.showToast('warning', 'Instruksi / deskripsi tugas wajib diisi.', 'Form Belum Lengkap');
            e.preventDefault();
        }
    });

    const btnChangeCase = document.getElementById('btn-change-case');
    let caseState = 0;

    btnChangeCase.addEventListener('click', function () {
        const range = quill.getSelection();
        if (range && range.length > 0) {
            const selectedText = quill.getText(range.index, range.length);
            let transformed = selectedText;

            if (caseState === 0) {
                transformed = selectedText.toUpperCase();
                caseState = 1;
            } else if (caseState === 1) {
                transformed = selectedText.toLowerCase();
                caseState = 2;
            } else {
                transformed = selectedText.replace(/\w\S*/g, (txt) => txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase());
                caseState = 0;
            }

            quill.deleteText(range.index, range.length);
            quill.insertText(range.index, transformed);
            quill.setSelection(range.index, transformed.length);
        } else {
            window.showToast('info', 'Sorot / pilih teks yang ingin diubah case-nya terlebih dahulu.', 'Informasi Editor');
        }
    });
});
</script>
@endsection
