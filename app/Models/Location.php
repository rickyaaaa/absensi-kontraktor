<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'name',
        'type',
        'latitude',
        'longitude',
        'radius',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'radius' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Calculate distance in meters between two GPS coordinates using Haversine formula
     */
    public static function distanceInMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if a GPS coordinate is within any active location's radius
     * Returns ['valid' => bool, 'nearest' => string|null, 'distance' => float]
     */
    public static function checkPosition(float $latitude, float $longitude): array
    {
        $locations = self::where('is_active', true)->get();

        if ($locations->isEmpty()) {
            // No locations configured — allow all
            return ['valid' => true, 'nearest' => 'Tidak ada lokasi', 'distance' => 0];
        }

        $nearestName = null;
        $nearestDistance = PHP_FLOAT_MAX;

        foreach ($locations as $loc) {
            $distance = self::distanceInMeters($latitude, $longitude, (float) $loc->latitude, (float) $loc->longitude);

            if ($distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearestName = $loc->name;
            }

            if ($distance <= $loc->radius) {
                return [
                    'valid' => true,
                    'nearest' => $loc->name,
                    'distance' => round($distance, 1),
                ];
            }
        }

        return [
            'valid' => false,
            'nearest' => $nearestName,
            'distance' => round($nearestDistance, 1),
        ];
    }
}
