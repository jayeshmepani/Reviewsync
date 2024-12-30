<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Google\Client as GoogleClient;

class GoogleOAuthController
{
    public function callback(Request $request)
    {
        $googleClient = new GoogleClient();
        $credentialsPath = base_path(env('GOOGLE_CREDENTIALS_PATH'));
        $googleClient->setAuthConfig($credentialsPath);

        $code = $request->input('code');
        if ($code) {
            $token = $googleClient->fetchAccessTokenWithAuthCode($code);
            Session::put('google_access_token', $token);
            return redirect()->route('dashboard')->with('success', 'Google authenticated successfully.');
        }

        return redirect()->route('dashboard')->with('error', 'Failed to authenticate with Google.');
    }
}
