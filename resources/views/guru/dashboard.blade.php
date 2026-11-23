@extends('layouts.siakad')

@php
    $pageTitle = 'Dashboard Guru';
    $pageSubtitle = 'Ruang Pendidik & Manajemen Kelas';
    $initials = collect(explode(' ', $guru->name))->map(fn($seg) => mb_substr($seg, 0, 1))->take(2)->join('');
@endphp

@section('content')
<div class="space-y-5">

    <!-- 1. Kartu Identitas Guru (Formal, Bersih, Terstruktur) -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5">
        <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-5">
            <!-- Avatar Formal Guru -->
            <div class="w-16 h-20 rounded-md bg-[#F4F6F8] border border-[#E5E7EB] flex flex-col items-center justify-center flex-shrink-0">
                <span class="font-heading font-bold text-lg text-[#1F2937]">{{ strtoupper($initials) }}</span>
                <span class="text-[9px] font-semibold uppercase tracking-wider text-[#6B7280] mt-0.5">Guru</span>
            </div>

            <div class="flex-1 min-w-0 space-y-2.5 w-full">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <h2 class="font-heading font-bold text-base sm:text-lg text-[#1F2937] tracking-tight">
                        Selamat Datang, {{ $guru->name }}
                    </h2>
                    <a href="{{ route('guru.presensi.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors self-start sm:self-auto cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">fact_check</span>
                        <span>Input Presensi</span>
                    </a>
                </div>

                <!-- Badge Identitas Berbingkai Tipis -->
                <div class="flex flex-wrap items-center gap-2 text-xs sm:text-[13px]">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded border border-[#D1D5DB] bg-[#F9FAFB] text-[#374151] font-medium">
                        NIP: <strong class="ml-1 text-[#1F2937]">{{ $guru->nis_nip ?? '-' }}</strong>
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded border border-[#D1D5DB] bg-[#F9FAFB] text-[#374151] font-medium">
                        Status: <strong class="ml-1 text-[#146C43]">Tenaga Pendidik Aktif</strong>
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded border border-[#D1D5DB] bg-[#F9FAFB] text-[#374151] font-medium">
                        Wali Kelas: <strong class="ml-1 text-[#1F2937]">{{ $guru->kelasWali->pluck('nama_kelas')->join(', ') ?: '-' }}</strong>
                    </span>
                </div>

                <!-- Banner Informasi Akademik -->
                <div class="px-3.5 py-2 rounded-md bg-[#FFF7ED] border border-[#FED7AA] text-xs text-[#9A3412] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[17px] text-[#D97706] flex-shrink-0">info</span>
                    <span>
                        Sesi presensi tatap muka &amp; QR Code dinamis tersedia pada menu <strong class="font-semibold">Input Presensi</strong> sesuai jadwal mengajar hari ini ({{ $hariIni }}).
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Kartu Statistik Ringkas Guru -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Rombel Diajar -->
        <div class="bg-white rounded-lg border border-[#E5E7EB] p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Rombel Diajar</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="font-heading font-bold text-2xl text-[#1F2937]">{{ $jumlahKelasDiajar }}</span>
                    <span class="text-xs text-[#6B7280] font-medium">Kelas</span>
                </div>
                <p class="text-[11px] text-[#6B7280] mt-1">Total alokasi bimbingan mengajar</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] flex items-center justify-center text-[#1E9E63]">
                <span class="material-symbols-outlined text-[22px]">meeting_room</span>
            </div>
        </div>

        <!-- Jadwal Mengajar Guru -->
        <div class="bg-white rounded-lg border border-[#E5E7EB] p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Jadwal Mengajar</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="font-heading font-bold text-2xl text-[#1F2937]">{{ $totalJadwal }}</span>
                    <span class="text-xs text-[#6B7280] font-medium">Sesi Mingguan</span>
                </div>
                <p class="text-[11px] text-[#6B7280] mt-1">{{ $jadwalHariIni->count() }} sesi aktif hari ini ({{ $hariIni }})</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] flex items-center justify-center text-[#1E9E63]">
                <span class="material-symbols-outlined text-[22px]">calendar_month</span>
            </div>
        </div>

        <!-- Materi Daring -->
        <div class="bg-white rounded-lg border border-[#E5E7EB] p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Materi Daring</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="font-heading font-bold text-2xl text-[#1F2937]">{{ $totalMateri }}</span>
                    <span class="text-xs text-[#6B7280] font-medium">Bahan Ajar</span>
                </div>
                <p class="text-[11px] text-[#6B7280] mt-1">Modul &amp; materi pembelajaran aktif</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] flex items-center justify-center text-[#1E9E63]">
                <span class="material-symbols-outlined text-[22px]">menu_book</span>
            </div>
        </div>
    </div>

    <!-- 3. Dua Kolom: Jadwal Mengajar & Materi/Pengumuman -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- Jadwal Mengajar Guru (8 Kolom) -->
        <div class="lg:col-span-8 bg-white rounded-lg border border-[#E5E7EB] overflow-hidden" id="jadwal-mengajar">
            <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between">
                <div>
                    <h3 class="font-heading font-semibold text-base text-[#1F2937]">Jadwal Mengajar Anda</h3>
                    <p class="text-xs text-[#6B7280] mt-0.5">Daftar kelas bimbingan Semester Ganjil T.A. 2026/2027</p>
                </div>
                <span class="text-xs font-medium px-2.5 py-1 rounded bg-[#F9FAFB] border border-[#E5E7EB] text-[#4B5563]">
                    {{ $semuaJadwal->count() }} Jadwal
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F9FAFB] border-b border-[#E5E7EB] text-[#6B7280] font-semibold">
                            <th class="py-3 px-4">Hari</th>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4">Mata Pelajaran</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] text-[#374151]">
                        @forelse ($semuaJadwal as $j)
                            <tr class="hover:bg-[#F9FAFB] transition-colors">
                                <td class="py-3 px-4 font-semibold {{ $j->hari === $hariIni ? 'text-[#1E9E63]' : 'text-[#1F2937]' }}">
                                    {{ $j->hari }}
                                </td>
                                <td class="py-3 px-4 text-[#4B5563] whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px] text-[#1E9E63]">schedule</span>
                                        <span>{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</span>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-semibold px-2 py-0.5 rounded bg-[#F9FAFB] border border-[#E5E7EB] text-[#1F2937] text-[11px]">
                                        {{ $j->kelas->nama_kelas ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-[#1F2937] font-medium">
                                    {{ $j->mataPelajaran->nama_mapel ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('guru.presensi.create', $j->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-[#1E9E63] hover:bg-[#168251] text-white rounded-md text-[11px] font-medium transition-colors cursor-pointer">
                                        <span class="material-symbols-outlined text-[14px]">fact_check</span>
                                        <span>Buka Presensi</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-[#6B7280]">
                                    Belum ada jadwal mengajar yang dialokasikan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Materi Pembelajaran & Pengumuman Guru (4 Kolom) -->
        <div class="lg:col-span-4 space-y-5">

            <!-- Materi Yang Pernah Diunggah -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] p-5" id="materi-pelajaran">
                <div class="flex items-center justify-between pb-3 border-b border-[#E5E7EB]">
                    <h3 class="font-heading font-semibold text-sm text-[#1F2937]">Materi Anda</h3>
                    <a href="{{ route('guru.materi.index') }}" class="text-xs font-semibold text-[#1E9E63] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="divide-y divide-[#E5E7EB]">
                    @forelse ($materis as $m)
                        <div class="py-3 space-y-1">
                            <div class="flex flex-wrap items-center justify-between gap-1 text-xs">
                                <span class="px-2 py-0.5 rounded bg-[#F9FAFB] border border-[#E5E7EB] text-[#1F2937] font-medium">
                                    {{ $m->kelas->nama_kelas ?? '-' }}
                                </span>
                                <span class="text-[#6B7280] break-all">{{ basename($m->file_path) }}</span>
                            </div>
                            <h4 class="font-semibold text-xs text-[#1F2937] leading-snug pt-0.5">
                                {{ $m->judul }}
                            </h4>
                            <p class="text-[11px] text-[#6B7280]">
                                {{ $m->mataPelajaran->nama_mapel ?? '-' }}
                            </p>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-[#6B7280]">
                            Belum ada materi yang diunggah.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pengumuman Guru -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] p-5" id="pengumuman">
                <div class="flex items-center justify-between pb-3 border-b border-[#E5E7EB]">
                    <h3 class="font-heading font-semibold text-sm text-[#1F2937]">Pengumuman Sekolah</h3>
                    <a href="{{ route('guru.pengumuman.index') }}" class="text-xs font-semibold text-[#1E9E63] hover:underline">
                        Lihat Semua Pengumuman
                    </a>
                </div>

                <div class="divide-y divide-[#E5E7EB]">
                    @forelse ($pengumumans as $p)
                        <a href="{{ route('guru.pengumuman.show', $p->id) }}" class="block py-3 space-y-1 hover:bg-[#F9FAFB] -mx-2 px-2 rounded-md transition-colors">
                            <span class="text-xs text-[#6B7280]">{{ \Carbon\Carbon::parse($p->created_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</span>
                            <h4 class="font-semibold text-xs sm:text-sm text-[#1F2937] leading-snug">{{ $p->judul }}</h4>
                            <p class="text-xs text-[#6B7280] leading-relaxed line-clamp-2">{{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 140) }}</p>
                        </a>
                    @empty
                        <div class="py-5 text-center text-xs text-[#6B7280]">
                            Tidak ada pengumuman baru.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
