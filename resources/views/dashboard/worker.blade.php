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
                {{-- Real-time Clock --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-center">
                        <p class="text-sm text-gray-500 mb-1">Waktu Server (Jakarta)</p>
                        <p id="live-clock" class="text-4xl font-bold text-indigo-600 font-mono">--:--:--</p>
                        <p id="live-date" class="text-sm text-gray-500 mt-1">-</p>
                    </div>
                </div>

                {{-- Clock In/Out Section with Selfie --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Absensi Hari Ini</h3>

                        {{-- Status --}}
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
                                    @if($todayAttendance->location_status === 'luar_lokasi')
                                        <p class="text-sm text-orange-500">⚠️ Luar Lokasi</p>
                                    @endif
                                @else
                                    <p class="text-lg font-bold text-green-600">Sudah Pulang ({{ $todayAttendance->time_out }})</p>
                                @endif
                            </div>

                            {{-- GPS Status --}}
                            <div id="gps-status" class="text-center">
                                <p class="text-sm text-gray-500">📍 GPS:</p>
                                <p id="gps-info" class="text-sm font-medium text-gray-400">Mendeteksi...</p>
                            </div>
                        </div>

                        {{-- Camera + Action --}}
                        @if(!$todayAttendance || !$todayAttendance->time_out)
                            <div id="attendance-section">
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

                                {{-- Action Buttons --}}
                                <div class="flex justify-center gap-3">
                                    @if(!$todayAttendance)
                                        {{-- Start Camera for Clock In --}}
                                        <button type="button" id="btn-start-clockin" onclick="startAttendance('clockin')"
                                            class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium shadow-sm">
                                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                            Absen Masuk
                                        </button>

                                        {{-- Submit Button (hidden until photo captured) --}}
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
                                        {{-- Start Camera for Clock Out --}}
                                        <button type="button" id="btn-start-clockout" onclick="startAttendance('clockout')"
                                            class="inline-flex items-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium shadow-sm">
                                            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Absen Pulang
                                        </button>

                                        {{-- Submit Button (hidden until photo captured) --}}
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

                {{-- Employee Info + Payroll --}}
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
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
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
                                            <td class="px-4 py-3 text-sm">
                                                @if($att->location_status === 'valid')
                                                    <span class="text-green-600">✅ {{ $att->location }}</span>
                                                @elseif($att->location_status === 'luar_lokasi')
                                                    <span class="text-orange-600">⚠️ Luar Lokasi</span>
                                                @else
                                                    <span class="text-gray-400">-</span>
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
        let currentMode = null; // 'clockin' or 'clockout'
        let userLatitude = null;
        let userLongitude = null;

        // ============ REAL-TIME CLOCK (Asia/Jakarta) ============
        function updateClock() {
            const now = new Date();
            // Force Jakarta time
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

        // ============ GPS DETECTION ============
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

        // Request GPS on page load
        getGPSPosition();
        setInterval(getGPSPosition, 30000); // refresh every 30s

        // ============ CAMERA / SELFIE ============
        async function startAttendance(mode) {
            currentMode = mode;

            // Hide start buttons
            const btnIn = document.getElementById('btn-start-clockin');
            const btnOut = document.getElementById('btn-start-clockout');
            if (btnIn) btnIn.classList.add('hidden');
            if (btnOut) btnOut.classList.add('hidden');

            // Show camera
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
            // Mirror the image
            ctx.translate(canvas.width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);

            // Show preview
            document.getElementById('captured-photo').src = dataUrl;
            document.getElementById('camera-container').classList.add('hidden');
            document.getElementById('photo-preview').classList.remove('hidden');

            // Stop camera stream
            if (currentStream) {
                currentStream.getTracks().forEach(t => t.stop());
            }

            // Refresh GPS one more time
            getGPSPosition();

            // Fill form and show submit
            if (currentMode === 'clockin') {
                document.getElementById('selfie-data-in').value = dataUrl;
                document.getElementById('lat-in').value = userLatitude || '';
                document.getElementById('lng-in').value = userLongitude || '';
                document.getElementById('loc-in').value = `${userLatitude},${userLongitude}`;
                document.getElementById('form-clockin').classList.remove('hidden');
            } else {
                document.getElementById('selfie-data-out').value = dataUrl;
                document.getElementById('lat-out').value = userLatitude || '';
                document.getElementById('lng-out').value = userLongitude || '';
                document.getElementById('loc-out').value = `${userLatitude},${userLongitude}`;
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
