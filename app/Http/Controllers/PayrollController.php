<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Employee;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    protected PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function index(Request $request)
    {
        $query = Payroll::with('employee.user');

        if ($request->filled('type')) {
            if ($request->type === 'weekly') {
                $query->whereHas('employee', fn($q) => $q->where('salary_type', 'weekly'));
            } else {
                $query->whereHas('employee', fn($q) => $q->where('salary_type', 'monthly'));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payrolls = $query->orderByDesc('period_end')->paginate(20);

        return view('payrolls.index', compact('payrolls'));
    }

    public function show(Payroll $payroll)
    {
        $payroll->load('employee.user');
        return view('payrolls.show', compact('payroll'));
    }

    /**
     * Generate weekly payrolls
     */
    public function generateWeekly(Request $request)
    {
        $weekEnding = $request->filled('week_ending')
            ? Carbon::parse($request->week_ending)
            : null;

        $payrolls = $this->payrollService->generateWeeklyPayrolls($weekEnding);

        $count = count($payrolls);
        return redirect()->route('payrolls.index')
            ->with('success', "$count payroll mingguan berhasil digenerate.");
    }

    /**
     * Generate monthly payrolls
     */
    public function generateMonthly(Request $request)
    {
        $month = $request->filled('month')
            ? Carbon::parse($request->month . '-01')
            : null;

        $payrolls = $this->payrollService->generateMonthlyPayrolls($month);

        $count = count($payrolls);
        return redirect()->route('payrolls.index')
            ->with('success', "$count payroll bulanan berhasil digenerate.");
    }

    /**
     * Mark payroll as paid
     */
    public function markPaid(Payroll $payroll)
    {
        $payroll->update(['status' => 'paid']);
        return back()->with('success', 'Payroll ditandai sudah dibayar.');
    }
}
