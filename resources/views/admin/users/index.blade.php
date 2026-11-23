@extends('layouts.siakad')

@php
    $pageTitle = 'Data Guru & Siswa';
    $pageSubtitle = 'Manajemen Akun Pendidik & Peserta Didik';
@endphp

@section('content')
<div class="space-y-5" x-data="{
    showAddGuruModal: false,
    showAddSiswaModal: false
}">

    <!-- 1. Ringkasan Statistik & 2 Kartu/Tab Navigasi Besar yang Terpisah -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Kartu/Tab 1: Data Guru -->
        <a href="{{ route('admin.users.index', ['tab' => 'guru']) }}"
           class="bg-white rounded-lg border p-5 flex items-center justify-between transition-colors {{ $activeTab === 'guru' ? 'border-[#1E9E63] ring-2 ring-[#1E9E63]/15 bg-[#E8F5EE]/25' : 'border-[#E5E7EB] hover:border-[#1E9E63]/60' }}">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded text-xs font-semibold {{ $activeTab === 'guru' ? 'bg-[#1E9E63] text-white' : 'bg-[#F9FAFB] text-[#4B5563] border border-[#E5E7EB]' }}">
                        Tab 1
                    </span>
                    <span class="text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Tenaga Pendidik</span>
                </div>
                <h2 class="font-heading font-bold text-lg text-[#1F2937]">Data Guru</h2>
                <p class="text-xs sm:text-[13px] text-[#6B7280]">
                    Total <strong class="text-[#1F2937]">{{ $totalGuru }} Guru</strong> aktif terdaftar
                </p>
            </div>
            <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 {{ $activeTab === 'guru' ? 'bg-[#1E9E63] text-white' : 'bg-[#E8F5EE] border border-[#BBF7D0] text-[#1E9E63]' }}">
                <span class="material-symbols-outlined text-[26px]">school</span>
            </div>
        </a>

        <!-- Kartu/Tab 2: Data Siswa -->
        <a href="{{ route('admin.users.index', ['tab' => 'siswa']) }}"
           class="bg-white rounded-lg border p-5 flex items-center justify-between transition-colors {{ $activeTab === 'siswa' ? 'border-[#1E9E63] ring-2 ring-[#1E9E63]/15 bg-[#E8F5EE]/25' : 'border-[#E5E7EB] hover:border-[#1E9E63]/60' }}">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded text-xs font-semibold {{ $activeTab === 'siswa' ? 'bg-[#1E9E63] text-white' : 'bg-[#F9FAFB] text-[#4B5563] border border-[#E5E7EB]' }}">
                        Tab 2
                    </span>
                    <span class="text-xs font-semibold text-[#6B7280] uppercase tracking-wider">Peserta Didik</span>
                </div>
                <h2 class="font-heading font-bold text-lg text-[#1F2937]">Data Siswa (Per Kelas)</h2>
                <p class="text-xs sm:text-[13px] text-[#6B7280]">
                    Total <strong class="text-[#1F2937]">{{ $totalSiswa }} Siswa</strong> tersebar di {{ $totalKelas }} kelas
                </p>
            </div>
            <div class="w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 {{ $activeTab === 'siswa' ? 'bg-[#1E9E63] text-white' : 'bg-[#E8F5EE] border border-[#BBF7D0] text-[#1E9E63]' }}">
                <span class="material-symbols-outlined text-[26px]">groups</span>
            </div>
        </a>
    </div>

    @if ($activeTab === 'guru')
        <!-- ============================================================== -->
        <!-- KONTEN TAB 1: DATA GURU                                        -->
        <!-- ============================================================== -->
        <div class="bg-white rounded-lg border border-[#E5E7EB] p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2 flex-1 max-w-md">
                <input type="hidden" name="tab" value="guru">
                <div class="relative flex-1">
                    <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Cari nama, NIP, atau email guru..." class="w-full text-sm pl-9 pr-3.5 py-2 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15">
                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-[18px] text-[#9CA3AF]">search</span>
                </div>
                <button type="submit" class="px-3.5 py-2 rounded-lg bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold cursor-pointer">Cari</button>
                @if ($searchQuery !== '')
                    <a href="{{ route('admin.users.index', ['tab' => 'guru']) }}" class="px-3 py-2 rounded-lg bg-[#F9FAFB] border border-[#D1D5DB] text-[#4B5563] text-xs font-medium">Reset</a>
                @endif
            </form>

            <button type="button" @click="showAddGuruModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer self-start sm:self-auto">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                <span>+ Tambah Guru Baru</span>
            </button>
        </div>

        <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
            <div class="px-5 py-4 border-b border-[#E5E7EB] bg-[#F9FAFB] flex items-center justify-between">
                <div>
                    <h3 class="font-heading font-semibold text-base text-[#1F2937]">Daftar Tenaga Pendidik (Guru)</h3>
                    <p class="text-xs text-[#6B7280] mt-0.5">Menampilkan {{ $gurus->count() }} akun guru terdaftar.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-[13px] text-[#374151] border-collapse">
                    <thead class="bg-[#F9FAFB] text-[#6B7280] font-semibold border-b border-[#E5E7EB]">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">NIP</th>
                            <th class="py-3 px-4">Nama Lengkap</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Perwalian &amp; Mapel Diampu</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]">
                        @forelse ($gurus as $index => $guru)
                            @php
                                $mapelNames = $guru->mataPelajarans->pluck('nama_mapel')
                                    ->merge($guru->jadwals->pluck('mataPelajaran.nama_mapel'))
                                    ->filter()
                                    ->unique()
                                    ->values();
                            @endphp
                            <tr class="hover:bg-[#F9FAFB] transition-colors">
                                <td class="py-3 px-4 text-center text-[#6B7280]">{{ $index + 1 }}</td>
                                <td class="py-3 px-4 font-medium text-[#1F2937]">{{ $guru->nis_nip ?? '-' }}</td>
                                <td class="py-3 px-4 font-semibold text-[#1F2937]">{{ $guru->name }}</td>
                                <td class="py-3 px-4 text-[#6B7280]">{{ $guru->email }}</td>
                                <td class="py-3 px-4">
                                    <div class="space-y-1">
                                        @if ($guru->kelasWali->isNotEmpty())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-[#E8F5EE] border border-[#BBF7D0] text-[#146C43] text-xs font-semibold">
                                                Wali Kelas: {{ $guru->kelasWali->pluck('nama_kelas')->join(', ') }}
                                            </span>
                                        @endif
                                        @if ($mapelNames->isNotEmpty())
                                            <p class="text-xs text-[#6B7280]">Mapel: {{ $mapelNames->join(', ') }}</p>
                                        @elseif ($guru->kelasWali->isEmpty())
                                            <span class="text-xs text-[#9CA3AF] italic">Belum ada penugasan</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.users.guru.show', $guru->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors">
                                        <span class="material-symbols-outlined text-[15px]">visibility</span>
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 text-center text-[#6B7280]">
                                    Tidak ada data guru yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        <!-- ============================================================== -->
        <!-- KONTEN TAB 2: DATA SISWA DIKELOMPOKKAN BERDASARKAN KELAS       -->
        <!-- ============================================================== -->
        <div class="bg-white rounded-lg border border-[#E5E7EB] p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2 flex-1 max-w-md">
                <input type="hidden" name="tab" value="siswa">
                @if ($selectedKelasId)
                    <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
                @endif
                <div class="relative flex-1">
                    <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Cari nama, NIS, atau email siswa..." class="w-full text-sm pl-9 pr-3.5 py-2 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15">
                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-[18px] text-[#9CA3AF]">search</span>
                </div>
                <button type="submit" class="px-3.5 py-2 rounded-lg bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold cursor-pointer">Cari</button>
                @if ($searchQuery !== '' || $selectedKelasId)
                    <a href="{{ route('admin.users.index', ['tab' => 'siswa']) }}" class="px-3 py-2 rounded-lg bg-[#F9FAFB] border border-[#D1D5DB] text-[#4B5563] text-xs font-medium">Reset</a>
                @endif
            </form>

            <button type="button" @click="showAddSiswaModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer self-start sm:self-auto">
                <span class="material-symbols-outlined text-[18px]">person_add</span>
                <span>+ Tambah Siswa Baru</span>
            </button>
        </div>

        <!-- Grid Kartu Kelas untuk Pengelompokan Siswa -->
        <div class="space-y-2.5">
            <h3 class="text-sm font-heading font-semibold text-[#1F2937]">Pilih Kelas untuk Menampilkan Daftar Siswa</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach ($kelasCards as $kCard)
                    @php $isSelected = (string) $selectedKelasId === (string) $kCard->id; @endphp
                    <a href="{{ route('admin.users.index', ['tab' => 'siswa', 'kelas_id' => $kCard->id]) }}"
                       class="bg-white rounded-lg border p-4 transition-colors flex flex-col justify-between gap-2 {{ $isSelected ? 'border-[#1E9E63] ring-2 ring-[#1E9E63]/20 bg-[#E8F5EE]/40' : 'border-[#E5E7EB] hover:border-[#1E9E63]/60' }}">
                        <div class="flex items-center justify-between">
                            <span class="font-heading font-bold text-sm text-[#1F2937]">{{ $kCard->nama_kelas }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $isSelected ? 'bg-[#1E9E63] text-white' : 'bg-[#E8F5EE] text-[#146C43]' }}">
                                {{ $kCard->siswas_count }} Siswa
                            </span>
                        </div>
                        <p class="text-xs text-[#6B7280]">
                            Wali: {{ $kCard->waliKelas->name ?? 'Belum ditentukan' }}
                        </p>
                    </a>
                @endforeach

                @if ($unassignedSiswaCount > 0)
                    @php $isUnassignedSelected = $selectedKelasId === 'unassigned'; @endphp
                    <a href="{{ route('admin.users.index', ['tab' => 'siswa', 'kelas_id' => 'unassigned']) }}"
                       class="bg-white rounded-lg border p-4 transition-colors flex flex-col justify-between gap-2 {{ $isUnassignedSelected ? 'border-[#D97706] ring-2 ring-[#D97706]/20 bg-[#FFF7ED]' : 'border-[#FED7AA] hover:border-[#D97706]' }}">
                        <div class="flex items-center justify-between">
                            <span class="font-heading font-bold text-sm text-[#9A3412]">Belum Ada Kelas</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-[#FFF7ED] border border-[#FED7AA] text-[#9A3412]">
                                {{ $unassignedSiswaCount }} Siswa
                            </span>
                        </div>
                        <p class="text-xs text-[#9A3412]/80">Siswa belum ditempatkan di rombel</p>
                    </a>
                @endif
            </div>
        </div>

        <!-- Tabel Siswa untuk Kelas Terpilih atau Hasil Pencarian -->
        @if ($selectedKelas || $selectedKelasId === 'unassigned' || $searchQuery !== '')
            <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
                <div class="px-5 py-4 border-b border-[#E5E7EB] bg-[#F9FAFB] flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="font-heading font-semibold text-base text-[#1F2937]">
                            @if ($selectedKelas)
                                Daftar Siswa Kelas {{ $selectedKelas->nama_kelas }}
                            @elseif ($selectedKelasId === 'unassigned')
                                Siswa Belum Terdaftar di Kelas
                            @else
                                Hasil Pencarian Siswa: "{{ $searchQuery }}"
                            @endif
                        </h3>
                        <p class="text-xs text-[#6B7280] mt-0.5">Menampilkan {{ $siswas->count() }} peserta didik.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-[13px] text-[#374151] border-collapse">
                        <thead class="bg-[#F9FAFB] text-[#6B7280] font-semibold border-b border-[#E5E7EB]">
                            <tr>
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">NIS</th>
                                <th class="py-3 px-4">Nama Siswa</th>
                                <th class="py-3 px-4">Email</th>
                                @if (!$selectedKelas && $selectedKelasId !== 'unassigned')
                                    <th class="py-3 px-4">Kelas</th>
                                @endif
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
                                    @if (!$selectedKelas && $selectedKelasId !== 'unassigned')
                                        <td class="py-3 px-4">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                                    @endif
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('admin.users.siswa.show', $siswa->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md bg-[#E8F5EE] border border-[#BBF7D0] hover:bg-[#D1FAE5] text-[#146C43] text-xs font-semibold transition-colors">
                                                <span class="material-symbols-outlined text-[15px]">visibility</span>
                                                <span>Detail</span>
                                            </a>
                                            <a href="{{ route('admin.users.siswa.show', $siswa->id) }}#edit-biodata" class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-[#FFF7ED] hover:text-[#B45309] text-[#4B5563] transition-colors" title="Edit Siswa">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                            </a>
                                            <form method="POST" action="{{ route('admin.users.siswa.destroy', $siswa->id) }}" onsubmit="return handleConfirmForm(event, this, 'Konfirmasi Hapus Data', 'Apakah Anda yakin ingin menghapus akun siswa {{ addslashes($siswa->name) }}?', 'Ya, Hapus Data');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-red-50 hover:text-red-600 text-[#4B5563] transition-colors cursor-pointer" title="Hapus Siswa">
                                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-[#6B7280]">
                                        Tidak ada siswa yang terdaftar pada pilihan ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg border border-[#E5E7EB] p-8 text-center text-xs sm:text-sm text-[#6B7280]">
                Klik salah satu kartu kelas di atas untuk menampilkan daftar siswa di kelas tersebut.
            </div>
        @endif
    @endif

    <!-- MODAL TAMBAH GURU BARU -->
    <div x-show="showAddGuruModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
        <div @click.away="showAddGuruModal = false" class="bg-white w-full max-w-lg rounded-xl border border-[#E5E7EB] shadow-xl overflow-hidden">
            <div class="px-6 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between">
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Tambah Akun Guru Baru</h3>
                <button type="button" @click="showAddGuruModal = false" class="text-[#6B7280] hover:text-[#1F2937] p-1 rounded-md cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.users.guru.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1.5">Nama Lengkap Guru <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Drs. Immanuel Harefa, M.Pd" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">NIP <span class="text-red-500">*</span></label>
                        <input type="text" name="nis_nip" value="{{ old('nis_nip') }}" placeholder="1980..." required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="guru@sman1gst.sch.id" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Kata Sandi <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="6" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Konfirmasi Kata Sandi <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required minlength="6" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                </div>
                <div class="pt-3 border-t border-[#E5E7EB] flex items-center justify-end gap-2.5">
                    <button type="button" @click="showAddGuruModal = false" class="px-4 py-2 rounded-md border border-[#D1D5DB] bg-white hover:bg-[#F9FAFB] text-[#374151] text-xs font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold cursor-pointer">Simpan Akun Guru</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL TAMBAH SISWA BARU -->
    <div x-show="showAddSiswaModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
        <div @click.away="showAddSiswaModal = false" class="bg-white w-full max-w-lg rounded-xl border border-[#E5E7EB] shadow-xl overflow-visible">
            <div class="px-6 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between rounded-t-xl">
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Tambah Akun Siswa Baru</h3>
                <button type="button" @click="showAddSiswaModal = false" class="text-[#6B7280] hover:text-[#1F2937] p-1 rounded-md cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.users.siswa.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1.5">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Jeli Alvonzo Laia" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">NIS <span class="text-red-500">*</span></label>
                        <input type="text" name="nis_nip" value="{{ old('nis_nip') }}" placeholder="260101" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="siswa@sman1gst.sch.id" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1.5">Penempatan Kelas</label>
                    <x-select-dropdown
                        id="create_siswa_kelas_id"
                        name="kelas_id"
                        :options="collect([['value' => '', 'label' => '-- Pilih Kelas --']])->concat($kelasCards->map(fn($k) => ['value' => $k->id, 'label' => $k->nama_kelas . ' (Tingkat ' . $k->tingkat . ')']))"
                        :value="old('kelas_id', $selectedKelas ? $selectedKelas->id : '')"
                        placeholder="-- Pilih Kelas --"
                    />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Kata Sandi <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="6" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Konfirmasi Kata Sandi <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required minlength="6" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                </div>
                <div class="pt-3 border-t border-[#E5E7EB] flex items-center justify-end gap-2.5">
                    <button type="button" @click="showAddSiswaModal = false" class="px-4 py-2 rounded-md border border-[#D1D5DB] bg-white hover:bg-[#F9FAFB] text-[#374151] text-xs font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold cursor-pointer">Simpan Akun Siswa</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
