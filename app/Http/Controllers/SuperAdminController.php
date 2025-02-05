<?php


namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Review;
use App\Models\Location;
use App\Models\AiReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Fetch token data for all users
        $tokenData = DB::table('ai_replies')
            ->selectRaw('
            DATE_FORMAT(DATE_SUB(created_at, INTERVAL SECOND(created_at) % 3 SECOND), "%Y-%m-%d %H:%i:00") as grouped_time,
            input_tokens,
            output_tokens
            ')
            ->groupBy('grouped_time', 'input_tokens', 'output_tokens')
            ->orderBy('grouped_time')
            ->get()
            ->map(function ($item) {
                $inputCost = $item->input_tokens * 0.0375 / 1000000;
                $outputCost = $item->output_tokens * 0.15 / 1000000;
                $item->total_cost = $inputCost + $outputCost;

                $item->grouped_time = \Carbon\Carbon::parse($item->grouped_time)
                    ->format('d-M-Y h:i:s A');

                    Log::info('Cost Calculation:', [
                        // 'user_id' => Auth::id(), // Add user_id to logging
                        'grouped_time' => $item->grouped_time,
                        'input_tokens' => $item->input_tokens,
                        'output_tokens' => $item->output_tokens,
                        'input_cost' => number_format($inputCost, 9),
                        'output_cost' => number_format($outputCost, 9),
                        'total_cost' => number_format($item->total_cost, 9),
                    ]);

                return $item;
            });

        $totalCost = $tokenData->sum('total_cost');

        return view('superadmin.dashboard', compact('stats', 'users', 'tokenData', 'totalCost'));
    }

    public function users()
    {
        $users = User::regularUsers()->withCount(['locations', 'reviews'])->get();
        return view('superadmin.users', compact('users'));
    }
    public function profile()
    {
        $users = User::regularUsers()->withCount(['locations', 'reviews'])->get();
        $user = auth()->user();
        return view('superadmin.profile', compact('users', 'user')); // Pass $user to the view
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