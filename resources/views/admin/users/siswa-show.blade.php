@extends('layouts.siakad')

@php
    $pageTitle = 'Detail Siswa';
    $pageSubtitle = $siswa->name;
    $initials = collect(explode(' ', $siswa->name))->map(fn($seg) => mb_substr($seg, 0, 1))->take(2)->join('');
@endphp

@section('content')
<div class="space-y-5">

    <a href="{{ route('admin.users.index', ['tab' => 'siswa', 'kelas_id' => $siswa->kelas_id ?? 'unassigned']) }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-[#6B7280] hover:text-[#1F2937] transition-colors">
        <span class="material-symbols-outlined text-[17px]">arrow_back</span>
        <span>Kembali ke Daftar Siswa</span>
    </a>

    <!-- Profil Ringkas & Rekap Kehadiran Siswa -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="w-16 h-20 rounded-md bg-[#F4F6F8] border border-[#E5E7EB] flex flex-col items-center justify-center flex-shrink-0">
                <span class="font-heading font-bold text-lg text-[#1F2937]">{{ strtoupper($initials) }}</span>
                <span class="text-[11px] font-semibold uppercase text-[#6B7280] mt-0.5">Siswa</span>
            </div>
            <div class="space-y-2">
                <h2 class="font-heading font-bold text-lg text-[#1F2937]">{{ $siswa->name }}</h2>
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="px-2.5 py-0.5 rounded border border-[#D1D5DB] bg-[#F9FAFB] text-[#374151]">
                        NIS: <strong class="text-[#1F2937]">{{ $siswa->nis_nip ?? '-' }}</strong>
                    </span>
                    <span class="px-2.5 py-0.5 rounded border border-[#D1D5DB] bg-[#F9FAFB] text-[#374151]">
                        Email: <strong class="text-[#1F2937]">{{ $siswa->email }}</strong>
                    </span>
                    <span class="px-2.5 py-0.5 rounded border border-[#BBF7D0] bg-[#E8F5EE] text-[#146C43] font-semibold">
                        Kelas: {{ $siswa->kelas->nama_kelas ?? 'Belum Ditempatkan' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Ringkasan Persentase Kehadiran -->
        <div class="grid grid-cols-5 gap-2 text-center bg-[#F9FAFB] p-3 rounded-lg border border-[#E5E7EB]">
            <div class="px-2">
                <p class="text-[11px] font-semibold text-[#6B7280]">Hadir</p>
                <p class="font-heading font-bold text-base text-[#146C43]">{{ $persentaseKehadiran }}%</p>
            </div>
            <div class="px-2 border-l border-[#E5E7EB]">
                <p class="text-[11px] font-semibold text-[#6B7280]">H</p>
                <p class="font-heading font-bold text-base text-[#1F2937]">{{ $totalHadir }}</p>
            </div>
            <div class="px-2 border-l border-[#E5E7EB]">
                <p class="text-[11px] font-semibold text-[#6B7280]">S</p>
                <p class="font-heading font-bold text-base text-[#1F2937]">{{ $totalSakit }}</p>
            </div>
            <div class="px-2 border-l border-[#E5E7EB]">
                <p class="text-[11px] font-semibold text-[#6B7280]">I</p>
                <p class="font-heading font-bold text-base text-[#1F2937]">{{ $totalIzin }}</p>
            </div>
            <div class="px-2 border-l border-[#E5E7EB]">
                <p class="text-[11px] font-semibold text-red-600">A</p>
                <p class="font-heading font-bold text-base text-red-600">{{ $totalAlpha }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- Kolom Kiri (7 Kolom): Form Edit Biodata & Pindah Kelas -->
        <div class="lg:col-span-7 bg-white rounded-lg border border-[#E5E7EB] overflow-visible" id="edit-biodata">
            <div class="px-5 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] rounded-t-lg">
                <h3 class="font-heading font-semibold text-base text-[#1F2937]">Edit Biodata &amp; Penempatan Kelas</h3>
                <p class="text-xs text-[#6B7280] mt-0.5">Ubah nama, NIS, email, atau pindahkan siswa ke kelas lain.</p>
            </div>
            <form method="POST" action="{{ route('admin.users.siswa.update', $siswa->id) }}" class="p-5 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1.5">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $siswa->name) }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">NIS <span class="text-red-500">*</span></label>
                        <input type="text" name="nis_nip" value="{{ old('nis_nip', $siswa->nis_nip) }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $siswa->email) }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1.5">Kelas Saat Ini (Pindah Kelas)</label>
                    <x-select-dropdown
                        id="edit_siswa_kelas_id"
                        name="kelas_id"
                        :options="collect([['value' => '', 'label' => '-- Belum Ditempatkan --']])->concat($kelasList->map(fn($k) => ['value' => $k->id, 'label' => $k->nama_kelas . ' (Tingkat ' . $k->tingkat . ')']))"
                        :value="old('kelas_id', $siswa->kelas_id)"
                        placeholder="-- Pilih Kelas --"
                    />
                </div>
                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                        Simpan Perubahan Siswa
                    </button>
                </div>
            </form>
        </div>

        <!-- Kolom Kanan (5 Kolom): Reset Password & Hapus Akun -->
        <div class="lg:col-span-5 space-y-5">
            <!-- Form Ganti Password Siswa -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
                <div class="px-5 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[19px] text-[#1E9E63]">lock_reset</span>
                    <div>
                        <h3 class="font-heading font-semibold text-base text-[#1F2937]">Ganti Password Siswa</h3>
                        <p class="text-xs text-[#6B7280]">Atur ulang kata sandi siswa tanpa password lama.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.users.siswa.reset-password', $siswa->id) }}" class="p-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Kata Sandi Baru <span class="text-red-500">*</span></label>
                        <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Konfirmasi Kata Sandi Baru <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" required minlength="6" placeholder="Ulangi kata sandi baru" class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                        Atur Ulang Password Siswa
                    </button>
                </form>
            </div>

            <!-- Hapus Akun Siswa -->
            <div class="bg-white rounded-lg border border-red-200 overflow-hidden">
                <div class="px-5 py-4 bg-red-50 border-b border-red-200 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[19px] text-red-600">warning</span>
                    <div>
                        <h3 class="font-heading font-semibold text-base text-red-800">Zona Bahaya</h3>
                        <p class="text-xs text-red-700">Penghapusan akun siswa bersifat permanen.</p>
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    <p class="text-xs text-[#6B7280] leading-relaxed">
                        Menghapus akun siswa <strong>{{ $siswa->name }}</strong> akan menghapus seluruh data presensi dan pengumpulan tugas siswa ini.
                    </p>
                    <form method="POST" action="{{ route('admin.users.siswa.destroy', $siswa->id) }}" onsubmit="return handleConfirmForm(event, this, 'Konfirmasi Hapus Akun Siswa', 'Apakah Anda yakin ingin menghapus akun siswa {{ addslashes($siswa->name) }}? Tindakan ini tidak dapat dibatalkan.', 'Ya, Hapus Data');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2.5 rounded-md bg-red-600 hover:bg-red-700 text-white text-xs font-semibold transition-colors cursor-pointer flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-[17px]">delete_forever</span>
                            <span>Hapus Akun Siswa</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
