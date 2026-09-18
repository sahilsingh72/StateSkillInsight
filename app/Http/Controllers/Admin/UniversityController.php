<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\University;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    public function index()
    {
        $institutions = University::with('parent', 'colleges')->latest()->paginate(15);
        $parentUniversities = University::where('type', 'university')->get();

        return view('admin.university.index', compact('institutions', 'parentUniversities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'type' => 'required|in:university,affiliated_college,autonomous_college',
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
        if ($user && !$user->isSuperAdmin() && $user->university_id) {
            $university = University::find($user->university_id);
        } else if (!$university) {
            $university = University::first() ?? new University();
        }

        return view('admin.university.edit', compact('university'));
    }

    public function update(Request $request, ?University $university = null)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin() && $user->university_id) {
            $university = University::findOrFail($user->university_id);
        } else if (!$university) {
            $university = University::first();
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'type' => 'nullable|in:university,affiliated_college,autonomous_college',
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

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('branding', 'public');
            $validated['logo'] = $logoPath;
        }

        $university->update($validated);

        AuditLog::log('updated_university_settings', 'University', $university->id);

        return back()->with('success', 'Institution identity & branding settings updated successfully!');
    }
}
