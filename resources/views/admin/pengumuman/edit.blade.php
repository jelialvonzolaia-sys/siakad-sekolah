@extends('layouts.siakad')

@php
    $pageTitle = 'Edit Pengumuman';
    $pageSubtitle = $pengumuman->judul;
@endphp

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    <a href="{{ route('admin.pengumuman.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-[#6B7280] hover:text-[#1F2937] transition-colors">
        <span class="material-symbols-outlined text-[17px]">arrow_back</span>
        <span>Kembali ke Daftar Pengumuman</span>
    </a>

    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-visible">
        <div class="px-6 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between rounded-t-lg">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] text-[#1E9E63] flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[20px]">edit_note</span>
                </div>
                <div>
                    <h2 class="text-base font-heading font-semibold text-[#1F2937]">Perbarui Pengumuman Sekolah</h2>
                    <p class="text-xs text-[#6B7280]">Ubah judul, target penerima, status publikasi, atau konten pengumuman.</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.pengumuman.update', $pengumuman->id) }}" id="form-pengumuman-edit" enctype="multipart/form-data" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="judul" class="block text-xs font-semibold text-[#374151] mb-1.5">Judul Pengumuman <span class="text-red-500">*</span></label>
                <input type="text" id="judul" name="judul" value="{{ old('judul', $pengumuman->judul) }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937]">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1.5">Target Penerima <span class="text-red-500">*</span></label>
                    <x-select-dropdown
                        id="target_role"
                        name="target_role"
                        :options="[
                            ['value' => 'semua', 'label' => 'Semua Warga Sekolah (Guru & Siswa)'],
                            ['value' => 'guru', 'label' => 'Khusus Guru'],
                            ['value' => 'siswa', 'label' => 'Khusus Siswa'],
                        ]"
                        :value="old('target_role', $pengumuman->target_role)"
                        required
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1.5">Status Publikasi <span class="text-red-500">*</span></label>
                    <x-select-dropdown
                        id="is_published"
                        name="is_published"
                        :options="[
                            ['value' => '1', 'label' => 'Publish (Tampilkan Sekarang)'],
                            ['value' => '0', 'label' => 'Draft (Simpan Sementara)'],
                        ]"
                        :value="old('is_published', $pengumuman->is_published ? '1' : '0')"
                        required
                    />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-[#374151] mb-1.5">Isi Pengumuman (Rich Text Editor) <span class="text-red-500">*</span></label>
                <div class="rounded-lg border border-[#D1D5DB] overflow-hidden focus-within:border-[#1E9E63]">
                    <div id="quill-toolbar" class="bg-[#F9FAFB] border-b border-[#E5E7EB]">
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
                            <button class="ql-list" value="ordered"></button>
                            <button class="ql-list" value="bullet"></button>
                            <select class="ql-align"></select>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-table"></button>
                            <button class="ql-link"></button>
                            <button class="ql-clean"></button>
                        </span>
                    </div>
                    <div id="editor-container" class="min-h-[240px] bg-white text-[#1F2937] text-sm">
                        {!! old('isi', $pengumuman->isi) !!}
                    </div>
                </div>
                <textarea id="isi" name="isi" class="hidden">{{ old('isi', $pengumuman->isi) }}</textarea>
            </div>

            <!-- Upload / Kelola Poster Pengumuman -->
            <div class="space-y-2">
                <label for="gambar" class="block text-xs font-semibold text-[#374151]">
                    Gambar / Poster Pengumuman (Opsional)
                </label>
                <div class="p-4 rounded-lg border border-dashed border-[#A3B18A] bg-[#F9FAFB] space-y-3">
                    @if ($pengumuman->gambar_path)
                        <div class="space-y-2 pb-2 border-b border-[#E5E7EB]">
                            <p class="text-[11px] font-semibold text-[#374151]">Poster Terpasang Saat Ini:</p>
                            <img src="{{ asset('storage/' . $pengumuman->gambar_path) }}" alt="Poster {{ $pengumuman->judul }}" class="max-h-56 rounded-lg border border-[#E5E7EB] object-contain bg-white p-1">
                            <label class="inline-flex items-center gap-2 text-xs text-red-700 font-medium cursor-pointer pt-1">
                                <input type="checkbox" name="hapus_gambar" value="1" class="rounded border-[#D1D5DB] text-red-600 focus:ring-red-500">
                                <span>Hapus gambar poster saat ini</span>
                            </label>
                        </div>
                    @endif

                    <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/webp" onchange="previewPosterEdit(event)" class="block w-full text-xs text-[#374151] file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#1F3B2E] file:text-white hover:file:bg-[#3C5A3A] file:cursor-pointer cursor-pointer">
                    <p class="text-[11px] text-[#6B7280]">Unggah gambar baru untuk mengganti poster. Format: JPG, PNG, WEBP (Maks. 5 MB).</p>
                    @error('gambar')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                    <div id="poster-preview-wrapper" class="hidden pt-2">
                        <p class="text-[11px] font-semibold text-[#374151] mb-1.5">Pratinjau Poster Baru:</p>
                        <img id="poster-preview-img" src="#" alt="Pratinjau Poster Baru" class="max-h-64 rounded-lg border border-[#E5E7EB] object-contain bg-white p-1">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-[#E5E7EB] flex items-center justify-end gap-3">
                <a href="{{ route('admin.pengumuman.index') }}" class="px-4 py-2 rounded-md border border-[#D1D5DB] bg-white hover:bg-[#F9FAFB] text-[#374151] text-xs font-medium transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-[17px]">save</span>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function previewPosterEdit(event) {
    const file = event.target.files[0];
    const wrapper = document.getElementById('poster-preview-wrapper');
    const img = document.getElementById('poster-preview-img');
    if (file) {
        img.src = URL.createObjectURL(file);
        wrapper.classList.remove('hidden');
    } else {
        wrapper.classList.add('hidden');
        img.src = '#';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const quill = new Quill('#editor-container', {
        theme: 'snow',
        modules: { toolbar: '#quill-toolbar' }
    });

    const form = document.getElementById('form-pengumuman-edit');
    const hiddenInput = document.getElementById('isi');

    form.addEventListener('submit', function (e) {
        hiddenInput.value = quill.root.innerHTML;
        if (quill.getText().trim().length === 0) {
            window.showToast('warning', 'Isi pengumuman wajib diisi terlebih dahulu.', 'Form Belum Lengkap');
            e.preventDefault();
        }
    });
});
</script>
@endsection
