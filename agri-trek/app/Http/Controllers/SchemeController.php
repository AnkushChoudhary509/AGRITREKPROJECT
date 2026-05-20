<?php
namespace App\Http\Controllers;

use App\Models\Scheme;
use App\Models\Farmer;
use App\Models\SchemeApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchemeController
{
    public function index()
    {
        $schemes = Scheme::withCount('applications')->latest()->paginate(12);
        return view('schemes.index', compact('schemes'));
    }

    public function create()
    {
        return view('schemes.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:150',
            'description'    => 'nullable|string',
            'eligibility'    => 'nullable|string',
            'subsidy_amount' => 'required|numeric|min:0',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date|after_or_equal:start_date',
            'department'     => 'nullable|string|max:100',
            'is_active'      => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        Scheme::create($validated);
        return redirect()->route('schemes.index')->with('success', 'Scheme created!');
    }

    public function show(Scheme $scheme)
    {
        $scheme->load('applications.farmer');
        return view('schemes.show', compact('scheme'));
    }

    public function edit(Scheme $scheme)
    {
        return view('schemes.form', compact('scheme'));
    }

    public function update(Request $request, Scheme $scheme)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:150',
            'description'    => 'nullable|string',
            'eligibility'    => 'nullable|string',
            'subsidy_amount' => 'required|numeric|min:0',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date',
            'department'     => 'nullable|string|max:100',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $scheme->update($validated);
        return redirect()->route('schemes.show', $scheme)->with('success', 'Scheme updated!');
    }

    public function destroy(Scheme $scheme)
    {
        $scheme->delete();
        return redirect()->route('schemes.index')->with('success', 'Scheme deleted.');
    }

    /** Show all applications for admin review */
    public function applications()
    {
        $applications = SchemeApplication::with(['farmer','scheme'])
            ->latest()->paginate(15);
        return view('schemes.applications', compact('applications'));
    }

    /** Farmer applies for a scheme */
    public function apply(Request $request, Scheme $scheme)
    {
        $farmer = Auth::user()->farmer ?? Farmer::first();
        if (!$farmer) return back()->with('error', 'No farmer profile found.');

        $existing = SchemeApplication::where('farmer_id', $farmer->id)
                                     ->where('scheme_id', $scheme->id)->first();
        if ($existing) return back()->with('error', 'You have already applied for this scheme.');

        SchemeApplication::create([
            'farmer_id'    => $farmer->id,
            'scheme_id'    => $scheme->id,
            'status'       => 'pending',
            'applied_date' => now(),
        ]);
        return back()->with('success', 'Application submitted!');
    }

    /** Admin approves/rejects application */
    public function updateApplication(Request $request, SchemeApplication $application)
    {
        $request->validate([
            'status'  => 'required|in:approved,rejected',
            'remarks' => 'nullable|string',
        ]);

        $application->update([
            'status'        => $request->status,
            'remarks'       => $request->remarks,
            'approved_date' => $request->status === 'approved' ? now() : null,
        ]);

        return back()->with('success', 'Application ' . $request->status . '!');
    }
}
