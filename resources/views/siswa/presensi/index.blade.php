@extends('layouts.siakad')

@php
    $breadcrumbs = [
        ['label' => 'Beranda', 'url' => route('siswa.dashboard')],
        ['label' => 'Presensi Kelas'],
    ];
@endphp

@section('content')
<div class="space-y-5">

    <!-- Kartu Atas: Detail Siswa -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E5E7EB]">
            <h2 class="text-base sm:text-lg font-heading font-bold text-[#1F2937]">Detail Siswa</h2>
        </div>
        <div class="divide-y divide-[#E5E7EB] text-sm">
            <div class="px-6 py-3 grid grid-cols-12 items-center gap-2">
                <div class="col-span-5 sm:col-span-4 text-[#4B5563] font-medium">Nama</div>
                <div class="col-span-1 text-center text-[#9CA3AF]">:</div>
                <div class="col-span-6 sm:col-span-7 text-[#1F2937] font-medium uppercase">{{ $siswa->name }}</div>
            </div>
            <div class="px-6 py-3 grid grid-cols-12 items-center gap-2">
                <div class="col-span-5 sm:col-span-4 text-[#4B5563] font-medium">NIS</div>
                <div class="col-span-1 text-center text-[#9CA3AF]">:</div>
                <div class="col-span-6 sm:col-span-7 text-[#1F2937]">{{ $siswa->nis_nip ?? '-' }}</div>
            </div>
            <div class="px-6 py-3 grid grid-cols-12 items-center gap-2">
                <div class="col-span-5 sm:col-span-4 text-[#4B5563] font-medium">Kelas</div>
                <div class="col-span-1 text-center text-[#9CA3AF]">:</div>
                <div class="col-span-6 sm:col-span-7 text-[#1F2937] font-medium">{{ $siswa->kelas->nama_kelas ?? '-' }}</div>
            </div>
            <div class="px-6 py-3 grid grid-cols-12 items-center gap-2">
                <div class="col-span-5 sm:col-span-4 text-[#4B5563] font-medium">Wali Kelas</div>
                <div class="col-span-1 text-center text-[#9CA3AF]">:</div>
                <div class="col-span-6 sm:col-span-7 text-[#1F2937]">{{ $siswa->kelas?->waliKelas?->name ?? '-' }}</div>
            </div>
            <div class="px-6 py-3 grid grid-cols-12 items-center gap-2">
                <div class="col-span-5 sm:col-span-4 text-[#4B5563] font-medium">Tahun Ajaran</div>
                <div class="col-span-1 text-center text-[#9CA3AF]">:</div>
                <div class="col-span-6 sm:col-span-7 text-[#1F2937]">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                        Semester Ganjil T.A. 2026/2027
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Bawah: Daftar Mata Pelajaran Kelas -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E5E7EB]">
            <h2 class="text-base sm:text-lg font-heading font-bold text-[#1F2937]">Daftar Mata Pelajaran Kelas</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#374151] border-collapse">
                <thead class="bg-[#F9FAFB] text-[#1F2937] font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="py-3.5 px-5 w-24 text-center">Aksi</th>
                        <th class="py-3.5 px-4 w-14">No</th>
                        <th class="py-3.5 px-4">Kode Mapel</th>
                        <th class="py-3.5 px-4">Mata Pelajaran</th>
                        <th class="py-3.5 px-4">Kelas</th>
                        <th class="py-3.5 px-4">Guru Pengampu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse ($jadwals as $index => $jadwal)
                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                            <td class="py-3.5 px-5 text-center">
                                <a href="{{ route('siswa.presensi.show', $jadwal->id) }}" title="Lihat Sesi Pertemuan {{ $jadwal->mataPelajaran->nama_mapel }}" class="inline-flex items-center justify-center w-9 h-9 rounded-md bg-[#1F3B2E] hover:bg-[#162C22] text-white transition-colors shadow-xs">
                                    <span class="material-symbols-outlined text-[19px]">format_list_bulleted</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-[#4B5563]">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-4 font-medium text-[#1F2937]">{{ $jadwal->mataPelajaran->kode_mapel }}</td>
                            <td class="py-3.5 px-4 font-medium text-[#1F2937]">{{ $jadwal->mataPelajaran->nama_mapel }}</td>
                            <td class="py-3.5 px-4 text-[#374151]">{{ $jadwal->kelas->nama_kelas }}</td>
                            <td class="py-3.5 px-4 text-[#374151]">{{ $jadwal->guru->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 px-6 text-center text-[#6B7280] text-sm">
                                Belum ada jadwal mata pelajaran yang terdaftar untuk kelas Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
