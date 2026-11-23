@extends('layouts.siakad')

@php
    $pageTitle = 'Rekap & Monitoring Presensi';
    $pageSubtitle = $sesi->jadwal->mataPelajaran->nama_mapel . ' • ' . $sesi->jadwal->kelas->nama_kelas;
@endphp

@section('content')
<div class="space-y-5">

    <!-- Flash Status Notification -->
    @if (session('status'))
        <div class="p-3.5 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] text-[#146C43] text-xs font-medium flex items-center gap-2.5">
            <span class="material-symbols-outlined text-[#1E9E63] text-[19px]">check_circle</span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <!-- Navigasi & Status Bar Pertemuan -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-5 rounded-lg bg-white border border-[#E5E7EB]">
        <div class="flex items-start gap-3.5">
            <a href="{{ route('guru.presensi.index') }}" class="p-2 rounded-md bg-[#F9FAFB] border border-[#E5E7EB] hover:bg-[#F3F4F6] text-[#4B5563] transition-colors mt-0.5">
                <span class="material-symbols-outlined text-[19px]">arrow_back</span>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-[#F9FAFB] border border-[#D1D5DB] text-[#1F2937]">
                        {{ $sesi->jadwal->kelas->nama_kelas }}
                    </span>
                    <span class="text-xs text-[#9CA3AF]">•</span>
                    <span class="text-xs font-medium text-[#4B5563]">Pertemuan Ke-{{ $sesi->pertemuan_ke }}</span>
                    <span class="text-xs text-[#9CA3AF]">•</span>
                    @if ($sesi->status_sesi === 'dibuka')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#1E9E63]"></span>
                            <span>Sedang Dibuka</span>
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-[#F3F4F6] text-[#6B7280]">
                            Sesi Ditutup
                        </span>
                    @endif
                </div>
                <h2 class="text-lg font-heading font-bold text-[#1F2937] mt-1">
                    {{ $sesi->jadwal->mataPelajaran->nama_mapel }}
                </h2>
                <p class="text-xs text-[#6B7280] mt-0.5">
                    Materi: <strong class="text-[#1F2937]">{{ $sesi->materi }}</strong> • Tanggal: {{ \Carbon\Carbon::parse($sesi->tanggal)->translatedFormat('l, d F Y') }} ({{ substr($sesi->jam_mulai, 0, 5) }} - {{ substr($sesi->jam_selesai, 0, 5) }} WIB)
                </p>
            </div>
        </div>

        <!-- Tombol Aksi Tutup / Status -->
        <div class="flex items-center gap-3">
            @if ($sesi->status_sesi === 'dibuka')
                <form method="POST" action="{{ route('guru.presensi.tutup', $sesi->id) }}" onsubmit="return handleConfirmForm(event, this, 'Konfirmasi Tutup Sesi', 'Apakah Anda yakin ingin menutup sesi presensi ini? Siswa tidak akan dapat melakukan presensi mandiri lagi.', 'Ya, Tutup Sesi', 'warning');">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-md bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-[17px]">stop_circle</span>
                        <span>Tutup Sesi Presensi</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Ringkasan Kehadiran Kelas (4 Metrik) -->
    @php
        $presensis = $sesi->presensis;
        $totalSiswa = $presensis->count();
        $totalHadir = $presensis->where('status', 'Hadir')->count();
        $totalSakit = $presensis->where('status', 'Sakit')->count();
        $totalIzin = $presensis->where('status', 'Izin')->count();
        $totalAlpha = $presensis->whereIn('status', ['Alpha', 'Belum Absen'])->count();
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg p-4 border border-[#E5E7EB] flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Hadir</p>
                <p id="stat-hadir" class="text-2xl font-heading font-bold text-[#146C43] mt-0.5">{{ $totalHadir }}</p>
                <p class="text-[11px] text-[#6B7280] mt-0.5">Siswa terverifikasi</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#E8F5EE] border border-[#BBF7D0] text-[#1E9E63] flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">check_circle</span>
            </div>
        </div>
        <div class="bg-white rounded-lg p-4 border border-[#E5E7EB] flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Sakit</p>
                <p id="stat-sakit" class="text-2xl font-heading font-bold text-[#1D4ED8] mt-0.5">{{ $totalSakit }}</p>
                <p class="text-[11px] text-[#6B7280] mt-0.5">Surat dokter</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#EFF6FF] border border-[#BFDBFE] text-[#1D4ED8] flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">medication</span>
            </div>
        </div>
        <div class="bg-white rounded-lg p-4 border border-[#E5E7EB] flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Izin</p>
                <p id="stat-izin" class="text-2xl font-heading font-bold text-[#B45309] mt-0.5">{{ $totalIzin }}</p>
                <p class="text-[11px] text-[#6B7280] mt-0.5">Keperluan keluarga</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#FFFBEB] border border-[#FDE68A] text-[#B45309] flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">event_note</span>
            </div>
        </div>
        <div class="bg-white rounded-lg p-4 border border-[#E5E7EB] flex items-center justify-between">
            <div>
                <p class="text-[11px] font-semibold text-[#6B7280] uppercase tracking-wider">Alpha / Belum</p>
                <p id="stat-alpha" class="text-2xl font-heading font-bold text-[#B91C1C] mt-0.5">{{ $totalAlpha }}</p>
                <p class="text-[11px] text-[#6B7280] mt-0.5">Tanpa keterangan</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-[#FEF2F2] border border-[#FECACA] text-[#B91C1C] flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">cancel</span>
            </div>
        </div>
    </div>

    <!-- JIKA METODE == 'qr': KARTU QR CODE BESAR & REFRESH OTOMATIS SETIAP 20 DETIK -->
    @if ($sesi->metode === 'qr')
        <div class="bg-white rounded-lg border border-[#E5E7EB] p-6 flex flex-col md:flex-row items-center justify-between gap-6">
            <!-- Informasi Panduan -->
            <div class="flex-1 space-y-3">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0]">
                    <span class="material-symbols-outlined text-[15px]">qr_code_scanner</span>
                    <span>QR Code Dinamis Anti-Kecurangan</span>
                </div>
                <h3 class="text-base font-heading font-semibold text-[#1F2937]">
                    Tampilkan Kode QR Ini di Proyektor / Layar Kelas
                </h3>
                <p class="text-xs text-[#6B7280] leading-relaxed">
                    Siswa memindai kode QR ini menggunakan kamera di akun SIAKAD masing-masing. Kode di bawah ini <strong>otomatis me-refresh setiap 20 detik</strong> via AJAX.
                </p>

                <div class="p-4 rounded-lg bg-[#F9FAFB] border border-[#E5E7EB] space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[#6B7280] font-medium">Status Token Server:</span>
                        <span id="qr-status-indicator" class="font-semibold text-[#146C43] flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#1E9E63]"></span>
                            <span>Aktif</span>
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-[#6B7280] font-medium">Sisa Waktu Token:</span>
                        <span id="qr-countdown-text" class="font-semibold text-[#1F2937] ">20 Detik</span>
                    </div>
                    <!-- Bar Progress Countdown 20s -->
                    <div class="w-full bg-[#E5E7EB] rounded-full h-1.5 overflow-hidden">
                        <div id="qr-progress-bar" class="bg-[#1E9E63] h-1.5 rounded-full transition-all duration-1000 ease-linear" style="width: 100%;"></div>
                    </div>
                    <div class="pt-1 flex items-center justify-between text-[11px] text-[#6B7280] ">
                        <span>Token Terakhir: <span id="current-token-display" class="font-semibold text-[#1F2937]">{{ $sesi->qr_token ?? 'Membuat...' }}</span></span>
                    </div>
                </div>

                @if ($sesi->status_sesi !== 'dibuka')
                    <div class="p-3 rounded-md bg-[#FFF7ED] border border-[#FED7AA] text-xs text-[#9A3412] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">info</span>
                        <span>Sesi telah ditutup. QR Code tidak lagi menerima presensi baru.</span>
                    </div>
                @endif
            </div>

            <!-- Wadah QR Code Besar -->
            <div class="flex-shrink-0 flex flex-col items-center">
                <div class="p-4 bg-white rounded-lg border border-[#E5E7EB] flex items-center justify-center relative min-w-[270px] min-h-[270px]">
                    <div id="qrcode-box" class="flex items-center justify-center"></div>
                </div>
                <p class="text-[11px] text-[#6B7280] font-medium mt-2">Pindai dari perangkat siswa</p>
            </div>
        </div>
    @endif

    <!-- TABEL REKAP & OVERRIDE KEHADIRAN KELAS -->
    <div class="bg-white rounded-lg border border-[#E5E7EB] overflow-hidden">
        <!-- Header Tabel & Tombol Simpan Override -->
        <form method="POST" action="{{ route('guru.presensi.override', $sesi->id) }}" id="form-rekap-override">
            @csrf
            <div class="px-5 py-4 border-b border-[#E5E7EB] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-[#F9FAFB]">
                <div>
                    <h3 class="font-heading font-semibold text-base text-[#1F2937]">
                        Tabel Rekap &amp; Override Kehadiran Kelas
                    </h3>
                    <p class="text-xs text-[#6B7280] mt-0.5">
                        Status terupdate otomatis saat siswa melakukan presensi. Anda dapat mengubah status secara manual lalu simpan.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit" class="px-4 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer">
                        <span class="material-symbols-outlined text-[17px]">save</span>
                        <span>Simpan Perubahan Rekap</span>
                    </button>
                </div>
            </div>

            <!-- Kontainer Tabel Presensi -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-[#374151] border-collapse">
                    <thead class="bg-[#F9FAFB] text-[#6B7280] font-semibold border-b border-[#E5E7EB]">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Nama Siswa</th>
                            <th class="py-3 px-4 w-28">NIS</th>
                            <th class="py-3 px-4 min-w-[200px]">Lokasi / Waktu Presensi</th>
                            <th class="py-3 px-4 w-20 text-center text-[#146C43]">Hadir</th>
                            <th class="py-3 px-4 w-20 text-center text-[#1D4ED8]">Sakit</th>
                            <th class="py-3 px-4 w-20 text-center text-[#B45309]">Izin</th>
                            <th class="py-3 px-4 w-20 text-center text-[#B91C1C]">Alpha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E7EB]" id="tbody-presensi-siswa">
                        @foreach ($presensis as $index => $p)
                            <tr class="hover:bg-[#F9FAFB] transition-colors" data-siswa-id="{{ $p->siswa_id }}">
                                <td class="py-3 px-4 text-center text-[#6B7280]">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-3 px-4 font-medium text-[#1F2937]">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-[#F4F6F8] border border-[#E5E7EB] text-[#4B5563] flex items-center justify-center font-semibold text-[10px]">
                                            {{ strtoupper(substr($p->siswa->name, 0, 2)) }}
                                        </div>
                                        <span>{{ $p->siswa->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-[#6B7280]">
                                    {{ $p->siswa->nis_nip ?? '-' }}
                                </td>
                                <td class="py-3 px-4" id="cell-lokasi-{{ $p->siswa_id }}">
                                    @if ($p->status === 'Hadir')
                                        <div class="space-y-0.5">
                                            <div class="flex items-center gap-1.5 text-[#146C43] font-semibold text-[11px]">
                                                <span class="material-symbols-outlined text-[14px] text-[#1E9E63]">schedule</span>
                                                <span>{{ $p->waktu_presensi ? substr($p->waktu_presensi, 0, 5) . ' WIB' : 'Tercatat' }}</span>
                                                <span class="px-1.5 py-0.2 rounded bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0] text-[10px] uppercase font-semibold ml-1">
                                                    {{ $p->metode_absen ?? 'Hadir' }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-[#6B7280] line-clamp-1 break-words max-w-sm" title="{{ $p->alamat_lokasi }}">
                                                <span class="material-symbols-outlined text-[12px] align-middle text-[#9CA3AF]">pin_drop</span>
                                                {{ $p->alamat_lokasi ?? ($p->latitude ? $p->latitude . ', ' . $p->longitude : 'Lokasi Terverifikasi') }}
                                            </p>
                                        </div>
                                    @else
                                        <span class="text-[#9CA3AF] text-[11px] italic">
                                            {{ $p->keterangan ?? 'Belum ada rekaman lokasi' }}
                                        </span>
                                    @endif
                                </td>

                                <!-- 4 Kolom Status Menggunakan Tombol Radio Bulat -->
                                <!-- 1. Hadir -->
                                <td class="py-3 px-4 text-center">
                                    <label class="cursor-pointer inline-flex items-center justify-center p-1 group">
                                        <input type="radio" name="presensi[{{ $p->siswa_id }}]" value="Hadir" {{ $p->status === 'Hadir' ? 'checked' : '' }} class="hidden peer">
                                        <div class="w-5 h-5 rounded-full border-2 border-[#D1D5DB] peer-checked:border-[#1E9E63] peer-checked:bg-[#1E9E63] flex items-center justify-center transition-all group-hover:border-[#1E9E63]">
                                            <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100"></div>
                                        </div>
                                    </label>
                                </td>

                                <!-- 2. Sakit -->
                                <td class="py-3 px-4 text-center">
                                    <label class="cursor-pointer inline-flex items-center justify-center p-1 group">
                                        <input type="radio" name="presensi[{{ $p->siswa_id }}]" value="Sakit" {{ $p->status === 'Sakit' ? 'checked' : '' }} class="hidden peer">
                                        <div class="w-5 h-5 rounded-full border-2 border-[#D1D5DB] peer-checked:border-blue-600 peer-checked:bg-blue-600 flex items-center justify-center transition-all group-hover:border-blue-400">
                                            <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100"></div>
                                        </div>
                                    </label>
                                </td>

                                <!-- 3. Izin -->
                                <td class="py-3 px-4 text-center">
                                    <label class="cursor-pointer inline-flex items-center justify-center p-1 group">
                                        <input type="radio" name="presensi[{{ $p->siswa_id }}]" value="Izin" {{ $p->status === 'Izin' ? 'checked' : '' }} class="hidden peer">
                                        <div class="w-5 h-5 rounded-full border-2 border-[#D1D5DB] peer-checked:border-amber-500 peer-checked:bg-amber-500 flex items-center justify-center transition-all group-hover:border-amber-400">
                                            <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100"></div>
                                        </div>
                                    </label>
                                </td>

                                <!-- 4. Alpha -->
                                <td class="py-3 px-4 text-center">
                                    <label class="cursor-pointer inline-flex items-center justify-center p-1 group">
                                        <input type="radio" name="presensi[{{ $p->siswa_id }}]" value="Alpha" {{ $p->status === 'Alpha' ? 'checked' : '' }} class="hidden peer">
                                        <div class="w-5 h-5 rounded-full border-2 border-[#D1D5DB] peer-checked:border-rose-600 peer-checked:bg-rose-600 flex items-center justify-center transition-all group-hover:border-rose-400">
                                            <div class="w-2 h-2 rounded-full bg-white opacity-0 peer-checked:opacity-100"></div>
                                        </div>
                                    </label>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer Simpan Rekap -->
            <div class="px-5 py-3.5 bg-[#F9FAFB] border-t border-[#E5E7EB] flex items-center justify-between">
                <span class="text-xs text-[#6B7280]">
                    Total Siswa di Kelas: <strong class="text-[#1F2937]">{{ $totalSiswa }} Orang</strong>
                </span>
                <button type="submit" class="px-5 py-2 rounded-md bg-[#1E9E63] hover:bg-[#168251] text-white text-xs font-semibold transition-colors flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-[17px]">save</span>
                    <span>Simpan Perubahan Rekap</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<!-- Library QRCodeJS via CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sesiId = {{ $sesi->id }};
    const isMetodeQr = {{ $sesi->metode === 'qr' ? 'true' : 'false' }};
    const isSesiDibuka = {{ $sesi->status_sesi === 'dibuka' ? 'true' : 'false' }};

    let qrcodeInstance = null;
    let qrCountdown = 20;
    const qrCountdownIntervalMs = 1000;

    if (isMetodeQr && isSesiDibuka) {
        const qrContainer = document.getElementById('qrcode-box');
        const tokenDisplay = document.getElementById('current-token-display');
        const countdownText = document.getElementById('qr-countdown-text');
        const progressBar = document.getElementById('qr-progress-bar');
        let currentToken = '{{ $sesi->qr_token }}';

        if (qrContainer && typeof QRCode !== 'undefined') {
            qrcodeInstance = new QRCode(qrContainer, {
                text: currentToken || 'SMAN1GST-INITIAL',
                width: 240,
                height: 240,
                colorDark: "#1F2937",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        function refreshQrCode() {
            fetch(`/guru/presensi/sesi/${sesiId}/qr-token`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.token) {
                    currentToken = data.token;
                    if (tokenDisplay) tokenDisplay.textContent = data.token;
                    if (qrcodeInstance) {
                        qrcodeInstance.clear();
                        qrcodeInstance.makeCode(data.token);
                    }
                    qrCountdown = 20;
                    updateProgressBar();
                }
            })
            .catch(err => console.error('Gagal memperbarui QR Token:', err));
        }

        function updateProgressBar() {
            if (!progressBar || !countdownText) return;
            const percentage = (qrCountdown / 20) * 100;
            progressBar.style.width = percentage + '%';
            countdownText.textContent = qrCountdown + ' Detik';
        }

        setInterval(function() {
            qrCountdown--;
            if (qrCountdown <= 0) {
                qrCountdown = 20;
                refreshQrCode();
            }
            updateProgressBar();
        }, qrCountdownIntervalMs);
    }

    if (isSesiDibuka) {
        setInterval(function() {
            fetch(`/guru/presensi/sesi/${sesiId}/status-live`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const elHadir = document.getElementById('stat-hadir');
                    const elSakit = document.getElementById('stat-sakit');
                    const elIzin = document.getElementById('stat-izin');
                    const elAlpha = document.getElementById('stat-alpha');
                    if (elHadir) elHadir.textContent = data.hadir_count;
                    if (elSakit) elSakit.textContent = data.sakit_count;
                    if (elIzin) elIzin.textContent = data.izin_count;
                    if (elAlpha) elAlpha.textContent = data.alpha_count;

                    data.presensis.forEach(p => {
                        const row = document.querySelector(`tr[data-siswa-id="${p.siswa_id}"]`);
                        if (!row) return;

                        const targetRadio = row.querySelector(`input[name="presensi[${p.siswa_id}]"][value="${p.status}"]`);
                        if (targetRadio && !targetRadio.checked) {
                            targetRadio.checked = true;
                        }

                        const cellLokasi = document.getElementById(`cell-lokasi-${p.siswa_id}`);
                        if (cellLokasi && p.status === 'Hadir' && p.waktu_presensi !== '-') {
                            cellLokasi.innerHTML = `
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-1.5 text-[#146C43] font-semibold text-[11px]">
                                        <span class="material-symbols-outlined text-[14px] text-[#1E9E63]">schedule</span>
                                        <span>${p.waktu_presensi}</span>
                                        <span class="px-1.5 py-0.2 rounded bg-[#E8F5EE] text-[#146C43] border border-[#BBF7D0] text-[10px] uppercase font-semibold ml-1">
                                            Hadir
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-[#6B7280] line-clamp-1 break-words max-w-sm" title="${p.lokasi}">
                                        <span class="material-symbols-outlined text-[12px] align-middle text-[#9CA3AF]">pin_drop</span>
                                        ${p.lokasi}
                                    </p>
                                </div>
                            `;
                        }
                    });
                }
            })
            .catch(err => console.error('Gagal polling kehadiran siswa:', err));
        }, 6000);
    }
});
</script>
@endpush
