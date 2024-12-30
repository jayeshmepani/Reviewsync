<?php

namespace App\Http\Controllers\Auth;

use App\Interfaces\TokenStorageInterface;
use App\Models\User;
use App\Models\Location;
use Google\Service\MyBusinessBusinessInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use Google\Client as Client;
use Str;

class GoogleController
{
    private const TOKEN_EXPIRY_BUFFER = 300;
    private const LOCATION_FIELDS = 'name,languageCode,storeCode,title,phoneNumbers,categories,storefrontAddress,websiteUri,regularHours,specialHours,serviceArea,labels,adWordsLocationExtensions,latlng,openInfo,metadata,profile,relationshipData,moreHours,serviceItems';

    private Client $client;
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
        $this->client = new Client();
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes([
                'https://www.googleapis.com/auth/business.manage'
            ])
            ->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $code = $request->get('code');

            if (!$code) {
                throw new Exception('Authorization code is missing.');
            }

            $googleUser = Socialite::driver('google')->user();
            
            $imageUrl =  $googleUser->getAvatar();

            $imageName = md5($imageUrl) . '.jpg';
        
            $savedImagePath = public_path('images/profiles/' . $imageName);

            if (!file_exists($savedImagePath)) {
                try {
                    $imageContents = file_get_contents($imageUrl);
                    file_put_contents($savedImagePath, $imageContents);
                } catch (Exception $e) {
                    Log::error('Failed to save image: ' . $e->getMessage());
                }
            }

            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'uuid' => Str::uuid(),
                    'name' => $googleUser->getName(),
                    'first_name' => $googleUser->user['given_name'],
                    'last_name' => $googleUser->user['family_name'],
                    'email' => $googleUser->getEmail(),
                    'profile_picture' =>'images/profiles/' . $imageName,
                    'google_avatar_original' => $googleUser->attributes['avatar_original'],
                    'password' => bcrypt('admin123'),
                    'google_id' => $googleUser->getId(),
                    'google_token' => $googleUser->token,
                ]
            );

            Auth::login($user);

            $this->initClient();

            $token = $this->client->fetchAccessTokenWithAuthCode($code);

            if (!empty($token['error'])) {
                Log::error('Token fetch failed', ['error' => $token]);
                // throw new Exception('Authentication failed: ' . $token['error_description']);
            }

            $user->update(['google_token' => $token]);

            return redirect()->route('dashboard');

        } catch (Exception $e) {
            throw $e;
            Log::error('Google authentication failed', ['error' => $e->getMessage()]);
            return redirect()->route('login')->withErrors(['msg' => 'Authentication failed. Please try again.']);
        }
    }

    private function initClient()
    {
        $this->client->setClientId(config('google.client_id'));
        $this->client->setClientSecret(config('google.client_secret'));
        $this->client->setRedirectUri(config('google.redirect_uri'));

        $this->client->addScope([
            'https://www.googleapis.com/auth/business.manage'
        ]);

        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setIncludeGrantedScopes(true);

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->google_token) {
                $this->client->setAccessToken($user->google_token);
            }
        }
    }

    public function fetchToken(string $code): array
    {
        try {

            $token = $this->client->fetchAccessTokenWithAuthCode($code);

            if (!empty($token['error'])) {
                Log::error('Token fetch failed', ['error' => $token]);
                throw new Exception('Authentication failed: ' . $token['error_description']);
            }

            $this->validateAndStoreToken($token);
            return $token;
        } catch (Exception $e) {
            Log::error('Google Authentication Error', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    private function validateAndStoreToken(array $token, int $userId): void
    {
        if (!isset($token['access_token'])) {
            throw new Exception('Invalid token format: missing access_token');
        }

        $this->tokenStorage->store($token, $userId);
    }

    private function isTokenExpired(): bool
    {
        $token = $this->client->getAccessToken();
        if (empty($token)) {
            return true;
        }

        $expiresAt = $token['created'] + $token['expires_in'] - self::TOKEN_EXPIRY_BUFFER;

        return time() >= $expiresAt;
    }

    private function refreshToken(): array
    {
        try {
            if (!$this->client->getRefreshToken()) {
                throw new Exception('No refresh token available');
            }

            $token = $this->client->fetchAccessTokenWithRefreshToken();

            if (!empty($token['error'])) {
                throw new Exception('Token refresh failed: ' . $token['error']);
            }

            $this->validateAndStoreToken($token);

            return $token;
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function ensureToken(): ?array
    {
        if (!$this->client || !$this->client->getAccessToken()) {
            return null;
        }

        if ($this->isTokenExpired()) {
            try {
                return $this->refreshToken();
            } catch (Exception $e) {
                return null;
            }
        }

        return $this->client->getAccessToken();
    }

    public function locations()
    {
        try {
            $this->initClient();
            $service = new MyBusinessBusinessInformation($this->client);

            $locationsResponse = $service->accounts_locations->listAccountsLocations(
                "accounts/" . auth()->user()->google_id,
                ['readMask' => self::LOCATION_FIELDS, 'pageSize' => 100]
            );

            if (isset($locationsResponse['locations']) && is_array($locationsResponse['locations'])) {
                $locations = $locationsResponse['locations'];
                $savedLocations = [];
                $userId = auth()->id();

                foreach ($locations as $location) {
                    $formattedAddress = '';
                    $storefrontAddress = $location['storefrontAddress'] ?? null;

                    if ($storefrontAddress) {
                        $formattedAddress = implode(', ', array_filter([
                            $storefrontAddress['addressLines'][0] ?? '',
                            $storefrontAddress['locality'] ?? '',
                            $storefrontAddress['administrativeArea'] ?? '',
                            $storefrontAddress['postalCode'] ?? '',
                            $storefrontAddress['regionCode'] ?? '',
                        ]));
                    }

                    $locationData = [
                        'uuid' => Str::uuid(),
                        'store_code' => $location['storeCode'] ?? null,
                        'name' => $location['name'] ?? null,
                        'title' => $location['title'] ?? null,
                        'website_uri' => $location['websiteUri'] ?? null,
                        'primary_phone' => $location['phoneNumbers']['primaryPhone'] ?? null,
                        'primary_category' => $location['categories']['primaryCategory']['displayName'] ?? null,
                        'address_lines' => $location['storefrontAddress']['addressLines'][0] ?? null,
                        'locality' => $location['storefrontAddress']['locality'] ?? null,
                        'region' => $location['storefrontAddress']['administrativeArea'] ?? null,
                        'postal_code' => $location['storefrontAddress']['postalCode'] ?? null,
                        'country_code' => $location['storefrontAddress']['regionCode'] ?? null,
                        'latitude' => $location['latlng']['latitude'] ?? null,
                        'longitude' => $location['latlng']['longitude'] ?? null,
                        'status' => $location['openInfo']['status'] ?? null,
                        'description' => $location['profile']['description'] ?? null,
                        'place_id' => $location['metadata']['placeId'] ?? null,
                        'maps_uri' => $location['metadata']['mapsUri'] ?? null,
                        'new_review_uri' => $location['metadata']['newReviewUri'] ?? null,
                        'formatted_address' => $formattedAddress,
                        'user_id' => $userId,
                    ];

                    $savedLocation = Location::updateOrCreate(
                        [
                            'store_code' => $locationData['store_code'],
                            'user_id' => $userId
                        ],
                        $locationData
                    );

                    $savedLocations[] = $savedLocation;
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Locations synced and saved successfully.',
                    'count' => count($savedLocations),
                    'data' => $savedLocations
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No locations found.',
                    'count' => 0,
                    'data' => [],
                ]);
            }
        } catch (Exception $e) {
            Log::error('Failed to fetch and save locations', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch and save locations: ' . $e->getMessage()
            ], 500);
        }
    }
}

