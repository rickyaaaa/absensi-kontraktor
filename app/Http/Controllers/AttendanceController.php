<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * AUDIT FIXES APPLIED:
 * #1 — N+1: All list queries now use with(['employee.user']) / with(['employee'])
 * #2 — Secure Upload: Base64 images are validated for real MIME, saved with
 *        random names, always as .jpg. No user-controlled extension possible.
 * #4 — Rate Limiting: clockIn and clockOut are limited to 3 hits / 1 min per user.
 */
class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * AUDIT #1 — N+1 FIX:
     * Added with(['employee.user']) which was already correct here.
     * Also added select() to avoid loading unnecessary columns on the list.
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['employee.user'])
            ->select(['id', 'employee_id', 'date', 'time_in', 'time_out',
                      'location', 'location_status', 'late_minutes', 'created_at']);

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
     * AUDIT #1 — N+1 FIX:
     * Role_level filter now uses index (idx_employees_role_level).
     * with(['employee.user']) prevents N+1 when rendering names in view.
     */
    public function workerMonitoring(Request $request)
    {
        $query = Attendance::with(['employee.user'])
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
     * AUDIT #2 & #4 — Secure Upload + Rate Limiting
     *
     * Rate Limit: max 3 attempts per minute per user to prevent DB spam.
     * Secure upload: random filename, forced .jpg extension, real MIME validation.
     */
    public function clockIn(Request $request)
    {
        $user   = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        // ── AUDIT #4: Rate Limiting ───────────────────────────────────────────
        $rateLimitKey = 'clock-in:' . $user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->with('error',
                "Terlalu banyak percobaan absen. Coba lagi dalam {$seconds} detik.");
        }
        RateLimiter::hit($rateLimitKey, decay: 60);
        // ─────────────────────────────────────────────────────────────────────

        try {
            $photoPath = null;

            if ($request->filled('selfie_data')) {
                // ── AUDIT #2: Secure Base64 Upload ───────────────────────────
                $photoPath = $this->saveBase64ImageSecurely($request->selfie_data, 'attendances');
                // ─────────────────────────────────────────────────────────────
            } elseif ($request->hasFile('photo')) {
                // ── AUDIT #2: Secure file upload (non-base64 fallback) ───────
                $file = $request->file('photo');
                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

                if (!in_array($file->getMimeType(), $allowedMimes)) {
                    return back()->with('error', 'Format file tidak diizinkan. Gunakan JPEG, PNG, atau WebP.');
                }
                // Force safe extension — never trust client extension
                $photoPath = $file->storeAs(
                    'attendances',
                    Str::random(40) . '.jpg',
                    'public'
                );
                // ─────────────────────────────────────────────────────────────
            }

            $latitude  = $request->filled('latitude')  ? (float) $request->latitude  : null;
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
     * AUDIT #2 & #4 — Secure Upload + Rate Limiting (clock-out)
     */
    public function clockOut(Request $request)
    {
        $user     = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        // ── AUDIT #4: Rate Limiting ───────────────────────────────────────────
        $rateLimitKey = 'clock-out:' . $user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->with('error',
                "Terlalu banyak percobaan absen pulang. Coba lagi dalam {$seconds} detik.");
        }
        RateLimiter::hit($rateLimitKey, decay: 60);
        // ─────────────────────────────────────────────────────────────────────

        try {
            $photoOutPath = null;

            if ($request->filled('selfie_data')) {
                // ── AUDIT #2: Secure Base64 Upload ───────────────────────────
                $photoOutPath = $this->saveBase64ImageSecurely($request->selfie_data, 'attendances');
                // ─────────────────────────────────────────────────────────────
            } elseif ($request->hasFile('photo')) {
                // ── AUDIT #2: Secure file upload ─────────────────────────────
                $file = $request->file('photo');
                $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

                if (!in_array($file->getMimeType(), $allowedMimes)) {
                    return back()->with('error', 'Format file tidak diizinkan. Gunakan JPEG, PNG, atau WebP.');
                }
                $photoOutPath = $file->storeAs(
                    'attendances',
                    Str::random(40) . '.jpg',
                    'public'
                );
                // ─────────────────────────────────────────────────────────────
            }

            $latitude  = $request->filled('latitude')  ? (float) $request->latitude  : null;
            $longitude = $request->filled('longitude') ? (float) $request->longitude : null;

            $this->attendanceService->clockOut($employee, $photoOutPath, $latitude, $longitude);

            return back()->with('success', 'Absen pulang berhasil dicatat.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * AUDIT #1 — N+1 FIX:
     * history now eagerly loads employee.user in all branches.
     */
    public function history(Request $request)
    {
        $user = auth()->user();

        if ($user->isAdmin() && $request->filled('employee_id')) {
            $attendances = Attendance::with(['employee.user'])
                ->where('employee_id', $request->employee_id)
                ->orderByDesc('date')
                ->paginate(20);
        } else {
            $employee = $user->employee;
            if (!$employee) {
                return back()->with('error', 'Data karyawan tidak ditemukan.');
            }

            $attendances = Attendance::with(['employee.user'])
                ->where('employee_id', $employee->id)
                ->orderByDesc('date')
                ->paginate(20);
        }

        return view('attendances.history', compact('attendances'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * AUDIT #2 — Secure Base64 Image Save
     *
     * WHY this is safer than the old approach:
     * 1. We decode the Base64 and then use PHP's finfo to detect the REAL MIME
     *    type from the binary data — the client cannot lie about it.
     * 2. The filename is Str::random(40) — completely unpredictable, so attackers
     *    cannot guess or enumerate stored files.
     * 3. We wrenforce the .jpg extension regardless of what the Base64 header claims.
     *    This prevents a crafted payload like data:image/gif;base64,...<?php...>
     *    from being stored with a .php extension.
     * 4. Files are stored in storage/app/public (not public/) and the web server
     *    should NEVER execute files from that directory.
     */
    private function saveBase64ImageSecurely(string $base64Data, string $folder): string
    {
        // Strip the data URI prefix if present
        if (str_contains($base64Data, ',')) {
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
        }

        $imageData = base64_decode($base64Data, strict: false);

        if ($imageData === false || strlen($imageData) < 8) {
            throw new \Exception('Data gambar selfie tidak valid.');
        }

        // Validate real MIME from binary content — client cannot fake this
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($imageData);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mimeType, $allowedMimes)) {
            throw new \Exception('Format gambar selfie tidak valid. Hanya JPEG/PNG/WebP yang diizinkan.');
        }

        // Always save as .jpg — safe, predictable extension
        $fileName = $folder . '/' . Str::random(40) . '.jpg';

        \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageData);

        return $fileName;
    }
}
