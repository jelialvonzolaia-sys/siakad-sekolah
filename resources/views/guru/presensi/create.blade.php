@extends('layouts.siakad')

@php
    $pageTitle = 'Buka Sesi Presensi';
    $pageSubtitle = $jadwal->mataPelajaran->nama_mapel . ' • ' . $jadwal->kelas->nama_kelas;
@endphp

@section('content')
<div class="max-w-3xl mx-auto space-y-5">

    <!-- Tombol Kembali -->
    <a href="{{ route('guru.presensi.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-[#6B7280] hover:text-[#1F2937] transition-colors">
        <span class="material-symbols-outlined text-[17px]">arrow_back</span>
        <span>Kembali ke Daftar Presensi</span>
    </a>

    <!-- Kartu Form Pembukaan Sesi Presensi -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <!-- Header Kartu -->
        <div class="px-6 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] text-[#1E9E63] flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[20px]">fact_check</span>
                </div>
                <div>
                    <h2 class="text-base font-heading font-semibold text-[#1F2937]">Buka Sesi Presensi Baru</h2>
                    <p class="text-xs text-[#6B7280]">Konfigurasikan pertemuan tatap muka dan metode absensi siswa.</p>
                </div>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                Tatap Muka
            </span>
        </div>

        <!-- Form Submission -->
        <form method="POST" action="{{ route('guru.presensi.store', $jadwal->id) }}" class="p-6 space-y-5">
            @csrf

            <!-- Informasi Read-Only: Kelas & Mata Pelajaran -->
            <div>
                <label class="block text-xs font-semibold text-[#374151] mb-1.5">
                    Kelas &amp; Mata Pelajaran
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-lg bg-[#F9FAFB] border border-[#E5E7EB]">
                    <div>
                        <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Mata Pelajaran</p>
                        <p class="text-sm font-heading font-semibold text-[#1F2937]">{{ $jadwal->mataPelajaran->kode_mapel }} — {{ $jadwal->mataPelajaran->nama_mapel }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider mb-1">Kelas</p>
                        <p class="text-sm font-heading font-semibold text-[#1F2937]">{{ $jadwal->kelas->nama_kelas }} <span class="text-xs font-normal text-[#6B7280]">(Tingkat {{ $jadwal->kelas->tingkat }} • {{ $jadwal->hari }})</span></p>
                    </div>
                </div>
            </div>

            <!-- Deskripsi / Pertemuan & Topik Pembelajaran -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-1">
                    <label for="pertemuan_ke" class="block text-xs font-semibold text-[#374151] mb-1.5">
                        Deskripsi / Pertemuan <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center rounded-lg border border-[#D1D5DB] focus-within:border-[#1E9E63] focus-within:ring-2 focus-within:ring-[#1E9E63]/15 overflow-hidden bg-white">
                        <span class="px-3 py-2 bg-[#F9FAFB] border-r border-[#E5E7EB] text-xs font-semibold text-[#4B5563] select-none">Pertemuan</span>
                        <input type="number" id="pertemuan_ke" name="pertemuan_ke" value="{{ old('pertemuan_ke', $suggestedPertemuan) }}" min="1" required class="w-full px-3 py-2 text-sm text-[#1F2937] font-semibold border-0 focus:outline-none focus:ring-0">
                    </div>
                    @error('pertemuan_ke')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="materi" class="block text-xs font-semibold text-[#374151] mb-1.5">
                        Topik Pembelajaran <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="materi" name="materi" value="{{ old('materi') }}" placeholder="Contoh: Ikatan Ion dan Kovalen" required class="w-full px-3.5 py-2 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937]">
                    @error('materi')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Jenis Presensi (Pilihan Radio: Mandiri vs QR) -->
            <div>
                <label class="block text-xs font-semibold text-[#374151] mb-2">
                    Jenis Presensi <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <!-- Opsi Mandiri -->
                    <label class="relative flex items-start gap-3 p-4 rounded-lg border border-[#E5E7EB] hover:border-[#1E9E63] cursor-pointer transition-colors has-[:checked]:border-[#1E9E63] has-[:checked]:bg-[#E8F5EE]/50">
                        <input type="radio" name="metode" value="mandiri" {{ old('metode', 'mandiri') === 'mandiri' ? 'checked' : '' }} class="mt-0.5 text-[#1E9E63] focus:ring-[#1E9E63]">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[17px] text-[#4B5563]">touch_app</span>
                                <span class="text-xs font-semibold text-[#1F2937]">Mandiri</span>
                            </div>
                            <p class="text-[11px] text-[#6B7280] mt-1 leading-relaxed">
                                Siswa menekan tombol "Presensi" melalui lokasi GPS sekolah.
                            </p>
                        </div>
                    </label>

                    <!-- Opsi QR Code -->
                    <label class="relative flex items-start gap-3 p-4 rounded-lg border border-[#E5E7EB] hover:border-[#1E9E63] cursor-pointer transition-colors has-[:checked]:border-[#1E9E63] has-[:checked]:bg-[#E8F5EE]/50">
                        <input type="radio" name="metode" value="qr" {{ old('metode') === 'qr' ? 'checked' : '' }} class="mt-0.5 text-[#1E9E63] focus:ring-[#1E9E63]">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[17px] text-[#1E9E63]">qr_code_scanner</span>
                                <span class="text-xs font-semibold text-[#1F2937]">QR</span>
                            </div>
                            <p class="text-[11px] text-[#6B7280] mt-1 leading-relaxed">
                                Guru menampilkan QR dinamis (refresh setiap 20 detik) untuk dipindai siswa.
                            </p>
                        </div>
                    </label>
                </div>
                @error('metode')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tanggal Pertemuan & Jam / Batas Waktu Presensi -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                <div>
                    <label for="tanggal" class="block text-xs font-semibold text-[#374151] mb-1.5">
                        Tanggal Pertemuan <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required class="w-full px-3.5 py-2 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937]">
                    @error('tanggal')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-[#374151] mb-1.5">
                        Jam / Batas Waktu Presensi <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <input type="time" id="jam_mulai" name="jam_mulai" value="{{ old('jam_mulai', $defaultJamMulai) }}" required class="w-full px-3.5 py-2 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937]">
                            <span class="block text-[10px] text-[#6B7280] mt-1">Jam Mulai (contoh: 07:30)</span>
                            @error('jam_mulai')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <input type="time" id="jam_selesai" name="jam_selesai" value="{{ old('jam_selesai', $defaultJamSelesai) }}" required class="w-full px-3.5 py-2 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-sm text-[#1F2937]">
                            <span class="block text-[10px] text-[#6B7280] mt-1">Batas Selesai (contoh: 09:00)</span>
                            @error('jam_selesai')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="pt-4 border-t border-[#E5E7EB] flex items-center justify-end gap-3">
                <a href="{{ route('guru.presensi.index') }}" class="px-4 py-2 rounded-md border border-[#D1D5DB] bg-white hover:bg-[#F9FAFB] text-[#374151] text-xs font-medium transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-[17px]">play_circle</span>
                    <span>Buka Sesi Presensi Sekarang</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
