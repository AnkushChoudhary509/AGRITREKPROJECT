<?php

namespace App\Http\Controllers;

use App\Models\Farmer;
use Illuminate\Http\Request;

class FarmerController
{
    /**
     * List all farmers with search/filter.
     */
    public function index(Request $request)
    {
        $query = Farmer::withCount(['lands', 'applications']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('mobile', 'like', "%$search%")
                  ->orWhere('village', 'like', "%$search%")
                  ->orWhere('aadhaar', 'like', "%$search%");
            });
        }

        if ($request->filled('village')) {
            $query->where('village', $request->village);
        }

        $farmers  = $query->latest()->paginate(15);
        $villages = Farmer::distinct()->pluck('village')->sort()->values();

        return view('farmers.index', compact('farmers', 'villages'));
    }

    /**
     * Show form to create a new farmer.
     */
    public function create()
    {
        return view('farmers.form');
    }

    /**
     * Store a new farmer record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'mobile'       => 'required|string|size:10|unique:farmers,mobile',
            'address'      => 'nullable|string|max:255',
            'village'      => 'required|string|max:100',
            'district'     => 'nullable|string|max:100',
            'aadhaar'      => 'nullable|string|size:12|unique:farmers,aadhaar',
            'dob'          => 'nullable|date',
            'bank_account' => 'nullable|string|max:20',
            'ifsc_code'    => 'nullable|string|max:15',
            'notes'        => 'nullable|string',
        ]);

        Farmer::create($validated);

        return redirect()->route('farmers.index')
                         ->with('success', 'Farmer record added successfully!');
    }

    /**
     * Show a single farmer's profile.
     */
    public function show(Farmer $farmer)
    {
        $farmer->load(['lands', 'applications.scheme']);
        return view('farmers.show', compact('farmer'));
    }

    /**
     * Show form to edit a farmer.
     */
    public function edit(Farmer $farmer)
    {
        return view('farmers.form', compact('farmer'));
    }

    /**
     * Update farmer record.
     */
    public function update(Request $request, Farmer $farmer)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'mobile'       => 'required|string|size:10|unique:farmers,mobile,' . $farmer->id,
            'address'      => 'nullable|string|max:255',
            'village'      => 'required|string|max:100',
            'district'     => 'nullable|string|max:100',
            'aadhaar'      => 'nullable|string|size:12|unique:farmers,aadhaar,' . $farmer->id,
            'dob'          => 'nullable|date',
            'bank_account' => 'nullable|string|max:20',
            'ifsc_code'    => 'nullable|string|max:15',
            'notes'        => 'nullable|string',
        ]);

        $farmer->update($validated);

        return redirect()->route('farmers.show', $farmer)
                         ->with('success', 'Farmer record updated successfully!');
    }

    /**
     * Delete a farmer record.
     */
    public function destroy(Farmer $farmer)
    {
        $farmer->delete();

        return redirect()->route('farmers.index')
                         ->with('success', 'Farmer deleted successfully.');
    }
}
