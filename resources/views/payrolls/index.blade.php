<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Payroll</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            {{-- Generate Actions --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Generate Payroll</h3>
                    <div class="flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('payrolls.generateWeekly') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
                                Generate Mingguan (Level 3)
                            </button>
                        </form>
                        <form method="POST" action="{{ route('payrolls.generateMonthly') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm font-medium">
                                Generate Bulanan (Level 2)
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Filter --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4">
                    <form method="GET" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700">Tipe</label>
                            <select name="type" id="type" class="mt-1 rounded-md border-gray-300 shadow-sm sm:text-sm">
                                <option value="">Semua</option>
                                <option value="weekly" {{ request('type') == 'weekly' ? 'selected' : '' }}>Mingguan</option>
                                <option value="monthly" {{ request('type') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status" class="mt-1 rounded-md border-gray-300 shadow-sm sm:text-sm">
                                <option value="">Semua</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">Filter</button>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Posisi</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gaji Pokok</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lembur</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pot. Telat</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kasbon</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Final</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($payrolls as $payroll)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ $payroll->employee->user->name ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-500">{{ $payroll->employee->position ?? '-' }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-500">{{ $payroll->period_start->format('d/m') }} - {{ $payroll->period_end->format('d/m/Y') }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-900">Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-sm text-green-600">+{{ number_format($payroll->total_overtime, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-sm text-red-600">-{{ number_format($payroll->total_late_deduction, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-sm text-red-600">-{{ number_format($payroll->total_kasbon, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-sm font-bold text-gray-900">Rp {{ number_format($payroll->final_salary, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-sm">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $payroll->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $payroll->status === 'paid' ? 'Dibayar' : 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-sm space-x-2">
                                        <a href="{{ route('payrolls.show', $payroll) }}" class="text-indigo-600 hover:text-indigo-900">Detail</a>
                                        @if($payroll->status === 'pending')
                                            <form method="POST" action="{{ route('payrolls.markPaid', $payroll) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-green-600 hover:text-green-900" onclick="return confirm('Tandai sudah dibayar?')">Bayar</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-8 text-center text-gray-500">Tidak ada data payroll. Generate payroll terlebih dahulu.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $payrolls->appends(request()->query())->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
