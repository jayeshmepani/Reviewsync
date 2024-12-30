<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class ProfileController
{

    public function profile()
    {
        $user = Auth::user(); // Get the authenticated user

        return view('profile.profile', compact('user'));
    }

    public function businessProfile()
    {
        $user = Auth::user();
        $locations = $user->locations;

        $locations = $locations->map(function ($location) {
            $reviewLink = $location->new_review_uri ?? "https://search.google.com/local/writereview?placeid={$location->place_id}&hl=en";

            $location->review_link = $reviewLink;
            $location->qr_code = QrCode::size(150)->generate($reviewLink);

            $location->formatted_address = $location->formatted_address;
            $location->international_phone_number = $location->primary_phone;
            $location->website = $location->website_uri;

            return $location;
        });

        return view('businesses.info', [
            'user' => $user,
            'locations' => $locations
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
