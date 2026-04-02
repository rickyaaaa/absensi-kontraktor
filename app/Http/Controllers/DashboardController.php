<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\Location;
use Carbon\Carbon;

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
        $totalEmployees = Employee::count();
        $todayAttendances = Attendance::where('date', Carbon::today('Asia/Jakarta')->toDateString())->count();
        $pendingPayrolls = Payroll::where('status', 'pending')->count();
        $recentAttendances = Attendance::with('employee.user')
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
        $user = auth()->user();
        $employee = $user->employee;

        // Supervisor's own attendance
        $todayAttendance = null;
        $recentAttendances = collect();
        $recentPayrolls = collect();

        if ($employee) {
            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->where('date', Carbon::today('Asia/Jakarta')->toDateString())
                ->first();

            $recentAttendances = Attendance::where('employee_id', $employee->id)
                ->orderByDesc('date')
                ->take(7)
                ->get();

            $recentPayrolls = Payroll::where('employee_id', $employee->id)
                ->orderByDesc('period_end')
                ->take(5)
                ->get();
        }

        // Workers' attendance for monitoring
        $workerAttendances = Attendance::with('employee.user')
            ->whereHas('employee', fn($q) => $q->where('role_level', 3))
            ->where('date', Carbon::today('Asia/Jakarta')->toDateString())
            ->latest()
            ->get();

        // Active locations for clock-in selector
        $locations = Location::where('is_active', true)->orderBy('type')->orderBy('name')->get();

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
        $user = auth()->user();
        $employee = $user->employee;

        if (!$employee) {
            return view('dashboard.worker', [
                'employee' => null,
                'todayAttendance' => null,
                'recentAttendances' => collect(),
                'recentPayrolls' => collect(),
            ]);
        }

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', Carbon::today('Asia/Jakarta')->toDateString())
            ->first();

        $recentAttendances = Attendance::where('employee_id', $employee->id)
            ->orderByDesc('date')
            ->take(7)
            ->get();

        $recentPayrolls = Payroll::where('employee_id', $employee->id)
            ->orderByDesc('period_end')
            ->take(5)
            ->get();

        // Active locations for clock-in selector
        $locations = Location::where('is_active', true)->orderBy('type')->orderBy('name')->get();

        return view('dashboard.worker', compact(
            'employee',
            'todayAttendance',
            'recentAttendances',
            'recentPayrolls',
            'locations'
        ));
    }
}
