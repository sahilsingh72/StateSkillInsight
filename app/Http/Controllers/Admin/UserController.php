<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();

        if (!$currentUser || !$currentUser->isUniversityAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        if ($currentUser->isSuperAdmin()) {
            $users = User::with(['university', 'roleRelation'])->latest()->paginate(15);
            $roles = Role::all();
            $universities = University::all();
        } else {
            $myUniId = $currentUser->university_id ?? University::first()?->id;
            $users = User::with(['university', 'roleRelation'])
                ->where('university_id', $myUniId)
                ->latest()
                ->paginate(15);
            
            // University admin can only assign specific roles
            $roles = Role::whereIn('name', ['survey_administrator', 'data_operator', 'analyst'])->get();
            $universities = University::where('id', $myUniId)->get();
        }

        return view('admin.users.index', compact('users', 'roles', 'universities'));
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();

        if (!$currentUser || !$currentUser->isUniversityAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|string|max:20',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|string|min:6',
        ];

        if ($currentUser && $currentUser->isSuperAdmin()) {
            $rules['university_id'] = 'required|exists:universities,id';
        }

        $validated = $request->validate($rules);

        // Security check for role assignment
        $targetRole = Role::findOrFail($validated['role_id']);
        if ($targetRole->name === 'super_admin') {
            $validated['university_id'] = null;
        } elseif ($currentUser && !$currentUser->isSuperAdmin()) {
            if (in_array($targetRole->name, ['super_admin', 'university_admin'])) {
                return back()->withErrors(['role_id' => 'You do not have permission to create Administrator accounts.']);
            }
            $validated['university_id'] = $currentUser->university_id;
        }

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        AuditLog::log('created_user', 'User', $user->id);

        return back()->with('success', 'User account created successfully with assigned role ('.$targetRole->display_name.')!');
    }
}
