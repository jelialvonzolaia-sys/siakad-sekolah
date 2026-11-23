@extends('layouts.siakad')

@php
    $pageTitle = 'Detail Guru';
    $pageSubtitle = $guru->name;
    $initials = collect(explode(' ', $guru->name))->map(fn($seg) => mb_substr($seg, 0, 1))->take(2)->join('');
@endphp

@section('content')
<div class="space-y-5">

    <a href="{{ route('admin.users.index', ['tab' => 'guru']) }}" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-[#6B7280] hover:text-[#1F2937] transition-colors">
        <span class="material-symbols-outlined text-[17px]">arrow_back</span>
        <span>Kembali ke Daftar Guru</span>
    </a>

    <!-- Profil Ringkas Guru -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] p-5 flex flex-col sm:flex-row items-start gap-4">
        <div class="w-16 h-20 rounded-md bg-[#F4F6F8] border border-[#E5E7EB] flex flex-col items-center justify-center flex-shrink-0">
            <span class="font-heading font-bold text-lg text-[#1F2937]">{{ strtoupper($initials) }}</span>
            <span class="text-[11px] font-semibold uppercase text-[#6B7280] mt-0.5">Guru</span>
        </div>
        <div class="flex-1 space-y-2">
            <h2 class="font-heading font-bold text-lg text-[#1F2937]">{{ $guru->name }}</h2>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="px-2.5 py-0.5 rounded border border-[#D1D5DB] bg-[#F9FAFB] text-[#374151]">
                    NIP: <strong class="text-[#1F2937]">{{ $guru->nis_nip ?? '-' }}</strong>
                </span>
                <span class="px-2.5 py-0.5 rounded border border-[#D1D5DB] bg-[#F9FAFB] text-[#374151]">
                    Email: <strong class="text-[#1F2937]">{{ $guru->email }}</strong>
                </span>
                <span class="px-2.5 py-0.5 rounded border border-[#BBF7D0] bg-[#E8F5EE] text-[#146C43] font-semibold">
                    Wali Kelas: {{ $guru->kelasWali->pluck('nama_kelas')->join(', ') ?: 'Tidak Ada' }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- Kolom Kiri (7 Kolom): Perwalian, Jadwal Mengajar & Edit Data -->
        <div class="lg:col-span-7 space-y-5">
            <!-- Form Edit Data Guru -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
                <div class="px-5 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB]">
                    <h3 class="font-heading font-semibold text-base text-[#1F2937]">Edit Data Guru</h3>
                    <p class="text-xs text-[#6B7280] mt-0.5">Perbarui identitas nama, NIP, atau alamat email guru.</p>
                </div>
                <form method="POST" action="{{ route('admin.users.guru.update', $guru->id) }}" class="p-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-[#374151] mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $guru->name) }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#374151] mb-1.5">NIP <span class="text-red-500">*</span></label>
                            <input type="text" name="nis_nip" value="{{ old('nis_nip', $guru->nis_nip) }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#374151] mb-1.5">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $guru->email) }}" required class="w-full px-3.5 py-2.5 rounded-lg border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] text-sm text-[#1F2937]">
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="px-5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors cursor-pointer">
                            Simpan Perubahan Data
                        </button>
                    </div>
                </form>
            </div>

            <!-- Jadwal Mengajar Guru -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
                <div class="px-5 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB]">
                    <h3 class="font-heading font-semibold text-base text-[#1F2937]">Jadwal Mengajar &amp; Kelas Diampu</h3>
                    <p class="text-xs text-[#6B7280] mt-0.5">Daftar alokasi jadwal mengajar aktif guru ini.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-[13px] text-[#374151] border-collapse">
                        <thead class="bg-[#F9FAFB] text-[#6B7280] font-semibold border-b border-[#E5E7EB]">
                            <tr>
                                <th class="py-2.5 px-4">Hari</th>
                                <th class="py-2.5 px-4">Waktu</th>
                                <th class="py-2.5 px-4">Kelas</th>
                                <th class="py-2.5 px-4">Mata Pelajaran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5E7EB]">
                            @forelse ($guru->jadwals as $j)
                                <tr class="hover:bg-[#F9FAFB]">
                                    <td class="py-2.5 px-4 font-semibold text-[#1F2937]">{{ $j->hari }}</td>
                                    <td class="py-2.5 px-4 text-[#4B5563]">{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} WIB</td>
                                    <td class="py-2.5 px-4 font-medium">{{ $j->kelas->nama_kelas ?? '-' }}</td>
                                    <td class="py-2.5 px-4">{{ $j->mataPelajaran->nama_mapel ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-[#6B7280]">Belum ada jadwal mengajar yang dialokasikan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan (5 Kolom): Reset Password & Zona Bahaya -->
        <div class="lg:col-span-5 space-y-5">
            <!-- Form Reset / Ganti Password Guru (Tanpa Password Lama) -->
            <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
                <div class="px-5 py-4 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[19px] text-[#1E9E63]">lock_reset</span>
                    <div>
                        <h3 class="font-heading font-semibold text-base text-[#1F2937]">Reset / Ganti Password Guru</h3>
                        <p class="text-xs text-[#6B7280]">Atur ulang kata sandi akun secara langsung tanpa password lama.</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.users.guru.reset-password', $guru->id) }}" class="p-5 space-y-4">
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
                        Atur Ulang Password Guru
                    </button>
                </form>
            </div>

            <!-- Zona Bahaya: Hapus Akun Guru -->
            <div class="bg-white rounded-lg border border-red-200 overflow-hidden">
                <div class="px-5 py-4 bg-red-50 border-b border-red-200 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[19px] text-red-600">warning</span>
                    <div>
                        <h3 class="font-heading font-semibold text-base text-red-800">Zona Bahaya</h3>
                        <p class="text-xs text-red-700">Penghapusan akun bersifat permanen.</p>
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    <p class="text-xs text-[#6B7280] leading-relaxed">
                        Menghapus akun guru <strong>{{ $guru->name }}</strong> akan menghapus jadwal mengajar serta relasi terkait dari database.
                    </p>
                    <form method="POST" action="{{ route('admin.users.guru.destroy', $guru->id) }}" onsubmit="return handleConfirmForm(event, this, 'Konfirmasi Hapus Akun Guru', 'Apakah Anda yakin ingin menghapus akun guru {{ addslashes($guru->name) }}? Tindakan ini tidak dapat dibatalkan.', 'Ya, Hapus Data');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2.5 rounded-md bg-red-600 hover:bg-red-700 text-white text-xs font-semibold transition-colors cursor-pointer flex items-center justify-center gap-1.5">
                            <span class="material-symbols-outlined text-[17px]">delete_forever</span>
                            <span>Hapus Akun Guru</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
