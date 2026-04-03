<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard Saya
        </h2>
    </x-slot>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- Inline styles for pulse / glow animations  --}}
    {{-- ═══════════════════════════════════════════ --}}
    <style>
        /* Pulsing glow ring around attendance button */
        @keyframes pulseGlow {
            0%   { transform: scale(1);   opacity: 0.7; }
            50%  { transform: scale(1.25); opacity: 0; }
            100% { transform: scale(1);   opacity: 0; }
        }
        @keyframes pulseGlowDelay {
            0%   { transform: scale(1);   opacity: 0.5; }
            50%  { transform: scale(1.35); opacity: 0; }
            100% { transform: scale(1);   opacity: 0; }
        }
        .pulse-ring {
            animation: pulseGlow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        .pulse-ring-delay {
            animation: pulseGlowDelay 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            animation-delay: 0.4s;
        }

        /* Shimmer effect on clock digits */
        @keyframes clockShimmer {
            0%   { text-shadow: 0 0 8px rgba(99,102,241,0.3); }
            50%  { text-shadow: 0 0 20px rgba(99,102,241,0.6), 0 0 40px rgba(99,102,241,0.2); }
            100% { text-shadow: 0 0 8px rgba(99,102,241,0.3); }
        }
        .clock-glow {
            animation: clockShimmer 2s ease-in-out infinite;
        }

        /* Subtle float animation for the circle button */
        @keyframes floatBtn {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-6px); }
        }
        .float-btn {
            animation: floatBtn 3s ease-in-out infinite;
        }

        /* Colon blink */
        @keyframes colonBlink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.3; }
        }
        .colon-blink {
            animation: colonBlink 1s step-start infinite;
        }
    </style>

    <div class="py-4 sm:py-6">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:max-w-2xl">
            {{-- Flash messages handled globally by SweetAlert2 in layout --}}

            @if(!$employee)
                <div class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 rounded-2xl p-6 text-center transition-colors">
                    <svg class="mx-auto h-12 w-12 text-amber-400 dark:text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <p class="mt-3 text-amber-800 dark:text-amber-300 font-medium">Data karyawan Anda belum terdaftar. Hubungi admin.</p>
                </div>
            @else

                {{-- ══════════════════════════════════════════════ --}}
                {{-- REAL-TIME DIGITAL CLOCK                       --}}
                {{-- ══════════════════════════════════════════════ --}}
                <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 mb-6 shadow-xl shadow-indigo-900/20 text-center overflow-hidden relative">
                    {{-- Decorative blurred circles --}}
                    <div class="absolute -top-10 -left-10 h-40 w-40 rounded-full bg-indigo-500/10 blur-2xl"></div>
                    <div class="absolute -bottom-10 -right-10 h-40 w-40 rounded-full bg-violet-500/10 blur-2xl"></div>

                    <p class="text-xs font-medium uppercase tracking-widest text-indigo-300/80 mb-2">Waktu Server — Jakarta (WIB)</p>

                    <div class="flex items-center justify-center gap-1 sm:gap-2">
                        <span id="clock-hours" class="text-5xl sm:text-6xl font-extrabold text-white font-mono clock-glow tracking-wider">--</span>
                        <span id="clock-colon-1" class="text-5xl sm:text-6xl font-extrabold text-indigo-400 font-mono colon-blink">:</span>
                        <span id="clock-minutes" class="text-5xl sm:text-6xl font-extrabold text-white font-mono clock-glow tracking-wider">--</span>
                        <span id="clock-colon-2" class="text-5xl sm:text-6xl font-extrabold text-indigo-400 font-mono colon-blink">:</span>
                        <span id="clock-seconds" class="text-4xl sm:text-5xl font-bold text-indigo-300 font-mono tracking-wider">--</span>
                    </div>

                    <p id="live-date" class="mt-3 text-sm text-indigo-200/70 font-medium">-</p>
                </div>

                {{-- ══════════════════════════════════════════════ --}}
                {{-- ATTENDANCE CIRCLE BUTTON                      --}}
                {{-- ══════════════════════════════════════════════ --}}
                <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 mb-6 overflow-hidden transition-colors">
                    <div class="p-6 sm:p-8">

                        {{-- Status Badge --}}
                        <div class="text-center mb-6">
                            @if(!$todayAttendance)
                                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-red-50 text-red-600 text-sm font-semibold border border-red-100">
                                    <span class="h-2 w-2 rounded-full bg-red-500 animate-pulse"></span>
                                    Belum Absen
                                </span>
                            @elseif(!$todayAttendance->time_out)
                                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 text-blue-600 text-sm font-semibold border border-blue-100">
                                    <span class="h-2 w-2 rounded-full bg-blue-500 animate-pulse"></span>
                                    Sudah Masuk — {{ $todayAttendance->time_in }}
                                </span>
                                @if($todayAttendance->late_minutes > 0)
                                    <p class="mt-1 text-xs text-red-500 font-medium">Terlambat {{ $todayAttendance->late_minutes }} menit</p>
                                @endif
                                @if($todayAttendance->location_status === 'luar_lokasi')
                                    <p class="mt-1 text-xs text-orange-500 font-medium">⚠️ Luar Lokasi</p>
                                @endif
                            @else
                                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-green-50 text-green-600 text-sm font-semibold border border-green-100">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Sudah Pulang — {{ $todayAttendance->time_out }}
                                </span>
                            @endif
                        </div>

                        {{-- GPS Status --}}
                        <div id="gps-status" class="text-center mb-6">
                            <div class="inline-flex items-center gap-2 text-sm text-gray-400">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span id="gps-info">Mendeteksi GPS...</span>
                            </div>
                        </div>

                        @if(!$todayAttendance || !$todayAttendance->time_out)
                            <div id="attendance-section">

                                {{-- Location Selector --}}
                                <div class="mb-6">
                                    <label for="location-select" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        📍 Pilih Lokasi Absensi
                                    </label>
                                    <select id="location-select" onchange="onLocationSelect(this)"
                                        class="block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-3 transition-colors text-gray-900 dark:text-gray-100">
                                        <option value="" disabled selected>-- Pilih Lokasi --</option>
                                        @isset($locations)
                                            @foreach($locations as $loc)
                                                <option value="{{ $loc->id }}" data-name="{{ $loc->name }}" data-type="{{ $loc->type }}">
                                                    {{ $loc->type === 'kantor_pusat' ? '🏢' : '🏗️' }} {{ $loc->name }}
                                                    ({{ $loc->type === 'kantor_pusat' ? 'Kantor Pusat' : 'Project' }})
                                                </option>
                                            @endforeach
                                        @endisset
                                    </select>
                                </div>

                                {{-- ── The Big Circle Button ── --}}
                                <div class="flex justify-center my-8">
                                    <div class="relative float-btn">
                                        {{-- Pulse rings --}}
                                        @if(!$todayAttendance)
                                            <div class="absolute inset-0 rounded-full bg-emerald-400/30 pulse-ring"></div>
                                            <div class="absolute inset-0 rounded-full bg-emerald-400/20 pulse-ring-delay"></div>
                                        @else
                                            <div class="absolute inset-0 rounded-full bg-rose-400/30 pulse-ring"></div>
                                            <div class="absolute inset-0 rounded-full bg-rose-400/20 pulse-ring-delay"></div>
                                        @endif

                                        @if(!$todayAttendance)
                                            {{-- Clock IN button --}}
                                            <button type="button" id="btn-start-clockin"
                                                onclick="startAttendance('clockin')"
                                                disabled
                                                class="relative z-10 flex flex-col items-center justify-center h-40 w-40 sm:h-48 sm:w-48 rounded-full
                                                       bg-gradient-to-br from-emerald-400 to-emerald-600
                                                       text-white shadow-2xl shadow-emerald-300/50
                                                       hover:from-emerald-500 hover:to-emerald-700 hover:shadow-emerald-400/60
                                                       active:scale-95 transition-all duration-300
                                                       disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:shadow-emerald-300/50
                                                       focus:outline-none focus:ring-4 focus:ring-emerald-300/50">
                                                <svg class="h-10 w-10 sm:h-12 sm:w-12 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                                </svg>
                                                <span class="text-lg sm:text-xl font-bold tracking-wide">MASUK</span>
                                                <span class="text-xs font-medium opacity-80 mt-0.5">Tap untuk absen</span>
                                            </button>
                                        @elseif(!$todayAttendance->time_out)
                                            {{-- Clock OUT button --}}
                                            <button type="button" id="btn-start-clockout"
                                                onclick="startAttendance('clockout')"
                                                disabled
                                                class="relative z-10 flex flex-col items-center justify-center h-40 w-40 sm:h-48 sm:w-48 rounded-full
                                                       bg-gradient-to-br from-rose-400 to-rose-600
                                                       text-white shadow-2xl shadow-rose-300/50
                                                       hover:from-rose-500 hover:to-rose-700 hover:shadow-rose-400/60
                                                       active:scale-95 transition-all duration-300
                                                       disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:shadow-rose-300/50
                                                       focus:outline-none focus:ring-4 focus:ring-rose-300/50">
                                                <svg class="h-10 w-10 sm:h-12 sm:w-12 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                                </svg>
                                                <span class="text-lg sm:text-xl font-bold tracking-wide">PULANG</span>
                                                <span class="text-xs font-medium opacity-80 mt-0.5">Tap untuk absen</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                {{-- Camera Preview --}}
                                <div id="camera-container" class="mb-4 hidden">
                                    <div class="relative max-w-sm mx-auto">
                                        <video id="camera-preview" autoplay playsinline class="w-full rounded-2xl border-2 border-indigo-200 shadow-lg" style="transform: scaleX(-1);"></video>
                                        <canvas id="camera-canvas" class="hidden"></canvas>
                                        <div class="absolute bottom-3 left-0 right-0 text-center">
                                            <button type="button" id="btn-capture" onclick="captureSelfie()"
                                                class="inline-flex items-center px-6 py-3 bg-white/90 backdrop-blur text-indigo-700 rounded-full shadow-lg hover:bg-white font-semibold transition-all text-sm">
                                                📸 Ambil Foto
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Captured Photo Preview --}}
                                <div id="photo-preview" class="mb-4 hidden">
                                    <div class="max-w-sm mx-auto">
                                        <img id="captured-photo" class="w-full rounded-2xl border-2 border-green-200 shadow-lg" style="transform: scaleX(-1);">
                                        <div class="text-center mt-3">
                                            <button type="button" onclick="retakeSelfie()" class="text-sm text-indigo-600 hover:text-indigo-800 font-semibold transition-colors">
                                                🔄 Foto Ulang
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Hidden Submit Forms --}}
                                @if(!$todayAttendance)
                                    <form method="POST" action="{{ route('attendance.clockIn') }}" id="form-clockin" class="hidden text-center">
                                        @csrf
                                        <input type="hidden" name="selfie_data" id="selfie-data-in">
                                        <input type="hidden" name="latitude" id="lat-in">
                                        <input type="hidden" name="longitude" id="lng-in">
                                        <input type="hidden" name="location" id="loc-in">
                                        <button type="submit" class="inline-flex items-center px-8 py-3 bg-emerald-600 text-white rounded-2xl hover:bg-emerald-700 transition-all duration-200 font-semibold shadow-lg shadow-emerald-200 text-sm">
                                            ✅ Kirim Absen Masuk
                                        </button>
                                    </form>
                                @elseif(!$todayAttendance->time_out)
                                    <form method="POST" action="{{ route('attendance.clockOut') }}" id="form-clockout" class="hidden text-center">
                                        @csrf
                                        <input type="hidden" name="selfie_data" id="selfie-data-out">
                                        <input type="hidden" name="latitude" id="lat-out">
                                        <input type="hidden" name="longitude" id="lng-out">
                                        <input type="hidden" name="location" id="loc-out">
                                        <button type="submit" class="inline-flex items-center px-8 py-3 bg-rose-600 text-white rounded-2xl hover:bg-rose-700 transition-all duration-200 font-semibold shadow-lg shadow-rose-200 text-sm">
                                            ✅ Kirim Absen Pulang
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @else
                            {{-- Attendance Complete --}}
                            <div class="flex justify-center my-8">
                                <div class="flex flex-col items-center justify-center h-40 w-40 sm:h-48 sm:w-48 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 border-2 border-gray-200 dark:border-gray-600 transition-colors">
                                    <svg class="h-12 w-12 text-emerald-500 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">Selesai</span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Absensi Lengkap</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════ --}}
                {{-- EMPLOYEE INFO + PAYROLL                       --}}
                {{-- ══════════════════════════════════════════════ --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    {{-- Employee Info Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 transition-colors">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                            <svg class="h-4 w-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            Info Karyawan
                        </h3>
                        <dl class="space-y-2.5">
                            <div class="flex justify-between items-center">
                                <dt class="text-xs text-gray-400 dark:text-gray-500">Posisi</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $employee->position }}</dd>
                            </div>
                            <div class="flex justify-between items-center">
                                <dt class="text-xs text-gray-400 dark:text-gray-500">Tipe Gaji</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($employee->salary_type) }}</dd>
                            </div>
                            <div class="flex justify-between items-center">
                                <dt class="text-xs text-gray-400 dark:text-gray-500">Gaji Harian</dt>
                                <dd class="text-sm font-bold text-indigo-600 dark:text-indigo-400">Rp {{ number_format($employee->base_salary, 0, ',', '.') }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Recent Payroll Card --}}
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 transition-colors">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                            <svg class="h-4 w-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            </svg>
                            Riwayat Gaji
                        </h3>
                        @if($recentPayrolls->isEmpty())
                            <p class="text-gray-400 dark:text-gray-500 text-xs text-center py-4">Belum ada data.</p>
                        @else
                            <div class="space-y-2">
                                @foreach($recentPayrolls as $payroll)
                                    <div class="flex justify-between items-center py-1.5 border-b border-gray-50 dark:border-gray-700 last:border-b-0">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $payroll->period_start->format('d/m') }} - {{ $payroll->period_end->format('d/m/Y') }}</p>
                                        <div class="text-right">
                                            <p class="text-xs font-bold text-gray-900 dark:text-gray-100">Rp {{ number_format($payroll->final_salary, 0, ',', '.') }}</p>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $payroll->status === 'paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                                {{ $payroll->status === 'paid' ? 'Dibayar' : 'Pending' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ══════════════════════════════════════════════ --}}
                {{-- RECENT ATTENDANCE TABLE                       --}}
                {{-- ══════════════════════════════════════════════ --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors">
                    <div class="p-5">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                            <svg class="h-4 w-4 text-violet-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                            </svg>
                            Riwayat 7 Hari Terakhir
                        </h3>
                        @if($recentAttendances->isEmpty())
                            <p class="text-gray-400 dark:text-gray-500 text-xs text-center py-6">Belum ada data absensi.</p>
                        @else
                            <div class="overflow-x-auto -mx-5">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-gray-100 dark:border-gray-700">
                                            <th class="px-5 py-2 text-left text-xs font-semibold text-gray-400 uppercase">Tanggal</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-400 uppercase">Masuk</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-400 uppercase">Pulang</th>
                                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-400 uppercase">Status</th>
                                            <th class="px-5 py-2 text-left text-xs font-semibold text-gray-400 uppercase">Lokasi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                                        @foreach($recentAttendances as $att)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors">
                                            <td class="px-5 py-2.5 whitespace-nowrap text-gray-900 dark:text-gray-100 font-medium">{{ $att->date->format('d/m/Y') }}</td>
                                            <td class="px-3 py-2.5 whitespace-nowrap text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $att->time_in ?? '-' }}</td>
                                            <td class="px-3 py-2.5 whitespace-nowrap text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $att->time_out ?? '-' }}</td>
                                            <td class="px-3 py-2.5 whitespace-nowrap">
                                                @if($att->late_minutes > 0)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 text-red-600 dark:bg-red-900/40 dark:text-red-300">Telat {{ $att->late_minutes }}m</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-50 text-green-600 dark:bg-green-900/40 dark:text-green-300">Tepat Waktu</span>
                                                @endif
                                            </td>
                                            <td class="px-5 py-2.5 whitespace-nowrap text-xs">
                                                @if($att->location_status === 'valid')
                                                    <span class="text-green-600 dark:text-green-400">✅ {{ $att->location }}</span>
                                                @elseif($att->location_status === 'luar_lokasi')
                                                    <span class="text-orange-500">⚠️ Luar</span>
                                                @else
                                                    <span class="text-gray-300">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- JavaScript — Camera, GPS, Real-time Clock          --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <script>
        let currentStream = null;
        let currentMode = null;
        let userLatitude = null;
        let userLongitude = null;
        let selectedLocationName = '';

        // ============ LOCATION SELECTOR ============
        function onLocationSelect(select) {
            const option = select.options[select.selectedIndex];
            selectedLocationName = option.dataset.name || '';

            const btnIn = document.getElementById('btn-start-clockin');
            const btnOut = document.getElementById('btn-start-clockout');
            if (btnIn) btnIn.disabled = false;
            if (btnOut) btnOut.disabled = false;
        }

        // ============ REAL-TIME CLOCK (Asia/Jakarta) ============
        function updateClock() {
            const now = new Date();
            const jakartaTime = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Jakarta' }));
            const hours   = String(jakartaTime.getHours()).padStart(2, '0');
            const minutes = String(jakartaTime.getMinutes()).padStart(2, '0');
            const seconds = String(jakartaTime.getSeconds()).padStart(2, '0');

            const elH = document.getElementById('clock-hours');
            const elM = document.getElementById('clock-minutes');
            const elS = document.getElementById('clock-seconds');

            if (elH) elH.textContent = hours;
            if (elM) elM.textContent = minutes;
            if (elS) elS.textContent = seconds;

            const days   = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const dateEl = document.getElementById('live-date');
            if (dateEl) {
                dateEl.textContent = `${days[jakartaTime.getDay()]}, ${jakartaTime.getDate()} ${months[jakartaTime.getMonth()]} ${jakartaTime.getFullYear()}`;
            }
        }

        setInterval(updateClock, 1000);
        updateClock();

        // ============ GPS DETECTION ============
        function getGPSPosition() {
            const gpsInfo = document.getElementById('gps-info');
            if (!gpsInfo) return;

            if (!navigator.geolocation) {
                gpsInfo.textContent = '❌ GPS tidak didukung';
                gpsInfo.className = 'text-sm font-medium text-red-600';
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    userLatitude = position.coords.latitude;
                    userLongitude = position.coords.longitude;
                    gpsInfo.textContent = `✅ ${userLatitude.toFixed(6)}, ${userLongitude.toFixed(6)}`;
                    gpsInfo.parentElement.className = 'inline-flex items-center gap-2 text-sm text-green-600';
                    // Replace spinner with location pin
                    const spinner = gpsInfo.previousElementSibling;
                    if (spinner && spinner.tagName === 'svg') {
                        spinner.outerHTML = '<svg class="h-4 w-4 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>';
                    }
                },
                (error) => {
                    gpsInfo.textContent = '❌ Izinkan akses lokasi';
                    gpsInfo.parentElement.className = 'inline-flex items-center gap-2 text-sm text-red-500';
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        getGPSPosition();
        setInterval(getGPSPosition, 30000);

        // ============ CAMERA / SELFIE ============
        async function startAttendance(mode) {
            currentMode = mode;

            const btnIn = document.getElementById('btn-start-clockin');
            const btnOut = document.getElementById('btn-start-clockout');
            if (btnIn) btnIn.classList.add('hidden');
            if (btnOut) btnOut.classList.add('hidden');

            // Hide pulse rings
            const pulseRings = document.querySelectorAll('.pulse-ring, .pulse-ring-delay');
            pulseRings.forEach(el => el.style.display = 'none');

            document.getElementById('camera-container').classList.remove('hidden');
            document.getElementById('photo-preview').classList.add('hidden');

            try {
                currentStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                    audio: false
                });
                document.getElementById('camera-preview').srcObject = currentStream;
            } catch (err) {
                Swal.fire({ title: 'Kamera Tidak Tersedia', text: 'Pastikan izin kamera sudah diberikan.', icon: 'warning', confirmButtonColor: '#6366f1' });
                resetCamera();
            }
        }

        function captureSelfie() {
            const video = document.getElementById('camera-preview');
            const canvas = document.getElementById('camera-canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const ctx = canvas.getContext('2d');
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);

            document.getElementById('captured-photo').src = dataUrl;
            document.getElementById('camera-container').classList.add('hidden');
            document.getElementById('photo-preview').classList.remove('hidden');

            if (currentStream) {
                currentStream.getTracks().forEach(t => t.stop());
            }

            getGPSPosition();

            if (currentMode === 'clockin') {
                document.getElementById('selfie-data-in').value = dataUrl;
                document.getElementById('lat-in').value = userLatitude || '';
                document.getElementById('lng-in').value = userLongitude || '';
                document.getElementById('loc-in').value = selectedLocationName || `${userLatitude},${userLongitude}`;
                document.getElementById('form-clockin').classList.remove('hidden');
            } else {
                document.getElementById('selfie-data-out').value = dataUrl;
                document.getElementById('lat-out').value = userLatitude || '';
                document.getElementById('lng-out').value = userLongitude || '';
                document.getElementById('loc-out').value = selectedLocationName || `${userLatitude},${userLongitude}`;
                document.getElementById('form-clockout').classList.remove('hidden');
            }
        }

        function retakeSelfie() {
            document.getElementById('photo-preview').classList.add('hidden');
            if (currentMode === 'clockin') {
                document.getElementById('form-clockin').classList.add('hidden');
            } else {
                document.getElementById('form-clockout').classList.add('hidden');
            }
            startAttendance(currentMode);
        }

        function resetCamera() {
            if (currentStream) {
                currentStream.getTracks().forEach(t => t.stop());
            }
            document.getElementById('camera-container').classList.add('hidden');
            document.getElementById('photo-preview').classList.add('hidden');

            const btnIn = document.getElementById('btn-start-clockin');
            const btnOut = document.getElementById('btn-start-clockout');
            if (btnIn) btnIn.classList.remove('hidden');
            if (btnOut) btnOut.classList.remove('hidden');

            // Restore pulse rings
            const pulseRings = document.querySelectorAll('.pulse-ring, .pulse-ring-delay');
            pulseRings.forEach(el => el.style.display = '');
        }
    </script>
</x-app-layout>
