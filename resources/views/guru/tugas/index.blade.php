@extends('layouts.siakad')

@php
    $pageTitle = 'Tugas & Evaluasi';
    $pageSubtitle = 'Manajemen Penugasan & Penilaian Guru';
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

    <!-- Header Section & Tombol Buat Tugas -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-heading font-bold text-[#1F2937] tracking-tight">Manajemen Tugas &amp; Evaluasi</h2>
            <p class="text-xs text-[#6B7280] mt-0.5">Kelola penugasan siswa, batas waktu pengumpulan, periksa berkas jawaban, dan penilaian terpadu.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.tugas.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">add_task</span>
                <span>+ Buat Tugas Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar: Pilihan Kelas & Kolom Pencarian -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-4">
        <form method="GET" action="{{ route('guru.tugas.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
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
                <label for="search-tugas" class="block text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Cari Judul / Topik Tugas</label>
                <div class="relative">
                    <input type="text" id="search-tugas" name="q" value="{{ $searchQuery }}" placeholder="Ketik kata kunci judul penugasan..." class="w-full text-xs sm:text-sm font-normal pl-9 pr-3.5 py-2.5 rounded-lg border border-[#D1D5DB] bg-white text-[#1F2937] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15">
                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-[18px] text-[#9CA3AF]">search</span>
                </div>
            </div>

            <!-- Tombol Submit / Reset -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full px-3 py-2.5 rounded-lg bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                    Cari
                </button>
                @if ($selectedKelasId || $searchQuery)
                    <a href="{{ route('guru.tugas.index') }}" class="px-3 py-2.5 rounded-lg bg-[#F9FAFB] border border-[#D1D5DB] hover:bg-[#F3F4F6] text-[#4B5563] text-xs font-semibold transition-colors text-center" title="Reset Filter">
                        <span class="material-symbols-outlined text-[16px] align-middle">restart_alt</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Grid / Daftar Tugas Guru -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($tugasList as $tugas)
            @php
                $totalSiswa = \App\Models\User::where('role', 'siswa')->where('kelas_id', $tugas->kelas_id)->count();
                $submittedCount = $tugas->pengumpulanTugas->where('status_pengajuan', 'Terkirim')->count();
                $gradedCount = $tugas->pengumpulanTugas->where('status_penilaian', 'Sudah dinilai')->count();
                $isLewat = $tugas->isLewatBatas();
                $persenKumpul = $totalSiswa > 0 ? round(($submittedCount / $totalSiswa) * 100) : 0;
            @endphp
            <div class="bg-white rounded-lg border border-[#E5E7EB] flex flex-col justify-between p-5 hover:border-[#1E9E63]/60 transition-colors">
                <div>
                    <!-- Header Kartu: Kelas, Mapel & Status Batas -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#F9FAFB] text-[#374151] border border-[#E5E7EB]">
                            {{ $tugas->kelas->nama_kelas }}
                        </span>
                        @if ($isLewat)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-[#F3F4F6] text-[#6B7280] border border-[#E5E7EB]">
                                Berakhir
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#1E9E63]"></span>
                                <span>Aktif</span>
                            </span>
                        @endif
                    </div>

                    <h3 class="font-heading font-semibold text-sm text-[#1F2937] leading-snug line-clamp-2">
                        {{ $tugas->judul }}
                    </h3>
                    <p class="text-xs text-[#6B7280] mt-1 font-medium">{{ $tugas->mataPelajaran->nama_mapel }}</p>

                    <!-- Informasi Waktu & Batas Waktu -->
                    <div class="mt-4 pt-3 border-t border-[#E5E7EB] space-y-1.5 text-[11px] text-[#4B5563]">
                        <div class="flex items-center justify-between">
                            <span class="text-[#6B7280]">Waktu Buka:</span>
                            <span class="text-[#1F2937]">{{ $tugas->waktu_buka->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[#6B7280]">Jatuh Tempo:</span>
                            <span class="font-semibold {{ $isLewat ? 'text-red-600' : 'text-[#146C43]' }}">{{ $tugas->batas_waktu->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                    </div>

                    <!-- Progress Bar Pengumpulan -->
                    <div class="mt-4 pt-3 border-t border-[#E5E7EB] space-y-1.5">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-[#6B7280] font-medium">Terkumpul:</span>
                            <span class="font-semibold text-[#1F2937]">{{ $submittedCount }} / {{ $totalSiswa }} Siswa ({{ $persenKumpul }}%)</span>
                        </div>
                        <div class="w-full bg-[#E5E7EB] rounded-full h-1.5 overflow-hidden">
                            <div class="bg-[#1E9E63] h-1.5 rounded-full transition-all duration-500" style="width: {{ $persenKumpul }}%;"></div>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-[#6B7280]">
                            <span>Sudah Dinilai: <strong class="text-[#1F2937]">{{ $gradedCount }}</strong></span>
                            <span>Maks: {{ $tugas->max_jumlah_file }} file ({{ $tugas->max_ukuran_mb }}MB)</span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="mt-4 pt-3 border-t border-[#E5E7EB] flex items-center justify-between gap-2">
                    <a href="{{ route('guru.tugas.show', $tugas->id) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors">
                        <span class="material-symbols-outlined text-[15px]">fact_check</span>
                        <span>Rekap &amp; Nilai</span>
                    </a>
                    <a href="{{ route('guru.tugas.edit', $tugas->id) }}" class="p-2 rounded-md bg-white border border-[#D1D5DB] hover:bg-[#FFF7ED] hover:text-[#B45309] text-[#4B5563] transition-colors" title="Edit Tugas">
                        <span class="material-symbols-outlined text-[16px]">edit</span>
                    </a>
                    <form method="POST" action="{{ route('guru.tugas.destroy', $tugas->id) }}" onsubmit="return handleConfirmForm(event, this, 'Konfirmasi Hapus Tugas', 'Apakah Anda yakin ingin menghapus tugas \'{{ addslashes($tugas->judul) }}\'? Seluruh data berkas jawaban siswa akan dihapus permanen.', 'Ya, Hapus Data');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-md bg-white border border-[#D1D5DB] hover:bg-red-50 hover:text-red-600 text-[#4B5563] transition-colors cursor-pointer" title="Hapus Tugas">
                            <span class="material-symbols-outlined text-[16px]">delete</span>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full p-10 text-center bg-white rounded-lg border border-[#E5E7EB] space-y-2.5">
                <span class="material-symbols-outlined text-[40px] text-[#9CA3AF]">assignment_late</span>
                <h4 class="font-heading font-semibold text-sm text-[#1F2937]">Belum Ada Penugasan Pembelajaran</h4>
                <p class="text-xs text-[#6B7280] max-w-md mx-auto">
                    Anda belum membuat penugasan untuk rombel kelas yang diajar. Mulai buat tugas baru dengan klik tombol di bawah.
                </p>
                <a href="{{ route('guru.tugas.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors">
                    <span class="material-symbols-outlined text-[16px]">add_task</span>
                    <span>Buat Tugas Pertama</span>
                </a>
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
