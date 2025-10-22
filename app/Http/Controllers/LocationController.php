<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends BaseController
{
    public function index(Request $request)
    {
        $query = Location::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('location_name', 'like', "%{$search}%")
                  ->orWhere('gln', 'like', "%{$search}%")
                  ->orWhere('ffrn', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('location_type', $request->type);
        }

        $locations = $query->latest()->paginate(20);

        return view('master-data.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('master-data.locations.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateWithLocale($request, [
            'location_name' => 'required|string|max:200',
            'gln' => 'nullable|string|size:13|unique:locations',
            'ffrn' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:50',
            'location_type' => 'required|in:warehouse,farm,processing,distribution',
        ]);

        $location = Location::create($validated);

        return redirect()->route('master-data.locations.index')
            ->with('success', $this->getLocalizedSuccessMessage('location_created_successfully'));
    }

    public function show(Location $location)
    {
        $location->load(['traceRecords' => function($query) {
            $query->latest()->take(10);
        }]);

        return view('master-data.locations.show', compact('location'));
    }

    public function edit(Location $location)
    {
        return view('master-data.locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $validated = $this->validateWithLocale($request, [
            'location_name' => 'required|string|max:200',
            'gln' => 'nullable|string|size:13|unique:locations,gln,' . $location->id,
            'ffrn' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:50',
            'location_type' => 'required|in:warehouse,farm,processing,distribution',
        ]);

        $location->update($validated);

        return redirect()->route('master-data.locations.index')
            ->with('success', $this->getLocalizedSuccessMessage('location_updated_successfully'));
    }

    public function destroy(Location $location)
    {
        try {
            $location->delete();
            return redirect()->route('master-data.locations.index')
                ->with('success', 'Location deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Cannot delete location with existing records.');
        }
    }
}
