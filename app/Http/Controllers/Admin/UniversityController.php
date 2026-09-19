<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\University;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Institution Enrollment directory is reserved for Super Administrator role only.');
        }

        $query = University::with('parent', 'colleges');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('short_name', 'LIKE', "%{$search}%")
                  ->orWhere('tagline', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        $sort = $request->get('sort', 'latest');
        match ($sort) {
            'oldest' => $query->oldest(),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'type' => $query->orderBy('type', 'asc'),
            default => $query->latest(),
        };

        $institutions = $query->paginate(15)->withQueryString();
        $parentUniversities = University::where('type', 'university')->orderBy('name')->get();
        $states = University::whereNotNull('state')->where('state', '!=', '')->distinct()->orderBy('state')->pluck('state');

        return view('admin.university.index', compact('institutions', 'parentUniversities', 'states'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Institution Enrollment is reserved for Super Administrator role only.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'type' => 'required|in:ini,university,autonomous_college,affiliated_college,polytechnic_iti',
            'parent_id' => 'nullable|required_if:type,affiliated_college|exists:universities,id',
            'tagline' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'state' => 'nullable|string',
            'primary_color' => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
        ]);

        if ($validated['type'] !== 'affiliated_college') {
            $validated['parent_id'] = null;
        }

        $institution = University::create($validated);
        AuditLog::log('enrolled_institution', 'University', $institution->id);

        return back()->with('success', 'Institution ('.$institution->name.') enrolled successfully!');
    }

    public function edit(?University $university = null)
    {
        $user = auth()->user();
        if (!$user || (!$user->isSuperAdmin() && !$user->hasRole('university_admin'))) {
            abort(403, 'Unauthorized access. University Profile & Branding Configurator is reserved for Super Administrator or University Administrator roles.');
        }

        if ($user->isSuperAdmin()) {
            $targetUniversity = $university ?? University::find($user->university_id) ?? University::first() ?? new University();
        } else {
            $targetUniversity = University::find($user->university_id) ?? $university ?? University::first() ?? new University();
        }

        $parentUniversities = University::where('type', 'university')->where('id', '!=', $targetUniversity->id ?? 0)->get();

        return view('admin.university.edit', [
            'university' => $targetUniversity,
            'parentUniversities' => $parentUniversities,
        ]);
    }

    public function update(Request $request, ?University $university = null)
    {
        $user = auth()->user();
        if (!$user || (!$user->isSuperAdmin() && !$user->hasRole('university_admin'))) {
            abort(403, 'Unauthorized access. University Profile & Branding Configurator is reserved for Super Administrator or University Administrator roles.');
        }

        if ($user->isSuperAdmin()) {
            $targetUniversity = $university ?? University::find($request->input('university_id')) ?? University::find($user->university_id) ?? University::first();
        } else {
            $targetUniversity = University::find($user->university_id) ?? $university;
        }

        if (!$targetUniversity) {
            return back()->with('error', 'Target institution not found.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'type' => 'nullable|in:ini,university,autonomous_college,affiliated_college,polytechnic_iti',
            'parent_id' => 'nullable|exists:universities,id',
            'tagline' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'survey_header' => 'nullable|string|max:255',
            'footer_text' => 'nullable|string',
            'privacy_text' => 'nullable|string',
        ]);

        if (isset($validated['type']) && $validated['type'] !== 'affiliated_college') {
            $validated['parent_id'] = null;
        }

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('branding', 'public');
            $validated['logo'] = $logoPath;
        }

        $targetUniversity->update($validated);

        AuditLog::log('updated_university_settings', 'University', $targetUniversity->id);

        return back()->with('success', 'Institution ('.$targetUniversity->name.') profile & branding settings updated successfully!');
    }
}
