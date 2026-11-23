@extends('layouts.siakad')

@php
    $pageTitle = 'Pengumuman Sekolah';
    $pageSubtitle = 'Papan Informasi Resmi SMA Negeri 1 Gunungsitoli';
    $detailRouteName = $role === 'guru' ? 'guru.pengumuman.show' : 'siswa.pengumuman.show';
    $indexRouteName = $role === 'guru' ? 'guru.pengumuman.index' : 'siswa.pengumuman.index';
@endphp

@section('content')
<div class="space-y-5">

    <!-- Header Section -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-lg font-heading font-bold text-[#1F2937] tracking-tight">Pengumuman Sekolah</h2>
            <p class="text-xs sm:text-[13px] text-[#6B7280] mt-0.5">
                Informasi resmi, edaran akademik, dan pengumuman kegiatan SMA Negeri 1 Gunungsitoli.
            </p>
        </div>

        <form method="GET" action="{{ route($indexRouteName) }}" class="flex items-center gap-2 w-full md:w-80">
            <div class="relative flex-1">
                <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Cari judul pengumuman..." class="w-full text-xs sm:text-sm pl-9 pr-3.5 py-2 rounded-lg border border-[#D1D5DB] bg-white text-[#1F2937] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15">
                <span class="material-symbols-outlined absolute left-2.5 top-2 text-[18px] text-[#9CA3AF]">search</span>
            </div>
            <button type="submit" class="px-3.5 py-2 rounded-lg bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                Cari
            </button>
        </form>
    </div>

    <!-- Daftar Pengumuman -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($pengumumans as $item)
            <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden flex flex-col justify-between hover:border-[#1E9E63]/60 transition-colors">
                <div>
                    @if ($item->gambar_path)
                        <div class="w-full h-44 bg-[#F9FAFB] border-b border-[#E5E7EB] overflow-hidden">
                            <img src="{{ asset('storage/' . $item->gambar_path) }}" alt="Poster {{ $item->judul }}" class="w-full h-full object-cover">
                        </div>
                    @endif
                    <div class="p-5 space-y-2.5">
                        <div class="flex items-center justify-between text-xs text-[#6B7280] gap-2">
                            <span class="inline-flex items-center gap-1">
                                <span class="material-symbols-outlined text-[15px] text-[#1E9E63]">calendar_today</span>
                                <span>{{ $item->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') }} WIB</span>
                            </span>
                            <span class="px-2 py-0.5 rounded bg-[#E8F5EE] border border-[#BBF7D0] text-[#146C43] font-semibold text-[11px]">
                                {{ $item->target_role === 'semua' ? 'Semua Warga' : 'Khusus ' . ucfirst($item->target_role) }}
                            </span>
                        </div>

                        <h3 class="font-heading font-semibold text-sm sm:text-base text-[#1F2937] leading-snug">
                            {{ $item->judul }}
                        </h3>

                        <p class="text-xs sm:text-[13px] text-[#6B7280] leading-relaxed">
                            {{ \Illuminate\Support\Str::limit(strip_tags($item->isi), 150) }}
                        </p>
                    </div>
                </div>

                <div class="px-5 py-3.5 border-t border-[#E5E7EB] flex items-center justify-between gap-2 bg-[#F9FAFB]/50">
                    <span class="text-xs text-[#6B7280]">
                        Oleh: <strong class="text-[#1F2937]">{{ $item->penulis->name ?? 'Admin TU' }}</strong>
                    </span>
                    <a href="{{ route($detailRouteName, $item->id) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#1E9E63] hover:underline">
                        <span>Baca Selengkapnya</span>
                        <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full p-10 text-center bg-white rounded-lg border border-[#E5E7EB] space-y-2">
                <span class="material-symbols-outlined text-[40px] text-[#9CA3AF]">campaign</span>
                <h4 class="font-heading font-semibold text-sm text-[#1F2937]">Belum Ada Pengumuman</h4>
                <p class="text-xs text-[#6B7280]">Tidak ada pengumuman sekolah yang tersedia saat ini.</p>
            </div>
        @endforelse
    </div>

    @if ($pengumumans->hasPages())
        <div class="p-4 bg-white rounded-lg border border-[#E5E7EB]">
            {{ $pengumumans->links() }}
        </div>
    @endif

</div>
@endsection
