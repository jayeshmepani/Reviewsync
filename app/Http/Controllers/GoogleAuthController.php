<?php

namespace App\Http\Controllers;

use App\Interfaces\TokenStorageInterface;
use App\Models\Location;
use App\Models\User;
use App\Services\GoogleAuthService;
use Carbon\Carbon;
use Exception;
use Google\Client;
use Google\Service\MyBusinessAccountManagement;
use Google\Service\MyBusinessBusinessInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Str;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;


class GoogleAuthController
{
    private const TOKEN_EXPIRY_BUFFER = 300;

    private const LOCATION_FIELDS = 'name,languageCode,storeCode,title,phoneNumbers,categories,storefrontAddress,websiteUri,regularHours,specialHours,serviceArea,labels,adWordsLocationExtensions,latlng,openInfo,metadata,profile,relationshipData,moreHours,serviceItems';

    private Client $client;

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
        $this->initClient();
    }

    protected function initClient()
    {
        $this->client = new Client;
        $this->client->setClientId(config('google.client_id'));
        $this->client->setClientSecret(config('google.client_secret'));
        $this->client->setRedirectUri(config('google.redirect_uri_signup'));
        $this->client->addScope([
            'https://www.googleapis.com/auth/business.manage',
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/userinfo.email'
        ]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }

    public function redirectToGoogleSignIn()
    {
        return Socialite::driver('google')
            ->scopes(['https://www.googleapis.com/auth/business.manage'])
            ->redirect();
    }

    public function handleGoogleSignInCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if the email exists in the database
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                return redirect()->route('login')->withErrors([
                    'email' => 'This email is not registered. Please sign up first.',
                ]);
            }

            // Log the user in
            Auth::login($user);

            return redirect()->route('dashboard')->with('status', 'Logged in successfully using Google!');
        } catch (Exception $e) {
            Log::error('Google login error: ' . $e->getMessage());
            return redirect()->route('login')->withErrors([
                'google' => 'There was an error logging in with Google. Please try again.',
            ]);
        }
    }

    public function redirect()
    {
        return redirect()->away($this->client->createAuthUrl());
    }

    public function callback(Request $request)
    {
        try {
            $code = $request->get('code');

            if (!$code) {
                return to_route('dashboard')->with('error', __('Authorization code is missing.'));
            }

            $token = $this->client->fetchAccessTokenWithAuthCode($code);

            if (!empty($token['error'])) {
                throw new Exception('Authentication failed: ' . $token['error']);
            }

            $service = new MyBusinessAccountManagement($this->client);

            $accounts = $service->accounts->listAccounts()->getAccounts();

            if (empty($accounts)) {
                return to_route('dashboard')->with('error', __('No accounts found.'));
            }

            $account = reset($accounts);

            if (!$account) {
                return to_route('dashboard')->with('error', __('Invalid account data received from Google.'));
            }

            $validator = Validator::make([
                'account' => $account->getName(),
            ], [
                'account' => 'required|unique:users,google_id',
            ]);

            if ($validator->fails()) {
                return to_route('dashboard')
                    ->with('error', __('Invalid account data or account already exists.'));
            }

            $uri = "https://people.googleapis.com/v1/people/me?personFields=names,emailAddresses,photos";

            $response = Http::withToken($token['access_token'])
                ->get($uri);

            if ($response->successful()) {
                $profile = $response->json();

                $profileData = [
                    'display_name' => $profile['names'][0]['displayName'] ?? null,
                    'last_name' => $profile['names'][0]['familyName'] ?? null,
                    'first_name' => $profile['names'][0]['givenName'] ?? null,
                    'profile_picture' => $profile['photos'][0]['url'] ?? null,
                    'email' => $profile['emailAddresses'][0]['value'] ?? null,
                ];

                $imageUrl = $profile['photos'][0]['url'] ?? null;
                $imageName = md5($imageUrl) . '.jpg';
                $savedImagePath = storage_path('app/public/images/profiles/' . $imageName);

                // Ensure the directory exists
                if (!file_exists(dirname($savedImagePath))) {
                    mkdir(dirname($savedImagePath), 0755, true);
                }

                if (!file_exists($savedImagePath)) {
                    try {
                        $imageContents = file_get_contents($imageUrl);
                        file_put_contents($savedImagePath, $imageContents);
                    } catch (Exception $e) {
                        Log::error('Failed to save image: ' . $e->getMessage());
                    }
                }

                $user = User::updateOrCreate([
                    'uuid' => Str::uuid(),
                    'name' => $profileData['display_name'],
                    'last_name' => $profileData['last_name'],
                    'first_name' => $profileData['first_name'],
                    'profile_picture' => 'storage/images/profiles/' . $imageName,
                    'email' => $profileData['email'],
                    'google_id' => $account->getName(),
                    'password' => bcrypt('admin123'),
                    'subscription_billing_start' => Carbon::now(),
                    'subscription_billing_end' => Carbon::now()->addMonth(),
                ]);

                Auth::login($user);

                $client = new GoogleAuthService(auth()->id());
                $client->validateAndStoreToken($token);
            } else {
                return back()->with('success', __('Successfully authenticated with Google!'));
            }

            return to_route('dashboard')->with('success', __('Successfully authenticated with Google!'));
        } catch (Exception $e) {

            if (config('app.env') === 'local') {
                throw $e;
            }

            Log::error($e);

            return to_route('dashboard')->with('error', __('Google authentication failed. Please try again.'));
        }
    }
}
