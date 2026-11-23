@extends('layouts.siakad')

@php
    $pageTitle = 'Unggah Materi';
    $pageSubtitle = 'Tambah Bahan Ajar Baru';
@endphp

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    <!-- Tombol Kembali -->
    <a href="{{ route('guru.materi.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-[#6B7280] hover:text-[#1F2937] transition-colors">
        <span class="material-symbols-outlined text-[17px]">arrow_back</span>
        <span>Kembali ke Daftar Materi</span>
    </a>

    <!-- Kartu Form Tambah Materi -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <!-- Header Kartu -->
        <div class="px-6 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] text-[#1E9E63] flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[20px]">upload_file</span>
                </div>
                <div>
                    <h2 class="text-base font-heading font-semibold text-[#1F2937]">Unggah Materi Pembelajaran Baru</h2>
                    <p class="text-xs text-[#6B7280]">Materi akan langsung tampil dan dapat diunduh oleh siswa di kelas yang dipilih.</p>
                </div>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                Bahan Ajar
            </span>
        </div>

        <!-- Form Submission -->
        <form method="POST" action="{{ route('guru.materi.store') }}" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf

            <!-- Pilihan Alokasi Jadwal / Kelas & Mata Pelajaran -->
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

            <!-- Judul Materi -->
            <div>
                <label for="judul" class="block text-xs font-semibold text-[#374151] mb-1.5">
                    Judul Materi Pembelajaran <span class="text-red-500">*</span>
                </label>
                <input type="text" id="judul" name="judul" value="{{ old('judul') }}" placeholder="Contoh: Modul Bab 3 - Persamaan Diferensial dan Aplikasi Fisika" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937]">
                @error('judul')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Deskripsi Singkat -->
            <div>
                <label for="deskripsi" class="block text-xs font-semibold text-[#374151] mb-1.5">
                    Deskripsi / Petunjuk Belajar <span class="text-[#6B7280] font-normal">(Opsional)</span>
                </label>
                <textarea id="deskripsi" name="deskripsi" rows="3" placeholder="Tuliskan rangkuman pokok bahasan, instruksi pengerjaan tugas, atau panduan membaca modul ini..." class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-xs sm:text-sm text-[#1F2937]">{{ old('deskripsi') }}</textarea>
                @error('deskripsi')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Upload Berkas File -->
            <div>
                <label for="file_materi" class="block text-xs font-semibold text-[#374151] mb-1.5">
                    Unggah Berkas Materi <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-[#D1D5DB] border-dashed rounded-lg hover:border-[#1E9E63] transition-colors bg-[#F9FAFB]">
                    <div class="space-y-2 text-center">
                        <span class="material-symbols-outlined text-[40px] text-[#9CA3AF]">cloud_upload</span>
                        <div class="flex text-xs text-[#4B5563] justify-center items-center">
                            <label for="file_materi" class="relative cursor-pointer font-semibold text-[#1E9E63] hover:text-[#168251] underline">
                                <span>Pilih Berkas dari Perangkat</span>
                                <input id="file_materi" name="file_materi" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx" required class="sr-only" onchange="updateFileName(this)">
                            </label>
                        </div>
                        <p id="file-name-display" class="text-xs text-[#146C43] font-semibold"></p>
                        <p class="text-[11px] text-[#6B7280]">
                            Format yang didukung: <strong>PDF, DOC, DOCX, PPT, PPTX</strong> (Maksimal 10 MB)
                        </p>
                    </div>
                </div>
                @error('file_materi')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-[#E5E7EB] flex items-center justify-end gap-3">
                <a href="{{ route('guru.materi.index') }}" class="px-4 py-2 rounded-md border border-[#D1D5DB] bg-white hover:bg-[#F9FAFB] text-[#374151] text-xs font-medium transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-[17px]">cloud_upload</span>
                    <span>Unggah Materi Sekarang</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function updateFileName(input) {
    const display = document.getElementById('file-name-display');
    if (input.files && input.files[0]) {
        display.textContent = 'Berkas terpilih: ' + input.files[0].name + ' (' + (input.files[0].size / 1024 / 1024).toFixed(2) + ' MB)';
    } else {
        display.textContent = '';
    }
}
</script>
@endsection
