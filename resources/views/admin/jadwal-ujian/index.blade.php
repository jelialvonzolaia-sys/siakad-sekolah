@extends('layouts.siakad')

@php
    $pageTitle = 'Jadwal Ujian';
    $pageSubtitle = 'Penjadwalan Asesmen & Ruang Ujian';
@endphp

@section('content')
<div class="space-y-5" x-data="{
    showCreateModal: false,
    showEditModal: false,
    editAction: '',
    editTanggal: '',
    editWaktuMulai: '',
    editRuangan: '',

    openEditModal(ujian) {
        this.editAction = '/admin/jadwal-ujian/' + ujian.id;
        this.editTanggal = String(ujian.tanggal).substring(0, 10);
        this.editWaktuMulai = String(ujian.waktu_mulai).substring(0, 5);
        this.editRuangan = ujian.ruangan;
        this.showEditModal = true;
        this.$nextTick(() => {
            this.$dispatch('set-select-value', { id: 'edit_ujian_kelas_id', value: ujian.kelas_id });
            this.$dispatch('set-select-value', { id: 'edit_ujian_mapel_id', value: ujian.mapel_id });
            this.$dispatch('set-select-value', { id: 'edit_ujian_jenis', value: ujian.jenis_ujian });
        });
    }
}">

    <!-- Header Section -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-heading font-bold text-[#1F2937] tracking-tight">Jadwal Penilaian &amp; Ujian</h2>
            <p class="text-xs sm:text-[13px] text-[#6B7280] mt-0.5">Kelola jadwal PTS, PAS, dan Ujian Sekolah lengkap dengan tanggal, waktu mulai, dan ruangan.</p>
        </div>
        <button type="button" @click="showCreateModal = true" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer self-start sm:self-auto">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            <span>+ Tambah Jadwal Ujian</span>
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-4">
        <form method="GET" action="{{ route('admin.jadwal-ujian.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-6">
                <label for="filter-kelas" class="block text-xs font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Filter Kelas Peserta Ujian</label>
                <x-select-dropdown
                    id="filter-kelas"
                    name="kelas_id"
                    :options="collect([['value' => '', 'label' => 'Semua Kelas']])->concat($kelasList->map(fn($k) => ['value' => $k->id, 'label' => $k->nama_kelas . ' (Tingkat ' . $k->tingkat . ')']))"
                    :value="$selectedKelasId"
                    submit-on-select
                />
            </div>
            <div class="sm:col-span-6 flex items-center gap-2">
                @if ($selectedKelasId !== null && $selectedKelasId !== '')
                    <a href="{{ route('admin.jadwal-ujian.index') }}" class="px-4 py-2.5 rounded-lg bg-[#F9FAFB] border border-[#D1D5DB] hover:bg-[#F3F4F6] text-[#4B5563] text-xs font-semibold transition-colors inline-flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">restart_alt</span>
                        <span>Reset Filter</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Jadwal Ujian -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between bg-[#F9FAFB]">
            <div>
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Daftar Jadwal Ujian</h3>
                <p class="text-xs text-[#6B7280] mt-0.5">Total {{ $jadwalUjians->count() }} jadwal asesmen terdaftar.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-[13px] text-[#374151] border-collapse">
                <thead class="bg-[#F9FAFB] text-[#6B7280] font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Kelas</th>
                        <th class="py-3 px-4">Mata Pelajaran</th>
                        <th class="py-3 px-4">Jenis Ujian</th>
                        <th class="py-3 px-4">Tanggal &amp; Waktu</th>
                        <th class="py-3 px-4">Ruangan Ujian</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse ($jadwalUjians as $index => $ujian)
                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                            <td class="py-3 px-4 text-center text-[#6B7280]">{{ $index + 1 }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded bg-[#E8F5EE] border border-[#BBF7D0] text-[#146C43] text-xs font-semibold">
                                    {{ $ujian->kelas->nama_kelas ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-[#1F2937]">{{ $ujian->mataPelajaran->nama_mapel ?? '-' }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded bg-[#F9FAFB] border border-[#E5E7EB] text-xs font-medium text-[#374151]">
                                    {{ $ujian->jenis_ujian }}
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <p class="font-medium text-[#1F2937]">{{ \Carbon\Carbon::parse($ujian->tanggal)->translatedFormat('d M Y') }}</p>
                                <p class="text-xs text-[#6B7280] inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px] text-[#1E9E63]">schedule</span>
                                    <span>{{ substr($ujian->waktu_mulai, 0, 5) }} WIB</span>
                                </p>
                            </td>
                            <td class="py-3 px-4 text-[#4B5563]">{{ $ujian->ruangan }}</td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" @click='openEditModal(@json($ujian))' class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-[#FFF7ED] hover:text-[#B45309] text-[#4B5563] transition-colors cursor-pointer" title="Edit Jadwal Ujian">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                    </button>
                                    <form method="POST" action="{{ route('admin.jadwal-ujian.destroy', $ujian->id) }}" onsubmit="return handleConfirmForm(event, this, 'Konfirmasi Hapus Jadwal Ujian', 'Apakah Anda yakin ingin menghapus jadwal ujian {{ addslashes($ujian->mataPelajaran->nama_mapel ?? '') }} kelas {{ addslashes($ujian->kelas->nama_kelas ?? '') }}?', 'Ya, Hapus Data');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-red-50 hover:text-red-600 text-[#4B5563] transition-colors cursor-pointer" title="Hapus Jadwal Ujian">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-[#6B7280]">
                                Belum ada jadwal ujian yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @php
        $jenisUjianOptions = [
            ['value' => 'PAS Ganjil 2026/2027', 'label' => 'PAS Ganjil 2026/2027'],
            ['value' => 'PTS Ganjil 2026/2027', 'label' => 'PTS Ganjil 2026/2027'],
            ['value' => 'PTS Genap 2026/2027', 'label' => 'PTS Genap 2026/2027'],
            ['value' => 'PAS Genap 2026/2027', 'label' => 'PAS Genap 2026/2027'],
            ['value' => 'Ujian Sekolah', 'label' => 'Ujian Sekolah'],
            ['value' => 'Ujian Praktik', 'label' => 'Ujian Praktik'],
        ];
    @endphp

    <!-- MODAL TAMBAH JADWAL UJIAN -->
    <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
        <div @click.away="showCreateModal = false" class="bg-white w-full max-w-xl rounded-xl border border-[#E5E7EB] shadow-xl overflow-visible">
            <div class="px-6 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between rounded-t-xl">
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Tambah Jadwal Ujian</h3>
                <button type="button" @click="showCreateModal = false" class="text-[#6B7280] hover:text-[#1F2937] p-1 rounded-md cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.jadwal-ujian.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Kelas <span class="text-red-500">*</span></label>
                        <x-select-dropdown
                            id="create_ujian_kelas_id"
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
                            id="create_ujian_mapel_id"
                            name="mapel_id"
                            :options="$mapelList->map(fn($m) => ['value' => $m->id, 'label' => $m->nama_mapel . ' (' . $m->kode_mapel . ')'])"
                            :value="old('mapel_id')"
                            placeholder="-- Pilih Mata Pelajaran --"
                            required
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1.5">Jenis Ujian <span class="text-red-500">*</span></label>
                    <x-select-dropdown
                        id="create_ujian_jenis"
                        name="jenis_ujian"
                        :options="$jenisUjianOptions"
                        :value="old('jenis_ujian', 'PAS Ganjil 2026/2027')"
                        placeholder="-- Pilih Jenis Ujian --"
                        required
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Tanggal Ujian <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', now()->addDays(7)->format('Y-m-d')) }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Waktu Mulai <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_mulai" value="{{ old('waktu_mulai', '08:00') }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1.5">Ruangan Ujian <span class="text-red-500">*</span></label>
                    <input type="text" name="ruangan" value="{{ old('ruangan') }}" placeholder="Contoh: Ruang 01 - Gedung Utama atau Lab Komputer SMANSA" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                </div>

                <div class="pt-3 border-t border-[#E5E7EB] flex items-center justify-end gap-2.5">
                    <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-md border border-[#D1D5DB] bg-white hover:bg-[#F9FAFB] text-[#374151] text-xs font-medium cursor-pointer">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold cursor-pointer">Simpan Jadwal Ujian</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT JADWAL UJIAN -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs" role="dialog" aria-modal="true">
        <div @click.away="showEditModal = false" class="bg-white w-full max-w-xl rounded-xl border border-[#E5E7EB] shadow-xl overflow-visible">
            <div class="px-6 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between rounded-t-xl">
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Edit Jadwal Ujian</h3>
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
                            id="edit_ujian_kelas_id"
                            name="kelas_id"
                            :options="$kelasList->map(fn($k) => ['value' => $k->id, 'label' => $k->nama_kelas . ' (Tingkat ' . $k->tingkat . ')'])"
                            placeholder="-- Pilih Kelas --"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Mata Pelajaran <span class="text-red-500">*</span></label>
                        <x-select-dropdown
                            id="edit_ujian_mapel_id"
                            name="mapel_id"
                            :options="$mapelList->map(fn($m) => ['value' => $m->id, 'label' => $m->nama_mapel . ' (' . $m->kode_mapel . ')'])"
                            placeholder="-- Pilih Mata Pelajaran --"
                            required
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1.5">Jenis Ujian <span class="text-red-500">*</span></label>
                    <x-select-dropdown
                        id="edit_ujian_jenis"
                        name="jenis_ujian"
                        :options="$jenisUjianOptions"
                        placeholder="-- Pilih Jenis Ujian --"
                        required
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Tanggal Ujian <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" x-model="editTanggal" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Waktu Mulai <span class="text-red-500">*</span></label>
                        <input type="time" name="waktu_mulai" x-model="editWaktuMulai" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1.5">Ruangan Ujian <span class="text-red-500">*</span></label>
                    <input type="text" name="ruangan" x-model="editRuangan" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
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
