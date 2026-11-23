@extends('layouts.siakad')

@php
    $pageTitle = 'Dashboard Administrator';
    $pageSubtitle = 'Panel Manajemen Sistem Akademik SMAN 1 Gunungsitoli';
    $initials = collect(explode(' ', auth()->user()->name))->map(fn($seg) => mb_substr($seg, 0, 1))->take(2)->join('');
@endphp

@section('content')
<div class="space-y-5">

    <!-- 1. Kartu Identitas Admin -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5">
        <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-5">
            <div class="w-16 h-20 rounded-md bg-[#F4F6F8] border border-[#E5E7EB] flex flex-col items-center justify-center flex-shrink-0">
                <span class="font-heading font-bold text-lg text-[#1F2937]">{{ strtoupper($initials) }}</span>
                <span class="text-[9px] font-semibold uppercase tracking-wider text-[#6B7280] mt-0.5">Admin</span>
            </div>

            <div class="flex-1 min-w-0 space-y-2.5 w-full">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <h2 class="font-heading font-bold text-base sm:text-lg text-[#1F2937] tracking-tight">
                        Panel Utama Administrator SIAKAD
                    </h2>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-[#E8F5EE] border border-[#BBF7D0] text-[11px] font-semibold text-[#146C43] self-start sm:self-auto">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1E9E63]"></span>
                        <span>T.A. 2026/2027 Ganjil</span>
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded border border-[#D1D5DB] bg-[#F9FAFB] text-[#374151] font-medium">
                        Pengguna: <strong class="ml-1 text-[#1F2937]">{{ auth()->user()->name }}</strong>
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded border border-[#D1D5DB] bg-[#F9FAFB] text-[#374151] font-medium">
                        Status: <strong class="ml-1 text-[#146C43]">Administrator Sistem Terpadu</strong>
                    </span>
                </div>

                <div class="px-3.5 py-2 rounded-md bg-[#FFF7ED] border border-[#FED7AA] text-xs text-[#9A3412] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[17px] text-[#D97706] flex-shrink-0">info</span>
                    <span>
                        Pembuatan akun guru/siswa dan reset kata sandi dikelola terpusat oleh Administrator <strong class="font-semibold">SMA NEGERI 1 GUNUNGSITOLI</strong>.
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. 4 Kartu Statistik Ringkas -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Siswa -->
        <div class="bg-white rounded-lg border border-[#E5E7EB] p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Total Siswa Aktif</p>
                <p class="font-heading font-bold text-2xl text-[#1F2937] mt-1">{{ $totalSiswa }}</p>
                <p class="text-[11px] text-[#6B7280] mt-0.5">Siswa Terdaftar</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] flex items-center justify-center text-[#1E9E63]">
                <span class="material-symbols-outlined text-[22px]">groups</span>
            </div>
        </div>

        <!-- Total Guru -->
        <div class="bg-white rounded-lg border border-[#E5E7EB] p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Total Tenaga Guru</p>
                <p class="font-heading font-bold text-2xl text-[#1F2937] mt-1">{{ $totalGuru }}</p>
                <p class="text-[11px] text-[#6B7280] mt-0.5">Guru Pengampu</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] flex items-center justify-center text-[#1E9E63]">
                <span class="material-symbols-outlined text-[22px]">school</span>
            </div>
        </div>

        <!-- Total Kelas -->
        <div class="bg-white rounded-lg border border-[#E5E7EB] p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Rombongan Belajar</p>
                <p class="font-heading font-bold text-2xl text-[#1F2937] mt-1">{{ $totalKelas }}</p>
                <p class="text-[11px] text-[#6B7280] mt-0.5">Kelas X, XI, XII</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] flex items-center justify-center text-[#1E9E63]">
                <span class="material-symbols-outlined text-[22px]">meeting_room</span>
            </div>
        </div>

        <!-- Total Mapel -->
        <div class="bg-white rounded-lg border border-[#E5E7EB] p-4 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Mata Pelajaran</p>
                <p class="font-heading font-bold text-2xl text-[#1F2937] mt-1">{{ $totalMapel }}</p>
                <p class="text-[11px] text-[#6B7280] mt-0.5">Kurikulum Aktif</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] flex items-center justify-center text-[#1E9E63]">
                <span class="material-symbols-outlined text-[22px]">menu_book</span>
            </div>
        </div>
    </div>

    <!-- 3. Dua Kolom: Tabel Jadwal Hari Ini & Daftar Pengumuman Terbaru -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- Tabel Jadwal Pelajaran Hari Ini (8 Kolom) -->
        <div class="lg:col-span-8 bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
            <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between gap-2">
                <div>
                    <h3 class="font-heading font-semibold text-base text-[#1F2937]">Jadwal Pelajaran Hari Ini ({{ $hariIni }})</h3>
                    <p class="text-xs text-[#6B7280] mt-0.5">Rekap kegiatan belajar mengajar aktif lintas kelas</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium px-2.5 py-1 rounded bg-[#F9FAFB] border border-[#E5E7EB] text-[#4B5563]">
                        Total: {{ $jadwalHariIni->count() }} Sesi
                    </span>
                    <a href="{{ route('admin.jadwal.index') }}" class="text-xs font-semibold text-[#1E9E63] hover:underline">
                        Kelola Jadwal
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F9FAFB] border-b border-[#E5E7EB] text-[#6B7280] font-semibold">
                            <th class="py-3 px-4">Jam Belajar</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4">Mata Pelajaran</th>
                            <th class="py-3 px-4">Guru Pengampu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB] text-[#374151]">
                        @forelse ($jadwalHariIni as $j)
                            <tr class="hover:bg-[#F9FAFB] transition-colors">
                                <td class="py-3 px-4 font-medium text-[#1F2937] whitespace-nowrap">
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
                                <td class="py-3 px-4 font-semibold text-[#1F2937]">
                                    {{ $j->mataPelajaran->nama_mapel ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-[#6B7280]">
                                    {{ $j->guru->name ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-[#6B7280]">
                                    Tidak ada jadwal pelajaran aktif pada hari ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Daftar Pengumuman Terbaru (4 Kolom) -->
        <div class="lg:col-span-4 bg-white rounded-lg border border-[#E5E7EB] p-5">
            <div class="flex items-center justify-between pb-3 border-b border-[#E5E7EB]">
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Pengumuman Sekolah</h3>
                <a href="{{ route('admin.pengumuman.index') }}" class="text-xs font-semibold text-[#1E9E63] hover:underline">
                    Kelola
                </a>
            </div>

            <div class="divide-y divide-[#E5E7EB]">
                @forelse ($pengumumanTerbaru as $pengumuman)
                    <div class="py-3.5 space-y-1">
                        <div class="flex items-center justify-between text-xs text-[#6B7280]">
                            <span>{{ \Carbon\Carbon::parse($pengumuman->created_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</span>
                            <span class="px-2 py-0.5 rounded bg-[#F9FAFB] border border-[#E5E7EB] text-[#4B5563] font-semibold uppercase text-[11px]">
                                {{ $pengumuman->target_role }}
                            </span>
                        </div>
                        <h4 class="font-semibold text-xs sm:text-sm text-[#1F2937] leading-snug">
                            {{ $pengumuman->judul }}
                        </h4>
                        <p class="text-xs text-[#6B7280] leading-relaxed">
                            {{ \Illuminate\Support\Str::limit(strip_tags($pengumuman->isi), 110) }}
                        </p>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-[#6B7280]">
                        Belum ada data pengumuman yang diterbitkan.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
