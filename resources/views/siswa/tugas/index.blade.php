@extends('layouts.siakad')

@php
    $pageTitle = 'Tugas & Evaluasi';
    $pageSubtitle = 'Daftar Penugasan Kelas ' . ($siswa->kelas->nama_kelas ?? '-');
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

    <!-- Header & Identitas Rombel Kelas Siswa Formal -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#F9FAFB] border border-[#D1D5DB] text-[#1F2937]">
                    Kelas {{ $siswa->kelas->nama_kelas ?? '-' }}
                </span>
                <span class="text-xs text-[#9CA3AF]">•</span>
                <span class="text-xs text-[#6B7280] font-medium">Tahun Ajaran 2026/2027 Ganjil • SMAN 1 Gunungsitoli</span>
            </div>
            <h2 class="text-lg font-heading font-bold text-[#1F2937] tracking-tight mt-1">
                Tugas &amp; Evaluasi Pembelajaran
            </h2>
            <p class="text-xs text-[#6B7280] mt-0.5">
                Pantau penugasan aktif, tenggat waktu pengumpulan, periksa nilai akhir, dan diskusikan pengajuan bersama guru.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-md bg-[#E8F5EE] border border-[#BBF7D0] text-xs font-semibold text-[#146C43] flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-[#1E9E63]">assignment</span>
                <span>{{ $tugasList->total() }} Tugas Terdaftar</span>
            </span>
        </div>
    </div>

    <!-- Filter Bar: Mata Pelajaran & Pencarian -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-4">
        <form method="GET" action="{{ route('siswa.tugas.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
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

            <!-- Pencarian Judul Tugas -->
            <div class="sm:col-span-5">
                <label for="search-tugas" class="block text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Cari Judul Penugasan</label>
                <div class="relative">
                    <input type="text" id="search-tugas" name="q" value="{{ $searchQuery }}" placeholder="Ketik kata kunci tugas..." class="w-full text-xs sm:text-sm font-normal pl-9 pr-3.5 py-2.5 rounded-lg border border-[#D1D5DB] bg-white text-[#1F2937] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15">
                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-[18px] text-[#9CA3AF]">search</span>
                </div>
            </div>

            <!-- Tombol Submit / Reset -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full px-3 py-2.5 rounded-lg bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                    Cari
                </button>
                @if ($selectedMapelId || $searchQuery)
                    <a href="{{ route('siswa.tugas.index') }}" class="px-3 py-2.5 rounded-lg bg-[#F9FAFB] border border-[#D1D5DB] hover:bg-[#F3F4F6] text-[#4B5563] text-xs font-semibold transition-colors text-center" title="Reset Filter">
                        <span class="material-symbols-outlined text-[18px] align-middle">restart_alt</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Grid Kartu Tugas Siswa -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($tugasList as $tugas)
            @php
                $submission = $tugas->pengumpulanTugas->first();
                $isSubmitted = $submission && $submission->status_pengajuan === 'Terkirim';
                $isGraded = $submission && $submission->status_penilaian === 'Sudah dinilai';
                $isLewat = $tugas->isLewatBatas();
            @endphp
            <div class="bg-white rounded-lg border border-[#E5E7EB] flex flex-col justify-between p-5 hover:border-[#1E9E63]/60 transition-colors">
                <div>
                    <!-- Header Kartu: Mata Pelajaran & Status Badge -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#F9FAFB] text-[#374151] border border-[#E5E7EB]">
                            {{ $tugas->mataPelajaran->nama_mapel }}
                        </span>

                        @if ($isGraded)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                                <span class="material-symbols-outlined text-[13px]">grade</span>
                                <span>Nilai: {{ $submission->nilai }}</span>
                            </span>
                        @elseif ($isSubmitted)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#1E9E63]"></span>
                                <span>Terkirim</span>
                            </span>
                        @elseif ($isLewat)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                Terlewat
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#FFF7ED] text-[#9A3412] border border-[#FED7AA]">
                                Belum Dikerjakan
                            </span>
                        @endif
                    </div>

                    <!-- Judul Tugas -->
                    <h3 class="font-heading font-semibold text-sm text-[#1F2937] leading-snug line-clamp-2">
                        {{ $tugas->judul }}
                    </h3>

                    <!-- Informasi Guru & Jadwal Batas Waktu -->
                    <div class="mt-4 pt-3 border-t border-[#E5E7EB] space-y-1.5 text-[11px] text-[#6B7280]">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[15px] text-[#9CA3AF]">person</span>
                            <span class="break-words">Guru: <strong class="text-[#1F2937]">{{ $tugas->guru->name }}</strong></span>
                        </div>
                        <div class="flex items-center justify-between pt-0.5">
                            <span>Jatuh Tempo:</span>
                            <span class="font-semibold {{ $isLewat ? 'text-red-600' : 'text-[#146C43]' }}">
                                {{ $tugas->batas_waktu->translatedFormat('d M Y, H:i') }} WIB
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-[#9CA3AF]">
                            <span>Maks: {{ $tugas->max_jumlah_file }} berkas</span>
                            <span>Maks: {{ $tugas->max_ukuran_mb }} MB/file</span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Buka Penugasan -->
                <div class="mt-4 pt-3 border-t border-[#E5E7EB]">
                    <a href="{{ route('siswa.tugas.show', $tugas->id) }}" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                        <span>Buka &amp; Kelola Pengajuan</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full p-10 text-center bg-white rounded-lg border border-[#E5E7EB] space-y-2.5">
                <span class="material-symbols-outlined text-[40px] text-[#9CA3AF]">task</span>
                <h4 class="font-heading font-semibold text-sm text-[#1F2937]">Belum Ada Penugasan Pembelajaran</h4>
                <p class="text-xs text-[#6B7280] max-w-md mx-auto">
                    Tidak ada penugasan yang aktif atau sesuai dengan filter pencarian pada rombel kelas {{ $siswa->kelas->nama_kelas ?? '' }}.
                </p>
                @if ($selectedMapelId || $searchQuery)
                    <a href="{{ route('siswa.tugas.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#1E9E63] hover:underline">
                        <span>Reset filter pencarian</span>
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if ($tugasList->hasPages())
        <div class="p-4 bg-white rounded-lg border border-[#E5E7EB]">
            {{ $tugasList->links() }}
        </div>
    @endif

</div>
@endsection
