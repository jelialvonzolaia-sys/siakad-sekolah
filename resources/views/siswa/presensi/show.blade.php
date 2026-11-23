@extends('layouts.siakad')

@php
    $breadcrumbs = [
        ['label' => 'Beranda', 'url' => route('siswa.dashboard')],
        ['label' => 'Presensi Kelas', 'url' => route('siswa.presensi.index')],
        ['label' => 'Pertemuan Kelas'],
    ];
@endphp

@section('content')
<div class="bg-white rounded-lg border border-[#E5E7EB] p-5 sm:p-6 space-y-5">

    <!-- Header Mata Pelajaran & Kembali -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-lg sm:text-xl font-heading font-bold text-[#1F2937] tracking-tight">
                Presensi - {{ $jadwal->mataPelajaran->kode_mapel }} {{ $jadwal->mataPelajaran->nama_mapel }}, Kelas {{ $jadwal->kelas->nama_kelas }}
            </h2>
            <p class="text-xs sm:text-sm text-[#6B7280] mt-1">
                Guru Pengampu: <span class="font-medium text-[#1F2937]">{{ $jadwal->guru->name }}</span> • Jadwal: {{ $jadwal->hari }}, {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }} WIB
            </p>
        </div>

        <a href="{{ route('siswa.presensi.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-md border border-[#D1D5DB] bg-white hover:bg-[#F9FAFB] text-xs font-semibold text-[#374151] transition-colors self-start sm:self-auto">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            <span>Kembali ke Daftar Mapel</span>
        </a>
    </div>

    <!-- Tab Penanda Daftar Sesi -->
    <div class="border-b border-[#E5E7EB]">
        <span class="inline-block pb-2.5 text-sm font-semibold text-[#1E9E63] border-b-2 border-[#1E9E63]">
            Daftar Pertemuan Kelas
        </span>
    </div>

    <!-- Tabel Daftar Sesi Pertemuan -->
    <div class="rounded-lg border border-[#E5E7EB] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-[#374151] border-collapse">
                <thead class="bg-[#F9FAFB] text-[#1F2937] font-semibold border-b border-[#E5E7EB]">
                    <tr>
                        <th class="py-3.5 px-4 w-14">No</th>
                        <th class="py-3.5 px-4 w-36">Deskripsi</th>
                        <th class="py-3.5 px-4 w-40">Tanggal Pertemuan</th>
                        <th class="py-3.5 px-4 w-44">Jam dan Jenis Presensi</th>
                        <th class="py-3.5 px-4">Topik Pembelajaran</th>
                        <th class="py-3.5 px-4 w-28">Kehadiran</th>
                        <th class="py-3.5 px-4 w-32 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E5E7EB]">
                    @forelse ($sesis as $index => $sesi)
                        @php
                            $presensi = $sesi->presensis->first();
                            $statusKehadiran = $presensi?->status ?? 'Belum Absen';
                            $sessionDateStr = \Carbon\Carbon::parse($sesi->tanggal)->format('Y-m-d');
                            $waktuMulaiSesi = \Carbon\Carbon::parse($sessionDateStr . ' ' . $sesi->jam_mulai, 'Asia/Jakarta');
                            $waktuSelesaiSesi = \Carbon\Carbon::parse($sessionDateStr . ' ' . $sesi->jam_selesai, 'Asia/Jakarta');
                            $isAktif = ($sesi->status_sesi === 'dibuka')
                                && ($statusKehadiran === 'Belum Absen')
                                && now('Asia/Jakarta')->between($waktuMulaiSesi, $waktuSelesaiSesi);
                        @endphp
                        <tr class="{{ $isAktif ? 'bg-[#C7D5A6]/20' : 'hover:bg-[#F9FAFB]' }} transition-colors">
                            <td class="py-3.5 px-4 text-[#4B5563]">{{ $index + 1 }}</td>
                            <td class="py-3.5 px-4 font-medium text-[#1F2937]">
                                Pertemuan {{ $sesi->pertemuan_ke }}
                            </td>
                            <td class="py-3.5 px-4 text-[#374151]">
                                {{ \Carbon\Carbon::parse($sesi->tanggal)->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-[#1F2937] font-medium">
                                    {{ substr($sesi->jam_mulai, 0, 5) }} - {{ substr($sesi->jam_selesai, 0, 5) }}
                                </div>
                                <div class="mt-1 flex flex-wrap gap-1.5">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-[#F9FAFB] text-[#4B5563] border border-[#E5E7EB]">
                                        {{ $sesi->metode === 'qr' ? 'QR' : 'Mandiri' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-[#374151] leading-relaxed">
                                {{ $sesi->materi }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($statusKehadiran === 'Hadir')
                                    <span class="font-semibold text-[#146C43]">Hadir</span>
                                @elseif ($statusKehadiran === 'Sakit')
                                    <span class="font-semibold text-[#1D4ED8]">Sakit</span>
                                @elseif ($statusKehadiran === 'Izin')
                                    <span class="font-semibold text-[#B45309]">Izin</span>
                                @elseif ($statusKehadiran === 'Alpha')
                                    <span class="font-semibold text-[#DC2626]">Alpha</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-[#FFF7ED] text-[#9A3412] border border-[#FED7AA] text-xs font-medium">
                                        Belum Absen
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if ($isAktif)
                                    <button type="button" onclick="bukaModalPresensi({{ $sesi->id }})" class="px-4 py-1.5 rounded-md bg-[#1F3B2E] hover:bg-[#3C5A3A] text-white text-xs font-semibold transition-colors cursor-pointer shadow-sm">
                                        Presensi
                                    </button>
                                @else
                                    <button type="button" disabled class="px-4 py-1.5 rounded-md text-xs font-medium bg-slate-200 text-slate-400 cursor-not-allowed pointer-events-none">
                                        Presensi
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 px-6 text-center text-[#6B7280] text-sm">
                                Belum ada daftar sesi pertemuan yang dibuka oleh guru pada mata pelajaran ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- BEGIN: ModalPresensiDialog -->
<div aria-labelledby="modal-presensi-title" aria-modal="true" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-900/50 transition-opacity duration-200 hidden" id="modal-presensi-container" role="dialog">
    <div class="bg-white w-full max-w-2xl rounded-lg shadow-xl border border-[#E5E7EB] overflow-hidden transform transition-all duration-200" id="modal-card">

        <div class="bg-[#F9FAFB] px-6 py-4 flex items-center justify-between border-b border-[#E5E7EB]">
            <div class="flex items-center space-x-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-[#1E9E63]"></span>
                <h3 class="text-base font-heading font-semibold text-[#1F2937] tracking-tight" id="modal-presensi-title">
                    Presensi Kelas Siswa
                </h3>
            </div>
            <button aria-label="Tutup jendela" class="text-[#6B7280] hover:text-[#1F2937] hover:bg-[#E5E7EB]/60 p-1.5 rounded-md transition-colors focus:outline-none cursor-pointer" id="btn-close-modal" type="button" onclick="tutupModalPresensi()">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
                </svg>
            </button>
        </div>

        <div class="px-6 py-5 space-y-3.5 text-[#374151] text-xs sm:text-sm">

            <div class="grid grid-cols-12 gap-2 items-baseline">
                <div class="col-span-4 text-[#6B7280] font-medium">Nama Mata Pelajaran</div>
                <div class="col-span-1 text-[#9CA3AF] text-center select-none">:</div>
                <div class="col-span-7 text-[#1F2937] leading-snug font-heading font-semibold" id="modal-mapel-text">
                    -
                </div>
            </div>

            <div class="grid grid-cols-12 gap-2 items-center">
                <div class="col-span-4 text-[#6B7280] font-medium">Kelas</div>
                <div class="col-span-1 text-[#9CA3AF] text-center select-none">:</div>
                <div class="col-span-7 text-[#1F2937]" id="modal-kelas-text">
                    -
                </div>
            </div>

            <div class="grid grid-cols-12 gap-2 items-center">
                <div class="col-span-4 text-[#6B7280] font-medium">Deskripsi / Materi</div>
                <div class="col-span-1 text-[#9CA3AF] text-center select-none">:</div>
                <div class="col-span-7 text-[#1F2937] font-medium" id="modal-deskripsi-text">
                    -
                </div>
            </div>

            <div class="grid grid-cols-12 gap-2 items-center">
                <div class="col-span-4 text-[#6B7280] font-medium">Tanggal Pertemuan</div>
                <div class="col-span-1 text-[#9CA3AF] text-center select-none">:</div>
                <div class="col-span-7 text-[#1F2937]" id="modal-tanggal-text">
                    -
                </div>
            </div>

            <div class="grid grid-cols-12 gap-2 items-center">
                <div class="col-span-4 text-[#6B7280] font-medium">Jam Presensi</div>
                <div class="col-span-1 text-[#9CA3AF] text-center select-none">:</div>
                <div class="col-span-7 text-[#1F2937] flex flex-wrap items-center gap-2">
                    <span id="modal-jam-text">-</span>
                    <span id="modal-sisa-badge" class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-[#FFF7ED] text-[#9A3412] border border-[#FED7AA]">
                        <svg class="w-3 h-3 mr-1 text-[#D97706]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                        <span id="modal-sisa-text">Sisa Waktu Aktif</span>
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-2 items-center">
                <div class="col-span-4 text-[#6B7280] font-medium">Jenis Presensi</div>
                <div class="col-span-1 text-[#9CA3AF] text-center select-none">:</div>
                <div class="col-span-7" id="modal-metode-badge">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#F3F4F6] text-[#374151] border border-[#E5E7EB]">
                        Mandiri
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-2 items-start pt-1">
                <div class="col-span-4 text-[#6B7280] font-medium pt-0.5">Lokasi GPS Siswa</div>
                <div class="col-span-1 text-[#9CA3AF] text-center select-none pt-0.5">:</div>
                <div class="col-span-7 space-y-2">
                    <div id="gps-loading" class="flex items-center gap-2 text-xs text-[#4B5563] bg-[#F9FAFB] p-2.5 rounded-md border border-[#E5E7EB]">
                        <svg class="w-4 h-4 text-[#1E9E63] animate-spin flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Mendeteksi koordinat GPS perangkat Anda...</span>
                    </div>

                    <div id="gps-result" class="hidden space-y-1.5">
                        <p class="font-normal text-[#374151] text-xs sm:text-sm leading-relaxed" id="gps-alamat-text">
                            -
                        </p>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[11px] text-[#6B7280]" id="gps-coords-text">Lat: -, Lon: -</span>
                            <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-md bg-[#E8F5EE] border border-[#BBF7D0] text-[11px] text-[#146C43] font-medium">
                                <svg class="w-3.5 h-3.5 text-[#1E9E63]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
                                </svg>
                                <span>GPS Terverifikasi dalam Radius Sekolah</span>
                            </div>
                        </div>
                    </div>

                    <div id="gps-error" class="hidden p-3 rounded-md bg-[#FFF7ED] border border-[#FED7AA] text-[#9A3412] text-xs space-y-1">
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="material-symbols-outlined text-[#D97706] text-[18px]">location_off</span>
                            <span>Izin Lokasi (GPS) Ditolak atau Tidak Aktif</span>
                        </div>
                        <p class="text-[11px] leading-relaxed">
                            Sistem presensi SMA Negeri 1 Gunungsitoli <strong>mewajibkan</strong> verifikasi koordinat lokasi. Tombol "Hadir" terkunci sampai izin lokasi disetujui di browser Anda.
                        </p>
                    </div>
                </div>
            </div>

            <div id="section-qr-input" class="hidden pt-2 border-t border-[#E5E7EB] space-y-2">
                <label class="block text-xs font-semibold text-[#374151]" for="qr-token-input">
                    Kode Token QR dari Guru <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <input type="text" id="qr-token-input" placeholder="Masukkan token atau scan kode QR..." class="w-full text-xs rounded-md border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 uppercase py-2 px-3">
                    <button type="button" onclick="bukaKameraScanner()" class="px-3 py-2 rounded-md bg-[#E8F5EE] border border-[#BBF7D0] text-[#146C43] hover:bg-[#D1FAE5] text-xs font-semibold flex items-center gap-1.5 flex-shrink-0 cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">qr_code_scanner</span>
                        <span>Scan Kamera</span>
                    </button>
                </div>
                <div id="qr-reader" class="hidden max-w-sm mx-auto p-2 bg-slate-900 rounded-lg overflow-hidden mt-2"></div>
                <p class="text-[11px] text-[#6B7280]">Kode QR otomatis diperbarui oleh Guru setiap 20 detik.</p>
            </div>

            <div class="pt-2 border-t border-[#E5E7EB]">
                <label class="block text-xs font-medium text-[#6B7280] mb-1" for="keterangan-presensi">
                    Catatan Kehadiran (Opsional)
                </label>
                <input class="w-full text-xs rounded-md border border-[#D1D5DB] focus:outline-none focus:border-[#1E9E63] focus:ring-2 focus:ring-[#1E9E63]/15 text-[#1F2937] placeholder-[#9CA3AF] py-2 px-3" id="keterangan-presensi" placeholder="Contoh: Mengikuti sesi di Ruang Kelas {{ $jadwal->kelas->nama_kelas }}" type="text"/>
            </div>

        </div>

        <div class="bg-[#F9FAFB] px-6 py-3.5 flex items-center justify-end space-x-3 border-t border-[#E5E7EB]">
            <button class="px-4 py-2 text-xs font-medium text-[#374151] bg-white border border-[#D1D5DB] rounded-md hover:bg-[#F3F4F6] transition cursor-pointer" type="button" onclick="tutupModalPresensi()">
                Batal
            </button>

            <button class="px-5 py-2 text-xs font-semibold text-white bg-slate-300 border border-transparent rounded-md transition duration-150 flex items-center space-x-2 cursor-not-allowed" id="btn-konfirmasi-hadir" type="button" disabled>
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
                </svg>
                <span>Hadir</span>
            </button>
        </div>

    </div>
</div>
<!-- END: ModalPresensiDialog -->

@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
let currentSesiId = null;
let currentSesiData = null;
let userCoordinates = { lat: null, lon: null };
let userAddress = null;
let html5QrScanner = null;

const modalContainer = document.getElementById('modal-presensi-container');
const btnHadir = document.getElementById('btn-konfirmasi-hadir');

function bukaModalPresensi(sesiId) {
    currentSesiId = sesiId;
    userCoordinates = { lat: null, lon: null };
    userAddress = null;

    document.getElementById('gps-loading').classList.remove('hidden');
    document.getElementById('gps-result').classList.add('hidden');
    document.getElementById('gps-error').classList.add('hidden');
    btnHadir.disabled = true;
    btnHadir.className = "px-5 py-2 text-xs font-semibold text-white bg-slate-300 border border-transparent rounded-md transition duration-150 flex items-center space-x-2 cursor-not-allowed";
    btnHadir.innerHTML = `
        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
        </svg>
        <span>Hadir</span>
    `;

    modalContainer.classList.remove('hidden');

    fetch(`/siswa/presensi/sesi/${sesiId}/detail`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            window.showToast('error', data.message || 'Gagal memuat detail sesi presensi.', 'Gagal Memuat');
            tutupModalPresensi();
            return;
        }

        currentSesiData = data;
        document.getElementById('modal-mapel-text').textContent = data.mapel;
        document.getElementById('modal-kelas-text').textContent = data.kelas;
        document.getElementById('modal-deskripsi-text').textContent = data.deskripsi;
        document.getElementById('modal-tanggal-text').textContent = data.tanggal;
        document.getElementById('modal-jam-text').textContent = data.jam_presensi;
        document.getElementById('modal-sisa-text').textContent = `Sisa ${data.sisa_menit} Menit`;

        const badgeMetode = document.getElementById('modal-metode-badge');
        const sectionQrInput = document.getElementById('section-qr-input');
        if (data.metode === 'qr') {
            badgeMetode.innerHTML = `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#EFF6FF] text-[#1D4ED8] border border-[#BFDBFE]">
                    Scan QR Code
                </span>
            `;
            sectionQrInput.classList.remove('hidden');
        } else {
            badgeMetode.innerHTML = `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-[#F3F4F6] text-[#374151] border border-[#E5E7EB]">
                    Mandiri
                </span>
            `;
            sectionQrInput.classList.add('hidden');
        }

        mintaLokasiGps();
    })
    .catch(err => {
        console.error('Error fetching session detail:', err);
        window.showToast('error', 'Terjadi kesalahan saat memuat detail presensi.', 'Kesalahan');
        tutupModalPresensi();
    });
}

function tutupModalPresensi() {
    modalContainer.classList.add('hidden');
    if (html5QrScanner) {
        try { html5QrScanner.stop(); } catch (e) {}
    }
    const readerDiv = document.getElementById('qr-reader');
    if (readerDiv) {
        readerDiv.classList.add('hidden');
    }
}

function mintaLokasiGps() {
    const gpsLoading = document.getElementById('gps-loading');
    const gpsResult = document.getElementById('gps-result');
    const gpsError = document.getElementById('gps-error');

    if (!navigator.geolocation) {
        gpsLoading.classList.add('hidden');
        gpsError.classList.remove('hidden');
        gpsError.querySelector('p').textContent = 'Peramban (browser) Anda tidak mendukung fitur geolokasi GPS.';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (position) {
            userCoordinates.lat = position.coords.latitude;
            userCoordinates.lon = position.coords.longitude;

            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${userCoordinates.lat}&lon=${userCoordinates.lon}`, {
                headers: {
                    'Accept-Language': 'id'
                }
            })
            .then(res => res.json())
            .then(geoData => {
                userAddress = geoData.display_name || `Area Gunungsitoli (${userCoordinates.lat}, ${userCoordinates.lon})`;
                tampilkanHasilGps(userAddress, userCoordinates.lat, userCoordinates.lon);
            })
            .catch(() => {
                userAddress = `Area Sekitar SMA Negeri 1 Gunungsitoli (Koordinat: ${userCoordinates.lat.toFixed(6)}, ${userCoordinates.lon.toFixed(6)})`;
                tampilkanHasilGps(userAddress, userCoordinates.lat, userCoordinates.lon);
            });
        },
        function (error) {
            console.warn('Geolocation Error:', error.message);
            gpsLoading.classList.add('hidden');
            gpsResult.classList.add('hidden');
            gpsError.classList.remove('hidden');

            btnHadir.disabled = true;
            btnHadir.className = "px-5 py-2 text-xs font-semibold text-white bg-slate-300 border border-transparent rounded-md transition duration-150 flex items-center space-x-2 cursor-not-allowed";
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );
}

function tampilkanHasilGps(address, lat, lon) {
    document.getElementById('gps-loading').classList.add('hidden');
    document.getElementById('gps-error').classList.add('hidden');
    const gpsResult = document.getElementById('gps-result');
    gpsResult.classList.remove('hidden');

    document.getElementById('gps-alamat-text').textContent = address;
    document.getElementById('gps-coords-text').textContent = `Lat: ${lat.toFixed(6)}, Lon: ${lon.toFixed(6)}`;

    btnHadir.disabled = false;
    btnHadir.className = "px-5 py-2 text-xs font-semibold text-white bg-[#1F3B2E] hover:bg-[#162C22] border border-transparent rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1F3B2E] transition duration-150 flex items-center space-x-2 cursor-pointer";
}

btnHadir.addEventListener('click', function () {
    if (!currentSesiId || !userCoordinates.lat || !userCoordinates.lon) {
        window.showToast('warning', 'Koordinat GPS wajib terdeteksi sebelum dapat melakukan presensi.', 'Validasi GPS');
        return;
    }

    const payload = {
        latitude: userCoordinates.lat,
        longitude: userCoordinates.lon,
        alamat_lokasi: userAddress || 'Lokasi SMA Negeri 1 Gunungsitoli',
        keterangan: document.getElementById('keterangan-presensi').value
    };

    if (currentSesiData && currentSesiData.metode === 'qr') {
        const qrVal = document.getElementById('qr-token-input').value.trim();
        if (!qrVal) {
            window.showToast('warning', 'Silakan masukkan atau scan kode token QR yang diberikan oleh Guru.', 'Token QR Wajib');
            return;
        }
        payload.qr_token = qrVal;
    }

    btnHadir.disabled = true;
    btnHadir.innerHTML = `
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        <span>Menyimpan...</span>
    `;

    fetch(`/siswa/presensi/sesi/${currentSesiId}/submit`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            window.showToast('success', 'Presensi berhasil dicatat! Status: Hadir.', 'Presensi Berhasil');
            tutupModalPresensi();
            setTimeout(() => window.location.reload(), 900);
        } else {
            window.showToast('error', result.message || 'Gagal menyimpan presensi. Periksa kembali lokasi atau kode token Anda.', 'Presensi Gagal');
            btnHadir.disabled = false;
            btnHadir.innerHTML = `
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
                </svg>
                <span>Hadir</span>
            `;
        }
    })
    .catch(err => {
        console.error('Error submitting attendance:', err);
        window.showToast('error', 'Terjadi kesalahan jaringan saat mengirim presensi.', 'Kesalahan Jaringan');
        btnHadir.disabled = false;
        btnHadir.innerHTML = `
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"></path>
            </svg>
            <span>Hadir</span>
        `;
    });
});

function bukaKameraScanner() {
    const readerDiv = document.getElementById('qr-reader');
    readerDiv.classList.remove('hidden');

    if (typeof Html5Qrcode !== 'undefined') {
        html5QrScanner = new Html5Qrcode("qr-reader");
        html5QrScanner.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: { width: 220, height: 220 }
            },
            (decodedText) => {
                document.getElementById('qr-token-input').value = decodedText;
                html5QrScanner.stop();
                readerDiv.classList.add('hidden');
            },
            () => {}
        ).catch(err => {
            console.error('Kamera Scanner Error:', err);
            window.showToast('error', 'Gagal mengakses kamera perangkat: ' + err, 'Kamera Error');
            readerDiv.classList.add('hidden');
        });
    } else {
        window.showToast('warning', 'Modul pemindai kamera belum terpasang. Silakan masukkan token QR secara manual.', 'Pemindai Tidak Tersedia');
    }
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        tutupModalPresensi();
    }
});
</script>
@endpush
