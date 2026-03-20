<?php

namespace App\Http\Controllers;

use App\Models\Kasbon;
use App\Models\Employee;
use Illuminate\Http\Request;

class KasbonController extends Controller
{
    public function index(Request $request)
    {
        $query = Kasbon::with('employee.user');

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        $kasbons = $query->orderByDesc('date')->paginate(20);
        $employees = Employee::with('user')->get();

        return view('kasbons.index', compact('kasbons', 'employees'));
    }

    public function create()
    {
        $employees = Employee::with('user')->get();
        return view('kasbons.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
            'note' => 'nullable|string|max:500',
        ]);

        Kasbon::create($request->only(['employee_id', 'amount', 'date', 'note']));

        return redirect()->route('kasbons.index')
            ->with('success', 'Kasbon berhasil ditambahkan.');
    }

    public function destroy(Kasbon $kasbon)
    {
        $kasbon->delete();
        return redirect()->route('kasbons.index')
            ->with('success', 'Kasbon berhasil dihapus.');
    }
}
