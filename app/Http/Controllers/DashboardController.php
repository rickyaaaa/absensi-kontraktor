<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\Location;
use Carbon\Carbon;

/**
 * AUDIT FIX #1 — N+1 Query Problem
 *
 * WHY: The old workerDashboard / supervisorDashboard loaded recentAttendances
 * and recentPayrolls without eager loading. The Blade view then accessed
 * $att->employee->user->name, triggering a separate query for each row.
 * On a dashboard showing 7 rows that is 14 extra queries. Fixed by adding
 * with(['employee.user']) on every collection that is iterated in the view.
 *
 * adminDashboard: recentAttendances already had with('employee.user') ✓
 * supervisorDashboard: recentAttendances, recentPayrolls had no eager load ✗ → fixed
 * workerDashboard:     recentAttendances, recentPayrolls had no eager load ✗ → fixed
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        if ($user->isSupervisor()) {
            return $this->supervisorDashboard();
        }

        return $this->workerDashboard();
    }

    private function adminDashboard()
    {
        $totalEmployees    = Employee::count();
        $todayAttendances  = Attendance::where('date', Carbon::today('Asia/Jakarta')->toDateString())->count();
        $pendingPayrolls   = Payroll::where('status', 'pending')->count();

        // N+1 already correct ✓
        $recentAttendances = Attendance::with(['employee.user'])
            ->where('date', Carbon::today('Asia/Jakarta')->toDateString())
            ->latest()
            ->take(10)
            ->get();

        $locations = Location::orderBy('name')->get();

        return view('dashboard.admin', compact(
            'totalEmployees',
            'todayAttendances',
            'pendingPayrolls',
            'recentAttendances',
            'locations'
        ));
    }

    private function supervisorDashboard()
    {
        $user     = auth()->user();
        $employee = $user->employee;

        $todayAttendance   = null;
        $recentAttendances = collect();
        $recentPayrolls    = collect();

        if ($employee) {
            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->where('date', Carbon::today('Asia/Jakarta')->toDateString())
                ->first();

            // AUDIT #1 FIX: load employee.user eager to prevent N+1 in Blade
            $recentAttendances = Attendance::with(['employee.user'])
                ->where('employee_id', $employee->id)
                ->orderByDesc('date')
                ->take(7)
                ->get();

            // AUDIT #1 FIX: load employee.user eager to prevent N+1 in Blade
            $recentPayrolls = Payroll::with(['employee.user'])
                ->where('employee_id', $employee->id)
                ->orderByDesc('period_end')
                ->take(5)
                ->get();
        }

        // Workers monitoring — N+1 already correct ✓
        $workerAttendances = Attendance::with(['employee.user'])
            ->whereHas('employee', fn($q) => $q->where('role_level', 3))
            ->where('date', Carbon::today('Asia/Jakarta')->toDateString())
            ->latest()
            ->get();

        // Active locations for clock-in location selector
        $locations = Location::where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('dashboard.supervisor', compact(
            'employee',
            'todayAttendance',
            'recentAttendances',
            'recentPayrolls',
            'workerAttendances',
            'locations'
        ));
    }

    private function workerDashboard()
    {
        $user     = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return view('dashboard.worker', [
                'employee'          => null,
                'todayAttendance'   => null,
                'recentAttendances' => collect(),
                'recentPayrolls'    => collect(),
                'locations'         => collect(),
            ]);
        }

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', Carbon::today('Asia/Jakarta')->toDateString())
            ->first();

        // AUDIT #1 FIX: load employee.user eager to prevent N+1 in Blade
        $recentAttendances = Attendance::with(['employee.user'])
            ->where('employee_id', $employee->id)
            ->orderByDesc('date')
            ->take(7)
            ->get();

        // AUDIT #1 FIX: load employee.user eager to prevent N+1 in Blade
        $recentPayrolls = Payroll::with(['employee.user'])
            ->where('employee_id', $employee->id)
            ->orderByDesc('period_end')
            ->take(5)
            ->get();

        // Active locations for clock-in location selector
        $locations = Location::where('is_active', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('dashboard.worker', compact(
            'employee',
            'todayAttendance',
            'recentAttendances',
            'recentPayrolls',
            'locations'
        ));
    }
}
