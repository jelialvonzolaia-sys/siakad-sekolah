@extends('layouts.siakad')

@php
    $pageTitle = 'Materi Pelajaran';
    $pageSubtitle = 'Kelola Bahan Ajar & Modul Pendidik';
@endphp

@section('content')
<div class="space-y-5">

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

    <!-- Header Section & Tombol Unggah -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-heading font-bold text-[#1F2937] tracking-tight">Materi Pembelajaran Pendidik</h2>
            <p class="text-xs text-[#6B7280] mt-0.5">Kelola modul pembelajaran, bahan ajar digital, slide presentasi, dan silabus untuk siswa.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.materi.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">upload_file</span>
                <span>+ Unggah Materi Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar: Pilihan Kelas & Kolom Pencarian -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-4">
        <form method="GET" action="{{ route('guru.materi.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <!-- Filter Rombel Kelas -->
            <div class="sm:col-span-4">
                <label for="filter-kelas" class="block text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Filter Rombel Kelas</label>
                <x-select-dropdown
                    id="filter-kelas"
                    name="kelas_id"
                    :options="collect([['value' => '', 'label' => 'Semua Kelas Bimbingan']])->concat($kelasList->map(fn($k) => ['value' => $k->id, 'label' => 'Kelas ' . $k->nama_kelas . ' (Tingkat ' . $k->tingkat . ')']))"
                    :value="$selectedKelasId"
                    submit-on-select
                />
            </div>

            <!-- Pencarian Judul -->
            <div class="sm:col-span-6">
                <label for="search-materi" class="block text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Cari Judul / Topik</label>
                <div class="relative">
                    <input type="text" id="search-materi" name="q" value="{{ $searchQuery }}" placeholder="Ketik kata kunci judul materi..." class="w-full text-xs sm:text-sm font-normal pl-9 pr-3.5 py-2.5 rounded-lg border border-[#D1D5DB] bg-white text-[#1F2937] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15">
                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-[18px] text-[#9CA3AF]">search</span>
                </div>
            </div>

            <!-- Tombol Reset / Submit -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full px-3 py-2.5 rounded-lg bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                    Cari
                </button>
                @if ($selectedKelasId || $searchQuery)
                    <a href="{{ route('guru.materi.index') }}" class="px-3 py-2.5 rounded-lg bg-[#F9FAFB] border border-[#D1D5DB] hover:bg-[#F3F4F6] text-[#4B5563] text-xs font-semibold transition-colors text-center" title="Reset Filter">
                        <span class="material-symbols-outlined text-[16px] align-middle">restart_alt</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Materi Pelajaran Guru -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between bg-[#F9FAFB]">
            <div>
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Berkas Materi yang Diunggah</h3>
                <p class="text-xs text-[#6B7280] mt-0.5">Total {{ $materis->total() }} materi pembelajaran tersimpan di server.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#374151] border-collapse">
                <thead class="bg-[#F9FAFB] text-[#6B7280] font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Mata Pelajaran &amp; Kelas</th>
                        <th class="py-3 px-4 min-w-[220px]">Judul &amp; Deskripsi Materi</th>
                        <th class="py-3 px-4 w-32">Format Berkas</th>
                        <th class="py-3 px-4 w-32">Tanggal Unggah</th>
                        <th class="py-3 px-4 w-36 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse ($materis as $index => $m)
                        @php
                            $ext = strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION));
                            $iconColor = match($ext) {
                                'pdf' => 'text-rose-700 bg-rose-50 border-rose-200',
                                'doc', 'docx' => 'text-blue-700 bg-blue-50 border-blue-200',
                                'ppt', 'pptx' => 'text-amber-700 bg-amber-50 border-amber-200',
                                default => 'text-[#4B5563] bg-[#F9FAFB] border-[#E5E7EB]'
                            };
                            $iconName = match($ext) {
                                'pdf' => 'picture_as_pdf',
                                'doc', 'docx' => 'description',
                                'ppt', 'pptx' => 'slideshow',
                                default => 'attach_file'
                            };
                        @endphp
                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                            <td class="py-3 px-4 text-center text-[#6B7280]">
                                {{ $materis->firstItem() + $index }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-[#1F2937]">
                                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#F9FAFB] text-[#374151] border border-[#E5E7EB] mr-1.5">
                                    {{ $m->kelas->nama_kelas }}
                                </span>
                                <span>{{ $m->mataPelajaran->nama_mapel }}</span>
                                <p class="text-[11px] text-[#6B7280] font-normal mt-0.5">Kode: {{ $m->mataPelajaran->kode_mapel }}</p>
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-semibold text-[#1F2937] text-xs sm:text-sm leading-snug">{{ $m->judul }}</p>
                                @if ($m->deskripsi)
                                    <p class="text-[11px] text-[#6B7280] line-clamp-1 mt-0.5">{{ $m->deskripsi }}</p>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded border text-[11px] font-semibold {{ $iconColor }}">
                                    <span class="material-symbols-outlined text-[15px]">{{ $iconName }}</span>
                                    <span>.{{ strtoupper($ext ?: 'FILE') }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-[#4B5563] whitespace-nowrap">
                                <p class="font-medium">{{ $m->created_at->translatedFormat('d M Y') }}</p>
                                <p class="text-[11px] text-[#6B7280] ">{{ $m->created_at->format('H:i') }} WIB</p>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ Storage::url($m->file_path) }}" target="_blank" class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-[#E8F5EE] hover:border-[#1E9E63] text-[#1E9E63] transition-colors" title="Lihat / Unduh Berkas">
                                        <span class="material-symbols-outlined text-[16px]">download</span>
                                    </a>
                                    <a href="{{ route('guru.materi.edit', $m->id) }}" class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-[#FFF7ED] hover:text-[#B45309] text-[#4B5563] transition-colors" title="Edit Materi">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('guru.materi.destroy', $m->id) }}" onsubmit="return handleConfirmForm(event, this, 'Konfirmasi Hapus Materi', 'Apakah Anda yakin ingin menghapus materi \'{{ addslashes($m->judul) }}\'? Berkas fisik di server akan dihapus permanen.', 'Ya, Hapus Data');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-red-50 hover:text-red-600 text-[#4B5563] transition-colors cursor-pointer" title="Hapus Materi">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-[#6B7280]">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <span class="material-symbols-outlined text-[34px] text-[#9CA3AF]">folder_off</span>
                                    <p class="text-xs">Belum ada materi pelajaran yang diunggah.</p>
                                    <a href="{{ route('guru.materi.create') }}" class="text-xs font-semibold text-[#1E9E63] hover:underline">
                                        + Unggah materi pertama Anda
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($materis->hasPages())
            <div class="p-4 border-t border-[#E5E7EB] bg-[#F9FAFB]">
                {{ $materis->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
