<!DOCTYPE html>
<html lang="id" class="text-[17px]">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>SIAKAD &amp; LMS - SMA NEGERI 1 GUNUNGSITOLI</title>
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'Roboto', 'Arial', 'sans-serif'],
                        heading: ['Poppins', 'Roboto', 'Arial', 'sans-serif'],
                        mono: ['Poppins', 'Roboto', 'Arial', 'sans-serif']
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
    </style>
</head>
<body class="min-h-screen bg-[#F4F6F8] text-[#1F2937] font-sans flex flex-col justify-between antialiased">

    <!-- Top Institutional Bar (Hijau Zamrud #1E9E63) -->
    <header class="w-full h-14 bg-[#1E9E63] text-white px-4 sm:px-8 flex items-center justify-between border-b border-[#168251] shadow-xs">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-full bg-white/15 border border-white/25 flex items-center justify-center text-white flex-shrink-0">
                <span class="material-symbols-outlined text-[20px]">school</span>
            </div>
            <div>
                <p class="font-heading text-sm font-bold tracking-tight text-white leading-none">SIAKAD &amp; LMS</p>
                <span class="text-[10px] font-medium text-white/90 uppercase tracking-wider">SMA NEGERI 1 GUNUNGSITOLI</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="showResetPasswordModal()" class="inline-flex items-center gap-1.5 text-xs font-medium text-white hover:bg-white/15 transition-colors px-3 py-1.5 rounded-md border border-white/30 cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">lock_reset</span>
                <span>Reset Password</span>
            </button>
            <button type="button" onclick="showHelpModal()" class="inline-flex items-center gap-1.5 text-xs font-medium text-[#1F2937] bg-white hover:bg-[#F9FAFB] transition-colors px-3 py-1.5 rounded-md cursor-pointer">
                <span class="material-symbols-outlined text-[16px] text-[#1E9E63]">help_center</span>
                <span class="hidden sm:inline">Pusat Bantuan</span>
            </button>
        </div>
    </header>

    <!-- Main Centered Authentication Container -->
    <main class="flex-1 flex items-center justify-center px-4 py-8 sm:px-6">
        <div class="w-full max-w-[460px] bg-white rounded-lg border border-[#E5E7EB] p-7 sm:p-9 shadow-xs">
            <!-- Emblem & Title Header -->
            <div class="flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] text-[#1E9E63] flex items-center justify-center mb-3">
                    <span class="material-symbols-outlined text-[28px]">school</span>
                </div>
                <h1 class="text-xl font-bold font-heading tracking-tight text-[#1F2937]">Masuk ke Akun Anda</h1>
                <p class="text-xs text-[#6B7280] mt-1">Portal Sistem Informasi Akademik SMA NEGERI 1 GUNUNGSITOLI</p>
            </div>

            <!-- Segmented Role Switcher -->
            <div class="mt-6">
                <div class="flex h-10 items-center justify-between rounded-lg bg-[#F4F6F8] p-1 border border-[#E5E7EB]">
                    <label class="flex-1 h-full cursor-pointer">
                        <input class="peer sr-only" name="role_radio" type="radio" value="siswa" onchange="onRoleChanged('siswa')" {{ old('role', 'siswa') === 'siswa' ? 'checked' : '' }}>
                        <span class="flex h-full w-full items-center justify-center rounded-md text-xs font-medium text-[#6B7280] transition-all peer-checked:bg-[#1E9E63] peer-checked:text-white peer-checked:font-semibold">
                            Siswa
                        </span>
                    </label>
                    <label class="flex-1 h-full cursor-pointer">
                        <input class="peer sr-only" name="role_radio" type="radio" value="guru" onchange="onRoleChanged('guru')" {{ old('role') === 'guru' ? 'checked' : '' }}>
                        <span class="flex h-full w-full items-center justify-center rounded-md text-xs font-medium text-[#6B7280] transition-all peer-checked:bg-[#1E9E63] peer-checked:text-white peer-checked:font-semibold">
                            Guru / Pendidik
                        </span>
                    </label>
                    <label class="flex-1 h-full cursor-pointer">
                        <input class="peer sr-only" name="role_radio" type="radio" value="admin" onchange="onRoleChanged('admin')" {{ old('role') === 'admin' ? 'checked' : '' }}>
                        <span class="flex h-full w-full items-center justify-center rounded-md text-xs font-medium text-[#6B7280] transition-all peer-checked:bg-[#1E9E63] peer-checked:text-white peer-checked:font-semibold">
                            Staf / Admin
                        </span>
                    </label>
                </div>
            </div>

            <!-- Error and Status Notifications -->
            @if ($errors->any())
                <div class="mt-4 p-3.5 rounded-md bg-red-50 text-red-800 flex items-start gap-2.5 border border-red-200 text-xs leading-relaxed">
                    <span class="material-symbols-outlined text-[18px] text-red-600 flex-shrink-0 mt-0.5">error</span>
                    <div class="space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (session('status'))
                <div class="mt-4 p-3.5 rounded-md bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0] flex items-center gap-2.5 text-xs font-medium">
                    <span class="material-symbols-outlined text-[18px] text-[#1E9E63] flex-shrink-0">check_circle</span>
                    <p>{{ session('status') }}</p>
                </div>
            @endif

            <!-- Database-Integrated Login Form -->
            <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-4" id="login-form" onsubmit="handleFormSubmit()">
                @csrf
                <input type="hidden" name="role" id="form-role-input" value="{{ old('role', 'siswa') }}">

                <!-- Field 1: Identifier -->
                <div>
                    <label class="block text-xs font-semibold text-[#374151] mb-1.5" for="identifier-input" id="identifier-label">
                        NISN / NIS / Email Siswa
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 text-[#9CA3AF] material-symbols-outlined text-[19px] pointer-events-none">badge</span>
                        <input class="w-full h-10 pl-10 pr-4 text-sm bg-white border border-[#D1D5DB] rounded-lg text-[#1F2937] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#1E9E63]/15 focus:border-[#1E9E63] transition-all"
                               id="identifier-input"
                               name="login"
                               value="{{ old('login', old('email')) }}"
                               placeholder="Contoh: 260001 atau siswa001@sman1gst.sch.id"
                               required
                               autofocus
                               type="text">
                    </div>
                </div>

                <!-- Field 2: Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-[#374151]" for="password-input">
                            Kata Sandi Akun
                        </label>
                        <button type="button" onclick="showResetPasswordModal()" class="text-[11px] font-semibold text-[#1E9E63] hover:underline cursor-pointer flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-[14px]">lock_reset</span>
                            <span>Lupa Kata Sandi?</span>
                        </button>
                    </div>
                    <div class="relative flex items-center">
                        <span class="absolute left-3.5 text-[#9CA3AF] material-symbols-outlined text-[19px] pointer-events-none">lock</span>
                        <input class="w-full h-10 pl-10 pr-10 text-sm bg-white border border-[#D1D5DB] rounded-lg text-[#1F2937] placeholder:text-[#9CA3AF] focus:outline-none focus:ring-2 focus:ring-[#1E9E63]/15 focus:border-[#1E9E63] transition-all"
                               id="password-input"
                               name="password"
                               placeholder="••••••••••••"
                               required
                               autocomplete="current-password"
                               type="password">
                        <button aria-label="Lihat kata sandi" class="absolute right-3 text-[#9CA3AF] hover:text-[#4B5563] transition-colors p-1 cursor-pointer" id="toggle-password-btn" type="button">
                            <span class="material-symbols-outlined text-[19px]" id="toggle-password-icon">visibility</span>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between pt-0.5">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input class="size-4 rounded border-[#D1D5DB] text-[#1E9E63] focus:ring-[#1E9E63]/20 focus:ring-offset-0 cursor-pointer"
                               name="remember"
                               value="1"
                               {{ old('remember', true) ? 'checked' : '' }}
                               type="checkbox">
                        <span class="text-xs text-[#6B7280] font-medium group-hover:text-[#1F2937] transition-colors">
                            Ingat sesi saya di perangkat ini
                        </span>
                    </label>
                    <span class="text-[11px] text-[#6B7280] flex items-center gap-1 font-medium">
                        <span class="material-symbols-outlined text-[13px] text-[#1E9E63]">verified</span>
                        <span>SMAN 1 Gunungsitoli</span>
                    </span>
                </div>

                <!-- Primary Action Button -->
                <button class="w-full h-10 rounded-lg bg-[#1E9E63] hover:bg-[#168251] text-white text-sm font-semibold transition-colors flex items-center justify-center gap-2 mt-2 cursor-pointer" id="btn-submit" type="submit">
                    <span id="btn-submit-text">Masuk ke Portal SIAKAD</span>
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>

                <!-- Divider -->
                <div class="relative py-2 flex items-center justify-center">
                    <div class="w-full border-t border-[#E5E7EB]"></div>
                    <span class="absolute bg-white px-3 text-[11px] font-medium text-[#6B7280] uppercase tracking-wider">
                        atau
                    </span>
                </div>

                <!-- Single Sign-On Button -->
                <button class="w-full h-10 rounded-lg bg-white hover:bg-[#F9FAFB] border border-[#D1D5DB] text-[#374151] text-xs font-medium transition-colors flex items-center justify-center gap-2.5 cursor-pointer" onclick="showSsoNotice()" type="button">
                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"></path>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"></path>
                    </svg>
                    <span class="break-words">Masuk dengan Akun SMAN 1 Gunungsitoli (@belajar.id)</span>
                </button>
            </form>

            <!-- Quick Account Testing Helper -->
            <div class="mt-5 pt-3 border-t border-[#E5E7EB]">
                <div class="flex items-center justify-between text-xs">
                    <button type="button" onclick="toggleCredentialsHelper()" class="text-[#1E9E63] hover:underline font-semibold flex items-center gap-1 cursor-pointer">
                        <span class="material-symbols-outlined text-[15px]">badge</span>
                        <span>Akun Uji Coba SMAN 1 Gunungsitoli</span>
                        <span class="material-symbols-outlined text-[15px]" id="creds-chevron">expand_more</span>
                    </button>
                    <span class="text-[11px] text-[#6B7280] font-medium">Password: <strong class="text-[#1F2937]">password123</strong></span>
                </div>
                <div id="credentials-helper-box" class="hidden mt-2 p-2.5 rounded-md bg-[#F9FAFB] text-xs space-y-1.5 border border-[#E5E7EB]">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-1.5 text-[11px]">
                        <button type="button" onclick="fillTestAccount('admin')" class="p-2 bg-white rounded border border-[#E5E7EB] text-left hover:border-[#1E9E63] transition-colors cursor-pointer">
                            <span class="font-semibold text-[#1F2937] block">Admin</span>
                            <span class="text-[#6B7280] break-all block">admin@sman1gst.sch.id</span>
                        </button>
                        <button type="button" onclick="fillTestAccount('guru')" class="p-2 bg-white rounded border border-[#E5E7EB] text-left hover:border-[#1E9E63] transition-colors cursor-pointer">
                            <span class="font-semibold text-[#1F2937] block">Guru (Rosmina)</span>
                            <span class="text-[#6B7280] break-all block">197103151998012001</span>
                        </button>
                        <button type="button" onclick="fillTestAccount('siswa')" class="p-2 bg-white rounded border border-[#E5E7EB] text-left hover:border-[#1E9E63] transition-colors cursor-pointer">
                            <span class="font-semibold text-[#1F2937] block">Siswa (Alvaro)</span>
                            <span class="text-[#6B7280] break-all block">260001</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Footnote / IT Help Link -->
            <div class="mt-4 pt-4 border-t border-[#E5E7EB] text-center">
                <p class="text-xs text-[#6B7280] font-medium">
                    Butuh bantuan masuk akun?
                    <button type="button" onclick="showHelpModal()" class="text-[#1E9E63] font-semibold hover:underline inline-flex items-center gap-0.5 cursor-pointer">
                        Layanan IT &amp; Tata Usaha SMA NEGERI 1 GUNUNGSITOLI
                    </button>
                </p>
            </div>
        </div>
    </main>

    <!-- Page Bottom Chrome / Footer -->
    <footer class="w-full py-4 px-6 text-center bg-white border-t border-[#E5E7EB]">
        <div class="inline-flex flex-wrap items-center justify-center gap-2 text-[11px] text-[#6B7280] font-medium">
            <span class="material-symbols-outlined text-[14px] text-[#1E9E63]">lock</span>
            <span>Pusat Data Akademik Terenkripsi (TLS 1.3)</span>
            <span class="text-[#D1D5DB]">•</span>
            <span class="font-semibold text-[#1F2937]">SMA NEGERI 1 GUNUNGSITOLI</span>
            <span class="text-[#D1D5DB]">•</span>
            <span>Hak Cipta &copy; 2026/2027 SIAKAD &amp; LMS</span>
        </div>
    </footer>

    <!-- Dedicated Modal Reset Password -->
    <div id="reset-password-modal" class="fixed inset-0 z-50 bg-slate-900/50 hidden items-center justify-center p-4">
        <div class="bg-white max-w-md w-full rounded-lg shadow-xl p-6 space-y-4 border border-[#E5E7EB]">
            <div class="flex items-center justify-between pb-3 border-b border-[#E5E7EB]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-md bg-[#E8F5EE] border border-[#BBF7D0] text-[#1E9E63] flex items-center justify-center">
                        <span class="material-symbols-outlined text-[19px]">lock_reset</span>
                    </div>
                    <div>
                        <h3 class="font-heading text-sm font-semibold text-[#1F2937]">Informasi Reset Password</h3>
                        <p class="text-[10px] font-semibold text-[#6B7280] tracking-wider uppercase">SMA NEGERI 1 GUNUNGSITOLI</p>
                    </div>
                </div>
                <button type="button" onclick="hideResetPasswordModal()" class="text-[#6B7280] hover:text-[#1F2937] p-1 rounded-md cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <div class="p-4 rounded-md bg-[#FFF7ED] border border-[#FED7AA] text-center space-y-2">
                <span class="material-symbols-outlined text-[#D97706] text-[30px]">info</span>
                <p class="font-bold text-sm text-[#9A3412] leading-snug uppercase">
                    SILAHKAN HUBUNGI ADMIN UNTUK MELAKUKAN RESET PASSWORD
                </p>
                <p class="text-xs text-[#9A3412]/90">
                    Demi keamanan data akademik dan privasi siswa/guru, reset kata sandi dilakukan melalui verifikasi resmi oleh Administrator Sekolah.
                </p>
            </div>

            <div class="space-y-2 text-xs text-[#4B5563] bg-[#F9FAFB] p-3.5 rounded-md border border-[#E5E7EB]">
                <p class="font-semibold text-[#1F2937]">Kontak Layanan Administrator SMA NEGERI 1 GUNUNGSITOLI:</p>
                <div class="space-y-1 text-[11px]">
                    <p class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[15px] text-[#1E9E63]">location_on</span>
                        <span>Ruang Tata Usaha: Jl. Pendidikan No. 3, Gunungsitoli</span>
                    </p>
                    <p class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[15px] text-[#1E9E63]">mail</span>
                        <span>Email Admin: <strong>admin@sman1gst.sch.id</strong></span>
                    </p>
                    <p class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[15px] text-[#1E9E63]">call</span>
                        <span>Helpdesk TU: (0639) 21015 / Hubungi Wali Kelas</span>
                    </p>
                </div>
            </div>

            <div class="flex justify-end pt-1">
                <button type="button" onclick="hideResetPasswordModal()" class="px-5 py-2 bg-[#1E9E63] hover:bg-[#168251] text-white rounded-md text-xs font-semibold transition-colors cursor-pointer">
                    Mengerti &amp; Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- General Help Modal -->
    <div id="help-modal" class="fixed inset-0 z-50 bg-slate-900/50 hidden items-center justify-center p-4">
        <div class="bg-white max-w-md w-full rounded-lg shadow-xl p-6 space-y-4 border border-[#E5E7EB]">
            <div class="flex items-center justify-between pb-3 border-b border-[#E5E7EB]">
                <div class="flex items-center gap-2 text-[#1E9E63]">
                    <span class="material-symbols-outlined text-[21px]">help_center</span>
                    <div>
                        <h3 class="font-heading text-sm font-semibold text-[#1F2937]">Pusat Bantuan Akun</h3>
                        <p class="text-[10px] font-semibold text-[#6B7280] uppercase">SMA NEGERI 1 GUNUNGSITOLI</p>
                    </div>
                </div>
                <button type="button" onclick="hideHelpModal()" class="text-[#6B7280] hover:text-[#1F2937] p-1 rounded-md cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <div class="space-y-2.5 text-xs text-[#4B5563] leading-relaxed">
                <div class="p-3 bg-[#F9FAFB] border border-[#E5E7EB] rounded-md">
                    <p class="font-semibold text-[#1F2937]">1. Akun Siswa SMA NEGERI 1 GUNUNGSITOLI:</p>
                    <p>Gunakan nomor NIS (contoh: <code>260001</code>) atau email siswa resmi (<code>siswa001@sman1gst.sch.id</code>).</p>
                </div>
                <div class="p-3 bg-[#F9FAFB] border border-[#E5E7EB] rounded-md">
                    <p class="font-semibold text-[#1F2937]">2. Akun Guru SMA NEGERI 1 GUNUNGSITOLI:</p>
                    <p>Gunakan NIP resmi (contoh: <code>197103151998012001</code>) atau email guru (<code>rosmina.zebua@sman1gst.sch.id</code>).</p>
                </div>
                <div class="p-3 bg-[#F9FAFB] border border-[#E5E7EB] rounded-md">
                    <p class="font-semibold text-[#1F2937]">3. Tidak Ada Registrasi Publik:</p>
                    <p>Pembuatan akun guru dan siswa dilakukan langsung oleh Administrator SMA Negeri 1 Gunungsitoli.</p>
                </div>
            </div>
            <div class="flex justify-end pt-1">
                <button type="button" onclick="hideHelpModal()" class="px-5 py-2 bg-[#1E9E63] hover:bg-[#168251] text-white rounded-md text-xs font-semibold transition-colors cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notice SSO Login -->
    <div id="sso-toast" class="fixed top-16 right-4 z-50 max-w-sm w-[calc(100%-2rem)] hidden bg-white rounded-xl shadow-lg border border-[#E5E7EB] border-l-4 border-l-[#D97706] overflow-hidden">
        <div class="p-3.5 flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-[#FFF7ED] text-[#D97706] flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-[19px]">info</span>
            </div>
            <div class="flex-1">
                <p class="font-heading font-bold text-xs text-[#1F2937]">Informasi Single Sign-On (SSO)</p>
                <p class="text-xs text-[#6B7280] mt-0.5 leading-relaxed">Layanan SSO Akun @belajar.id untuk civitas SMA NEGERI 1 GUNUNGSITOLI tersinkronisasi otomatis dengan Dapodik.</p>
            </div>
            <button type="button" onclick="hideSsoNotice()" class="text-[#9CA3AF] hover:text-[#1F2937] p-0.5 cursor-pointer">
                <span class="material-symbols-outlined text-[17px]">close</span>
            </button>
        </div>
    </div>

    <!-- Interactive JavaScript Logic -->
    <script>
        let ssoTimer = null;
        function showSsoNotice() {
            const toast = document.getElementById('sso-toast');
            toast.classList.remove('hidden');
            clearTimeout(ssoTimer);
            ssoTimer = setTimeout(hideSsoNotice, 4000);
        }
        function hideSsoNotice() {
            document.getElementById('sso-toast').classList.add('hidden');
        }
        const toggleBtn = document.getElementById('toggle-password-btn');
        const passwordInput = document.getElementById('password-input');
        const toggleIcon = document.getElementById('toggle-password-icon');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleIcon.textContent = isPassword ? 'visibility_off' : 'visibility';
            });
        }

        function onRoleChanged(role) {
            const hiddenRoleInput = document.getElementById('form-role-input');
            const labelEl = document.getElementById('identifier-label');
            const inputEl = document.getElementById('identifier-input');
            const btnSubmitText = document.getElementById('btn-submit-text');

            hiddenRoleInput.value = role;

            if (role === 'siswa') {
                labelEl.innerText = 'NISN / NIS / Email Siswa SMA NEGERI 1 GUNUNGSITOLI';
                inputEl.placeholder = 'Contoh: 260001 atau siswa001@sman1gst.sch.id';
                btnSubmitText.innerText = 'Masuk ke Portal Siswa';
            } else if (role === 'guru') {
                labelEl.innerText = 'NIP / NUPTK / Email Guru SMA NEGERI 1 GUNUNGSITOLI';
                inputEl.placeholder = 'Contoh: 197103151998012001 atau rosmina.zebua@sman1gst.sch.id';
                btnSubmitText.innerText = 'Masuk ke Ruang Guru';
            } else if (role === 'admin') {
                labelEl.innerText = 'Email Admin / NIP Pegawai TU SMA NEGERI 1 GUNUNGSITOLI';
                inputEl.placeholder = 'Contoh: admin@sman1gst.sch.id atau 198001012005011001';
                btnSubmitText.innerText = 'Masuk ke Konsol Admin';
            }
        }

        function handleFormSubmit() {
            const btn = document.getElementById('btn-submit');
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span><span>Memverifikasi Database...</span>';
        }

        function showResetPasswordModal() {
            const modal = document.getElementById('reset-password-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function hideResetPasswordModal() {
            const modal = document.getElementById('reset-password-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function showHelpModal() {
            const modal = document.getElementById('help-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function hideHelpModal() {
            const modal = document.getElementById('help-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function toggleCredentialsHelper() {
            const box = document.getElementById('credentials-helper-box');
            const chevron = document.getElementById('creds-chevron');
            if (box.classList.contains('hidden')) {
                box.classList.remove('hidden');
                chevron.innerText = 'expand_less';
            } else {
                box.classList.add('hidden');
                chevron.innerText = 'expand_more';
            }
        }

        function fillTestAccount(role) {
            const radio = document.querySelector(`input[name="role_radio"][value="${role}"]`);
            if (radio) {
                radio.checked = true;
                onRoleChanged(role);
            }
            const loginInput = document.getElementById('identifier-input');
            const passInput = document.getElementById('password-input');

            if (role === 'admin') {
                loginInput.value = 'admin@sman1gst.sch.id';
            } else if (role === 'guru') {
                loginInput.value = '197103151998012001';
            } else if (role === 'siswa') {
                loginInput.value = '260001';
            }
            passInput.value = 'password123';
            loginInput.focus();
        }

        document.addEventListener('DOMContentLoaded', () => {
            const currentRole = document.getElementById('form-role-input').value || 'siswa';
            onRoleChanged(currentRole);
        });
    </script>
</body>
</html>
