<!DOCTYPE html>
<html lang="id" class="text-[17px]">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>{{ $title ?? ($pageTitle ?? 'SIAKAD SMANSA') }} - SMA Negeri 1 Gunungsitoli</title>

    <!-- Google Fonts: Poppins & Roboto (fallback Arial) -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">

    <!-- Alpine.js & Tailwind CSS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emeraldbrand: '#1E9E63',
                        emeraldhover: '#168251',
                        emeralddark: '#146C43',
                        emeraldsoft: '#E8F5EE',
                        evergreen: '#1F2937',
                        moss: '#168251',
                        leaf: '#1E9E63',
                        sage: '#9CA3AF',
                        softlime: '#E8F5EE',
                        beige: '#E5E7EB',
                        canvas: '#F4F6F8',
                    },
                    fontFamily: {
                        sans: ['Poppins', 'Roboto', 'Arial', 'sans-serif'],
                        heading: ['Poppins', 'Roboto', 'Arial', 'sans-serif'],
                        mono: ['Poppins', 'Roboto', 'Arial', 'sans-serif'],
                    }
                }
            }
        };
    </script>
    <style>
        body {
            font-family: 'Poppins', 'Roboto', Arial, sans-serif;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 20;
        }
        [x-cloak] {
            display: none !important;
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #F4F6F8;
        }
        ::-webkit-scrollbar-thumb {
            background: #D1D5DB;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #9CA3AF;
        }
    </style>
