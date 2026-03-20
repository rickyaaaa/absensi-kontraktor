<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Karyawan: {{ $employee->user->name }}
            </h2>
            <a href="{{ route('employees.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Kembali</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Employee Info --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Karyawan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Nama</p>
                            <p class="font-medium text-gray-900">{{ $employee->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium text-gray-900">{{ $employee->user->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Level</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $employee->role_level === 2 ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                Level {{ $employee->role_level }} - {{ $employee->role_level === 2 ? 'Staff' : 'Worker' }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Posisi</p>
                            <p class="font-medium text-gray-900">{{ $employee->position }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Gaji Harian</p>
                            <p class="font-medium text-gray-900">Rp {{ number_format($employee->base_salary, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tipe Payroll</p>
                            <p class="font-medium text-gray-900">{{ ucfirst($employee->salary_type) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Attendance --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Absensi (10 Terakhir)</h3>
                    @if($employee->attendances->isEmpty())
                        <p class="text-gray-500 text-sm">Belum ada data.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Masuk</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pulang</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($employee->attendances->sortByDesc('date')->take(10) as $att)
                                <tr>
                                    <td class="px-4 py-3 text-sm">{{ $att->date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $att->time_in ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $att->time_out ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($att->late_minutes > 0)
                                            <span class="text-red-600">{{ $att->late_minutes }} menit</span>
                                        @else
                                            <span class="text-green-600">Tepat Waktu</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            {{-- Recent Payrolls --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Payroll</h3>
                    @if($employee->payrolls->isEmpty())
                        <p class="text-gray-500 text-sm">Belum ada data.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gaji Pokok</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lembur</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pot. Telat</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kasbon</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Final</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($employee->payrolls->sortByDesc('period_end') as $payroll)
                                <tr>
                                    <td class="px-4 py-3 text-sm">{{ $payroll->period_start->format('d/m') }} - {{ $payroll->period_end->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-sm">Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-green-600">+Rp {{ number_format($payroll->total_overtime, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-red-600">-Rp {{ number_format($payroll->total_late_deduction, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm text-red-600">-Rp {{ number_format($payroll->total_kasbon, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm font-bold">Rp {{ number_format($payroll->final_salary, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $payroll->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $payroll->status === 'paid' ? 'Dibayar' : 'Pending' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
