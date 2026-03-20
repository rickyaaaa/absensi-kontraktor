<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * List all attendances (admin/supervisor)
     */
    public function index(Request $request)
    {
        $query = Attendance::with('employee.user');

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        } else {
            $query->where('date', Carbon::today()->toDateString());
        }

        if ($request->filled('search')) {
            $query->whereHas('employee.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $attendances = $query->orderByDesc('time_in')->paginate(20);

        return view('attendances.index', compact('attendances'));
    }

    /**
     * Clock in
     */
    public function clockIn(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        try {
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('attendances', 'public');
            }

            $attendance = $this->attendanceService->clockIn(
                $employee,
                $photoPath,
                $request->location
            );

            $message = 'Absen masuk berhasil dicatat.';
            if ($attendance->late_minutes > 0) {
                $message .= ' Anda terlambat ' . $attendance->late_minutes . ' menit.';
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Clock out
     */
    public function clockOut()
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        try {
            $this->attendanceService->clockOut($employee);
            return back()->with('success', 'Absen pulang berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show attendance history for a specific employee (or for self)
     */
    public function history(Request $request)
    {
        $user = auth()->user();

        if ($user->isAdmin() && $request->filled('employee_id')) {
            $attendances = Attendance::where('employee_id', $request->employee_id)
                ->orderByDesc('date')
                ->paginate(20);
        } else {
            $employee = $user->employee;
            if (!$employee) {
                return back()->with('error', 'Data karyawan tidak ditemukan.');
            }

            $attendances = Attendance::where('employee_id', $employee->id)
                ->orderByDesc('date')
                ->paginate(20);
        }

        return view('attendances.history', compact('attendances'));
    }
}
