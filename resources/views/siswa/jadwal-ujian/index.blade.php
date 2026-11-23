@extends('layouts.siakad')

@php
    $breadcrumbs = [
        ['label' => 'Beranda', 'url' => route('siswa.dashboard')],
        ['label' => 'Jadwal Ujian'],
    ];
@endphp

@section('content')
<div class="bg-white rounded-lg border border-[#E5E7EB] p-5 sm:p-6 space-y-5">

    <!-- Header Judul Jadwal Ujian -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h2 class="text-lg sm:text-xl font-heading font-bold text-[#1F2937] tracking-tight">
                Jadwal {{ $jenisUjianAktif }}
            </h2>
            <p class="text-xs sm:text-sm text-[#6B7280] mt-0.5">
                Kelas {{ $siswa->kelas->nama_kelas ?? '-' }} • SMA Negeri 1 Gunungsitoli
            </p>
        </div>
    </div>

    <!-- Tabel Jadwal Ujian Sekolah -->
    <div class="rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#374151] border-collapse">
                <thead class="bg-[#F9FAFB] text-[#1F2937] font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="py-3.5 px-4 w-14">No</th>
                        <th class="py-3.5 px-4 w-32">Kode Mapel</th>
                        <th class="py-3.5 px-4">Mata Pelajaran</th>
                        <th class="py-3.5 px-4 w-28">Kelas</th>
                        <th class="py-3.5 px-4 w-44">Jenis Ujian</th>
                        <th class="py-3.5 px-4 w-32">Ruangan</th>
                        <th class="py-3.5 px-4">Jadwal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse ($jadwalUjians as $index => $ujian)
                        @php
                            $carbonTanggal = \Carbon\Carbon::parse($ujian->tanggal)->locale('id');
                            $carbonTanggal->settings(['formatFunction' => 'translatedFormat']);
                            $mulai = substr($ujian->waktu_mulai, 0, 5);
                            $selesai = \Carbon\Carbon::parse($ujian->waktu_mulai)->addMinutes(90)->format('H:i');
                        @endphp
                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                            <td class="py-3.5 px-4 text-[#4B5563]">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-4 font-medium text-[#1F2937]">{{ $ujian->mataPelajaran->kode_mapel }}</td>
                            <td class="py-3.5 px-4 font-medium text-[#1F2937]">{{ $ujian->mataPelajaran->nama_mapel }}</td>
                            <td class="py-3.5 px-4 text-[#374151]">{{ $ujian->kelas->nama_kelas }}</td>
                            <td class="py-3.5 px-4 text-[#374151]">{{ $ujian->jenis_ujian }}</td>
                            <td class="py-3.5 px-4 text-[#374151]">{{ $ujian->ruangan }}</td>
                            <td class="py-3.5 px-4 text-[#1F2937]">
                                {{ $carbonTanggal->translatedFormat('l, d F Y') }}, {{ $mulai }} - {{ $selesai }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 px-6 text-center text-[#6B7280] text-sm">
                                Belum ada jadwal ujian yang diterbitkan untuk kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