</head>
<body class="bg-[#F4F6F8] text-[#1F2937] font-sans antialiased min-h-screen flex flex-col text-sm" x-data="{ sidebarOpen: false }">

    @php
        $user = auth()->user();
        $role = $user->role;
    @endphp

    <!-- TOP NAVBAR FULL-WIDTH (Hijau Zamrud Institusional #1E9E63) -->
    <header class="w-full min-h-[3.75rem] py-1.5 bg-[#1E9E63] text-white px-4 sm:px-6 flex items-center justify-between sticky top-0 z-40 border-b border-[#168251] shadow-xs select-none gap-3">
        <!-- Kiri: Hamburger (Mobile) + Logo & Nama Sistem -->
        <div class="flex items-center gap-3 min-w-0">
            <button type="button" @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-1.5 rounded-lg text-white/90 hover:bg-white/15 focus:outline-none cursor-pointer" aria-label="Buka Menu Navigasi">
                <span class="material-symbols-outlined text-[22px]">menu</span>
            </button>

            <a href="{{ $role === 'admin' ? route('admin.dashboard') : ($role === 'guru' ? route('guru.dashboard') : route('siswa.dashboard')) }}" class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-full bg-white/15 border border-white/25 flex items-center justify-center text-white flex-shrink-0">
                    <span class="material-symbols-outlined text-[21px]">school</span>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-heading font-bold text-sm sm:text-base tracking-tight text-white uppercase leading-none">SIAKAD SMANSA</span>
                    </div>
                    <p class="text-[11px] text-white/90 font-medium tracking-wide uppercase hidden sm:block mt-0.5">
                        SMA NEGERI 1 GUNUNGSITOLI
                    </p>
                </div>
            </a>
        </div>

        <!-- Tengah: Periode Semester Aktif & Tanggal/Jam Real-Time -->
        <div class="hidden md:flex flex-col items-center justify-center text-center leading-tight">
            <span class="text-xs text-white/90 font-normal">Semester Ganjil T.A. 2026/2027</span>
            <div class="flex items-center gap-1.5 text-xs sm:text-[13px] font-semibold text-white mt-0.5">
                <span id="realtime-header-date">Memuat tanggal...</span>
                <span class="text-white/60">•</span>
                <span id="realtime-header-clock" class="tabular-nums tracking-wide">--:--:-- WIB</span>
            </div>
        </div>

        <!-- Kanan: Ikon Notifikasi + Kartu Profil Pengguna -->
        <div class="flex items-center gap-2.5 sm:gap-3 flex-shrink-0">
            @php
                $notifUnreadPayload = ($unreadNotifikasis ?? collect())->map(fn($n) => [
                    'id' => $n->id,
                    'judul' => $n->judul,
                    'pesan' => $n->pesan,
                    'kategori' => $n->kategori,
                    'url' => route('notifikasi.baca', $n->id),
                    'waktu' => $n->created_at ? $n->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : '',
                ])->values();

                $notifReadPayload = ($readNotifikasis ?? collect())->map(fn($n) => [
                    'id' => $n->id,
                    'judul' => $n->judul,
                    'pesan' => $n->pesan,
                    'kategori' => $n->kategori,
                    'url' => route('notifikasi.baca', $n->id),
                    'waktu' => $n->created_at ? $n->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : '',
                ])->values();
            @endphp

            <!-- Lonceng & Dropdown Notifikasi Terintegrasi -->
            <div class="relative" x-data="{
                notifOpen: false,
                unreadCount: {{ (int) ($unreadNotifCount ?? 0) }},
                unreadItems: {{ json_encode($notifUnreadPayload) }},
                readItems: {{ json_encode($notifReadPayload) }},
                isMarking: false,
                kategoriLabel(kat) {
                    const labels = {
                        presensi: 'Presensi',
                        tugas: 'Tugas',
                        pengumuman: 'Pengumuman',
                        ujian: 'Jadwal Ujian',
                        helpdesk: 'Help Desk',
                        sistem: 'Sistem'
                    };
                    return labels[kat] || 'Sistem';
                },
                markAllRead() {
                    if (this.isMarking || this.unreadCount === 0) return;
                    this.isMarking = true;
                    fetch('{{ route('notifikasi.baca-semua') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.readItems = [...this.unreadItems, ...this.readItems].slice(0, 20);
                            this.unreadItems = [];
                            this.unreadCount = 0;
                            if (typeof window.showToast === 'function') {
                                window.showToast('success', 'Seluruh notifikasi telah ditandai sudah dibaca.', 'Notifikasi');
                            }
                        }
                    })
                    .catch(() => {
                        if (typeof window.showToast === 'function') {
                            window.showToast('error', 'Gagal memperbarui status notifikasi.', 'Kesalahan');
                        }
                    })
                    .finally(() => {
                        this.isMarking = false;
                    });
                }
            }" @click.outside="notifOpen = false">
                <button type="button" @click="notifOpen = !notifOpen" aria-label="Buka Panel Notifikasi" class="relative w-10 h-10 rounded-full bg-white/15 hover:bg-white/25 border border-white/25 flex items-center justify-center text-white transition-colors focus:outline-none cursor-pointer">
                    <span class="material-symbols-outlined text-[21px]">notifications</span>
                    <template x-if="unreadCount > 0">
                        <span x-text="unreadCount > 99 ? '99+' : unreadCount" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 flex items-center justify-center bg-red-600 text-white text-[10px] font-bold rounded-full ring-2 ring-[#1E9E63] leading-none"></span>
                    </template>
                </button>

                <!-- Panel Dropdown Notifikasi -->
                <div x-show="notifOpen" x-cloak x-transition class="absolute right-0 mt-2 w-[calc(100vw-2rem)] sm:w-96 bg-white rounded-xl shadow-xl border border-[#A3B18A]/60 z-50 overflow-hidden text-[#1F2937]">
                    <!-- Header Panel -->
                    <div class="px-4 py-3 bg-[#F9FAFB] border-b border-[#E5E7EB] flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="font-heading font-bold text-sm text-[#1F3B2E]">Notifikasi</span>
                            <template x-if="unreadCount > 0">
                                <span x-text="unreadCount + ' Baru'" class="px-2 py-0.5 rounded-full bg-[#C7D5A6]/50 text-[#1F3B2E] text-[10px] font-bold"></span>
                            </template>
                        </div>
                        <button type="button" @click="markAllRead()" :disabled="unreadCount === 0 || isMarking" :class="unreadCount > 0 ? 'text-[#1F3B2E] hover:text-[#3C5A3A] hover:underline cursor-pointer' : 'text-[#9CA3AF] cursor-not-allowed'" class="text-xs font-semibold transition-colors">
                            Tandai semua sudah dibaca
                        </button>
                    </div>

                    <!-- Daftar Notifikasi (Scrollable) -->
                    <div class="max-h-[400px] overflow-y-auto divide-y divide-[#E5E7EB]">
                        <!-- Bagian 1: Belum Dibaca -->
                        <template x-if="unreadItems.length > 0">
                            <div>
                                <div class="px-4 py-2 bg-[#F9FAFB] border-b border-[#E5E7EB]">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#3C5A3A]">Belum Dibaca</span>
                                </div>
                                <div class="divide-y divide-[#E5E7EB]">
                                    <template x-for="item in unreadItems" :key="'unread-' + item.id">
                                        <a :href="item.url" class="block px-4 py-3 bg-[#C7D5A6]/20 hover:bg-[#C7D5A6]/35 transition-colors">
                                            <div class="flex items-start gap-2.5">
                                                <span class="w-2 h-2 rounded-full bg-[#1F3B2E] mt-1.5 flex-shrink-0"></span>
                                                <div class="flex-1 min-w-0 space-y-1">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span x-text="kategoriLabel(item.kategori)" class="px-2 py-0.5 rounded bg-white border border-[#A3B18A] text-[#1F3B2E] text-[10px] font-semibold"></span>
                                                        <span x-text="item.waktu" class="text-[10px] text-[#6B7280]"></span>
                                                    </div>
                                                    <p x-text="item.judul" class="text-xs font-bold text-[#1F2937] leading-snug"></p>
                                                    <p x-text="item.pesan" class="text-[11px] text-[#4B5563] leading-relaxed"></p>
                                                </div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Bagian 2: Sudah Dibaca -->
                        <template x-if="readItems.length > 0">
                            <div>
                                <div class="px-4 py-2 bg-[#F9FAFB] border-b border-[#E5E7EB]">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#6B7280]">Sudah Dibaca</span>
                                </div>
                                <div class="divide-y divide-[#E5E7EB]">
                                    <template x-for="item in readItems" :key="'read-' + item.id">
                                        <a :href="item.url" class="block px-4 py-3 bg-white hover:bg-[#F9FAFB] transition-colors">
                                            <div class="space-y-1">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span x-text="kategoriLabel(item.kategori)" class="px-2 py-0.5 rounded bg-[#F3F4F6] border border-[#E5E7EB] text-[#4B5563] text-[10px] font-medium"></span>
                                                    <span x-text="item.waktu" class="text-[10px] text-[#9CA3AF]"></span>
                                                </div>
                                                <p x-text="item.judul" class="text-xs font-semibold text-[#374151] leading-snug"></p>
                                                <p x-text="item.pesan" class="text-[11px] text-[#6B7280] leading-relaxed"></p>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <template x-if="unreadItems.length === 0 && readItems.length === 0">
                            <div class="py-10 px-6 text-center space-y-1.5 bg-white">
                                <span class="material-symbols-outlined text-[32px] text-[#9CA3AF]">notifications_off</span>
                                <p class="text-xs font-semibold text-[#374151]">Belum Ada Notifikasi</p>
                                <p class="text-[11px] text-[#6B7280]">Pembaruan presensi, tugas, ujian, dan pengumuman akan muncul di sini.</p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Kartu Profil Pengguna (Pill Putih Bersih) -->
            <div class="relative" x-data="{ profileOpen: false }" @click.outside="profileOpen = false">
                <button type="button" @click="profileOpen = !profileOpen" class="bg-white rounded-full py-1 pl-1.5 pr-3.5 flex items-center gap-2.5 shadow-xs hover:bg-[#F9FAFB] transition-colors focus:outline-none cursor-pointer">
                    <div class="w-8 h-8 rounded-full bg-[#E5E7EB] text-[#6B7280] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[18px]">person</span>
                    </div>
                    <div class="text-left hidden sm:block">
                        <p class="font-semibold text-xs text-[#1F2937] uppercase leading-tight">{{ $user->name }}</p>
                        <p class="text-[11px] text-[#6B7280] leading-tight">
                            {{ $user->nis_nip ?? strtoupper($role) }}
                        </p>
                    </div>
                    <span class="material-symbols-outlined text-[16px] text-[#6B7280] hidden sm:inline">expand_more</span>
                </button>

                <!-- Dropdown Menu Profil Ringkas -->
                <div x-show="profileOpen" x-cloak x-transition class="absolute right-0 mt-2 w-60 bg-white rounded-lg border border-[#E5E7EB] shadow-lg py-1.5 z-50 text-xs sm:text-sm">
                    <div class="px-3.5 py-2 border-b border-[#E5E7EB]">
                        <p class="font-semibold text-[#1F2937]">{{ $user->name }}</p>
                        <p class="text-xs text-[#6B7280] mt-0.5">
                            {{ $role === 'siswa' ? 'NIS: ' : ($role === 'guru' ? 'NIP: ' : 'ID Admin: ') }}<span>{{ $user->nis_nip ?? '-' }}</span>
                        </p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="p-1">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-2.5 py-2 rounded-md text-red-600 hover:bg-red-50 font-medium transition-colors cursor-pointer text-xs">
                            <span class="material-symbols-outlined text-[17px]">logout</span>
                            <span>Keluar dari Akun</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- CONTAINER UTAMA: SIDEBAR KIRI + AREA KONTEN -->
    <div class="flex-1 flex relative">

        <!-- Overlay Mobile Sidebar -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 bg-black/30 z-30 lg:hidden"></div>

        <!-- SIDEBAR NAVIGASI KIRI (Latar Putih Bersih #FFFFFF, Border Kanan Tipis #E5E7EB) -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="fixed lg:sticky top-[3.75rem] left-0 h-[calc(100vh-3.75rem)] w-64 bg-white border-r border-[#E5E7EB] flex-shrink-0 flex flex-col justify-between z-30 transition-transform duration-200 ease-out select-none overflow-y-auto">
            <div class="p-3.5 space-y-1.5">
                <!-- Identitas Portal Role Ringkas -->
                <div class="px-3 py-2 mb-2 rounded-lg bg-[#F9FAFB] border border-[#E5E7EB] flex items-center justify-between text-xs">
                    <span class="text-[#6B7280] font-medium">Portal Akademik</span>
                    <span class="px-2 py-0.5 rounded bg-[#E8F5EE] text-[#146C43] font-semibold uppercase tracking-wide text-[11px]">
                        {{ $role === 'siswa' ? 'Siswa' : ($role === 'guru' ? 'Guru' : 'Admin TU') }}
                    </span>
                </div>

                <!-- Menu Navigasi Vertikal (Outline Icons + Active #E8F5EE) -->
                <nav class="space-y-1 text-sm">
                    @if ($role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('admin.dashboard') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">home</span>
                            <span>Beranda</span>
                        </a>
                        <a href="{{ route('admin.kelas.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.kelas.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('admin.kelas.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">meeting_room</span>
                            <span>Data Kelas</span>
                        </a>
                        <a href="{{ route('admin.mapel.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.mapel.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('admin.mapel.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">menu_book</span>
                            <span>Mata Pelajaran</span>
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('admin.users.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">group</span>
                            <span>Data Guru &amp; Siswa</span>
                        </a>
                        <a href="{{ route('admin.jadwal.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.jadwal.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('admin.jadwal.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">calendar_today</span>
                            <span>Jadwal Pelajaran</span>
                        </a>
                        <a href="{{ route('admin.jadwal-ujian.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.jadwal-ujian.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('admin.jadwal-ujian.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">schedule</span>
                            <span>Jadwal Ujian</span>
                        </a>
                        <a href="{{ route('admin.pengumuman.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.pengumuman.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('admin.pengumuman.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">campaign</span>
                            <span>Pengumuman Sekolah</span>
                        </a>

                    @elseif ($role === 'guru')
                        <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('guru.dashboard') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('guru.dashboard') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">home</span>
                            <span>Beranda</span>
                        </a>
                        <a href="{{ route('guru.dashboard') }}#jadwal-mengajar" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937] transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-[#6B7280]">calendar_month</span>
                            <span>Jadwal Mengajar</span>
                        </a>
                        <a href="{{ route('guru.presensi.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('guru.presensi.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('guru.presensi.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">fact_check</span>
                            <span>Input Presensi</span>
                        </a>
                        <a href="{{ route('guru.materi.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('guru.materi.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('guru.materi.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">menu_book</span>
                            <span>Materi Pelajaran</span>
                        </a>
                        <a href="{{ route('guru.tugas.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('guru.tugas.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('guru.tugas.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">assignment</span>
                            <span>Tugas &amp; Evaluasi</span>
                        </a>
                        <a href="{{ route('guru.pengumuman.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('guru.pengumuman.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('guru.pengumuman.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">campaign</span>
                            <span>Pengumuman Sekolah</span>
                        </a>

                    @else
                        <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('siswa.dashboard') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('siswa.dashboard') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">home</span>
                            <span>Beranda</span>
                        </a>
                        <a href="{{ route('siswa.dashboard') }}#jadwal-pelajaran" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937] transition-colors">
                            <span class="material-symbols-outlined text-[20px] text-[#6B7280]">calendar_today</span>
                            <span>Jadwal Pelajaran</span>
                        </a>
                        <a href="{{ route('siswa.jadwal-ujian.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('siswa.jadwal-ujian.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('siswa.jadwal-ujian.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">schedule</span>
                            <span>Jadwal Ujian</span>
                        </a>
                        <a href="{{ route('siswa.presensi.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('siswa.presensi.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('siswa.presensi.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">fact_check</span>
                            <span>Presensi Kelas</span>
                        </a>
                        <a href="{{ route('siswa.materi.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('siswa.materi.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('siswa.materi.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">menu_book</span>
                            <span>Materi Pelajaran</span>
                        </a>
                        <a href="{{ route('siswa.tugas.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('siswa.tugas.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('siswa.tugas.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">assignment</span>
                            <span>Tugas &amp; Evaluasi</span>
                        </a>
                        <a href="{{ route('siswa.pengumuman.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('siswa.pengumuman.*') ? 'bg-[#E8F5EE] text-[#146C43] font-semibold' : 'text-[#4B5563] hover:bg-[#F4F6F8] hover:text-[#1F2937]' }}">
                            <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('siswa.pengumuman.*') ? 'text-[#1E9E63]' : 'text-[#6B7280]' }}">campaign</span>
                            <span>Pengumuman Sekolah</span>
                        </a>
                    @endif
                </nav>
            </div>

            <!-- Footer Sidebar: Info Sekolah & Tombol Keluar -->
            <div class="p-3.5 border-t border-[#E5E7EB] space-y-2.5">
                <div class="px-3 py-2 rounded-lg bg-[#F9FAFB] border border-[#E5E7EB] text-xs text-[#6B7280]">
                    <p class="font-semibold text-[#1F2937]">SMA NEGERI 1 GUNUNGSITOLI</p>
                    <p class="text-[11px] text-[#6B7280] mt-0.5">NPSN: 10258462 • T.A. 2026/2027</p>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-white hover:bg-red-50 text-[#4B5563] hover:text-red-600 transition-colors text-xs font-medium border border-[#E5E7EB] hover:border-red-200 cursor-pointer">
                        <span class="material-symbols-outlined text-[18px]">logout</span>
                        <span>Keluar dari Akun</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- AREA KONTEN UTAMA (#F4F6F8) -->
        <div class="flex-1 flex flex-col min-w-0 bg-[#F4F6F8]">
            <!-- Breadcrumb Ringkas di Atas Konten -->
            @if (isset($breadcrumbs) && is_array($breadcrumbs))
                <div class="px-4 sm:px-6 pt-5 pb-1 max-w-7xl w-full mx-auto">
                    <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-1.5 text-xs sm:text-sm text-[#6B7280]">
                        @foreach ($breadcrumbs as $crumb)
                            @if (!$loop->first)
                                <span class="text-[#9CA3AF]">&gt;</span>
                            @endif
                            @if (!empty($crumb['url']) && !$loop->last)
                                <a href="{{ $crumb['url'] }}" class="font-medium text-[#6B7280] hover:text-[#1F2937] transition-colors">{{ $crumb['label'] }}</a>
                            @else
                                <span class="{{ $loop->last ? 'text-[#6B7280] font-normal' : 'font-medium text-[#6B7280]' }}">{{ $crumb['label'] }}</span>
                            @endif
                        @endforeach
                    </nav>
                </div>
            @elseif (isset($pageTitle))
                <div class="px-4 sm:px-6 pt-5 pb-1 max-w-7xl w-full mx-auto">
                    <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-[#6B7280]">
                        <span class="font-medium text-[#6B7280]">{{ $pageTitle }}</span>
                        @if (isset($pageSubtitle))
                            <span class="text-[#D1D5DB]">/</span>
                            <span class="text-[#9CA3AF]">{{ $pageSubtitle }}</span>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Main Body Content -->
            <main class="flex-1 px-4 sm:px-6 py-4 max-w-7xl w-full mx-auto">
                @yield('content')
            </main>

            <!-- Footer Aplikasi Formal -->
            <footer class="py-4 px-6 border-t border-[#E5E7EB] text-center text-xs text-[#6B7280] bg-white">
                <p>
                    Sistem Informasi Akademik • <strong class="text-[#1F2937]">SMA NEGERI 1 GUNUNGSITOLI</strong> (Jl. Pendidikan No. 3, Gunungsitoli) • Tahun Ajaran 2026/2027
                </p>
            </footer>
        </div>
    </div>

    <!-- ====================================================================== -->
    <!-- 1. GLOBAL TOAST NOTIFICATION CARD (Pojok Kanan Atas, Auto-Close 4s)    -->
    <!-- ====================================================================== -->
    <div
        x-data="{
            toasts: [],
            add(detail) {
                const id = Date.now() + Math.random();
                const type = detail.type || 'success';
                const defaultTitles = {
                    success: 'Perubahan Berhasil Disimpan',
                    warning: 'Informasi Sistem',
                    info: 'Informasi Sistem',
                    error: 'Terjadi Kesalahan'
                };
                const toast = {
                    id,
                    type,
                    title: detail.title || defaultTitles[type] || 'Notifikasi',
                    message: detail.message || '',
                    progress: 100
                };
                this.toasts.push(toast);
                this.$nextTick(() => {
                    setTimeout(() => {
                        const target = this.toasts.find(t => t.id === id);
                        if (target) target.progress = 0;
                    }, 30);
                });
                setTimeout(() => this.remove(id), 4050);
            },
            remove(id) {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }
        }"
        @toast.window="add($event.detail)"
        x-init="
            window.showToast = (type, message, title = null) => {
                window.dispatchEvent(new CustomEvent('toast', { detail: { type, message, title } }));
            };
            @if (session('success'))
                add({ type: 'success', message: @js(session('success')) });
            @endif
            @if (session('status'))
                add({ type: 'success', message: @js(session('status')) });
            @endif
            @if (session('warning'))
                add({ type: 'warning', message: @js(session('warning')) });
            @endif
            @if (session('info'))
                add({ type: 'info', title: 'Informasi', message: @js(session('info')) });
            @endif
            @if (session('error'))
                add({ type: 'error', message: @js(session('error')) });
            @endif
            @if ($errors->any())
                add({ type: 'error', title: 'Validasi Gagal', message: @js($errors->first()) });
            @endif
        "
        class="fixed top-16 right-4 z-[80] w-[calc(100%-2rem)] max-w-sm space-y-2.5 pointer-events-none"
        aria-live="polite"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-transition:enter="transition ease-out duration-250"
                x-transition:enter-start="opacity-0 translate-x-8"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 translate-x-8"
                class="pointer-events-auto bg-white rounded-xl shadow-lg border border-[#E5E7EB] overflow-hidden"
                :class="{
                    'border-l-4 border-l-[#1E9E63]': toast.type === 'success',
                    'border-l-4 border-l-[#D97706]': toast.type === 'warning' || toast.type === 'info',
                    'border-l-4 border-l-[#DC2626]': toast.type === 'error'
                }"
            >
                <div class="p-3.5 flex items-start gap-3">
                    <div
                        class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                        :class="{
                            'bg-[#E8F5EE] text-[#1E9E63]': toast.type === 'success',
                            'bg-[#FFF7ED] text-[#D97706]': toast.type === 'warning' || toast.type === 'info',
                            'bg-red-50 text-[#DC2626]': toast.type === 'error'
                        }"
                    >
                        <span class="material-symbols-outlined text-[19px]" x-text="toast.type === 'success' ? 'check_circle' : (toast.type === 'error' ? 'error' : 'info')"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-heading font-bold text-xs sm:text-[13px] text-[#1F2937] leading-snug" x-text="toast.title"></p>
                        <p class="text-xs text-[#6B7280] mt-0.5 leading-relaxed break-words" x-text="toast.message"></p>
                    </div>
                    <button type="button" @click="remove(toast.id)" class="text-[#9CA3AF] hover:text-[#1F2937] p-0.5 rounded transition-colors cursor-pointer flex-shrink-0" aria-label="Tutup notifikasi">
                        <span class="material-symbols-outlined text-[17px]">close</span>
                    </button>
                </div>
                <div class="w-full h-1 bg-[#F3F4F6]">
                    <div
                        class="h-full transition-all duration-[4000ms] ease-linear"
                        :style="'width: ' + toast.progress + '%'"
                        :class="{
                            'bg-[#1E9E63]': toast.type === 'success',
                            'bg-[#D97706]': toast.type === 'warning' || toast.type === 'info',
                            'bg-[#DC2626]': toast.type === 'error'
                        }"
                    ></div>
                </div>
            </div>
        </template>
    </div>

    <!-- ====================================================================== -->
    <!-- 2. GLOBAL CUSTOM CONFIRMATION MODAL CARD (Pengganti Browser confirm()) -->
    <!-- ====================================================================== -->
    <div
        x-data="{
            open: false,
            title: 'Konfirmasi Tindakan',
            message: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
            confirmText: 'Ya, Hapus Data',
            cancelText: 'Batal',
            variant: 'danger',
            targetForm: null,
            onConfirmCallback: null,
            show(detail) {
                this.title = detail.title || 'Konfirmasi Hapus Data';
                this.message = detail.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
                this.confirmText = detail.confirmText || 'Ya, Hapus Data';
                this.cancelText = detail.cancelText || 'Batal';
                this.variant = detail.variant || 'danger';
                this.targetForm = detail.form || null;
                this.onConfirmCallback = detail.onConfirm || null;
                this.open = true;
            },
            confirm() {
                this.open = false;
                if (this.targetForm) {
                    this.targetForm.submit();
                } else if (typeof this.onConfirmCallback === 'function') {
                    this.onConfirmCallback();
                }
                this.targetForm = null;
                this.onConfirmCallback = null;
            },
            close() {
                this.open = false;
                this.targetForm = null;
                this.onConfirmCallback = null;
            }
        }"
        @open-confirm.window="show($event.detail)"
        @keydown.escape.window="if (open) close()"
        x-init="
            window.openConfirmModal = (detail) => {
                window.dispatchEvent(new CustomEvent('open-confirm', { detail }));
            };
            window.handleConfirmForm = (event, formEl, title, message, confirmText = 'Ya, Hapus Data', variant = 'danger') => {
                event.preventDefault();
                window.openConfirmModal({ title, message, confirmText, variant, form: formEl });
                return false;
            };
        "
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[90] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
        role="dialog"
        aria-modal="true"
    >
        <div
            @click.away="close()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="bg-white w-full max-w-md rounded-xl border border-[#E5E7EB] shadow-xl overflow-hidden"
        >
            <div class="p-6 space-y-4">
                <div class="flex items-start gap-3.5">
                    <div
                        class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                        :class="variant === 'danger' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-[#FFF7ED] text-[#D97706] border border-[#FED7AA]'"
                    >
                        <span class="material-symbols-outlined text-[22px]" x-text="variant === 'danger' ? 'warning' : 'help'"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-heading font-bold text-base text-[#1F2937]" x-text="title"></h3>
                        <p class="text-xs sm:text-[13px] text-[#6B7280] mt-1 leading-relaxed break-words" x-text="message"></p>
                    </div>
                </div>
            </div>
            <div class="px-6 py-3.5 bg-[#F9FAFB] border-t border-[#E5E7EB] flex items-center justify-end gap-2.5">
                <button type="button" @click="close()" class="px-4 py-2 rounded-lg bg-white border border-[#D1D5DB] hover:bg-[#F3F4F6] text-[#374151] text-xs font-semibold transition-colors cursor-pointer" x-text="cancelText"></button>
                <button
                    type="button"
                    @click="confirm()"
                    class="px-4 py-2 rounded-lg text-white text-xs font-semibold transition-colors cursor-pointer"
                    :class="variant === 'danger' ? 'bg-red-600 hover:bg-red-700' : 'bg-[#1E9E63] hover:bg-[#168251]'"
                    x-text="confirmText"
                ></button>
            </div>
        </div>
    </div>

    <!-- Skrip Tanggal & Jam Digital Real-Time SMAN 1 Gunungsitoli (WIB, Tabular-Nums, Visibility-Change Sync) -->
    <script>
        (function() {
            const dateEl = document.getElementById('realtime-header-date');
            const clockEl = document.getElementById('realtime-header-clock');
            if (!dateEl || !clockEl) return;

            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];

            function tickClock() {
                const now = new Date();
                const dayName = days[now.getDay()];
                const date = now.getDate();
                const monthName = months[now.getMonth()];
                const year = now.getFullYear();

                dateEl.textContent = `${dayName}, ${date} ${monthName} ${year}`;

                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                clockEl.textContent = `${hours}:${minutes}:${seconds} WIB`;
            }

            tickClock();
            let clockInterval = setInterval(tickClock, 1000);

            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    tickClock();
                    clearInterval(clockInterval);
                    clockInterval = setInterval(tickClock, 1000);
                }
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
