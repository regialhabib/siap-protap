<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="SIAP-PROTAP — Sistem Informasi Administrasi Pelaporan Proteksi Tanaman Perkebunan, Dinas Perkebunan Provinsi Jambi.">
    <title>{{ config('app.name', 'SIAP-PROTAP') }} — Pelaporan Proteksi Tanaman Perkebunan</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-800 bg-[#f4f7f6]">

    <!-- Navbar -->
    <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center">
                <img src="{{ asset('images/logo_final.jpg') }}" alt="Logo SIAP-PROTAP" class="w-9 h-9 rounded-full border-2 border-emerald-100 shadow-sm">
                <span class="ml-3 text-lg font-extrabold tracking-wide text-emerald-700">SIAP-PROTAP</span>
            </a>

            <nav class="hidden md:flex items-center space-x-8 text-sm font-medium text-gray-600">
                <a href="#fitur" class="hover:text-emerald-600 transition">Fitur</a>
                <a href="#alur" class="hover:text-emerald-600 transition">Alur Pelaporan</a>
                <a href="#peran" class="hover:text-emerald-600 transition">Hak Akses</a>
            </nav>

            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-emerald-600 text-white text-sm font-bold shadow-md hover:bg-emerald-700 transition">
                    Buka Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-emerald-600 text-white text-sm font-bold shadow-md hover:bg-emerald-700 transition">
                    Masuk
                </a>
            @endauth
        </div>
    </header>

    <main>
        <!-- Hero -->
        <section class="relative overflow-hidden bg-emerald-900">
            <img src="{{ asset('images/hero-kebun-teh-kerinci.jpg') }}" alt="" class="absolute inset-0 w-full h-full object-cover object-bottom">
            <div class="absolute inset-0 bg-gradient-to-r from-emerald-950/90 via-emerald-900/70 to-emerald-900/30"></div>
            <p class="absolute bottom-2 right-3 z-10 text-[10px] text-white/60">
                Foto: Kebun teh Kayu Aro, Kerinci &mdash;
                <a href="https://commons.wikimedia.org/wiki/File:Gunung_Kerinci_dari_kebun_teh.jpg" target="_blank" rel="noopener" class="underline hover:text-white">Muhamad Izzul Fiqih</a>,
                <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="noopener" class="underline hover:text-white">CC BY-SA 4.0</a>
            </p>

            <div class="relative max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-24 grid gap-12 md:grid-cols-2 items-center">
                <div class="text-center md:text-left">
                    <span class="inline-block px-3 py-1 rounded-full bg-white/15 text-emerald-50 text-xs font-semibold tracking-wide uppercase">
                        Dinas Perkebunan Provinsi Jambi
                    </span>
                    <h1 class="mt-5 text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white leading-tight">
                        Laporan serangan OPT, dari lapangan langsung ke meja rekap.
                    </h1>
                    <p class="mt-5 text-emerald-50 text-base sm:text-lg leading-relaxed max-w-xl mx-auto md:mx-0">
                        SIAP-PROTAP membantu petugas POPT mencatat hasil pengamatan Organisme Pengganggu Tumbuhan secara terpusat, sehingga Administrator dapat memantau kepatuhan UPPT dan menyusun laporan tanpa rekap manual.
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center md:justify-start">
                        @auth
                            <a href="{{ route('dashboard') }}" class="inline-flex justify-center items-center px-6 py-3 rounded-md bg-white text-emerald-700 text-sm font-bold shadow-lg hover:bg-emerald-50 transition">
                                Buka Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex justify-center items-center px-6 py-3 rounded-md bg-white text-emerald-700 text-sm font-bold shadow-lg hover:bg-emerald-50 transition">
                                Masuk ke Sistem
                            </a>
                        @endauth
                        <a href="#alur" class="inline-flex justify-center items-center px-6 py-3 rounded-md border border-white/60 text-white text-sm font-bold hover:bg-white/10 transition">
                            Lihat Alur Pelaporan
                        </a>
                    </div>
                </div>

                <!-- Ilustrasi formulir pengamatan -->
                <div class="hidden md:block">
                    <div class="bg-white rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.2)] p-6 max-w-md ml-auto" aria-hidden="true">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-bold text-gray-900">Formulir Pengamatan Lapangan</p>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            @foreach (['Tanggal Pengamatan', 'Kecamatan', 'Komoditas', 'OPT (Hama/Penyakit)'] as $fieldLabel)
                                <div>
                                    <p class="text-[11px] font-semibold text-gray-500">{{ $fieldLabel }}</p>
                                    <div class="mt-1 h-8 rounded-md border border-gray-200 bg-gray-50"></div>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-5 text-[11px] font-semibold text-gray-500">Kondisi Serangan</p>
                        <div class="mt-2 grid grid-cols-3 gap-2 text-center text-xs font-semibold">
                            <div class="rounded-md bg-amber-50 text-amber-700 py-2">Ringan</div>
                            <div class="rounded-md bg-orange-50 text-orange-700 py-2">Sedang</div>
                            <div class="rounded-md bg-red-50 text-red-700 py-2">Berat</div>
                        </div>
                        <p class="mt-5 text-[11px] font-semibold text-gray-500">Luas Pengendalian per Sumber (Ha)</p>
                        <div class="mt-2 grid grid-cols-4 gap-2 text-center text-[11px] font-medium text-gray-600">
                            @foreach (['APBD Kab', 'APBD Prov', 'APBN', 'Masyarakat'] as $fundingSource)
                                <div class="rounded-md border border-gray-200 py-2">{{ $fundingSource }}</div>
                            @endforeach
                        </div>
                        <div class="mt-6 h-10 rounded-md bg-emerald-600 flex items-center justify-center text-sm font-bold text-white">Simpan</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Fitur -->
        <section id="fitur" class="scroll-mt-16 max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-sm font-bold text-emerald-600 uppercase tracking-widest">Fitur Utama</p>
                <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold text-gray-900">Semua kebutuhan pelaporan proteksi tanaman dalam satu tempat</h2>
            </div>

            @php
                $features = [
                    [
                        'title' => 'Input Pengamatan Lapangan',
                        'description' => 'Petugas mencatat lokasi, komoditas, OPT, luas serangan ringan/sedang/berat, dan luas pengendalian per sumber dana.',
                        'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                    ],
                    [
                        'title' => 'Dashboard Kepatuhan UPPT',
                        'description' => 'Ringkasan pengamatan bulan berjalan serta daftar UPPT yang sudah dan belum mengirimkan laporan.',
                        'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    ],
                    [
                        'title' => 'Laporan Bulanan, Triwulan & Tahunan',
                        'description' => 'Rekapitulasi otomatis yang dapat difilter berdasarkan periode, UPPT, dan komoditas.',
                        'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    ],
                    [
                        'title' => 'Ekspor PDF & Excel',
                        'description' => 'Cetak arsip laporan resmi dalam PDF atau unduh Excel untuk pengolahan data lebih lanjut.',
                        'icon' => 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    ],
                    [
                        'title' => 'Master Data Terpusat',
                        'description' => 'Kelola komoditas, OPT beserta jenis turunannya, serta wilayah UPPT dan kecamatan cakupannya.',
                        'icon' => 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4',
                    ],
                    [
                        'title' => 'Manajemen Petugas',
                        'description' => 'Daftarkan akun Admin maupun POPT dan atur status aktif atau nonaktif sesuai penugasan.',
                        'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                    ],
                ];
            @endphp

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($features as $feature)
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 hover:shadow-md transition">
                        <div class="w-11 h-11 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"></path></svg>
                        </div>
                        <h3 class="mt-4 text-base font-bold text-gray-900">{{ $feature['title'] }}</h3>
                        <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $feature['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Alur Pelaporan -->
        <section id="alur" class="scroll-mt-16 bg-white border-y border-gray-100">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20">
                <div class="text-center max-w-2xl mx-auto">
                    <p class="text-sm font-bold text-emerald-600 uppercase tracking-widest">Alur Pelaporan</p>
                    <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold text-gray-900">Empat langkah dari pengamatan hingga laporan</h2>
                </div>

                @php
                    $reportingSteps = [
                        ['title' => 'Login Petugas', 'description' => 'Petugas POPT masuk menggunakan akun yang didaftarkan Administrator.'],
                        ['title' => 'Isi Pengamatan', 'description' => 'Pilih kecamatan, komoditas, dan OPT, lalu isi kondisi serangan serta cara pengendalian.'],
                        ['title' => 'Validasi & Simpan', 'description' => 'Sistem memeriksa kelengkapan data sebelum menyimpannya secara terpusat.'],
                        ['title' => 'Pantau & Rekap', 'description' => 'Administrator memantau dashboard dan mencetak laporan bulanan, triwulan, atau tahunan.'],
                    ];
                @endphp

                <ol class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($reportingSteps as $step)
                        <li class="relative rounded-xl bg-[#f4f7f6] p-6">
                            <span class="flex items-center justify-center w-9 h-9 rounded-full bg-emerald-600 text-white text-sm font-extrabold shadow-md">{{ $loop->iteration }}</span>
                            <h3 class="mt-4 text-base font-bold text-gray-900">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $step['description'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <!-- Hak Akses -->
        <section id="peran" class="scroll-mt-16 max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20">
            <div class="text-center max-w-2xl mx-auto">
                <p class="text-sm font-bold text-emerald-600 uppercase tracking-widest">Hak Akses</p>
                <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold text-gray-900">Dirancang untuk dua peran utama</h2>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl bg-[#0f172a] text-gray-300 p-8 shadow-xl">
                    <p class="text-xs font-bold text-emerald-400 uppercase tracking-widest">Administrator BPTP</p>
                    <h3 class="mt-2 text-xl font-bold text-white">Mengelola & menyusun laporan</h3>
                    <ul class="mt-6 space-y-3 text-sm">
                        @foreach (['Mengelola master komoditas, OPT, dan wilayah UPPT', 'Mendaftarkan serta mengaktifkan akun petugas', 'Memantau kepatuhan pelaporan setiap UPPT', 'Mengekspor laporan ke PDF dan Excel'] as $adminCapability)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                {{ $adminCapability }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="rounded-2xl bg-white border border-gray-100 p-8 shadow-xl">
                    <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest">Petugas Lapangan (POPT)</p>
                    <h3 class="mt-2 text-xl font-bold text-gray-900">Mencatat kondisi di lapangan</h3>
                    <ul class="mt-6 space-y-3 text-sm text-gray-600">
                        @foreach (['Menginput hasil pengamatan OPT di wilayah tugas', 'Mencatat luas serangan dan tindakan pengendalian', 'Memperbarui data pengamatan yang sudah dikirim', 'Melihat riwayat pengamatan pribadi'] as $poptCapability)
                            <li class="flex items-start">
                                <svg class="w-5 h-5 mr-3 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                {{ $poptCapability }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        <!-- CTA -->
        <section class="max-w-6xl mx-auto px-4 sm:px-6 pb-16 md:pb-20">
            <div class="rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 px-6 py-12 sm:px-12 text-center shadow-[0_20px_50px_rgba(0,0,0,0.1)]">
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Siap mengirim laporan pengamatan?</h2>
                <p class="mt-3 text-emerald-50 max-w-xl mx-auto">Masuk dengan akun yang telah didaftarkan oleh Administrator BPTP.</p>
                <div class="mt-8">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex justify-center items-center px-8 py-3 rounded-md bg-white text-emerald-700 text-sm font-bold shadow-lg hover:bg-emerald-50 transition">
                            Buka Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex justify-center items-center px-8 py-3 rounded-md bg-white text-emerald-700 text-sm font-bold shadow-lg hover:bg-emerald-50 transition">
                            Masuk ke Sistem
                        </a>
                    @endauth
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-gray-200 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-400">
            <p>&copy; {{ date('Y') }} Dinas Perkebunan Provinsi Jambi</p>
            <p>Sistem Informasi Administrasi Pelaporan Proteksi Tanaman Perkebunan</p>
        </div>
    </footer>

</body>
</html>
