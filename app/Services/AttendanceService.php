<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Overtime;
use Carbon\Carbon;

class AttendanceService
{
    const WORK_START = '08:00';
    const WORK_END = '17:00';

    /**
     * Validate GPS position against registered locations
     * Returns location_status and location name
     */
    public function validateLocation(?float $latitude, ?float $longitude): array
    {
        if ($latitude === null || $longitude === null) {
            return [
                'status' => 'no_gps',
                'location' => 'GPS tidak tersedia',
                'valid' => false,
            ];
        }

        $check = Location::checkPosition($latitude, $longitude);

        return [
            'status' => $check['valid'] ? 'valid' : 'luar_lokasi',
            'location' => $check['nearest'] . ' (' . $check['distance'] . 'm)',
            'valid' => $check['valid'],
        ];
    }

    /**
     * Clock in an employee
     */
    public function clockIn(
        Employee $employee,
        ?string $photo = null,
        ?string $location = null,
        ?float $latitude = null,
        ?float $longitude = null
    ): Attendance {
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        // Check if already clocked in today
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($existing) {
            throw new \Exception('Anda sudah absen masuk hari ini.');
        }

        // Validate selfie
        if (!$photo) {
            throw new \Exception('Foto selfie wajib diambil sebelum absen.');
        }

        // Validate GPS location
        $locationCheck = $this->validateLocation($latitude, $longitude);
        if (!$locationCheck['valid']) {
            throw new \Exception('Absensi ditolak: Anda berada di luar radius lokasi. Jarak ke lokasi terdekat: ' . $locationCheck['location']);
        }

        // Calculate late minutes
        $lateMinutes = 0;
        $workStart = Carbon::createFromTimeString(self::WORK_START, 'Asia/Jakarta');
        if ($now->gt($workStart)) {
            $lateMinutes = (int) $now->diffInMinutes($workStart);
        }

        return Attendance::create([
            'employee_id' => $employee->id,
            'date' => $today,
            'time_in' => $now->format('H:i:s'),
            'photo' => $photo,
            'location' => $locationCheck['location'],
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location_status' => $locationCheck['status'],
            'late_minutes' => $lateMinutes,
        ]);
    }

    /**
     * Clock out an employee
     */
    public function clockOut(
        Employee $employee,
        ?string $photoOut = null,
        ?float $latitude = null,
        ?float $longitude = null
    ): Attendance {
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            throw new \Exception('Anda belum absen masuk hari ini.');
        }

        if ($attendance->time_out) {
            throw new \Exception('Anda sudah absen pulang hari ini.');
        }

        // Validate selfie
        if (!$photoOut) {
            throw new \Exception('Foto selfie wajib diambil sebelum absen pulang.');
        }

        // Validate GPS location
        $locationCheck = $this->validateLocation($latitude, $longitude);
        if (!$locationCheck['valid']) {
            throw new \Exception('Absensi pulang ditolak: Anda berada di luar radius lokasi. Jarak ke lokasi terdekat: ' . $locationCheck['location']);
        }

        $attendance->update([
            'time_out' => $now->format('H:i:s'),
            'photo_out' => $photoOut,
        ]);

        // Auto-calculate overtime if after 17:00
        $workEnd = Carbon::createFromTimeString(self::WORK_END, 'Asia/Jakarta');
        if ($now->gt($workEnd)) {
            $overtimeMinutes = (int) $now->diffInMinutes($workEnd);

            Overtime::create([
                'employee_id' => $employee->id,
                'date' => $today,
                'start_time' => self::WORK_END . ':00',
                'end_time' => $now->format('H:i:s'),
                'total_minutes' => $overtimeMinutes,
            ]);
        }

        return $attendance->fresh();
    }
}
