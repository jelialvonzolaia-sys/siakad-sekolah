@extends('layouts.siakad')

@php
    $pageTitle = 'Materi Pelajaran';
    $pageSubtitle = 'Katalog Bahan Ajar Kelas ' . ($siswa->kelas->nama_kelas ?? '-');
@endphp

@section('content')
<div class="space-y-5">

    <!-- Header & Identitas Rombel Kelas Siswa -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#F9FAFB] border border-[#D1D5DB] text-[#1F2937]">
                    Kelas {{ $siswa->kelas->nama_kelas ?? '-' }}
                </span>
                <span class="text-xs text-[#9CA3AF]">•</span>
                <span class="text-xs text-[#6B7280] font-medium">Tingkat {{ $siswa->kelas->tingkat ?? '-' }} • SMAN 1 Gunungsitoli</span>
            </div>
            <h2 class="text-lg font-heading font-bold text-[#1F2937] tracking-tight mt-1">
                Katalog Materi &amp; Modul Pembelajaran
            </h2>
            <p class="text-xs text-[#6B7280] mt-0.5">
                Akses modul pembelajaran digital, slide presentasi, ringkasan teori, dan berkas tugas yang dibagikan guru kelas Anda.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-md bg-[#E8F5EE] border border-[#BBF7D0] text-xs font-semibold text-[#146C43] flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-[#1E9E63]">menu_book</span>
                <span>{{ $materis->total() }} Berkas Tersedia</span>
            </span>
        </div>
    </div>

    <!-- Filter Bar: Dropdown Mata Pelajaran & Kolom Pencarian -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-4">
        <form method="GET" action="{{ route('siswa.materi.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <!-- Filter Mata Pelajaran -->
            <div class="sm:col-span-5">
                <label for="filter-mapel" class="block text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Mata Pelajaran</label>
                <x-select-dropdown
                    id="filter-mapel"
                    name="mapel_id"
                    :options="collect([['value' => '', 'label' => 'Semua Mata Pelajaran']])->concat($mapelList->map(fn($m) => ['value' => $m->id, 'label' => $m->nama_mapel . ' (' . $m->kode_mapel . ')']))"
                    :value="$selectedMapelId"
                    submit-on-select
                />
            </div>

            <!-- Pencarian Judul Materi -->
            <div class="sm:col-span-5">
                <label for="search-materi" class="block text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Cari Judul Materi</label>
                <div class="relative">
                    <input type="text" id="search-materi" name="q" value="{{ $searchQuery }}" placeholder="Ketik kata kunci judul materi..." class="w-full text-xs sm:text-sm font-normal pl-9 pr-3.5 py-2.5 rounded-lg border border-[#D1D5DB] bg-white text-[#1F2937] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15">
                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-[18px] text-[#9CA3AF]">search</span>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full px-3 py-2.5 rounded-lg bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                    Cari
                </button>
                @if ($selectedMapelId || $searchQuery)
                    <a href="{{ route('siswa.materi.index') }}" class="px-3 py-2.5 rounded-lg bg-[#F9FAFB] border border-[#D1D5DB] hover:bg-[#F3F4F6] text-[#4B5563] text-xs font-semibold transition-colors text-center" title="Reset Filter">
                        <span class="material-symbols-outlined text-[18px] align-middle">restart_alt</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Grid Kartu Materi Pelajaran Siswa -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($materis as $materi)
            @php
                $ext = strtolower(pathinfo($materi->file_path, PATHINFO_EXTENSION));
                $extBadge = match($ext) {
                    'pdf' => 'bg-rose-50 text-rose-700 border-rose-200',
                    'doc', 'docx' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'ppt', 'pptx' => 'bg-amber-50 text-amber-700 border-amber-200',
                    default => 'bg-[#F9FAFB] text-[#4B5563] border-[#E5E7EB]'
                };
                $extIcon = match($ext) {
                    'pdf' => 'picture_as_pdf',
                    'doc', 'docx' => 'description',
                    'ppt', 'pptx' => 'slideshow',
                    default => 'attach_file'
                };
            @endphp
            <div class="bg-white rounded-lg border border-[#E5E7EB] flex flex-col justify-between p-5 hover:border-[#1E9E63]/60 transition-colors">
                <div>
                    <!-- Header Kartu: Mata Pelajaran & Ekstensi -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                            {{ $materi->mataPelajaran->nama_mapel }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold border {{ $extBadge }}">
                            <span class="material-symbols-outlined text-[13px]">{{ $extIcon }}</span>
                            <span>.{{ strtoupper($ext ?: 'FILE') }}</span>
                        </span>
                    </div>

                    <!-- Judul Materi -->
                    <h3 class="font-heading font-semibold text-sm text-[#1F2937] leading-snug line-clamp-2">
                        {{ $materi->judul }}
                    </h3>

                    <!-- Deskripsi Singkat -->
                    <p class="text-xs text-[#6B7280] mt-2 line-clamp-3 leading-relaxed">
                        {{ $materi->deskripsi ?: 'Tidak ada deskripsi tambahan dari guru pengampu.' }}
                    </p>

                    <!-- Identitas Guru & Waktu Unggah -->
                    <div class="mt-4 pt-3 border-t border-[#E5E7EB] space-y-1 text-[11px] text-[#6B7280]">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[15px] text-[#9CA3AF]">person</span>
                            <span class="break-words">Guru: <strong class="text-[#1F2937]">{{ $materi->guru->name }}</strong></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[15px] text-[#1E9E63]">schedule</span>
                            <span>Diunggah: {{ $materi->created_at->translatedFormat('d F Y') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi: Unduh / Lihat Materi -->
                <div class="mt-4 pt-3">
                    <a href="{{ route('siswa.materi.download', $materi->id) }}" target="_blank" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">download</span>
                        <span>Unduh / Lihat Materi</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full p-10 text-center bg-white rounded-lg border border-[#E5E7EB] space-y-2.5">
                <span class="material-symbols-outlined text-[40px] text-[#9CA3AF]">menu_book</span>
                <h4 class="font-heading font-semibold text-sm text-[#1F2937]">Belum Ada Materi Pembelajaran</h4>
                <p class="text-xs text-[#6B7280] max-w-md mx-auto">
                    Guru mata pelajaran kelas {{ $siswa->kelas->nama_kelas ?? '' }} belum mengunggah materi yang sesuai dengan filter pencarian Anda.
                </p>
                @if ($selectedMapelId || $searchQuery)
                    <a href="{{ route('siswa.materi.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#1E9E63] hover:underline">
                        <span>Reset filter pencarian</span>
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($materis->hasPages())
        <div class="p-4 bg-white rounded-lg border border-[#E5E7EB]">
            {{ $materis->links() }}
        </div>
    @endif

</div>
@endsection
