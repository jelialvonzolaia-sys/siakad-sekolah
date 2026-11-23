@extends('layouts.siakad')

@php
    $pageTitle = 'Jadwal Pelajaran';
    $pageSubtitle = 'Penjadwalan KBM & Anti-Bentrok Kelas/Guru';
@endphp

@section('content')
<div class="space-y-5" x-data="{
    showCreateModal: false,
    showEditModal: false,
    editAction: '',
    editJamMulai: '',
    editJamSelesai: '',

    openEditModal(jadwal) {
        this.editAction = '/admin/jadwal/' + jadwal.id;
        this.editJamMulai = String(jadwal.jam_mulai).substring(0, 5);
        this.editJamSelesai = String(jadwal.jam_selesai).substring(0, 5);
        this.showEditModal = true;
        this.$nextTick(() => {
            this.$dispatch('set-select-value', { id: 'edit_jadwal_kelas_id', value: jadwal.kelas_id });
            this.$dispatch('set-select-value', { id: 'edit_jadwal_mapel_id', value: jadwal.mapel_id });
            this.$dispatch('set-select-value', { id: 'edit_jadwal_guru_id', value: jadwal.guru_id });
            this.$dispatch('set-select-value', { id: 'edit_jadwal_hari', value: jadwal.hari });
        });
    }
}">

    <!-- Header Section -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-heading font-bold text-[#1F2937] tracking-tight">Jadwal Pelajaran Sekolah</h2>
            <p class="text-xs sm:text-[13px] text-[#6B7280] mt-0.5">Kelola jadwal belajar mengajar per kelas dengan validasi bentrok otomatis untuk kelas dan guru.</p>
        </div>
        <button type="button" @click="showCreateModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer self-start sm:self-auto">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            <span>+ Tambah Jadwal Pelajaran</span>
        </button>
    </div>

    <!-- Filter Bar (Kelas & Hari) -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-4">
        <form method="GET" action="{{ route('admin.jadwal.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-5">
                <label for="filter-kelas" class="block text-xs font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Filter Kelas</label>
                <x-select-dropdown
                    id="filter-kelas"
                    name="kelas_id"
                    :options="collect([['value' => '', 'label' => 'Semua Kelas']])->concat($kelasList->map(fn($k) => ['value' => $k->id, 'label' => $k->nama_kelas . ' (Tingkat ' . $k->tingkat . ')']))"
                    :value="$selectedKelasId"
                    submit-on-select
                />
            </div>

            <div class="sm:col-span-5">
                <label for="filter-hari" class="block text-xs font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Filter Hari</label>
                <x-select-dropdown
                    id="filter-hari"
                    name="hari"
                    :options="collect([['value' => '', 'label' => 'Semua Hari (Senin - Sabtu)']])->concat(collect($hariList)->map(fn($h) => ['value' => $h, 'label' => $h]))"
                    :value="$selectedHari"
                    submit-on-select
                />
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full px-3 py-2.5 rounded-lg bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                    Terapkan
                </button>
                @if (($selectedKelasId !== null && $selectedKelasId !== '') || ($selectedHari !== null && $selectedHari !== ''))
                    <a href="{{ route('admin.jadwal.index') }}" class="px-3 py-2.5 rounded-lg bg-[#F9FAFB] border border-[#D1D5DB] hover:bg-[#F3F4F6] text-[#4B5563] text-xs font-semibold transition-colors" title="Reset Filter">
                        <span class="material-symbols-outlined text-[17px] align-middle">restart_alt</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Jadwal Pelajaran -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between bg-[#F9FAFB]">
            <div>
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Daftar Jadwal Pelajaran</h3>
                <p class="text-xs text-[#6B7280] mt-0.5">Menampilkan {{ $jadwals->count() }} sesi jadwal pembelajaran.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-[13px] text-[#374151] border-collapse">
                <thead class="bg-[#F9FAFB] text-[#6B7280] font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Hari</th>
                        <th class="py-3 px-4">Jam Belajar</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">Mata Pelajaran</th>
                        <th class="py-3 px-4">Guru Pengampu</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse ($jadwals as $index => $jadwal)
                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                            <td class="py-3 px-4 text-center text-[#6B7280]">{{ $index + 1 }}</td>
                            <td class="py-3 px-4 font-semibold text-[#1F2937]">{{ $jadwal->hari }}</td>
                            <td class="py-3 px-4 whitespace-nowrap text-[#1F2937]">
                                <span class="inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-[#1E9E63]">schedule</span>
                                    <span>{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB</span>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded bg-[#E8F5EE] border border-[#BBF7D0] text-[#146C43] text-xs font-semibold">
                                    {{ $jadwal->kelas->nama_kelas ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-[#1F2937]">
                                {{ $jadwal->mataPelajaran->nama_mapel ?? '-' }}
                                <span class="text-xs text-[#6B7280] font-normal">({{ $jadwal->mataPelajaran->kode_mapel ?? '-' }})</span>
                            </td>
                            <td class="py-3 px-4 text-[#4B5563]">{{ $jadwal->guru->name ?? '-' }}</td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" @click='openEditModal(@json($jadwal))' class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-[#FFF7ED] hover:text-[#B45309] text-[#4B5563] transition-colors cursor-pointer" title="Edit Jadwal">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.jadwal.destroy', $jadwal->id) }}" onsubmit="return handleConfirmForm(event, this, 'Konfirmasi Hapus Jadwal', 'Apakah Anda yakin ingin menghapus jadwal {{ addslashes($jadwal->mataPelajaran->nama_mapel ?? 'ini') }} untuk kelas {{ addslashes($jadwal->kelas->nama_kelas ?? '') }}?', 'Ya, Hapus Data');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-red-50 hover:text-red-600 text-[#4B5563] transition-colors cursor-pointer" title="Hapus Jadwal">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-[#6B7280]">
                                Belum ada jadwal pelajaran sesuai filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL TAMBAH JADWAL PELAJARAN -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
        <div @click.away="showCreateModal = false" class="bg-white w-full max-w-xl rounded-xl border border-[#E5E7EB] shadow-xl overflow-visible">
            <div class="px-6 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between rounded-t-xl">
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Tambah Jadwal Pelajaran</h3>
                <button type="button" @click="showCreateModal = false" class="text-[#6B7280] hover:text-[#1F2937] p-1 rounded-md cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.jadwal.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Kelas <span class="text-red-500">*</span></label>
                        <x-select-dropdown
                            id="create_jadwal_kelas_id"
                            name="kelas_id"
                            :options="$kelasList->map(fn($k) => ['value' => $k->id, 'label' => $k->nama_kelas . ' (Tingkat ' . $k->tingkat . ')'])"
                            :value="old('kelas_id')"
                            placeholder="-- Pilih Kelas --"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                        <x-select-dropdown
                            id="create_jadwal_mapel_id"
                            name="mapel_id"
                            :options="$mapelList->map(fn($m) => ['value' => $m->id, 'label' => $m->nama_mapel . ' (' . $m->kode_mapel . ')'])"
                            :value="old('mapel_id')"
                            placeholder="-- Pilih Mata Pelajaran --"
                            required
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Guru Pengampu <span class="text-red-500">*</span></label>
                        <x-select-dropdown
                            id="create_jadwal_guru_id"
                            name="guru_id"
                            :options="$guruList->map(fn($g) => ['value' => $g->id, 'label' => $g->name])"
                            :value="old('guru_id')"
                            placeholder="-- Pilih Guru Pengampu --"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Hari <span class="text-red-500">*</span></label>
                        <x-select-dropdown
                            id="create_jadwal_hari"
                            name="hari"
                            :options="collect($hariList)->map(fn($h) => ['value' => $h, 'label' => $h])"
                            :value="old('hari')"
                            placeholder="-- Pilih Hari --"
                            required
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="create_jam_mulai" class="block text-xs font-semibold text-[#374151] mb-1.5">Jam Mulai <span class="text-red-500">*</span></label>
                        <input type="time" id="create_jam_mulai" name="jam_mulai" value="{{ old('jam_mulai', '07:30') }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                    <div>
                        <label for="create_jam_selesai" class="block text-xs font-semibold text-[#374151] mb-1.5">Jam Selesai <span class="text-red-500">*</span></label>
                        <input type="time" id="create_jam_selesai" name="jam_selesai" value="{{ old('jam_selesai', '09:00') }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                </div>

                <div class="pt-3 border-t border-[#E5E7EB] flex items-center justify-end gap-2.5">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-md border border-[#D1D5DB] bg-white hover:bg-[#F9FAFB] text-[#374151] text-xs font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold cursor-pointer">Simpan Jadwal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT JADWAL PELAJARAN -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
        <div @click.away="showEditModal = false" class="bg-white w-full max-w-xl rounded-xl border border-[#E5E7EB] shadow-xl overflow-visible">
            <div class="px-6 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between rounded-t-xl">
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Edit Jadwal Pelajaran</h3>
                <button type="button" @click="showEditModal = false" class="text-[#6B7280] hover:text-[#1F2937] p-1 rounded-md cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" :action="editAction" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Kelas <span class="text-red-500">*</span></label>
                        <x-select-dropdown
                            id="edit_jadwal_kelas_id"
                            name="kelas_id"
                            :options="$kelasList->map(fn($k) => ['value' => $k->id, 'label' => $k->nama_kelas . ' (Tingkat ' . $k->tingkat . ')'])"
                            placeholder="-- Pilih Kelas --"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                        <x-select-dropdown
                            id="edit_jadwal_mapel_id"
                            name="mapel_id"
                            :options="$mapelList->map(fn($m) => ['value' => $m->id, 'label' => $m->nama_mapel . ' (' . $m->kode_mapel . ')'])"
                            placeholder="-- Pilih Mata Pelajaran --"
                            required
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Guru Pengampu <span class="text-red-500">*</span></label>
                        <x-select-dropdown
                            id="edit_jadwal_guru_id"
                            name="guru_id"
                            :options="$guruList->map(fn($g) => ['value' => $g->id, 'label' => $g->name])"
                            placeholder="-- Pilih Guru Pengampu --"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Hari <span class="text-red-500">*</span></label>
                        <x-select-dropdown
                            id="edit_jadwal_hari"
                            name="hari"
                            :options="collect($hariList)->map(fn($h) => ['value' => $h, 'label' => $h])"
                            placeholder="-- Pilih Hari --"
                            required
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_jam_mulai" class="block text-xs font-semibold text-[#374151] mb-1.5">Jam Mulai <span class="text-red-500">*</span></label>
                        <input type="time" id="edit_jam_mulai" name="jam_mulai" x-model="editJamMulai" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                    <div>
                        <label for="edit_jam_selesai" class="block text-xs font-semibold text-[#374151] mb-1.5">Jam Selesai <span class="text-red-500">*</span></label>
                        <input type="time" id="edit_jam_selesai" name="jam_selesai" x-model="editJamSelesai" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
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
