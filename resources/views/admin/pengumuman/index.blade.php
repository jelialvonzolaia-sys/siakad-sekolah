@extends('layouts.siakad')

@php
    $pageTitle = 'Pengumuman Sekolah';
    $pageSubtitle = 'Manajemen Informasi & Edaran Resmi';
@endphp

@section('content')
<div class="space-y-5">

    <!-- Header Section -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-heading font-bold text-[#1F2937] tracking-tight">Pengumuman Sekolah</h2>
            <p class="text-xs sm:text-[13px] text-[#6B7280] mt-0.5">Publikasikan pengumuman resmi untuk seluruh warga sekolah, guru, maupun siswa.</p>
        </div>
        <a href="{{ route('admin.pengumuman.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer self-start sm:self-auto">
            <span class="material-symbols-outlined text-[18px]">campaign</span>
            <span>+ Buat Pengumuman Baru</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-4">
        <form method="GET" action="{{ route('admin.pengumuman.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-3">
                <label class="block text-xs font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Target Penerima</label>
                <x-select-dropdown
                    id="filter-target"
                    name="target_role"
                    :options="[
                        ['value' => '', 'label' => 'Semua Target'],
                        ['value' => 'semua', 'label' => 'Semua Warga Sekolah'],
                        ['value' => 'guru', 'label' => 'Khusus Guru'],
                        ['value' => 'siswa', 'label' => 'Khusus Siswa'],
                    ]"
                    :value="$targetFilter"
                    submit-on-select
                />
            </div>

            <div class="sm:col-span-3">
                <label class="block text-xs font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Status</label>
                <x-select-dropdown
                    id="filter-status"
                    name="status"
                    :options="[
                        ['value' => '', 'label' => 'Semua Status'],
                        ['value' => 'published', 'label' => 'Publish (Diterbitkan)'],
                        ['value' => 'draft', 'label' => 'Draft'],
                    ]"
                    :value="$statusFilter"
                    submit-on-select
                />
            </div>

            <div class="sm:col-span-4">
                <label class="block text-xs font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Cari Judul</label>
                <div class="relative">
                    <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Kata kunci judul pengumuman..." class="w-full text-sm pl-9 pr-3.5 py-2.5 rounded-lg border border-[#D1D5DB] bg-white text-[#1F2937] focus:outline-none focus:border-[#1E9E63]">
                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-[18px] text-[#9CA3AF]">search</span>
                </div>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full px-3 py-2.5 rounded-lg bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                    Cari
                </button>
                @if ($targetFilter !== '' || $statusFilter !== '' || $searchQuery !== '')
                    <a href="{{ route('admin.pengumuman.index') }}" class="px-3 py-2.5 rounded-lg bg-[#F9FAFB] border border-[#D1D5DB] hover:bg-[#F3F4F6] text-[#4B5563] text-xs font-semibold transition-colors" title="Reset Filter">
                        <span class="material-symbols-outlined text-[17px] align-middle">restart_alt</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Pengumuman -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="px-5 py-4 border-b border-[#E5E7EB] flex items-center justify-between bg-[#F9FAFB]">
            <div>
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Daftar Pengumuman Sekolah</h3>
                <p class="text-xs text-[#6B7280] mt-0.5">Total {{ $pengumumans->total() }} pengumuman tercatat.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-[13px] text-[#374151] border-collapse">
                <thead class="bg-[#F9FAFB] text-[#6B7280] font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="py-3 px-4 w-12 text-center">No</th>
                        <th class="py-3 px-4">Judul &amp; Cuplikan Isi</th>
                        <th class="py-3 px-4">Target Penerima</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse ($pengumumans as $index => $p)
                        <tr class="hover:bg-[#F9FAFB] transition-colors">
                            <td class="py-3 px-4 text-center text-[#6B7280]">{{ $pengumumans->firstItem() + $index }}</td>
                            <td class="py-3 px-4">
                                <div class="flex items-start gap-3">
                                    @if ($p->gambar_path)
                                        <img src="{{ asset('storage/' . $p->gambar_path) }}" alt="Poster {{ $p->judul }}" class="w-14 h-14 rounded-md object-cover border border-[#E5E7EB] flex-shrink-0 bg-[#F9FAFB]">
                                    @endif
                                    <div>
                                        <p class="font-semibold text-[#1F2937]">{{ $p->judul }}</p>
                                        <p class="text-xs text-[#6B7280] mt-0.5">{{ \Illuminate\Support\Str::limit(strip_tags($p->isi), 100) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 rounded bg-[#F9FAFB] border border-[#E5E7EB] text-xs font-medium text-[#374151]">
                                    {{ $p->target_role === 'semua' ? 'Semua Warga Sekolah' : 'Khusus ' . ucfirst($p->target_role) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if ($p->is_published)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#E8F5EE] border border-[#BBF7D0] text-[#146C43] text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#1E9E63]"></span>
                                        <span>Publish</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-[#FFF7ED] border border-[#FED7AA] text-[#9A3412] text-xs font-semibold">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap text-[#6B7280]">
                                {{ $p->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a href="{{ route('admin.pengumuman.edit', $p->id) }}" class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-[#FFF7ED] hover:text-[#B45309] text-[#4B5563] transition-colors" title="Edit Pengumuman">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('admin.pengumuman.destroy', $p->id) }}" onsubmit="return handleConfirmForm(event, this, 'Konfirmasi Hapus Pengumuman', 'Apakah Anda yakin ingin menghapus pengumuman \'{{ addslashes($p->judul) }}\'?', 'Ya, Hapus Data');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-md bg-white border border-[#D1D5DB] hover:bg-red-50 hover:text-red-600 text-[#4B5563] transition-colors cursor-pointer" title="Hapus Pengumuman">
                                            <span class="material-symbols-outlined text-[16px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-[#6B7280]">
                                Belum ada pengumuman sekolah yang dibuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pengumumans->hasPages())
            <div class="p-4 border-t border-[#E5E7EB] bg-[#F9FAFB]">
                {{ $pengumumans->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
