@extends('layouts.siakad')

@php
    $pageTitle = 'Rekap Anggota Kelas';
    $pageSubtitle = 'Kelas ' . $kelas->nama_kelas;
@endphp

@section('content')
<div class="space-y-5">

    <a href="{{ route('admin.kelas.index') }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-[#6B7280] hover:text-[#1F2937] transition-colors">
        <span class="material-symbols-outlined text-[17px]">arrow_back</span>
        <span>Kembali ke Daftar Kelas</span>
    </a>

    <!-- Kartu Informasi Kelas & Wali Kelas -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded bg-[#E8F5EE] border border-[#BBF7D0] text-[#146C43] text-xs font-semibold">
                        Tingkat {{ $kelas->tingkat }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded bg-[#F9FAFB] border border-[#E5E7EB] text-[#374151] text-xs font-medium">
                        {{ $siswas->count() }} Siswa Terdaftar
                    </span>
                </div>
                <h2 class="text-xl font-heading font-bold text-[#1F2937]">Kelas {{ $kelas->nama_kelas }}</h2>
                <p class="text-xs sm:text-[13px] text-[#6B7280]">
                    Wali Kelas:
                    @if ($kelas->waliKelas)
                        <strong class="text-[#1F2937]">{{ $kelas->waliKelas->name }}</strong> (NIP: {{ $kelas->waliKelas->nis_nip ?? '-' }})
                    @else
                        <span class="italic text-[#9CA3AF]">Belum ditentukan</span>
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.index', ['tab' => 'siswa', 'kelas_id' => $kelas->id]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors">
                    <span class="material-symbols-outlined text-[16px]">manage_accounts</span>
                    <span>Kelola Siswa di Data Users</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Tabel Anggota Siswa -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E5E7EB] bg-[#F9FAFB] flex items-center justify-between">
            <div>
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Daftar Anggota Siswa Kelas {{ $kelas->nama_kelas }}</h3>
                <p class="text-xs text-[#6B7280] mt-0.5">Seluruh peserta didik yang terdaftar aktif di rombongan belajar ini.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-[13px] text-[#374151] border-collapse">
                <thead class="bg-[#F9FAFB] text-[#6B7280] font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">NIS</th>
                        <th class="py-3 px-4">Nama Lengkap</th>
                        <th class="py-3 px-4">Email</th>
                        <th class="py-3 px-4 text-center">Status Akun</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse ($siswas as $index => $siswa)
                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                            <td class="py-3 px-4 text-center text-[#6B7280]">{{ $index + 1 }}</td>
                            <td class="py-3 px-4 font-medium text-[#1F2937]">{{ $siswa->nis_nip ?? '-' }}</td>
                            <td class="py-3 px-4 font-semibold text-[#1F2937]">{{ $siswa->name }}</td>
                            <td class="py-3 px-4 text-[#6B7280]">{{ $siswa->email }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#E8F5EE] border border-[#BBF7D0] text-[#146C43] text-xs font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#1E9E63]"></span>
                                    <span>Aktif</span>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <a href="{{ route('admin.users.siswa.show', $siswa->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-[#E8F5EE] hover:border-[#1E9E63] text-[#1F2937] hover:text-[#146C43] text-xs font-semibold transition-colors">
                                    <span class="material-symbols-outlined text-[15px] text-[#1E9E63]">visibility</span>
                                    <span>Lihat Profil Siswa</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-[#6B7280]">
                                Belum ada siswa yang ditempatkan di kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
