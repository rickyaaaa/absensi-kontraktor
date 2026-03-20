<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'period_start',
        'period_end',
        'base_salary',
        'total_overtime',
        'total_late_deduction',
        'total_kasbon',
        'final_salary',
        'status',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'base_salary' => 'decimal:2',
        'total_overtime' => 'decimal:2',
        'total_late_deduction' => 'decimal:2',
        'total_kasbon' => 'decimal:2',
        'final_salary' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
