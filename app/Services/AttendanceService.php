<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AttendanceService
{
    const WORK_START = '08:00';
    const WORK_END = '17:00';

    /**
     * Clock in an employee
     */
    public function clockIn(Employee $employee, ?string $photo = null, ?string $location = null): Attendance
    {
        $now = Carbon::now();
        $today = $now->toDateString();

        // Check if already clocked in today
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if ($existing) {
            throw new \Exception('Anda sudah absen masuk hari ini.');
        }

        // Calculate late minutes
        $lateMinutes = 0;
        $workStart = Carbon::createFromTimeString(self::WORK_START);
        if ($now->gt($workStart)) {
            $lateMinutes = (int) $now->diffInMinutes($workStart);
        }

        return Attendance::create([
            'employee_id' => $employee->id,
            'date' => $today,
            'time_in' => $now->format('H:i:s'),
            'photo' => $photo,
            'location' => $location,
            'late_minutes' => $lateMinutes,
        ]);
    }

    /**
     * Clock out an employee
     */
    public function clockOut(Employee $employee): Attendance
    {
        $now = Carbon::now();
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

        $attendance->update([
            'time_out' => $now->format('H:i:s'),
        ]);

        // Auto-calculate overtime if after 17:00
        $workEnd = Carbon::createFromTimeString(self::WORK_END);
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
