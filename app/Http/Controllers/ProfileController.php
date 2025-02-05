<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Str;
use App\Models\User;


class ProfileController
{
    public function profile()
    {
        $user = Auth::user();

        return view('profile.profile', [
            'user' => $user,
        ]);

    }

    public function businessProfile(Request $request)
    {
        $user = Auth::user();

        $query = Location::where('user_id', $user->id)
        ->where('is_visible', true);
        
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('primary_category', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('formatted_address', 'like', "%{$searchTerm}%");
            });
        }
        
        $sortDirection = strtolower($request->input('sort', 'asc'));
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';
        $query->orderBy('title', $sortDirection);
        
        $locations = $query->get();
        // $locations = $query->paginate(10)->withQueryString();
        
        foreach($locations as $location) {
            $reviewLink = $location->new_review_uri ?? 
                         "https://search.google.com/local/writereview?placeid={$location->place_id}&hl=en";
            
            $location->review_link = $reviewLink;
            $location->qr_code = QrCode::size(300)->generate($reviewLink);
            
            if (!$location->formatted_address) {
                $location->formatted_address = null;
            }
            if (!$location->international_phone_number) {
                $location->international_phone_number = $location->primary_phone;
            }
            if (!$location->website) {
                $location->website = $location->website_uri;
            }
        }

        return view('businesses.info', [
            'user' => $user,
            'locations' => $locations,
            'searchTerm' => $request->search,
            'currentDirection' => $sortDirection
        ]);
    }

    public function updateName(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $user = Auth::user();
        $user->name = $request->name;
        $user->save();

        return back()->with('success', 'Name updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password does not match.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        Auth::logout();

        return redirect()->route('login')->with('success', 'Password updated successfully. Please log in with your new password.');
    }

    public function updateEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|unique:users,email']);
        $user = Auth::user();
        $user->email = $request->email;
        $user->save();

        return back()->with('success', 'Email updated successfully!');
    }

    public function updateContact(Request $request)
    {
        $request->validate(['phone' => 'required|string|max:15']);
        $user = Auth::user();
        $user->phone = $request->phone;
        $user->save();

        return back()->with('success', 'Contact updated successfully!');
    }
}
