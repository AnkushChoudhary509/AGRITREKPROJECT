<?php
namespace App\Http\Controllers;

use App\Models\Land;
use App\Models\Farmer;
use Illuminate\Http\Request;

class LandController
{
    public function index(Request $request)
    {
        $query = Land::with('farmer');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('crop_type', 'like', "%$s%")
                  ->orWhere('soil_type', 'like', "%$s%")
                  ->orWhereHas('farmer', fn($f) => $f->where('name', 'like', "%$s%"));
            });
        }
        if ($request->filled('soil_type'))      $query->where('soil_type', $request->soil_type);
        if ($request->filled('irrigation_type')) $query->where('irrigation_type', $request->irrigation_type);

        $lands           = $query->latest()->paginate(15);
        $mapLands        = Land::whereNotNull('latitude')->get(['id','latitude','longitude','crop_type','area','soil_type']);
        $totalArea       = Land::sum('area');
        $soilTypes       = Land::distinct()->pluck('soil_type');
        $irrigationTypes = Land::distinct()->pluck('irrigation_type');

        return view('lands.index', compact('lands', 'mapLands', 'totalArea', 'soilTypes', 'irrigationTypes'));
    }

    public function create()
    {
        $farmers = Farmer::orderBy('name')->get(['id','name','village']);
        return view('lands.form', compact('farmers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'farmer_id'       => 'required|exists:farmers,id',
            'area'            => 'required|numeric|min:0.01|max:9999',
            'soil_type'       => 'required|string',
            'crop_type'       => 'required|string|max:100',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'irrigation_type' => 'required|string',
            'survey_number'   => 'nullable|string|max:50',
            'description'     => 'nullable|string',
        ]);

        Land::create($validated);
        return redirect()->route('lands.index')->with('success', 'Land record added successfully!');
    }

    public function show(Land $land)
    {
        $land->load('farmer');
        return view('lands.show', compact('land'));
    }

    public function edit(Land $land)
    {
        $farmers = Farmer::orderBy('name')->get(['id','name','village']);
        return view('lands.form', compact('land', 'farmers'));
    }

    public function update(Request $request, Land $land)
    {
        $validated = $request->validate([
            'farmer_id'       => 'required|exists:farmers,id',
            'area'            => 'required|numeric|min:0.01|max:9999',
            'soil_type'       => 'required|string',
            'crop_type'       => 'required|string|max:100',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'irrigation_type' => 'required|string',
            'survey_number'   => 'nullable|string|max:50',
            'description'     => 'nullable|string',
        ]);

        $land->update($validated);
        return redirect()->route('lands.show', $land)->with('success', 'Land record updated!');
    }

    public function destroy(Land $land)
    {
        $land->delete();
        return redirect()->route('lands.index')->with('success', 'Land record deleted.');
    }
}
