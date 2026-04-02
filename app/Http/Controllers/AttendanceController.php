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
            $query->where('date', Carbon::today('Asia/Jakarta')->toDateString());
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
     * List only Level 3 worker attendances (for supervisor monitoring)
     */
    public function workerMonitoring(Request $request)
    {
        $query = Attendance::with('employee.user')
            ->whereHas('employee', function ($q) {
                $q->where('role_level', 3);
            });

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        } else {
            $query->where('date', Carbon::today('Asia/Jakarta')->toDateString());
        }

        if ($request->filled('search')) {
            $query->whereHas('employee.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $attendances = $query->orderByDesc('time_in')->paginate(20);

        return view('attendances.worker_monitoring', compact('attendances'));
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

            // Handle base64 selfie photo from camera
            if ($request->filled('selfie_data')) {
                $photoPath = $this->saveBase64Image($request->selfie_data, 'attendances');
            } elseif ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('attendances', 'public');
            }

            $latitude = $request->filled('latitude') ? (float) $request->latitude : null;
            $longitude = $request->filled('longitude') ? (float) $request->longitude : null;

            $attendance = $this->attendanceService->clockIn(
                $employee,
                $photoPath,
                $request->location,
                $latitude,
                $longitude
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
    public function clockOut(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        try {
            $photoOutPath = null;

            // Handle base64 selfie photo from camera
            if ($request->filled('selfie_data')) {
                $photoOutPath = $this->saveBase64Image($request->selfie_data, 'attendances');
            } elseif ($request->hasFile('photo')) {
                $photoOutPath = $request->file('photo')->store('attendances', 'public');
            }

            $latitude = $request->filled('latitude') ? (float) $request->latitude : null;
            $longitude = $request->filled('longitude') ? (float) $request->longitude : null;

            $this->attendanceService->clockOut($employee, $photoOutPath, $latitude, $longitude);
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

    /**
     * Save base64 encoded image to storage
     */
    private function saveBase64Image(string $base64Data, string $folder): string
    {
        // Remove data URI prefix if present
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            $extension = $matches[1];
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        } else {
            $extension = 'jpg';
        }

        $imageData = base64_decode($base64Data);
        $fileName = $folder . '/' . uniqid('selfie_') . '.' . $extension;

        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageData);

        return $fileName;
    }
}
