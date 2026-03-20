<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'role_level',
        'position',
        'salary_type',
        'base_salary',
        'overtime_rate_per_minute',
        'late_penalty_per_minute',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'overtime_rate_per_minute' => 'decimal:2',
        'late_penalty_per_minute' => 'decimal:2',
        'role_level' => 'integer',
    ];

    /**
     * Salary structure per position
     */
    public const SALARY_MAP = [
        // Level 3 — Worker (Weekly)
        'Helper' => 150000,
        'Sup Helper' => 160000,
        'Semi Worker' => 170000,
        'Worker' => 200000,
        'Head Worker' => 225000,
        // Level 2 — Staff (Monthly)
        'Engineering' => 208000,
        'Supervisor' => 240000,
        'Site Manager' => 280000,
    ];

    public const LEVEL_POSITIONS = [
        2 => ['Engineering', 'Supervisor', 'Site Manager'],
        3 => ['Helper', 'Sup Helper', 'Semi Worker', 'Worker', 'Head Worker'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class);
    }

    public function kasbons(): HasMany
    {
        return $this->hasMany(Kasbon::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get salary type based on level
     */
    public static function getSalaryType(int $level): string
    {
        return $level === 2 ? 'monthly' : 'weekly';
    }

    /**
     * Get base salary by position
     */
    public static function getBaseSalary(string $position): float
    {
        return self::SALARY_MAP[$position] ?? 0;
    }
}
