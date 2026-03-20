<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Saya
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if(!$employee)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <p class="mt-2 text-yellow-800 font-medium">Data karyawan Anda belum terdaftar. Hubungi admin.</p>
                </div>
            @else
                {{-- Clock In/Out Section --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Absensi Hari Ini</h3>
                        <div class="flex flex-col sm:flex-row items-center gap-4">
                            <div class="flex-1 text-center sm:text-left">
                                <p class="text-sm text-gray-500">Status:</p>
                                @if(!$todayAttendance)
                                    <p class="text-lg font-bold text-red-600">Belum Absen</p>
                                @elseif(!$todayAttendance->time_out)
                                    <p class="text-lg font-bold text-blue-600">Sudah Masuk ({{ $todayAttendance->time_in }})</p>
                                    @if($todayAttendance->late_minutes > 0)
                                        <p class="text-sm text-red-500">Terlambat {{ $todayAttendance->late_minutes }} menit</p>
                                    @endif
                                @else
                                    <p class="text-lg font-bold text-green-600">Sudah Pulang ({{ $todayAttendance->time_out }})</p>
                                @endif
                            </div>
                            <div class="flex gap-3">
                                @if(!$todayAttendance)
                                    <form method="POST" action="{{ route('attendance.clockIn') }}" enctype="multipart/form-data">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium shadow-sm">
                                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                            Absen Masuk
                                        </button>
                                    </form>
                                @elseif(!$todayAttendance->time_out)
                                    <form method="POST" action="{{ route('attendance.clockOut') }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium shadow-sm">
                                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Absen Pulang
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-medium">
                                        ✅ Absensi Lengkap
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Employee Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Info Karyawan</h3>
                            <dl class="space-y-2">
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Posisi</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ $employee->position }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Tipe Gaji</dt>
                                    <dd class="text-sm font-medium text-gray-900">{{ ucfirst($employee->salary_type) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm text-gray-500">Gaji Harian</dt>
                                    <dd class="text-sm font-medium text-gray-900">Rp {{ number_format($employee->base_salary, 0, ',', '.') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Recent Payrolls --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Riwayat Gaji Terakhir</h3>
                            @if($recentPayrolls->isEmpty())
                                <p class="text-gray-500 text-sm">Belum ada data.</p>
                            @else
                                <div class="space-y-2">
                                    @foreach($recentPayrolls as $payroll)
                                        <div class="flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0">
                                            <div>
                                                <p class="text-sm text-gray-600">{{ $payroll->period_start->format('d/m') }} - {{ $payroll->period_end->format('d/m/Y') }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-sm font-bold text-gray-900">Rp {{ number_format($payroll->final_salary, 0, ',', '.') }}</p>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $payroll->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $payroll->status === 'paid' ? 'Dibayar' : 'Pending' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Recent Attendance --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Absensi (7 Hari Terakhir)</h3>
                        @if($recentAttendances->isEmpty())
                            <p class="text-gray-500 text-sm text-center py-4">Belum ada data absensi.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Masuk</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pulang</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @foreach($recentAttendances as $att)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $att->date->format('d/m/Y') }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $att->time_in ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">{{ $att->time_out ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                @if($att->late_minutes > 0)
                                                    <span class="text-red-600 font-medium">Telat {{ $att->late_minutes }}m</span>
                                                @else
                                                    <span class="text-green-600 font-medium">Tepat Waktu</span>
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
</x-app-layout>
