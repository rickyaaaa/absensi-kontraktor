<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- Dark Mode: Inline script to prevent white flash --}}
        <script>
            (function() {
                const theme = localStorage.getItem('theme');
                if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- SweetAlert2 -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-300">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow dark:shadow-gray-700/30 transition-colors duration-300">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        {{-- ══════════════════════════════════════════════════════ --}}
        {{-- Global SweetAlert2 handler for session flash messages --}}
        {{-- ══════════════════════════════════════════════════════ --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const swalType    = @json(session('swal_type', ''));
                const successMsg  = @json(session('success', ''));
                const errorMsg    = @json(session('error', ''));

                // ── SUCCESS ──────────────────────────────────────
                if (successMsg) {
                    let title = 'Berhasil!';
                    let icon  = 'success';
                    let html  = successMsg;
                    let timer = 3000;
                    let confirmColor = '#10b981';

                    if (swalType === 'clockin') {
                        title = '✅ Absen Masuk Berhasil!';
                        html  = '<p class="text-gray-600">' + successMsg + '</p>';
                        confirmColor = '#10b981';
                    } else if (swalType === 'clockin_late') {
                        title = '⏰ Absen Masuk Tercatat';
                        icon  = 'warning';
                        html  = '<p class="text-gray-600">' + successMsg + '</p>';
                        confirmColor = '#f59e0b';
                        timer = 5000;
                    } else if (swalType === 'clockout') {
                        title = '👋 Absen Pulang Berhasil!';
                        html  = '<p class="text-gray-600">' + successMsg + '</p>'
                              + '<p class="text-sm text-gray-400 mt-1">Hati-hati di jalan!</p>';
                        confirmColor = '#6366f1';
                    }

                    Swal.fire({
                        title: title,
                        html: html,
                        icon: icon,
                        confirmButtonText: 'OK',
                        confirmButtonColor: confirmColor,
                        timer: timer,
                        timerProgressBar: true,
                        showClass: { popup: 'animate__animated animate__fadeInDown' },
                        hideClass: { popup: 'animate__animated animate__fadeOutUp' },
                    });
                }

                // ── ERROR ────────────────────────────────────────
                if (errorMsg) {
                    if (swalType === 'location_error') {
                        Swal.fire({
                            title: '📍 Di Luar Radius Lokasi!',
                            html: '<div class="text-left">'
                                + '<p class="text-gray-600 mb-3">' + errorMsg + '</p>'
                                + '<div class="bg-amber-50 border border-amber-200 rounded-lg p-3">'
                                + '<p class="text-sm text-amber-800 font-medium">💡 Tips:</p>'
                                + '<ul class="text-sm text-amber-700 mt-1 list-disc list-inside space-y-1">'
                                + '<li>Pastikan Anda berada di area kantor/proyek</li>'
                                + '<li>Periksa sinyal GPS di tempat terbuka</li>'
                                + '<li>Hubungi admin jika lokasi baru</li>'
                                + '</ul></div></div>',
                            icon: 'error',
                            confirmButtonText: 'Mengerti',
                            confirmButtonColor: '#ef4444',
                            showClass: { popup: 'animate__animated animate__shakeX' },
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: errorMsg,
                            icon: 'error',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#ef4444',
                        });
                    }
                }
            });

            // ── Dark Mode Toggle Script ───────────────────────
            document.addEventListener('DOMContentLoaded', function() {
                var themeToggleBtn = document.getElementById('theme-toggle');
                var themeToggleMobileBtn = document.getElementById('theme-toggle-mobile');
                var darkIcon = document.getElementById('theme-toggle-dark-icon');
                var lightIcon = document.getElementById('theme-toggle-light-icon');
                var darkIconMobile = document.getElementById('theme-toggle-dark-icon-mobile');
                var lightIconMobile = document.getElementById('theme-toggle-light-icon-mobile');

                // Change the icons inside the button based on previous settings
                if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    if (lightIcon) lightIcon.classList.remove('hidden');
                    if (lightIconMobile) lightIconMobile.classList.remove('hidden');
                } else {
                    if (darkIcon) darkIcon.classList.remove('hidden');
                    if (darkIconMobile) darkIconMobile.classList.remove('hidden');
                }

                function toggleTheme() {
                    // toggle icons inside button
                    if (darkIcon) darkIcon.classList.toggle('hidden');
                    if (lightIcon) lightIcon.classList.toggle('hidden');
                    if (darkIconMobile) darkIconMobile.classList.toggle('hidden');
                    if (lightIconMobile) lightIconMobile.classList.toggle('hidden');

                    // if set via local storage previously
                    if (localStorage.getItem('theme')) {
                        if (localStorage.getItem('theme') === 'light') {
                            document.documentElement.classList.add('dark');
                            localStorage.setItem('theme', 'dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                            localStorage.setItem('theme', 'light');
                        }
                    // if NOT set via local storage previously
                    } else {
                        if (document.documentElement.classList.contains('dark')) {
                            document.documentElement.classList.remove('dark');
                            localStorage.setItem('theme', 'light');
                        } else {
                            document.documentElement.classList.add('dark');
                            localStorage.setItem('theme', 'dark');
                        }
                    }
                }

                if (themeToggleBtn) {
                    themeToggleBtn.addEventListener('click', toggleTheme);
                }
                if (themeToggleMobileBtn) {
                    themeToggleMobileBtn.addEventListener('click', toggleTheme);
                }
            });
        </script>
    </body>
</html>
