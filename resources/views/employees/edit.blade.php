<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Karyawan: {{ $employee->user->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('employees.update', $employee) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $employee->user->name) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $employee->user->email) }}" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Password <span class="text-gray-400">(Kosongkan jika tidak diubah)</span></label>
                                <input type="password" name="password" id="password"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="role_level" class="block text-sm font-medium text-gray-700">Level</label>
                                <select name="role_level" id="role_level" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    onchange="updatePositions()">
                                    <option value="">Pilih Level</option>
                                    <option value="2" {{ old('role_level', $employee->role_level) == 2 ? 'selected' : '' }}>Level 2 - Staff (Bulanan)</option>
                                    <option value="3" {{ old('role_level', $employee->role_level) == 3 ? 'selected' : '' }}>Level 3 - Worker (Mingguan)</option>
                                </select>
                            </div>

                            <div>
                                <label for="position" class="block text-sm font-medium text-gray-700">Posisi</label>
                                <select name="position" id="position" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    onchange="updateSalaryPreview()">
                                    <option value="">Pilih Level terlebih dahulu</option>
                                </select>
                            </div>

                            <div id="salary-preview" class="hidden bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                                <p class="text-sm text-indigo-700">
                                    Gaji harian: <strong id="salary-amount">-</strong>
                                </p>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <a href="{{ route('employees.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium">
                                    Batal
                                </a>
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                                    Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const levelPositions = @json($levelPositions);
        const salaryMap = @json($salaryMap);
        const currentPosition = '{{ old("position", $employee->position) }}';

        function updatePositions() {
            const level = document.getElementById('role_level').value;
            const positionSelect = document.getElementById('position');
            positionSelect.innerHTML = '<option value="">Pilih Posisi</option>';

            if (levelPositions[level]) {
                levelPositions[level].forEach(pos => {
                    const option = document.createElement('option');
                    option.value = pos;
                    option.textContent = pos;
                    if (currentPosition === pos) option.selected = true;
                    positionSelect.appendChild(option);
                });
            }
            updateSalaryPreview();
        }

        function updateSalaryPreview() {
            const position = document.getElementById('position').value;
            const preview = document.getElementById('salary-preview');
            const amount = document.getElementById('salary-amount');

            if (salaryMap[position]) {
                preview.classList.remove('hidden');
                amount.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(salaryMap[position]);
            } else {
                preview.classList.add('hidden');
            }
        }

        // Init on page load
        if (document.getElementById('role_level').value) {
            updatePositions();
        }
    </script>
</x-app-layout>
