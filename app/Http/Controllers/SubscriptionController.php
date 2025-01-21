<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subscription;

class SubscriptionController
{
    public function index()
    {
        // Fetch predefined subscriptions
        $subscriptions = Subscription::all();
        return view('subscriptions.index', compact('subscriptions'));
    }
}