<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detail Payroll
            </h2>
            <a href="{{ route('payrolls.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">← Kembali</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{-- Employee Info --}}
                    <div class="border-b border-gray-200 pb-4 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $payroll->employee->user->name ?? '-' }}</h3>
                        <p class="text-sm text-gray-500">{{ $payroll->employee->position ?? '-' }} • Level {{ $payroll->employee->role_level ?? '-' }}</p>
                    </div>

                    {{-- Payroll Details --}}
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Periode</span>
                            <span class="font-medium">{{ $payroll->period_start->format('d/m/Y') }} - {{ $payroll->period_end->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Gaji Pokok (hari kerja × harian)</span>
                            <span class="font-medium">Rp {{ number_format($payroll->base_salary, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Total Lembur</span>
                            <span class="font-medium text-green-600">+ Rp {{ number_format($payroll->total_overtime, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Potongan Keterlambatan</span>
                            <span class="font-medium text-red-600">- Rp {{ number_format($payroll->total_late_deduction, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-600">Potongan Kasbon</span>
                            <span class="font-medium text-red-600">- Rp {{ number_format($payroll->total_kasbon, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between py-3 bg-indigo-50 rounded-lg px-4 mt-4">
                            <span class="text-lg font-bold text-indigo-900">Total Gaji Bersih</span>
                            <span class="text-lg font-bold text-indigo-900">Rp {{ number_format($payroll->final_salary, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Status & Action --}}
                    <div class="mt-6 flex justify-between items-center">
                        <div>
                            <span class="text-sm text-gray-500">Status:</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $payroll->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $payroll->status === 'paid' ? '✅ Dibayar' : '⏳ Pending' }}
                            </span>
                        </div>
                        @if($payroll->status === 'pending')
                            <form method="POST" action="{{ route('payrolls.markPaid', $payroll) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium" onclick="return confirm('Tandai payroll ini sudah dibayar?')">
                                    Tandai Dibayar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
