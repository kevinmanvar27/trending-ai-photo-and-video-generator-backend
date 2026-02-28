<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'user')->with(['activeSubscription.plan']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'suspended') {
                $query->where('is_suspended', true);
            } elseif ($request->status === 'active') {
                $query->where('is_suspended', false);
            }
        }

        $users = $query->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user details
     */
    public function show($id)
    {
        $user = User::with(['subscriptions.plan', 'activityLogs'])
            ->findOrFail($id);

        $totalSessions = $user->activityLogs()->count();
        $avgSessionDuration = $user->activityLogs()->avg('duration');

        return view('admin.users.show', compact('user', 'totalSessions', 'avgSessionDuration'));
    }

    /**
     * Show create user form
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'user';

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Suspend user
     */
    public function suspend(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'suspension_reason' => 'required|string|max:500',
        ]);

        $user->update([
            'is_suspended' => true,
            'suspended_at' => now(),
            'suspension_reason' => $validated['suspension_reason'],
        ]);

        return back()->with('success', 'User suspended successfully.');
    }

    /**
     * Unsuspend user
     */
    public function unsuspend($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'is_suspended' => false,
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        return back()->with('success', 'User unsuspended successfully.');
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Show user activity
     */
    public function activity($id)
    {
        $user = User::findOrFail($id);
        $activities = UserActivityLog::where('user_id', $id)
            ->latest('session_start')
            ->paginate(20);

        return view('admin.users.activity', compact('user', 'activities'));
    }
}
