<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Riwayat Absensi
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Masuk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pulang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($attendances as $att)
                                <tr>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $att->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $att->time_in ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $att->time_out ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        @if($att->late_minutes > 0)
                                            <span class="text-red-600 font-medium">Telat {{ $att->late_minutes }} menit</span>
                                        @else
                                            <span class="text-green-600 font-medium">Tepat Waktu</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">Tidak ada data.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $attendances->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
