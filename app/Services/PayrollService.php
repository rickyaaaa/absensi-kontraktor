<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\Overtime;
use App\Models\Kasbon;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Generate payroll for a specific employee and period
     */
    public function generatePayroll(Employee $employee, Carbon $periodStart, Carbon $periodEnd): Payroll
    {
        // Count working days in the period
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->get();

        $workingDays = $attendances->count();

        // Base salary = daily rate × working days
        $baseSalary = $employee->base_salary * $workingDays;

        // Calculate total overtime pay
        $totalOvertimeMinutes = Overtime::where('employee_id', $employee->id)
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->sum('total_minutes');

        $totalOvertime = $totalOvertimeMinutes * $employee->overtime_rate_per_minute;

        // Calculate total late deduction
        $totalLateMinutes = $attendances->sum('late_minutes');
        $totalLateDeduction = $totalLateMinutes * $employee->late_penalty_per_minute;

        // Calculate total kasbon
        $totalKasbon = Kasbon::where('employee_id', $employee->id)
            ->whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->sum('amount');

        // Final salary formula
        $finalSalary = $baseSalary + $totalOvertime - $totalLateDeduction - $totalKasbon;
        $finalSalary = max($finalSalary, 0); // Prevent negative salary

        return Payroll::create([
            'employee_id' => $employee->id,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'base_salary' => $baseSalary,
            'total_overtime' => $totalOvertime,
            'total_late_deduction' => $totalLateDeduction,
            'total_kasbon' => $totalKasbon,
            'final_salary' => $finalSalary,
            'status' => 'pending',
        ]);
    }

    /**
     * Generate weekly payroll for all Level 3 workers
     * Period: Sunday to Saturday
     */
    public function generateWeeklyPayrolls(?Carbon $weekEnding = null): array
    {
        $weekEnding = $weekEnding ?? Carbon::now()->endOfWeek(Carbon::SATURDAY);
        $weekStarting = $weekEnding->copy()->startOfWeek(Carbon::SUNDAY);

        $workers = Employee::where('salary_type', 'weekly')->get();
        $payrolls = [];

        foreach ($workers as $worker) {
            // Check if payroll already exists for this period
            $existing = Payroll::where('employee_id', $worker->id)
                ->where('period_start', $weekStarting->toDateString())
                ->where('period_end', $weekEnding->toDateString())
                ->first();

            if (!$existing) {
                $payrolls[] = $this->generatePayroll($worker, $weekStarting, $weekEnding);
            }
        }

        return $payrolls;
    }

    /**
     * Generate monthly payroll for all Level 2 staff
     */
    public function generateMonthlyPayrolls(?Carbon $month = null): array
    {
        $month = $month ?? Carbon::now();
        $periodStart = $month->copy()->startOfMonth();
        $periodEnd = $month->copy()->endOfMonth();

        $staff = Employee::where('salary_type', 'monthly')->get();
        $payrolls = [];

        foreach ($staff as $employee) {
            $existing = Payroll::where('employee_id', $employee->id)
                ->where('period_start', $periodStart->toDateString())
                ->where('period_end', $periodEnd->toDateString())
                ->first();

            if (!$existing) {
                $payrolls[] = $this->generatePayroll($employee, $periodStart, $periodEnd);
            }
        }

        return $payrolls;
    }
}
