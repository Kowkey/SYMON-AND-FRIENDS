<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main activity page with all 3 Eloquent relationships.
     */
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Eager load all 3 relationships for the current authenticated user
        $user->load([
            'profile',
            'posts' => fn ($query) => $query->latest(),
            'roles',
        ]);

        // Ensure available standard roles exist for demonstration
        Role::firstOrCreate(['name' => 'Admin']);
        Role::firstOrCreate(['name' => 'Editor']);
        Role::firstOrCreate(['name' => 'Member']);
        Role::firstOrCreate(['name' => 'Subscriber']);

        $allRoles = Role::orderBy('name')->get();

        return view('dashboard', compact('user', 'allRoles'));
    }

    /**
     * Update the user's One-to-One Profile.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'phone_number' => ['nullable', 'string', 'max:30'],
            'bio'          => ['nullable', 'string', 'max:500'],
        ]);

        // Update or create the 1:1 Profile
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone_number' => $validated['phone_number'],
                'bio'          => $validated['bio'],
            ]
        );

        // Requirement 4: Post/Redirect/Get pattern prevents duplicate submissions on refresh
        return redirect()->route('dashboard')
            ->with('success', 'Profile (One-to-One) updated successfully!');
    }

    /**
     * Store a new Post (One-to-Many).
     */
    public function storePost(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Server-side duplicate submission check (debounces double-clicks within 3 seconds)
        $lockKey = 'duplicate_post_lock_' . $user->id . '_' . md5($request->input('title', ''));
        if (!Cache::add($lockKey, true, 3)) {
            return redirect()->route('dashboard')
                ->with('warning', 'A post with this title was just submitted. Please wait a moment.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'body'  => ['required', 'string', 'max:5000'],
        ]);

        // Create 1:N Post via the relationship
        $user->posts()->create($validated);

        // Requirement 4: Post/Redirect/Get pattern prevents duplicate submissions on refresh
        return redirect()->route('dashboard')
            ->with('success', 'New Post (One-to-Many) published successfully!');
    }

    /**
     * Sync user roles (Many-to-Many).
     */
    public function syncRoles(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'roles'   => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $roleIds = $validated['roles'] ?? [];

        // Sync N:M Roles
        $user->roles()->sync($roleIds);

        // Requirement 4: Post/Redirect/Get pattern
        return redirect()->route('dashboard')
            ->with('success', 'Roles (Many-to-Many) updated successfully!');
    }
}
