<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-lg font-bold text-[#1F2937]">SMA NEGERI 1 GUNUNGSITOLI</h2>
        <p class="text-xs text-[#6B7280] font-semibold uppercase tracking-wider mt-0.5">Layanan Reset Kata Sandi Akun</p>
    </div>

    <!-- Alert Banner -->
    <div class="p-4 mb-4 rounded-lg bg-[#FFF7ED] border border-[#FED7AA] text-center space-y-2">
        <div class="flex items-center justify-center">
            <span class="text-2xl text-[#D97706] font-bold">&#9888;</span>
        </div>
        <p class="font-bold text-sm text-[#9A3412] uppercase leading-snug">
            SILAKAN HUBUNGI ADMIN SMA NEGERI 1 GUNUNGSITOLI UNTUK MELAKUKAN RESET PASSWORD
        </p>
        <p class="text-xs text-[#9A3412]/90">
            Demi keamanan data akademik dan privasi siswa/guru SMA Negeri 1 Gunungsitoli, reset kata sandi dilakukan melalui verifikasi resmi oleh Administrator Sekolah di Ruang Tata Usaha (Jl. Pendidikan No. 3, Gunungsitoli).
        </p>
    </div>

    <div class="flex items-center justify-center mt-6">
        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-[#1E9E63] hover:bg-[#168251] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none transition ease-in-out duration-150">
            Kembali ke Halaman Login
        </a>
    </div>
</x-guest-layout>
