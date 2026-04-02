<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Supervisor
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
                    <p class="text-yellow-800 font-medium">Data karyawan Anda belum terdaftar. Hubungi admin.</p>
                </div>
            @else
                {{-- Real-time Clock --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-center">
                        <p class="text-sm text-gray-500 mb-1">Waktu Server (Jakarta)</p>
                        <p id="live-clock" class="text-4xl font-bold text-indigo-600 font-mono">--:--:--</p>
                        <p id="live-date" class="text-sm text-gray-500 mt-1">-</p>
                    </div>
                </div>

                {{-- Clock In/Out Section --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Absensi Saya</h3>

                        <div class="flex flex-col sm:flex-row items-center gap-4 mb-6">
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

                            <div id="gps-status" class="text-center">
                                <p class="text-sm text-gray-500">📍 GPS:</p>
                                <p id="gps-info" class="text-sm font-medium text-gray-400">Mendeteksi...</p>
                            </div>
                        </div>

                        @if(!$todayAttendance || !$todayAttendance->time_out)
                            <div id="attendance-section">
                                {{-- Location Selector --}}
                                <div class="mb-4 p-4 bg-indigo-50 rounded-lg border border-indigo-100">
                                    <label for="location-select" class="block text-sm font-semibold text-indigo-800 mb-2">📍 Pilih Lokasi Absensi</label>
                                    <select id="location-select" onchange="onLocationSelect(this)"
                                        class="block w-full rounded-lg border-indigo-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-white">
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
                                    <p class="mt-1 text-xs text-indigo-500">Kantor Pusat = lokasi default | Project = ditentukan Admin</p>
                                </div>

                                {{-- Camera Preview --}}
                                <div id="camera-container" class="mb-4 hidden">
                                    <div class="relative max-w-md mx-auto">
                                        <video id="camera-preview" autoplay playsinline class="w-full rounded-lg border-2 border-indigo-300 shadow-lg" style="transform: scaleX(-1);"></video>
                                        <canvas id="camera-canvas" class="hidden"></canvas>
                                        <div class="absolute bottom-3 left-0 right-0 text-center">
                                            <button type="button" id="btn-capture" onclick="captureSelfie()"
                                                class="inline-flex items-center px-6 py-3 bg-white text-indigo-700 rounded-full shadow-lg hover:bg-indigo-50 font-medium transition-all">
                                                📸 Ambil Foto
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Captured Photo Preview --}}
                                <div id="photo-preview" class="mb-4 hidden">
                                    <div class="max-w-md mx-auto">
                                        <img id="captured-photo" class="w-full rounded-lg border-2 border-green-300 shadow-lg" style="transform: scaleX(-1);">
                                        <div class="text-center mt-2">
                                            <button type="button" onclick="retakeSelfie()" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                                🔄 Foto Ulang
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-center gap-3">
                                    @if(!$todayAttendance)
                                        <button type="button" id="btn-start-clockin" onclick="startAttendance('clockin')"
                                            class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                            Absen Masuk
                                        </button>
                                        <form method="POST" action="{{ route('attendance.clockIn') }}" id="form-clockin" class="hidden">
                                            @csrf
                                            <input type="hidden" name="selfie_data" id="selfie-data-in">
                                            <input type="hidden" name="latitude" id="lat-in">
                                            <input type="hidden" name="longitude" id="lng-in">
                                            <input type="hidden" name="location" id="loc-in">
                                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium shadow-sm">
                                                ✅ Kirim Absen Masuk
                                            </button>
                                        </form>
                                    @elseif(!$todayAttendance->time_out)
                                        <button type="button" id="btn-start-clockout" onclick="startAttendance('clockout')"
                                            class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Absen Pulang
                                        </button>
                                        <form method="POST" action="{{ route('attendance.clockOut') }}" id="form-clockout" class="hidden">
                                            @csrf
                                            <input type="hidden" name="selfie_data" id="selfie-data-out">
                                            <input type="hidden" name="latitude" id="lat-out">
                                            <input type="hidden" name="longitude" id="lng-out">
                                            <input type="hidden" name="location" id="loc-out">
                                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium shadow-sm">
                                                ✅ Kirim Absen Pulang
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <span class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-600 rounded-lg font-medium">
                                    ✅ Absensi Lengkap Hari Ini
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Worker Monitoring Quick View --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Absensi Worker Hari Ini</h3>
                            <a href="{{ route('supervisor.workerMonitoring') }}" class="text-sm text-indigo-600 hover:text-indigo-800">Lihat Semua →</a>
                        </div>

                        @if($workerAttendances->isEmpty())
                            <p class="text-gray-500 text-center py-8">Belum ada worker yang absen hari ini.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posisi</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Masuk</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pulang</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($workerAttendances as $attendance)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $attendance->employee->user->name ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->employee->position ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->time_in ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $attendance->time_out ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if($attendance->late_minutes > 0)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Telat {{ $attendance->late_minutes }} menit
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
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

                {{-- Supervisor's own attendance history --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Absensi Saya (7 Hari)</h3>
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

    {{-- JavaScript for Camera, GPS, Real-time Clock --}}
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

        function updateClock() {
            const now = new Date();
            const jakartaTime = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Jakarta' }));
            const hours = String(jakartaTime.getHours()).padStart(2, '0');
            const minutes = String(jakartaTime.getMinutes()).padStart(2, '0');
            const seconds = String(jakartaTime.getSeconds()).padStart(2, '0');

            document.getElementById('live-clock').textContent = `${hours}:${minutes}:${seconds}`;

            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            document.getElementById('live-date').textContent =
                `${days[jakartaTime.getDay()]}, ${jakartaTime.getDate()} ${months[jakartaTime.getMonth()]} ${jakartaTime.getFullYear()}`;
        }

        setInterval(updateClock, 1000);
        updateClock();

        function getGPSPosition() {
            const gpsInfo = document.getElementById('gps-info');
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
                    gpsInfo.className = 'text-sm font-medium text-green-600';
                },
                (error) => {
                    gpsInfo.textContent = '❌ Izinkan akses lokasi';
                    gpsInfo.className = 'text-sm font-medium text-red-600';
                },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        }

        getGPSPosition();
        setInterval(getGPSPosition, 30000);

        async function startAttendance(mode) {
            currentMode = mode;
            const btnIn = document.getElementById('btn-start-clockin');
            const btnOut = document.getElementById('btn-start-clockout');
            if (btnIn) btnIn.classList.add('hidden');
            if (btnOut) btnOut.classList.add('hidden');

            document.getElementById('camera-container').classList.remove('hidden');
            document.getElementById('photo-preview').classList.add('hidden');

            try {
                currentStream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                    audio: false
                });
                document.getElementById('camera-preview').srcObject = currentStream;
            } catch (err) {
                alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
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
        }
    </script>
</x-app-layout>
