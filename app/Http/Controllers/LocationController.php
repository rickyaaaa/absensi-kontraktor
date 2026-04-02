<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Show locations management page (embedded in admin dashboard)
     */
    public function index()
    {
        $locations = Location::orderBy('name')->get();
        return view('locations.index', compact('locations'));
    }

    /**
     * Store a new location
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:kantor_pusat,project',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:5000',
        ]);

        Location::create($request->only(['name', 'type', 'latitude', 'longitude', 'radius']));

        return redirect()->route('locations.index')
            ->with('success', 'Lokasi berhasil ditambahkan.');
    }

    /**
     * Update an existing location
     */
    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:kantor_pusat,project',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:5000',
        ]);

        $location->update($request->only(['name', 'type', 'latitude', 'longitude', 'radius']));

        return redirect()->route('locations.index')
            ->with('success', 'Lokasi berhasil diperbarui.');
    }

    /**
     * Toggle location active status
     */
    public function toggleActive(Location $location)
    {
        $location->update(['is_active' => !$location->is_active]);

        $status = $location->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('locations.index')
            ->with('success', "Lokasi {$location->name} berhasil {$status}.");
    }

    /**
     * Delete a location
     */
    public function destroy(Location $location)
    {
        $location->delete();
        return redirect()->route('locations.index')
            ->with('success', 'Lokasi berhasil dihapus.');
    }
}
