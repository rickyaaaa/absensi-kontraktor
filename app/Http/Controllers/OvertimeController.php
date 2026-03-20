<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use App\Models\Employee;
use Illuminate\Http\Request;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        $query = Overtime::with('employee.user');

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $overtimes = $query->orderByDesc('date')->paginate(20);
        $employees = Employee::with('user')->get();

        return view('overtimes.index', compact('overtimes', 'employees'));
    }

    public function create()
    {
        $employees = Employee::with('user')->get();
        return view('overtimes.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $start = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
        $end = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);
        $totalMinutes = $start->diffInMinutes($end);

        Overtime::create([
            'employee_id' => $request->employee_id,
            'date' => $request->date,
            'start_time' => $request->start_time . ':00',
            'end_time' => $request->end_time . ':00',
            'total_minutes' => $totalMinutes,
        ]);

        return redirect()->route('overtimes.index')
            ->with('success', 'Data lembur berhasil ditambahkan.');
    }

    public function destroy(Overtime $overtime)
    {
        $overtime->delete();
        return redirect()->route('overtimes.index')
            ->with('success', 'Data lembur berhasil dihapus.');
    }
}
