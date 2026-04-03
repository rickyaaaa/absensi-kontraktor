<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Dashboard Admin
        </h2>
    </x-slot>

    {{-- ApexCharts CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash messages handled globally by SweetAlert2 in layout --}}

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- QUICK STATS CARDS                                  --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

                {{-- 1 ─ Total Karyawan --}}
                <div id="stat-total-employees" class="relative group overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 p-5 shadow-lg shadow-indigo-200/50 transition-transform duration-300 hover:-translate-y-1">
                    <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                            {{-- Heroicon: user-group --}}
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-indigo-100">Total Karyawan</p>
                            <p class="text-3xl font-extrabold text-white">{{ $totalEmployees }}</p>
                        </div>
                    </div>
                </div>

                {{-- 2 ─ Hadir Hari Ini --}}
                <div id="stat-today-attendance" class="relative group overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-5 shadow-lg shadow-emerald-200/50 transition-transform duration-300 hover:-translate-y-1">
                    <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                            {{-- Heroicon: check-badge --}}
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.745 3.745 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-emerald-100">Hadir Hari Ini</p>
                            <p class="text-3xl font-extrabold text-white">{{ $todayAttendances }}</p>
                        </div>
                    </div>
                </div>

                {{-- 3 ─ Kasbon Bulan Ini --}}
                <div id="stat-pending-kasbon" class="relative group overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 p-5 shadow-lg shadow-amber-200/50 transition-transform duration-300 hover:-translate-y-1">
                    <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                            {{-- Heroicon: banknotes --}}
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-amber-100">Kasbon Bulan Ini</p>
                            <p class="text-3xl font-extrabold text-white">{{ $pendingKasbons }}</p>
                        </div>
                    </div>
                </div>

                {{-- 4 ─ Lembur Bulan Ini --}}
                <div id="stat-overtime-month" class="relative group overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-purple-700 p-5 shadow-lg shadow-violet-200/50 transition-transform duration-300 hover:-translate-y-1">
                    <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10"></div>
                    <div class="relative flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm">
                            {{-- Heroicon: clock --}}
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wider text-violet-100">Lembur Bulan Ini</p>
                            <p class="text-3xl font-extrabold text-white">{{ $totalOvertimeHours }} <span class="text-base font-medium">jam</span></p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- ATTENDANCE BAR CHART — Last 7 Days                 --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 mb-8 overflow-hidden transition-colors">
                <div class="px-6 pt-6 pb-2 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Rekap Absensi Harian</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">7 hari terakhir</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Live Data
                    </span>
                </div>
                <div class="px-2 sm:px-4 pb-4">
                    <div id="attendance-chart" class="w-full" style="min-height: 320px;"></div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- QUICK ACTIONS                                      --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 mb-8 overflow-hidden transition-colors">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Aksi Cepat</h3>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-all duration-200 text-sm font-medium shadow-sm shadow-indigo-200 hover:shadow-md hover:shadow-indigo-300">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Tambah Karyawan
                        </a>
                        <a href="{{ route('kasbons.create') }}" class="inline-flex items-center px-4 py-2.5 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition-all duration-200 text-sm font-medium shadow-sm shadow-amber-200 hover:shadow-md hover:shadow-amber-300">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Input Kasbon
                        </a>
                        <a href="{{ route('overtimes.create') }}" class="inline-flex items-center px-4 py-2.5 bg-violet-600 text-white rounded-xl hover:bg-violet-700 transition-all duration-200 text-sm font-medium shadow-sm shadow-violet-200 hover:shadow-md hover:shadow-violet-300">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Input Lembur
                        </a>
                        <form method="POST" action="{{ route('payrolls.generateWeekly') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 transition-all duration-200 text-sm font-medium shadow-sm shadow-emerald-200 hover:shadow-md hover:shadow-emerald-300">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                Generate Payroll Mingguan
                            </button>
                        </form>
                        <form method="POST" action="{{ route('payrolls.generateMonthly') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-teal-600 text-white rounded-xl hover:bg-teal-700 transition-all duration-200 text-sm font-medium shadow-sm shadow-teal-200 hover:shadow-md hover:shadow-teal-300">
                                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Generate Payroll Bulanan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════ --}}
            {{-- RECENT ATTENDANCE TABLE                            --}}
            {{-- ═══════════════════════════════════════════════════ --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Absensi Hari Ini</h3>
                    @if($recentAttendances->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <p class="mt-2 text-gray-500 dark:text-gray-400">Belum ada data absensi hari ini.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50/80 dark:bg-gray-700/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Posisi</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jam Masuk</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jam Pulang</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Telat</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($recentAttendances as $attendance)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $attendance->employee->user->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ $attendance->employee->position ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-mono">
                                            {{ $attendance->time_in ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-mono">
                                            {{ $attendance->time_out ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($attendance->late_minutes > 0)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300">
                                                    {{ $attendance->late_minutes }} menit
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                                    Tepat Waktu
                                                </span>
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
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════ --}}
    {{-- ApexCharts Initialization                          --}}
    {{-- ═══════════════════════════════════════════════════ --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const options = {
                series: [{
                    name: 'Karyawan Hadir',
                    data: @json($chartData)
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    fontFamily: 'Figtree, sans-serif',
                    toolbar: { show: false },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800,
                        animateGradually: { enabled: true, delay: 150 },
                        dynamicAnimation: { enabled: true, speed: 350 }
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 8,
                        columnWidth: '55%',
                        distributed: false,
                    }
                },
                colors: ['#6366f1'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        type: 'vertical',
                        shadeIntensity: 0.3,
                        gradientToColors: ['#818cf8'],
                        opacityFrom: 1,
                        opacityTo: 0.85,
                        stops: [0, 100]
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '13px',
                        fontWeight: 700,
                        colors: ['#fff']
                    },
                    offsetY: -2
                },
                xaxis: {
                    categories: @json($chartLabels),
                    labels: {
                        style: {
                            fontSize: '12px',
                            fontWeight: 500,
                            colors: '#6b7280'
                        }
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontSize: '12px',
                            colors: '#9ca3af'
                        },
                        formatter: (val) => Math.round(val)
                    }
                },
                grid: {
                    borderColor: '#f3f4f6',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: false } }
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: (val) => val + ' orang',
                        title: { formatter: () => 'Hadir: ' }
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector('#attendance-chart'), options);
            chart.render();
        });
    </script>
</x-app-layout>
