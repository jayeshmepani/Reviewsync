<?php


namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Review;
use App\Models\Location;
use App\Models\AiReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class SuperAdminController
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::regularUsers()->count(),
            'total_locations' => Location::count(),
            'total_reviews' => Review::count(),
            'total_ai_replies' => AiReply::count()
        ];

        $users = User::regularUsers()->with('locations')->get();

        return view('superadmin.dashboard', compact('stats', 'users'));
    }

    public function users()
    {
        $users = User::regularUsers()->withCount(['locations', 'reviews'])->get();
        return view('superadmin.users', compact('users'));
    }

    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);
            if ($user->isSuperAdmin()) {
                return back()->with('error', 'Cannot delete superadmin');
            }
            $user->delete();
            return back()->with('success', 'User deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return back()->with('error', 'Error deleting user');
        }
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('superadmin.edit-user', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return redirect()->route('superadmin.users')->with('success', 'User updated successfully');
    }

    public function viewUserData($userId)
    {
        $user = User::with(['locations', 'locations.reviews', 'locations.reviews.aiReplies'])
            ->findOrFail($userId);

        return view('superadmin.user-data', compact('user'));
    }

    public function storeUser(Request $request)
    {
        // Validate the input data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);
    
        // Create the new user
        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = bcrypt($validated['password']);
        $user->save();
    
        // Redirect or return success response
        return redirect()->route('superadmin.users')->with('success', 'User created successfully!');
    }

    public function deleteLocation($id)
    {
        $location = Location::findOrFail($id);

        $location->delete();
    
        return redirect()->back()->with('success', 'Location deleted successfully.');
    }
}