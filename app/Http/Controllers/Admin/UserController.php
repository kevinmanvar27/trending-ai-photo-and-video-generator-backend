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
     * Delete user via GET method with URL parameter
     */
    public function deleteViaGet(Request $request)
    {
        // Get user ID from query parameter
        $userId = $request->query('user_id');

        if (!$userId) {
            return view('admin.users.delete-result', [
                'success' => false,
                'message' => 'User ID is required',
                'error' => 'Please provide a user_id parameter in the URL'
            ]);
        }

        try {
            $user = User::findOrFail($userId);
            
            // Store user info before deletion
            $userName = $user->name;
            $userEmail = $user->email;
            $userRole = $user->role;

            // Prevent deleting admin users via this method
            if ($user->role === 'admin') {
                return view('admin.users.delete-result', [
                    'success' => false,
                    'message' => 'Cannot delete admin users via this method',
                    'error' => 'Admin users must be deleted through the admin panel',
                    'user' => $user
                ]);
            }

            // Delete all user's tokens
            $user->tokens()->delete();

            // Delete the user
            $user->delete();

            return view('admin.users.delete-result', [
                'success' => true,
                'message' => 'User deleted successfully',
                'deleted_user' => [
                    'id' => $userId,
                    'name' => $userName,
                    'email' => $userEmail,
                    'role' => $userRole
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return view('admin.users.delete-result', [
                'success' => false,
                'message' => 'User not found',
                'error' => "No user found with ID: {$userId}"
            ]);
        } catch (\Exception $e) {
            return view('admin.users.delete-result', [
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ]);
        }
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

    /**
     * Delete user account via email and password (Web version)
     * GET method with credentials in URL parameters
     */
    public function deleteAccountViaCredentialsWeb(Request $request)
    {
        // Get credentials from query parameters
        $email = $request->query('email');
        $password = $request->query('password');

        // Validate required parameters
        if (!$email || !$password) {
            return view('admin.users.delete-account-result', [
                'success' => false,
                'message' => 'Missing required parameters',
                'error' => 'Both email and password are required'
            ]);
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return view('admin.users.delete-account-result', [
                'success' => false,
                'message' => 'Invalid email format',
                'error' => 'Please provide a valid email address'
            ]);
        }

        try {
            // Find user by email
            $user = User::where('email', $email)->first();

            if (!$user) {
                return view('admin.users.delete-account-result', [
                    'success' => false,
                    'message' => 'User not found',
                    'error' => 'No account found with this email address'
                ]);
            }

            // Verify password
            if (!Hash::check($password, $user->password)) {
                return view('admin.users.delete-account-result', [
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'error' => 'The password provided is incorrect'
                ]);
            }

            // Prevent deleting admin accounts
            if ($user->role === 'admin') {
                return view('admin.users.delete-account-result', [
                    'success' => false,
                    'message' => 'Cannot delete admin account',
                    'error' => 'Admin accounts cannot be deleted through this method',
                    'user' => $user
                ]);
            }

            // Store user info before deletion
            $userName = $user->name;
            $userEmail = $user->email;
            $userId = $user->id;
            $userRole = $user->role;

            // Delete all user's tokens
            $user->tokens()->delete();

            // Delete the user
            $user->delete();

            return view('admin.users.delete-account-result', [
                'success' => true,
                'message' => 'Account deleted successfully',
                'deleted_user' => [
                    'id' => $userId,
                    'name' => $userName,
                    'email' => $userEmail,
                    'role' => $userRole
                ]
            ]);

        } catch (\Exception $e) {
            return view('admin.users.delete-account-result', [
                'success' => false,
                'message' => 'Failed to delete account',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display a listing of deleted (soft-deleted) users
     */
    public function deletedUsers(Request $request)
    {
        $query = User::onlyTrashed()->where('role', 'user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $deletedUsers = $query->latest('deleted_at')->paginate(15);

        return view('admin.users.deleted', compact('deletedUsers'));
    }

    /**
     * Restore a soft-deleted user
     */
    public function restore($id)
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);
            $user->restore();

            return redirect()->back()->with('success', "User '{$user->name}' has been restored successfully.");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Deleted user not found.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to restore user: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete a soft-deleted user (force delete)
     */
    public function forceDelete($id)
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);
            
            // Store user info before permanent deletion
            $userName = $user->name;
            $userEmail = $user->email;

            // Delete related data
            $user->tokens()->forceDelete();
            $user->subscriptions()->forceDelete();
            $user->activityLogs()->forceDelete();

            // Permanently delete the user
            $user->forceDelete();

            return redirect()->back()->with('success', "User '{$userName}' ({$userEmail}) has been permanently deleted.");
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Deleted user not found.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to permanently delete user: ' . $e->getMessage());
        }
    }

    /**
     * Restore a user via API (JSON response)
     */
    public function restoreApi($id)
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);
            $user->restore();

            return response()->json([
                'success' => true,
                'message' => 'User restored successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'restored_at' => now()->toDateTimeString()
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deleted user not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete a user via API (JSON response)
     */
    public function forceDeleteApi($id)
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);
            
            $userName = $user->name;
            $userEmail = $user->email;

            // Delete related data
            $user->tokens()->forceDelete();
            $user->subscriptions()->forceDelete();
            $user->activityLogs()->forceDelete();

            // Permanently delete
            $user->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'User permanently deleted',
                'deleted_user' => [
                    'name' => $userName,
                    'email' => $userEmail,
                    'permanently_deleted_at' => now()->toDateTimeString()
                ]
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Deleted user not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
