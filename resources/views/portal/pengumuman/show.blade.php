@extends('layouts.siakad')

@php
    $pageTitle = 'Detail Pengumuman';
    $pageSubtitle = $pengumuman->judul;
    $indexRouteName = $role === 'guru' ? 'guru.pengumuman.index' : 'siswa.pengumuman.index';
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-5">

    <a href="{{ route($indexRouteName) }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-[#6B7280] hover:text-[#1F2937] transition-colors">
        <span class="material-symbols-outlined text-[17px]">arrow_back</span>
        <span>Kembali ke Daftar Pengumuman</span>
    </a>

    <article class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="p-6 border-b border-[#E5E7EB] bg-[#F9FAFB] space-y-2.5">
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="px-2.5 py-0.5 rounded bg-[#E8F5EE] border border-[#BBF7D0] text-[#146C43] font-semibold">
                    {{ $pengumuman->target_role === 'semua' ? 'Semua Warga Sekolah' : 'Khusus ' . ucfirst($pengumuman->target_role) }}
                </span>
                <span class="text-[#9CA3AF]">•</span>
                <span class="text-[#6B7280] inline-flex items-center gap-1">
                    <span class="material-symbols-outlined text-[15px] text-[#1E9E63]">schedule</span>
                    <span>Diterbitkan {{ $pengumuman->created_at->timezone('Asia/Jakarta')->translatedFormat('l, d F Y, H:i') }} WIB</span>
                </span>
            </div>

            <h1 class="font-heading font-bold text-lg sm:text-xl text-[#1F2937] leading-snug">
                {{ $pengumuman->judul }}
            </h1>

            <p class="text-xs text-[#6B7280]">
                Oleh: <strong class="text-[#1F2937]">{{ $pengumuman->penulis->name ?? 'Administrator SMAN 1 Gunungsitoli' }}</strong>
            </p>
        </div>

        @if ($pengumuman->gambar_path)
            <div class="px-6 pt-6 sm:px-8">
                <div class="rounded-lg border border-[#E5E7EB] bg-[#F9FAFB] p-2 flex justify-center">
                    <img src="{{ asset('storage/' . $pengumuman->gambar_path) }}" alt="Poster {{ $pengumuman->judul }}" class="max-h-[480px] w-auto rounded-md object-contain">
                </div>
            </div>
        @endif

        <div class="p-6 sm:p-8 text-sm text-[#1F2937] leading-relaxed space-y-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_table]:w-full [&_table]:border-collapse [&_td]:border [&_td]:border-[#E5E7EB] [&_td]:p-2 [&_th]:border [&_th]:border-[#E5E7EB] [&_th]:p-2 [&_th]:bg-[#F9FAFB]">
            {!! $pengumuman->isi !!}
        </div>
    </article>

</div>
@endsection
