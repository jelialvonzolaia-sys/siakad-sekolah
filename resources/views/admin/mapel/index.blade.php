@extends('layouts.siakad')

@php
    $pageTitle = 'Mata Pelajaran';
    $pageSubtitle = 'Manajemen Kurikulum & Guru Pengampu';
@endphp

@section('content')
<div class="space-y-5" x-data="{
    showCreateModal: false,
    showEditModal: false,
    editAction: '',
    editKodeMapel: '',
    editNamaMapel: '',
    editKkm: 75,
    editGuruId: '',

    openEditModal(mapel) {
        this.editAction = '/admin/mapel/' + mapel.id;
        this.editKodeMapel = mapel.kode_mapel;
        this.editNamaMapel = mapel.nama_mapel;
        this.editKkm = mapel.kkm ?? 75;
        this.editGuruId = mapel.guru_id ? String(mapel.guru_id) : '';
        this.showEditModal = true;
        this.$nextTick(() => {
            this.$dispatch('set-select-value', { id: 'edit_mapel_guru_id', value: this.editGuruId });
        });
    }
}">

    <!-- Header Section -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-heading font-bold text-[#1F2937] tracking-tight">Data Mata Pelajaran</h2>
            <p class="text-xs sm:text-[13px] text-[#6B7280] mt-0.5">Kelola kode mapel, nama mata pelajaran, KKM, dan guru pengampu utama.</p>
        </div>
        <button type="button" @click="showCreateModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer self-start sm:self-auto">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            <span>+ Tambah Mata Pelajaran</span>
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-4">
        <form method="GET" action="{{ route('admin.mapel.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-5">
                <label for="filter-guru" class="block text-xs font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Filter Guru Pengampu</label>
                <x-select-dropdown
                    id="filter-guru"
                    name="guru_id"
                    :options="collect([['value' => '', 'label' => 'Semua Guru Pengampu']])->concat($gurus->map(fn($g) => ['value' => $g->id, 'label' => $g->name]))"
                    :value="$selectedGuruId"
                    submit-on-select
                />
            </div>

            <div class="sm:col-span-5">
                <label for="search-mapel" class="block text-xs font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Cari Kode / Nama Mapel</label>
                <div class="relative">
                    <input type="text" id="search-mapel" name="q" value="{{ $searchQuery }}" placeholder="Ketik kode atau nama mata pelajaran..." class="w-full text-sm pl-9 pr-3.5 py-2.5 rounded-lg border border-[#D1D5DB] bg-white text-[#1F2937] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15">
                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-[18px] text-[#9CA3AF]">search</span>
                </div>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full px-3 py-2.5 rounded-lg bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                    Cari
                </button>
                @if (($selectedGuruId !== null && $selectedGuruId !== '') || $searchQuery !== '')
                    <a href="{{ route('admin.mapel.index') }}" class="px-3 py-2.5 rounded-lg bg-[#F9FAFB] border border-[#D1D5DB] hover:bg-[#F3F4F6] text-[#4B5563] text-xs font-semibold transition-colors" title="Reset Filter">
                        <span class="material-symbols-outlined text-[17px] align-middle">restart_alt</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Mata Pelajaran -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between bg-[#F9FAFB]">
            <div>
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Daftar Mata Pelajaran</h3>
                <p class="text-xs text-[#6B7280] mt-0.5">Total {{ $mapels->count() }} mata pelajaran terdaftar pada kurikulum aktif.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-[13px] text-[#374151] border-collapse">
                <thead class="bg-[#F9FAFB] text-[#6B7280] font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Kode Mapel</th>
                        <th class="py-3 px-4">Nama Mata Pelajaran</th>
                        <th class="py-3 px-4 text-center">KKM</th>
                        <th class="py-3 px-4">Guru Pengampu</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse ($mapels as $index => $mapel)
                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                            <td class="py-3 px-4 text-center text-[#6B7280]">{{ $index + 1 }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded bg-[#F9FAFB] border border-[#D1D5DB] text-[#1F2937] text-xs font-semibold">
                                    {{ $mapel->kode_mapel }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-[#1F2937]">{{ $mapel->nama_mapel }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-[#E8F5EE] border border-[#BBF7D0] text-[#146C43] text-xs font-semibold">
                                    {{ $mapel->kkm ?? 75 }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if ($mapel->guru)
                                    <p class="font-medium text-[#1F2937]">{{ $mapel->guru->name }}</p>
                                    <p class="text-xs text-[#6B7280]">NIP: {{ $mapel->guru->nis_nip ?? '-' }}</p>
                                @else
                                    <span class="text-[#9CA3AF] italic">Belum ditentukan</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" @click='openEditModal(@json($mapel))' class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-[#FFF7ED] hover:text-[#B45309] text-[#4B5563] transition-colors cursor-pointer" title="Edit Mata Pelajaran">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.mapel.destroy', $mapel->id) }}" onsubmit="return handleConfirmForm(event, this, 'Konfirmasi Hapus Data', 'Apakah Anda yakin ingin menghapus mata pelajaran {{ addslashes($mapel->nama_mapel) }}?', 'Ya, Hapus Data');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-red-50 hover:text-red-600 text-[#4B5563] transition-colors cursor-pointer" title="Hapus Mata Pelajaran">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-[#6B7280]">
                                Belum ada mata pelajaran yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH MATA PELAJARAN -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
        <div @click.away="showCreateModal = false" class="bg-white w-full max-w-lg rounded-xl border border-[#E5E7EB] shadow-xl overflow-visible">
            <div class="px-6 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between rounded-t-xl">
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Tambah Mata Pelajaran</h3>
                <button type="button" @click="showCreateModal = false" class="text-[#6B7280] hover:text-[#1F2937] p-1 rounded-md cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.mapel.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="create_kode_mapel" class="block text-xs font-semibold text-[#374151] mb-1.5">Kode Mapel <span class="text-red-500">*</span></label>
                        <input type="text" id="create_kode_mapel" name="kode_mapel" value="{{ old('kode_mapel') }}" placeholder="Contoh: KIM-01" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937] uppercase">
                    </div>
                    <div>
                        <label for="create_kkm" class="block text-xs font-semibold text-[#374151] mb-1.5">KKM <span class="text-red-500">*</span></label>
                        <input type="number" id="create_kkm" name="kkm" value="{{ old('kkm', 75) }}" min="1" max="100" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937]">
                    </div>
                </div>

                <div>
                    <label for="create_nama_mapel" class="block text-xs font-semibold text-[#374151] mb-1.5">Nama Mata Pelajaran <span class="text-red-500">*</span></label>
                    <input type="text" id="create_nama_mapel" name="nama_mapel" value="{{ old('nama_mapel') }}" placeholder="Contoh: Kimia Peminatan" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937]">
                </div>

                <div>
                    <label for="create_mapel_guru_id" class="block text-xs font-semibold text-[#374151] mb-1.5">Guru Pengampu</label>
                    <x-select-dropdown
                        id="create_mapel_guru_id"
                        name="guru_id"
                        :options="collect([['value' => '', 'label' => '-- Pilih Guru Pengampu --']])->concat($gurus->map(fn($g) => ['value' => $g->id, 'label' => $g->name . ' (NIP: ' . ($g->nis_nip ?? '-') . ')']))"
                        :value="old('guru_id')"
                        placeholder="-- Pilih Guru Pengampu --"
                    />
                </div>

                <div class="pt-3 border-t border-[#E5E7EB] flex items-center justify-end gap-2.5">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-md border border-[#D1D5DB] bg-white hover:bg-[#F9FAFB] text-[#374151] text-xs font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold cursor-pointer">Simpan Mapel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT MATA PELAJARAN -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
        <div @click.away="showEditModal = false" class="bg-white w-full max-w-lg rounded-xl border border-[#E5E7EB] shadow-xl overflow-visible">
            <div class="px-6 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between rounded-t-xl">
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Edit Mata Pelajaran</h3>
                <button type="button" @click="showEditModal = false" class="text-[#6B7280] hover:text-[#1F2937] p-1 rounded-md cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" :action="editAction" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label for="edit_kode_mapel" class="block text-xs font-semibold text-[#374151] mb-1.5">Kode Mapel <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_kode_mapel" name="kode_mapel" x-model="editKodeMapel" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937] uppercase">
                    </div>
                    <div>
                        <label for="edit_kkm" class="block text-xs font-semibold text-[#374151] mb-1.5">KKM <span class="text-red-500">*</span></label>
                        <input type="number" id="edit_kkm" name="kkm" x-model="editKkm" min="1" max="100" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937]">
                    </div>
                </div>

                <div>
                    <label for="edit_nama_mapel" class="block text-xs font-semibold text-[#374151] mb-1.5">Nama Mata Pelajaran <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_nama_mapel" name="nama_mapel" x-model="editNamaMapel" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937]">
                </div>

                <div>
                    <label for="edit_mapel_guru_id" class="block text-xs font-semibold text-[#374151] mb-1.5">Guru Pengampu</label>
                    <x-select-dropdown
                        id="edit_mapel_guru_id"
                        name="guru_id"
                        :options="collect([['value' => '', 'label' => '-- Pilih Guru Pengampu --']])->concat($gurus->map(fn($g) => ['value' => $g->id, 'label' => $g->name . ' (NIP: ' . ($g->nis_nip ?? '-') . ')']))"
                        placeholder="-- Pilih Guru Pengampu --"
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
