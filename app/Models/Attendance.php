<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'time_in',
        'time_out',
        'photo',
        'photo_out',
        'location',
        'latitude',
        'longitude',
        'location_status',
        'late_minutes',
    ];

    protected $casts = [
        'date' => 'date',
        'late_minutes' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
