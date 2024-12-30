<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Support\Facades\Auth;

class BusinessController
{
    public function index()
    {
        $businesses = Auth::user()->locations;
        return view('businesses.index', compact('businesses'));
    }

    public function destroy($id)
    {
        $business = Location::findOrFail($id);
    
        if ($business->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    
        $business->delete();
    
        return redirect()->route('businesses.index')->with('success', 'Business deleted successfully!');
    }
    

    public function showDashboard()
    {
        $businesses = Auth::user()->locations;
        $user = Auth::user();

        return view('dashboard', compact('user', 'businesses'));
    }

}
