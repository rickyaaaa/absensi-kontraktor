<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Payroll;
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
        $todayAttendances = Attendance::where('date', Carbon::today()->toDateString())->count();
        $pendingPayrolls = Payroll::where('status', 'pending')->count();
        $recentAttendances = Attendance::with('employee.user')
            ->where('date', Carbon::today()->toDateString())
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.admin', compact(
            'totalEmployees',
            'todayAttendances',
            'pendingPayrolls',
            'recentAttendances'
        ));
    }

    private function supervisorDashboard()
    {
        $todayAttendances = Attendance::with('employee.user')
            ->where('date', Carbon::today()->toDateString())
            ->latest()
            ->get();

        return view('dashboard.supervisor', compact('todayAttendances'));
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
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $recentAttendances = Attendance::where('employee_id', $employee->id)
            ->orderByDesc('date')
            ->take(7)
            ->get();

        $recentPayrolls = Payroll::where('employee_id', $employee->id)
            ->orderByDesc('period_end')
            ->take(5)
            ->get();

        return view('dashboard.worker', compact(
            'employee',
            'todayAttendance',
            'recentAttendances',
            'recentPayrolls'
        ));
    }
}
