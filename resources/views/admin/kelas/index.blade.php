@extends('layouts.siakad')

@php
    $pageTitle = 'Data Kelas';
    $pageSubtitle = 'Manajemen Rombongan Belajar & Wali Kelas';
@endphp

@section('content')
<div class="space-y-5" x-data="{
    showCreateModal: false,
    showEditModal: false,
    editAction: '',
    editNamaKelas: '',
    editTingkat: '',
    editWaliKelasId: '',

    openEditModal(kelas) {
        this.editAction = '/admin/kelas/' + kelas.id;
        this.editNamaKelas = kelas.nama_kelas;
        this.editTingkat = kelas.tingkat;
        this.editWaliKelasId = kelas.wali_kelas_id ? String(kelas.wali_kelas_id) : '';
        this.showEditModal = true;
        this.$nextTick(() => {
            this.$dispatch('set-select-value', { id: 'edit_tingkat', value: this.editTingkat });
            this.$dispatch('set-select-value', { id: 'edit_wali_kelas_id', value: this.editWaliKelasId });
        });
    }
}">

    <!-- Header Section -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-heading font-bold text-[#1F2937] tracking-tight">Data Rombongan Belajar (Kelas)</h2>
            <p class="text-xs sm:text-[13px] text-[#6B7280] mt-0.5">Kelola daftar kelas, tingkat pendidikan, penugasan wali kelas, dan rekap anggota siswa.</p>
        </div>
        <button type="button" @click="showCreateModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer self-start sm:self-auto">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            <span>+ Tambah Kelas Baru</span>
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-4">
        <form method="GET" action="{{ route('admin.kelas.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-4">
                <label for="filter-tingkat" class="block text-xs font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Filter Tingkat</label>
                <x-select-dropdown
                    id="filter-tingkat"
                    name="tingkat"
                    :options="[
                        ['value' => '', 'label' => 'Semua Tingkat'],
                        ['value' => '10', 'label' => 'Tingkat 10'],
                        ['value' => '11', 'label' => 'Tingkat 11'],
                        ['value' => '12', 'label' => 'Tingkat 12'],
                        ['value' => 'X', 'label' => 'Tingkat X'],
                        ['value' => 'XI', 'label' => 'Tingkat XI'],
                        ['value' => 'XII', 'label' => 'Tingkat XII'],
                    ]"
                    :value="$tingkatFilter"
                    submit-on-select
                />
            </div>

            <div class="sm:col-span-6">
                <label for="search-kelas" class="block text-xs font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Cari Nama Kelas</label>
                <div class="relative">
                    <input type="text" id="search-kelas" name="q" value="{{ $searchQuery }}" placeholder="Contoh: X MIPA 1..." class="w-full text-sm pl-9 pr-3.5 py-2.5 rounded-lg border border-[#D1D5DB] bg-white text-[#1F2937] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15">
                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-[18px] text-[#9CA3AF]">search</span>
                </div>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full px-3 py-2.5 rounded-lg bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                    Cari
                </button>
                @if ($tingkatFilter !== '' || $searchQuery !== '')
                    <a href="{{ route('admin.kelas.index') }}" class="px-3 py-2.5 rounded-lg bg-[#F9FAFB] border border-[#D1D5DB] hover:bg-[#F3F4F6] text-[#4B5563] text-xs font-semibold transition-colors" title="Reset Filter">
                        <span class="material-symbols-outlined text-[17px] align-middle">restart_alt</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Kelas -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between bg-[#F9FAFB]">
            <div>
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Daftar Kelas Terdaftar</h3>
                <p class="text-xs text-[#6B7280] mt-0.5">Total {{ $kelasList->count() }} rombongan belajar aktif di SMA Negeri 1 Gunungsitoli.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-[13px] text-[#374151] border-collapse">
                <thead class="bg-[#F9FAFB] text-[#6B7280] font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Nama Kelas</th>
                        <th class="py-3 px-4">Tingkat</th>
                        <th class="py-3 px-4">Wali Kelas</th>
                        <th class="py-3 px-4 text-center">Jumlah Siswa</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse ($kelasList as $index => $kelas)
                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                            <td class="py-3 px-4 text-center text-[#6B7280]">{{ $index + 1 }}</td>
                            <td class="py-3 px-4 font-semibold text-[#1F2937]">{{ $kelas->nama_kelas }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded bg-[#F9FAFB] border border-[#E5E7EB] text-[#374151] text-xs font-medium">
                                    Tingkat {{ $kelas->tingkat }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if ($kelas->waliKelas)
                                    <p class="font-medium text-[#1F2937]">{{ $kelas->waliKelas->name }}</p>
                                    <p class="text-xs text-[#6B7280]">NIP: {{ $kelas->waliKelas->nis_nip ?? '-' }}</p>
                                @else
                                    <span class="text-[#9CA3AF] italic">Belum ditentukan</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-[#E8F5EE] border border-[#BBF7D0] text-[#146C43] text-xs font-semibold">
                                    {{ $kelas->siswas_count }} Siswa
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.kelas.show', $kelas->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md bg-[#E8F5EE] border border-[#BBF7D0] hover:bg-[#D1FAE5] text-[#146C43] text-xs font-semibold transition-colors">
                                        <span class="material-symbols-outlined text-[15px]">groups</span>
                                        <span>Rekap Anggota Kelas</span>
                                    </a>
                                    <button type="button" @click='openEditModal(@json($kelas))' class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-[#FFF7ED] hover:text-[#B45309] text-[#4B5563] transition-colors cursor-pointer" title="Edit Kelas">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.kelas.destroy', $kelas->id) }}" onsubmit="return handleConfirmForm(event, this, 'Konfirmasi Hapus Data', 'Apakah Anda yakin ingin menghapus kelas {{ addslashes($kelas->nama_kelas) }}? Siswa di kelas ini akan dilepas dari penempatan kelas.', 'Ya, Hapus Data');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-red-50 hover:text-red-600 text-[#4B5563] transition-colors cursor-pointer" title="Hapus Kelas">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-[#6B7280]">
                                Belum ada data kelas yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH KELAS BARU -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
        <div @click.away="showCreateModal = false" class="bg-white w-full max-w-lg rounded-xl border border-[#E5E7EB] shadow-xl overflow-visible">
            <div class="px-6 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between rounded-t-xl">
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Tambah Kelas Baru</h3>
                <button type="button" @click="showCreateModal = false" class="text-[#6B7280] hover:text-[#1F2937] p-1 rounded-md cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.kelas.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label for="create_nama_kelas" class="block text-xs font-semibold text-[#374151] mb-1.5">Nama Kelas <span class="text-red-500">*</span></label>
                    <input type="text" id="create_nama_kelas" name="nama_kelas" value="{{ old('nama_kelas') }}" placeholder="Contoh: X MIPA 1" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937]">
                </div>

                <div>
                    <label for="create_tingkat" class="block text-xs font-semibold text-[#374151] mb-1.5">Tingkat <span class="text-red-500">*</span></label>
                    <x-select-dropdown
                        id="create_tingkat"
                        name="tingkat"
                        :options="[
                            ['value' => '10', 'label' => '10 (Kelas X)'],
                            ['value' => '11', 'label' => '11 (Kelas XI)'],
                            ['value' => '12', 'label' => '12 (Kelas XII)'],
                        ]"
                        :value="old('tingkat')"
                        placeholder="-- Pilih Tingkat --"
                        required
                    />
                </div>

                <div>
                    <label for="create_wali_kelas_id" class="block text-xs font-semibold text-[#374151] mb-1.5">Wali Kelas</label>
                    <x-select-dropdown
                        id="create_wali_kelas_id"
                        name="wali_kelas_id"
                        :options="collect([['value' => '', 'label' => '-- Tanpa Wali Kelas --']])->concat($gurus->map(fn($g) => ['value' => $g->id, 'label' => $g->name . ' (NIP: ' . ($g->nis_nip ?? '-') . ')']))"
                        :value="old('wali_kelas_id')"
                        placeholder="-- Pilih Wali Kelas --"
                    />
                </div>

                <div class="pt-3 border-t border-[#E5E7EB] flex items-center justify-end gap-2.5">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-md border border-[#D1D5DB] bg-white hover:bg-[#F9FAFB] text-[#374151] text-xs font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold cursor-pointer">Simpan Kelas</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT KELAS -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
        <div @click.away="showEditModal = false" class="bg-white w-full max-w-lg rounded-xl border border-[#E5E7EB] shadow-xl overflow-visible">
            <div class="px-6 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between rounded-t-xl">
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Edit Data Kelas</h3>
                <button type="button" @click="showEditModal = false" class="text-[#6B7280] hover:text-[#1F2937] p-1 rounded-md cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" :action="editAction" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_nama_kelas" class="block text-xs font-semibold text-[#374151] mb-1.5">Nama Kelas <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_nama_kelas" name="nama_kelas" x-model="editNamaKelas" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937]">
                </div>

                <div>
                    <label for="edit_tingkat" class="block text-xs font-semibold text-[#374151] mb-1.5">Tingkat <span class="text-red-500">*</span></label>
                    <x-select-dropdown
                        id="edit_tingkat"
                        name="tingkat"
                        :options="[
                            ['value' => '10', 'label' => '10 (Kelas X)'],
                            ['value' => '11', 'label' => '11 (Kelas XI)'],
                            ['value' => '12', 'label' => '12 (Kelas XII)'],
                            ['value' => 'X', 'label' => 'X'],
                            ['value' => 'XI', 'label' => 'XI'],
                            ['value' => 'XII', 'label' => 'XII'],
                        ]"
                        placeholder="-- Pilih Tingkat --"
                        required
                    />
                </div>

                <div>
                    <label for="edit_wali_kelas_id" class="block text-xs font-semibold text-[#374151] mb-1.5">Wali Kelas</label>
                    <x-select-dropdown
                        id="edit_wali_kelas_id"
                        name="wali_kelas_id"
                        :options="collect([['value' => '', 'label' => '-- Tanpa Wali Kelas --']])->concat($gurus->map(fn($g) => ['value' => $g->id, 'label' => $g->name . ' (NIP: ' . ($g->nis_nip ?? '-') . ')']))"
                        placeholder="-- Pilih Wali Kelas --"
                    />
                </div>

                <div class="pt-3 border-t border-[#E5E7EB] flex items-center justify-end gap-2.5">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-md border border-[#D1D5DB] bg-white hover:bg-[#F9FAFB] text-[#374151] text-xs font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold cursor-pointer">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
