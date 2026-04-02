<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Manajemen Lokasi (Kantor/Proyek)
            </h2>
            <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Lokasi
            </button>
        </div>
    </x-slot>

    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .leaflet-container { z-index: 1; }
        #addMap, #editMap { height: 300px; width: 100%; border-radius: 0.5rem; margin-top: 0.5rem; }
        .search-box { position: relative; }
        .search-results {
            position: absolute; z-index: 1000; background: white; border: 1px solid #d1d5db;
            border-radius: 0.375rem; max-height: 200px; overflow-y: auto; width: 100%;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,.1);
        }
        .search-results div {
            padding: 8px 12px; cursor: pointer; font-size: 0.875rem; border-bottom: 1px solid #f3f4f6;
        }
        .search-results div:hover { background: #eef2ff; }
        .modal-overlay {
            background: rgba(0,0,0,0.5); backdrop-filter: blur(2px);
        }
    </style>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" id="flash-success">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>- {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lokasi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Koordinat</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Radius (m)</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($locations as $loc)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $loc->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="https://www.google.com/maps?q={{ $loc->latitude }},{{ $loc->longitude }}" target="_blank" class="text-indigo-600 hover:underline">
                                            {{ $loc->latitude }}, {{ $loc->longitude }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loc->radius }} m</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $loc->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $loc->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                        <button onclick="openEditModal({{ $loc->toJson() }})" class="text-blue-600 hover:text-blue-900 font-medium">Edit</button>

                                        <form method="POST" action="{{ route('locations.toggle', $loc) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-yellow-600 hover:text-yellow-900 font-medium">{{ $loc->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                        </form>

                                        <form method="POST" action="{{ route('locations.destroy', $loc) }}" class="inline" onsubmit="return confirm('Yakin hapus lokasi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada data lokasi. Klik "Tambah Lokasi" untuk menambahkan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ====== MODAL TAMBAH LOKASI ====== --}}
    <div id="addLocationModal" class="hidden fixed z-50 inset-0 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 modal-overlay" onclick="closeAddModal()"></div>
            <div class="relative bg-white rounded-xl shadow-2xl transform transition-all sm:max-w-2xl w-full">
                <form action="{{ route('locations.store') }}" method="POST">
                    @csrf
                    <div class="px-6 pt-6 pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Tambah Lokasi Baru</h3>
                            <button type="button" onclick="closeAddModal()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi</label>
                                <input type="text" name="name" required placeholder="cth: Proyek A, Kantor Cabang"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            {{-- Search alamat --}}
                            <div class="search-box">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Alamat / Lokasi</label>
                                <input type="text" id="add_search" placeholder="Ketik alamat lalu tekan Enter..."
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    onkeydown="if(event.key==='Enter'){event.preventDefault(); searchAddress('add')}">
                                <div id="add_search_results" class="search-results hidden"></div>
                            </div>

                            {{-- Peta --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Lokasi di Peta <span class="text-xs text-gray-400">(klik atau geser pin)</span></label>
                                <div id="addMap"></div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                                    <input type="text" name="latitude" id="add_lat" required readonly
                                        class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                                    <input type="text" name="longitude" id="add_lng" required readonly
                                        class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Radius Toleransi (Meter)</label>
                                <input type="number" name="radius" value="100" min="10" required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-3 rounded-b-xl">
                        <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ====== MODAL EDIT LOKASI ====== --}}
    <div id="editLocationModal" class="hidden fixed z-50 inset-0 overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 modal-overlay" onclick="closeEditModal()"></div>
            <div class="relative bg-white rounded-xl shadow-2xl transform transition-all sm:max-w-2xl w-full">
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="px-6 pt-6 pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Edit Lokasi</h3>
                            <button type="button" onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi</label>
                                <input type="text" name="name" id="edit_name" required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            {{-- Search alamat --}}
                            <div class="search-box">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cari Alamat / Lokasi</label>
                                <input type="text" id="edit_search" placeholder="Ketik alamat lalu tekan Enter..."
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    onkeydown="if(event.key==='Enter'){event.preventDefault(); searchAddress('edit')}">
                                <div id="edit_search_results" class="search-results hidden"></div>
                            </div>

                            {{-- Peta --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Lokasi di Peta <span class="text-xs text-gray-400">(klik atau geser pin)</span></label>
                                <div id="editMap"></div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
                                    <input type="text" name="latitude" id="edit_lat" required readonly
                                        class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
                                    <input type="text" name="longitude" id="edit_lng" required readonly
                                        class="block w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Radius Toleransi (Meter)</label>
                                <input type="number" name="radius" id="edit_radius" min="10" required
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-3 rounded-b-xl">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Default center: Jakarta
        const defaultLat = -6.2088;
        const defaultLng = 106.8456;

        let addMap, addMarker;
        let editMap, editMarker;
        let searchTimeout;

        // =========== ADD MAP ===========
        function initAddMap() {
            if (addMap) { addMap.remove(); }
            addMap = L.map('addMap').setView([defaultLat, defaultLng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(addMap);

            addMarker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(addMap);
            updateAddInputs(defaultLat, defaultLng);

            addMarker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                updateAddInputs(pos.lat, pos.lng);
            });

            addMap.on('click', function (e) {
                addMarker.setLatLng(e.latlng);
                updateAddInputs(e.latlng.lat, e.latlng.lng);
            });
        }

        function updateAddInputs(lat, lng) {
            document.getElementById('add_lat').value = parseFloat(lat).toFixed(7);
            document.getElementById('add_lng').value = parseFloat(lng).toFixed(7);
        }

        // =========== EDIT MAP ===========
        function initEditMap(lat, lng) {
            if (editMap) { editMap.remove(); }
            editMap = L.map('editMap').setView([lat, lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(editMap);

            editMarker = L.marker([lat, lng], { draggable: true }).addTo(editMap);

            editMarker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                updateEditInputs(pos.lat, pos.lng);
            });

            editMap.on('click', function (e) {
                editMarker.setLatLng(e.latlng);
                updateEditInputs(e.latlng.lat, e.latlng.lng);
            });
        }

        function updateEditInputs(lat, lng) {
            document.getElementById('edit_lat').value = parseFloat(lat).toFixed(7);
            document.getElementById('edit_lng').value = parseFloat(lng).toFixed(7);
        }

        // =========== SEARCH ADDRESS (Nominatim / OpenStreetMap) ===========
        function searchAddress(mode) {
            const query = document.getElementById(mode + '_search').value.trim();
            if (!query) return;

            const resultsDiv = document.getElementById(mode + '_search_results');
            resultsDiv.innerHTML = '<div class="text-gray-400 px-3 py-2 text-sm">Mencari...</div>';
            resultsDiv.classList.remove('hidden');

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&countrycodes=id`)
                .then(r => r.json())
                .then(data => {
                    resultsDiv.innerHTML = '';
                    if (data.length === 0) {
                        resultsDiv.innerHTML = '<div class="text-gray-400 px-3 py-2 text-sm">Tidak ditemukan.</div>';
                        return;
                    }
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.textContent = item.display_name;
                        div.onclick = function () {
                            const lat = parseFloat(item.lat);
                            const lng = parseFloat(item.lon);
                            if (mode === 'add') {
                                addMap.setView([lat, lng], 16);
                                addMarker.setLatLng([lat, lng]);
                                updateAddInputs(lat, lng);
                            } else {
                                editMap.setView([lat, lng], 16);
                                editMarker.setLatLng([lat, lng]);
                                updateEditInputs(lat, lng);
                            }
                            resultsDiv.classList.add('hidden');
                            document.getElementById(mode + '_search').value = item.display_name;
                        };
                        resultsDiv.appendChild(div);
                    });
                })
                .catch(() => {
                    resultsDiv.innerHTML = '<div class="text-red-500 px-3 py-2 text-sm">Gagal mencari. Coba lagi.</div>';
                });
        }

        // Hide search results when clicking outside
        document.addEventListener('click', function(e) {
            ['add_search_results', 'edit_search_results'].forEach(id => {
                const el = document.getElementById(id);
                if (el && !el.parentElement.contains(e.target)) {
                    el.classList.add('hidden');
                }
            });
        });

        // =========== MODAL OPEN/CLOSE ===========
        function openAddModal() {
            document.getElementById('addLocationModal').classList.remove('hidden');
            setTimeout(() => initAddMap(), 100);
        }
        function closeAddModal() {
            document.getElementById('addLocationModal').classList.add('hidden');
        }

        function openEditModal(loc) {
            document.getElementById('edit_name').value = loc.name;
            document.getElementById('edit_lat').value = loc.latitude;
            document.getElementById('edit_lng').value = loc.longitude;
            document.getElementById('edit_radius').value = loc.radius;
            document.getElementById('editForm').action = `/locations/${loc.id}`;

            document.getElementById('editLocationModal').classList.remove('hidden');
            setTimeout(() => initEditMap(parseFloat(loc.latitude), parseFloat(loc.longitude)), 100);
        }
        function closeEditModal() {
            document.getElementById('editLocationModal').classList.add('hidden');
        }

        // Auto-dismiss flash
        const flash = document.getElementById('flash-success');
        if (flash) setTimeout(() => flash.style.display = 'none', 4000);
    </script>
</x-app-layout>
