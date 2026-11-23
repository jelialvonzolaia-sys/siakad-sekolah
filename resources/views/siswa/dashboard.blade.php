@extends('layouts.siakad')

@php
    $pageTitle = 'Beranda Siswa';
    $pageSubtitle = 'Ringkasan Akademik Siswa';
    $initials = collect(explode(' ', $siswa->name))->map(fn($seg) => mb_substr($seg, 0, 1))->take(2)->join('');
@endphp

@section('content')
<div class="space-y-5">

    <!-- 1. KARTU IDENTITAS SISWA & BANNER INFORMASI -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5">
        <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-5">
            <!-- Avatar / Foto Formal Siswa -->
            <div class="w-16 h-20 rounded-md bg-[#F4F6F8] border border-[#E5E7EB] flex flex-col items-center justify-center flex-shrink-0 text-[#6B7280]">
                <span class="font-heading font-bold text-lg text-[#1F2937]">{{ strtoupper($initials) }}</span>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-[#6B7280] mt-0.5">Siswa</span>
            </div>

            <!-- Informasi Identitas & Banner Peringatan -->
            <div class="flex-1 min-w-0 space-y-2.5 w-full">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <h2 class="font-heading font-bold text-lg text-[#1F2937] tracking-tight">
                        Selamat Datang, {{ $siswa->name }}
                    </h2>
                    <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-[#E8F5EE] border border-[#BBF7D0] text-xs font-semibold text-[#146C43] self-start sm:self-auto">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1E9E63]"></span>
                        <span>Peserta Didik Aktif</span>
                    </span>
                </div>

                <!-- Badge/Pill Berbingkai Tipis untuk Kode Identitas -->
                <div class="flex flex-wrap items-center gap-2 text-xs sm:text-[13px]">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded border border-[#D1D5DB] bg-[#F9FAFB] text-[#374151] font-medium">
                        NIS: <strong class="ml-1 text-[#1F2937]">{{ $siswa->nis_nip ?? '-' }}</strong>
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded border border-[#D1D5DB] bg-[#F9FAFB] text-[#374151] font-medium">
                        Kelas: <strong class="ml-1 text-[#1F2937]">{{ $kelas->nama_kelas ?? 'Belum Terdaftar' }}</strong> (Tingkat {{ $kelas->tingkat ?? '-' }})
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded border border-[#D1D5DB] bg-[#F9FAFB] text-[#374151] font-medium">
                        Wali Kelas: <strong class="ml-1 text-[#1F2937]">{{ $kelas->waliKelas->name ?? '-' }}</strong>
                    </span>
                    <a href="{{ route('siswa.presensi.index') }}" class="inline-flex items-center px-3 py-0.5 rounded-full border border-[#D1D5DB] bg-white hover:bg-[#E8F5EE] hover:border-[#1E9E63] text-[#1E9E63] font-medium transition-colors">
                        Presensi Kelas
                    </a>
                </div>

                <!-- Banner Informasi Krem/Kuning Lembut (#FFF7ED border #FED7AA) -->
                <div class="px-3.5 py-2.5 rounded-md bg-[#FFF7ED] border border-[#FED7AA] text-xs sm:text-[13px] text-[#9A3412] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-[#D97706] flex-shrink-0">warning</span>
                    <span>
                        Pastikan Anda mengisi presensi kelas tepat waktu dan memeriksa tugas aktif di portal <strong class="font-semibold">SMA NEGERI 1 GUNUNGSITOLI</strong>.
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. TIGA KARTU STATISTIK RINGKAS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Kartu 1: Persentase Kehadiran -->
        <div class="bg-white rounded-lg border border-[#E5E7EB] p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Persentase Kehadiran</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="font-heading font-bold text-2xl text-[#1F2937]">{{ $persentaseKehadiran }}%</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded bg-[#E8F5EE] text-[#146C43]">
                        Hadir
                    </span>
                </div>
                <p class="text-xs text-[#6B7280] mt-1">
                    {{ $totalHadir }} Hadir dari {{ $totalPresensi }} pertemuan tercatat
                </p>
            </div>
            <div class="w-11 h-11 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] flex items-center justify-center text-[#1E9E63] flex-shrink-0">
                <span class="material-symbols-outlined text-[22px]">fact_check</span>
            </div>
        </div>

        <!-- Kartu 2: Jadwal Hari Ini -->
        <div class="bg-white rounded-lg border border-[#E5E7EB] p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Jadwal Hari Ini ({{ $hariIni }})</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="font-heading font-bold text-2xl text-[#1F2937]">{{ $jadwalHariIniCount }}</span>
                    <span class="text-xs sm:text-sm text-[#6B7280] font-medium">Mata Pelajaran</span>
                </div>
                <p class="text-xs text-[#6B7280] mt-1">
                    Kelas {{ $kelas->nama_kelas ?? '-' }} • SMAN 1 Gunungsitoli
                </p>
            </div>
            <div class="w-11 h-11 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] flex items-center justify-center text-[#1E9E63] flex-shrink-0">
                <span class="material-symbols-outlined text-[22px]">calendar_today</span>
            </div>
        </div>

        <!-- Kartu 3: Materi Tersedia -->
        <div class="bg-white rounded-lg border border-[#E5E7EB] p-4 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Materi Tersedia</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="font-heading font-bold text-2xl text-[#1F2937]">{{ $totalMateri }}</span>
                    <span class="text-xs sm:text-sm text-[#6B7280] font-medium">Modul Pembelajaran</span>
                </div>
                <p class="text-xs text-[#6B7280] mt-1">
                    Diunggah oleh guru pengampu kelas
                </p>
            </div>
            <div class="w-11 h-11 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] flex items-center justify-center text-[#1E9E63] flex-shrink-0">
                <span class="material-symbols-outlined text-[22px]">menu_book</span>
            </div>
        </div>
    </div>

    <!-- 3. AREA UTAMA DUA KOLOM -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- KOLOM KIRI (6 KOLOM): JADWAL PELAJARAN & JADWAL UJIAN -->
        <div class="lg:col-span-6 space-y-5">

            <!-- Card Jadwal Pelajaran dengan Tab Hari + Tanggal (Senin s.d. Sabtu) -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] p-5" id="jadwal-pelajaran">
                <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-[#E5E7EB]">
                    <div>
                        <h3 class="font-heading font-semibold text-base text-[#1F2937]">Jadwal Pelajaran</h3>
                        <p class="text-xs text-[#6B7280] mt-0.5">Kelas {{ $kelas->nama_kelas ?? '' }} • Semester Ganjil T.A. 2026/2027</p>
                    </div>
                    <span class="text-xs font-medium px-2.5 py-1 rounded border border-[#E5E7EB] bg-[#F9FAFB] text-[#4B5563]">
                        Hari: <strong class="text-[#1F2937]">{{ $hariIni }}</strong>
                    </span>
                </div>

                <!-- Tab Hari Horizontal (Senin s.d. Sabtu dengan Tanggal 3-huruf Bulan) -->
                @php
                    $bulanSingkat = [
                        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
                    ];
                    $startOfWeek = \Carbon\Carbon::now()->startOfWeek(\Carbon\CarbonInterface::MONDAY);
                    $hariList = [
                        ['nama' => 'Senin',  'tgl' => $startOfWeek->copy()],
                        ['nama' => 'Selasa', 'tgl' => $startOfWeek->copy()->addDay()],
                        ['nama' => 'Rabu',   'tgl' => $startOfWeek->copy()->addDays(2)],
                        ['nama' => 'Kamis',  'tgl' => $startOfWeek->copy()->addDays(3)],
                        ['nama' => 'Jumat',  'tgl' => $startOfWeek->copy()->addDays(4)],
                        ['nama' => 'Sabtu',  'tgl' => $startOfWeek->copy()->addDays(5)],
                    ];
                @endphp
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2 py-3.5 border-b border-[#E5E7EB]">
                    @foreach ($hariList as $itemHari)
                        @php
                            $h = $itemHari['nama'];
                            $tglStr = $itemHari['tgl']->day . ' ' . $bulanSingkat[$itemHari['tgl']->month];
                        @endphp
                        <button type="button"
                                onclick="switchJadwalTab('{{ $h }}')"
                                id="tab-btn-{{ $h }}"
                                class="tab-jadwal-btn py-2 px-1.5 rounded-md border text-center transition-colors cursor-pointer {{ $h === $hariIni ? 'bg-[#20A86B] text-white font-semibold border-[#20A86B]' : 'bg-white text-[#4B5563] border-[#E5E7EB] hover:bg-[#F9FAFB]' }}">
                            <span class="block text-xs sm:text-[13px] font-semibold leading-tight">{{ $h }}</span>
                            <span class="block text-xs opacity-90 mt-0.5 leading-tight">{{ $tglStr }}</span>
                        </button>
                    @endforeach
                </div>

                <!-- Daftar Jadwal Tiap Hari -->
                <div class="pt-1">
                    @foreach ($hariList as $itemHari)
                        @php $h = $itemHari['nama']; @endphp
                        <div id="jadwal-list-{{ $h }}" class="jadwal-hari-container divide-y divide-[#E5E7EB] {{ $h === $hariIni ? '' : 'hidden' }}">
                            @php
                                $jadwalHari = $jadwalSemuaHari->get($h, collect());
                            @endphp

                            @if ($jadwalHari->count() > 0)
                                @foreach ($jadwalHari as $item)
                                    <div class="py-3.5 space-y-1">
                                        <div class="flex items-center gap-1.5 text-xs sm:text-[13px] font-semibold text-[#1F2937]">
                                            <span class="material-symbols-outlined text-[16px] text-[#1E9E63]">schedule</span>
                                            <span>{{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}</span>
                                        </div>
                                        <h4 class="font-semibold text-sm text-[#1F2937] leading-snug">
                                            {{ $item->mataPelajaran->nama_mapel ?? '-' }}
                                        </h4>
                                        <p class="text-xs text-[#6B7280]">
                                            {{ $item->mataPelajaran->kode_mapel ?? 'MP' }} - Kelas {{ $kelas->nama_kelas ?? '-' }} • {{ $item->guru->name ?? 'Guru Pengampu' }}
                                        </p>
                                    </div>
                                @endforeach
                            @else
                                <div class="py-8 text-center text-xs sm:text-sm text-[#6B7280]">
                                    <span class="material-symbols-outlined text-[28px] text-[#9CA3AF] block mb-1">event_busy</span>
                                    <span>Tidak ada jadwal pelajaran kelas pada hari {{ $h }}.</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Card Jadwal Penilaian & Ujian -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden" id="jadwal-ujian">
                <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between">
                    <div>
                        <h3 class="font-heading font-semibold text-base text-[#1F2937]">Jadwal Ujian</h3>
                        <p class="text-xs text-[#6B7280] mt-0.5">Penilaian Akhir Semester (PAS) Ganjil T.A. 2026/2027</p>
                    </div>
                    <span class="text-xs font-medium px-2.5 py-0.5 rounded bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                        Resmi
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs sm:text-[13px] text-left border-collapse">
                        <thead>
                            <tr class="bg-[#F9FAFB] border-b border-[#E5E7EB] text-[#6B7280] font-semibold">
                                <th class="py-3 px-4">Mata Pelajaran</th>
                                <th class="py-3 px-3">Jenis</th>
                                <th class="py-3 px-3">Tanggal</th>
                                <th class="py-3 px-3">Waktu</th>
                                <th class="py-3 px-4">Ruangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB] text-[#374151]">
                            @forelse ($jadwalUjian as $ujian)
                                <tr class="hover:bg-[#F9FAFB] transition-colors">
                                    <td class="py-3 px-4 font-semibold text-[#1F2937]">
                                        {{ $ujian->mataPelajaran->nama_mapel ?? '-' }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 rounded bg-[#F9FAFB] border border-[#E5E7EB] text-xs font-medium text-[#4B5563]">
                                            {{ $ujian->jenis_ujian }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-[#4B5563]">
                                        {{ \Carbon\Carbon::parse($ujian->tanggal)->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="py-3 px-3 font-medium text-[#1F2937]">
                                        {{ substr($ujian->waktu_mulai, 0, 5) }} WIB
                                    </td>
                                    <td class="py-3 px-4 text-[#6B7280]">
                                        {{ $ujian->ruangan }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-[#6B7280]">
                                        Belum ada jadwal ujian yang dijadwalkan untuk kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN (6 KOLOM): PRESENSI KELAS, MATERI & PENGUMUMAN -->
        <div class="lg:col-span-6 space-y-5">

            <!-- 1. Rekap Presensi Kelas -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] p-5" id="rekap-presensi">
                <div class="flex items-center justify-between pb-3 border-b border-[#E5E7EB]">
                    <div>
                        <h3 class="font-heading font-semibold text-base text-[#1F2937]">Presensi Kelas</h3>
                        <p class="text-xs text-[#6B7280] mt-0.5">Rekapitulasi kehadiran Semester Ganjil 2026/2027</p>
                    </div>
                    <a href="{{ route('siswa.presensi.index') }}" class="text-xs sm:text-[13px] font-semibold text-[#1E9E63] hover:underline flex items-center gap-0.5">
                        <span>Lihat Detail</span>
                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 text-center">
                    <div class="p-3 rounded-md bg-[#E8F5EE]/70 border border-[#BBF7D0]">
                        <p class="text-xs font-semibold text-[#146C43]">Hadir</p>
                        <p class="font-heading font-bold text-xl text-[#146C43] mt-0.5">{{ $totalHadir }}</p>
                    </div>
                    <div class="p-3 rounded-md bg-[#F9FAFB] border border-[#E5E7EB]">
                        <p class="text-xs font-semibold text-[#6B7280]">Sakit</p>
                        <p class="font-heading font-bold text-xl text-[#1F2937] mt-0.5">{{ $totalSakit }}</p>
                    </div>
                    <div class="p-3 rounded-md bg-[#F9FAFB] border border-[#E5E7EB]">
                        <p class="text-xs font-semibold text-[#6B7280]">Izin</p>
                        <p class="font-heading font-bold text-xl text-[#1F2937] mt-0.5">{{ $totalIzin }}</p>
                    </div>
                    <div class="p-3 rounded-md bg-[#FEF2F2] border border-[#FECACA]">
                        <p class="text-xs font-semibold text-[#B91C1C]">Alpha</p>
                        <p class="font-heading font-bold text-xl text-[#B91C1C] mt-0.5">{{ $totalAlpha }}</p>
                    </div>
                </div>
            </div>

            <!-- 2. Kartu Materi Pembelajaran Terbaru -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] p-5" id="materi-pelajaran">
                <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-[#E5E7EB]">
                    <div>
                        <h3 class="font-heading font-semibold text-base text-[#1F2937]">Materi Pembelajaran</h3>
                        <p class="text-xs text-[#6B7280] mt-0.5">Bahan ajar terbaru kelas {{ $kelas->nama_kelas ?? '-' }}</p>
                    </div>
                    <a href="{{ route('siswa.materi.index') }}" class="text-xs sm:text-[13px] font-semibold text-[#1E9E63] hover:underline">
                        Lihat Semua ({{ $kelas->nama_kelas ?? '-' }})
                    </a>
                </div>

                <div class="divide-y divide-[#E5E7EB]">
                    @forelse ($materiTerbaru as $materi)
                        <div class="py-3.5 flex items-start justify-between gap-3">
                            <div class="min-w-0 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2 py-0.5 rounded bg-[#F9FAFB] border border-[#E5E7EB] text-xs font-semibold text-[#4B5563]">
                                        {{ $materi->mataPelajaran->nama_mapel ?? 'Mapel' }}
                                    </span>
                                    <span class="text-xs text-[#6B7280]">{{ $materi->guru->name ?? '-' }}</span>
                                </div>
                                <h4 class="font-semibold text-sm text-[#1F2937] leading-snug break-words">
                                    {{ $materi->judul }}
                                </h4>
                                <p class="text-xs text-[#6B7280] break-all">{{ basename($materi->file_path) }}</p>
                            </div>
                            <a href="{{ route('siswa.materi.download', $materi->id) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-medium transition-colors flex-shrink-0 cursor-pointer">
                                <span class="material-symbols-outlined text-[15px]">download</span>
                                <span>Unduh</span>
                            </a>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs sm:text-sm text-[#6B7280]">
                            Belum ada materi pembelajaran yang diunggah untuk kelas ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 3. Kartu Pengumuman Sekolah -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] p-5" id="pengumuman-sekolah">
                <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-[#E5E7EB]">
                    <div>
                        <h3 class="font-heading font-semibold text-base text-[#1F2937]">Pengumuman Sekolah</h3>
                        <p class="text-xs text-[#6B7280] mt-0.5">Informasi resmi SMA Negeri 1 Gunungsitoli</p>
                    </div>
                    <a href="{{ route('siswa.pengumuman.index') }}" class="text-xs sm:text-[13px] font-semibold text-[#1E9E63] hover:underline">
                        Lihat Semua Pengumuman
                    </a>
                </div>

                <div class="divide-y divide-[#E5E7EB]">
                    @forelse ($pengumumans as $item)
                        <a href="{{ route('siswa.pengumuman.show', $item->id) }}" class="block py-3.5 space-y-1 hover:bg-[#F9FAFB] -mx-2 px-2 rounded-md transition-colors">
                            <div class="flex items-center justify-between text-xs text-[#6B7280]">
                                <span>{{ \Carbon\Carbon::parse($item->created_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</span>
                                <span class="px-2 py-0.5 rounded bg-[#F9FAFB] border border-[#E5E7EB] text-[11px] font-medium text-[#4B5563]">
                                    {{ $item->target_role === 'semua' ? 'Semua' : 'Siswa' }}
                                </span>
                            </div>
                            <h4 class="font-semibold text-sm text-[#1F2937] leading-snug">
                                {{ $item->judul }}
                            </h4>
                            <p class="text-xs sm:text-[13px] text-[#6B7280] leading-relaxed line-clamp-2">
                                {{ \Illuminate\Support\Str::limit(strip_tags($item->isi), 140) }}
                            </p>
                            <p class="text-xs text-[#9CA3AF] pt-0.5">
                                Oleh: {{ $item->penulis->name ?? 'Admin SMAN 1 Gunungsitoli' }}
                            </p>
                        </a>
                    @empty
                        <div class="py-6 text-center text-xs sm:text-sm text-[#6B7280]">
                            Belum ada pengumuman baru untuk siswa.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

@push('scripts')
<script>
    function switchJadwalTab(hari) {
        document.querySelectorAll('.jadwal-hari-container').forEach(el => {
            el.classList.add('hidden');
        });

        const targetContainer = document.getElementById('jadwal-list-' + hari);
        if (targetContainer) {
            targetContainer.classList.remove('hidden');
        }

        document.querySelectorAll('.tab-jadwal-btn').forEach(btn => {
            btn.className = "tab-jadwal-btn py-2 px-1.5 rounded-md border text-center transition-colors cursor-pointer bg-white text-[#4B5563] border-[#E5E7EB] hover:bg-[#F9FAFB]";
        });

        const activeBtn = document.getElementById('tab-btn-' + hari);
        if (activeBtn) {
            activeBtn.className = "tab-jadwal-btn py-2 px-1.5 rounded-md border text-center transition-colors cursor-pointer bg-[#20A86B] text-white font-semibold border-[#20A86B]";
        }
    }
</script>
@endpush
@endsection
