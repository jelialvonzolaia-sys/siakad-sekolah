@extends('layouts.siakad')

@php
    $pageTitle = 'Presensi Kelas';
    $pageSubtitle = 'Kelola Sesi Tatap Muka & Kehadiran Siswa';
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

    <!-- Header Section -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-heading font-bold text-[#1F2937] tracking-tight">Presensi Kelas &amp; Tatap Muka</h2>
            <p class="text-xs text-[#6B7280] mt-0.5">Kelola pembukaan sesi absensi mandiri, pemindaian QR Code interaktif, dan rekap kehadiran siswa.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 rounded-md bg-[#F9FAFB] border border-[#E5E7EB] text-xs font-medium text-[#374151] inline-flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-[#1E9E63]"></span>
                <span>T.A. 2026/2027 Ganjil</span>
            </span>
        </div>
    </div>

    <!-- Bagian 1: Daftar Jadwal Mengajar Guru (Pintu Pembukaan Presensi) -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-0.5">
            <h3 class="text-sm font-heading font-semibold text-[#1F2937]">
                Pilih Jadwal Mengajar untuk Buka Presensi
            </h3>
            <span class="text-xs text-[#6B7280] font-medium">{{ $jadwals->count() }} Jadwal Terdaftar</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($jadwals as $jadwal)
                @php
                    $activeSession = $jadwal->sesiPresensis->where('status_sesi', 'dibuka')->first();
                    $totalSesi = $jadwal->sesiPresensis->count();
                @endphp
                <div class="bg-white rounded-lg border {{ $activeSession ? 'border-[#1E9E63] ring-1 ring-[#1E9E63]/30' : 'border-[#E5E7EB]' }} p-5 flex flex-col justify-between transition-colors">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold {{ $activeSession ? 'bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]' : 'bg-[#F9FAFB] text-[#374151] border border-[#E5E7EB]' }}">
                                {{ $jadwal->kelas->nama_kelas }}
                            </span>
                            @if ($activeSession)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-[#FFF7ED] text-[#9A3412] border border-[#FED7AA]">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#EA580C]"></span>
                                    <span>Sesi Aktif</span>
                                </span>
                            @else
                                <span class="text-[11px] font-medium text-[#6B7280] inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px] text-[#1E9E63]">schedule</span>
                                    <span>{{ $jadwal->hari }}, {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</span>
                                </span>
                            @endif
                        </div>

                        <h4 class="font-heading font-semibold text-sm text-[#1F2937] leading-snug">
                            {{ $jadwal->mataPelajaran->nama_mapel }}
                        </h4>
                        <p class="text-xs text-[#6B7280] mt-1">
                            Kode: <span class="font-medium">{{ $jadwal->mataPelajaran->kode_mapel }}</span> • Tingkat: {{ $jadwal->kelas->tingkat }}
                        </p>

                        <div class="mt-4 pt-3 border-t border-[#E5E7EB] flex items-center justify-between text-xs text-[#6B7280]">
                            <span>Total Pertemuan:</span>
                            <span class="font-semibold text-[#1F2937]">{{ $totalSesi }} Sesi</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        @if ($activeSession)
                            <a href="{{ route('guru.presensi.show', $activeSession->id) }}" class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors">
                                <span class="material-symbols-outlined text-[16px]">visibility</span>
                                <span>Pantau Sesi Sedang Dibuka</span>
                            </a>
                        @else
                            <a href="{{ route('guru.presensi.create', $jadwal->id) }}" class="w-full inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors">
                                <span class="material-symbols-outlined text-[16px]">add_circle</span>
                                <span>Buka Presensi Pertemuan {{ $totalSesi + 1 }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-white rounded-lg border border-[#E5E7EB]">
                    <p class="text-xs text-[#6B7280]">Belum ada jadwal pelajaran yang diampu oleh akun guru ini.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Bagian 2: Riwayat Sesi Presensi yang Telah Dibuka -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Riwayat Sesi Pertemuan &amp; Presensi</h3>
                <p class="text-xs text-[#6B7280] mt-0.5">Daftar rekaman pertemuan yang pernah Anda buka untuk seluruh rombel kelas.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-[#374151] border-collapse">
                <thead class="bg-[#F9FAFB] text-[#6B7280] font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="py-3 px-4 w-12">No</th>
                        <th class="py-3 px-4">Kelas &amp; Mapel</th>
                        <th class="py-3 px-4">Pertemuan &amp; Materi</th>
                        <th class="py-3 px-4">Waktu Sesi</th>
                        <th class="py-3 px-4">Metode</th>
                        <th class="py-3 px-4 text-center">Rekap Hadir</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse ($sesis as $index => $sesi)
                        @php
                            $totalPresensi = $sesi->presensis->count();
                            $totalHadir = $sesi->presensis->where('status', 'Hadir')->count();
                            $persenHadir = $totalPresensi > 0 ? round(($totalHadir / $totalPresensi) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                            <td class="py-3 px-4 text-[#6B7280]">{{ $index + 1 }}</td>
                            <td class="py-3 px-4 font-medium text-[#1F2937]">
                                <span class="px-2 py-0.5 rounded bg-[#F9FAFB] border border-[#E5E7EB] text-[#374151] font-semibold text-[11px] mr-1.5">
                                    {{ $sesi->jadwal->kelas->nama_kelas }}
                                </span>
                                {{ $sesi->jadwal->mataPelajaran->nama_mapel }}
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-semibold text-[#1F2937]">Pertemuan {{ $sesi->pertemuan_ke }}</p>
                                <p class="text-[11px] text-[#6B7280] break-words max-w-xs">{{ $sesi->materi }}</p>
                            </td>
                            <td class="py-3 px-4 text-[#4B5563] whitespace-nowrap">
                                <p class="font-medium">{{ \Carbon\Carbon::parse($sesi->tanggal)->translatedFormat('d M Y') }}</p>
                                <p class="text-[11px] text-[#6B7280] ">{{ substr($sesi->jam_mulai, 0, 5) }} - {{ substr($sesi->jam_selesai, 0, 5) }}</p>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if ($sesi->metode === 'qr')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-[#EFF6FF] text-[#1D4ED8] border border-[#BFDBFE]">
                                        <span class="material-symbols-outlined text-[14px]">qr_code</span>
                                        <span>QR Code</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-medium bg-[#F9FAFB] text-[#4B5563] border border-[#E5E7EB]">
                                        <span class="material-symbols-outlined text-[14px]">touch_app</span>
                                        <span>Mandiri</span>
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <span class="font-semibold text-[#1F2937]">{{ $totalHadir }}</span> / <span class="text-[#6B7280]">{{ $totalPresensi }} Siswa</span>
                                <span class="text-[11px] text-[#1E9E63] font-semibold ml-1">({{ $persenHadir }}%)</span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if ($sesi->status_sesi === 'dibuka')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#1E9E63]"></span>
                                        <span>Dibuka</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#F3F4F6] text-[#6B7280]">
                                        Ditutup
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('guru.presensi.show', $sesi->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-[#F9FAFB] text-[#1F2937] font-medium text-xs transition-colors">
                                    <span class="material-symbols-outlined text-[15px] text-[#1E9E63]">fact_check</span>
                                    <span>Rekap &amp; Override</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-[#6B7280]">
                                Belum ada riwayat sesi presensi yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
