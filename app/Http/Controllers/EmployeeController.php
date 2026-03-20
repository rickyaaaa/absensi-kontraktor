<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('user')->orderBy('role_level')->paginate(15);
        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        $levelPositions = Employee::LEVEL_POSITIONS;
        $salaryMap = Employee::SALARY_MAP;
        return view('employees.create', compact('levelPositions', 'salaryMap'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_level' => 'required|in:2,3',
            'position' => 'required|string',
        ]);

        $roleLevel = (int) $request->role_level;
        $position = $request->position;

        // Validate position for the given level
        if (!in_array($position, Employee::LEVEL_POSITIONS[$roleLevel] ?? [])) {
            return back()->withErrors(['position' => 'Posisi tidak valid untuk level ini.'])->withInput();
        }

        // Determine role for user
        $role = $roleLevel === 2 ? 'supervisor' : 'worker';

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        // Create employee with auto-calculated salary
        $baseSalary = Employee::getBaseSalary($position);
        $salaryType = Employee::getSalaryType($roleLevel);

        Employee::create([
            'user_id' => $user->id,
            'role_level' => $roleLevel,
            'position' => $position,
            'salary_type' => $salaryType,
            'base_salary' => $baseSalary,
            'overtime_rate_per_minute' => $baseSalary / 480, // 8 hours = 480 min
            'late_penalty_per_minute' => $baseSalary / 480,
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function show(Employee $employee)
    {
        $employee->load('user', 'attendances', 'overtimes', 'kasbons', 'payrolls');
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $employee->load('user');
        $levelPositions = Employee::LEVEL_POSITIONS;
        $salaryMap = Employee::SALARY_MAP;
        return view('employees.edit', compact('employee', 'levelPositions', 'salaryMap'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($employee->user_id)],
            'role_level' => 'required|in:2,3',
            'position' => 'required|string',
        ]);

        $roleLevel = (int) $request->role_level;
        $position = $request->position;

        if (!in_array($position, Employee::LEVEL_POSITIONS[$roleLevel] ?? [])) {
            return back()->withErrors(['position' => 'Posisi tidak valid untuk level ini.'])->withInput();
        }

        $role = $roleLevel === 2 ? 'supervisor' : 'worker';

        // Update user
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $role,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $employee->user->update($userData);

        // Update employee
        $baseSalary = Employee::getBaseSalary($position);
        $salaryType = Employee::getSalaryType($roleLevel);

        $employee->update([
            'role_level' => $roleLevel,
            'position' => $position,
            'salary_type' => $salaryType,
            'base_salary' => $baseSalary,
            'overtime_rate_per_minute' => $baseSalary / 480,
            'late_penalty_per_minute' => $baseSalary / 480,
        ]);

        return redirect()->route('employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $employee->user->delete();
        return redirect()->route('employees.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }
}
