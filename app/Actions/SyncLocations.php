<?php
// App/Actions/SyncLocations.php

namespace App\Actions;

use App\Models\Location;
use App\Services\GoogleAuthService;
use Exception;
use Google\Service\MyBusinessBusinessInformation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncLocations
{
    private const LOCATION_FIELDS = 'name,languageCode,storeCode,title,phoneNumbers,categories,storefrontAddress,websiteUri,regularHours,specialHours,serviceArea,labels,adWordsLocationExtensions,latlng,openInfo,metadata,profile,relationshipData,moreHours,serviceItems';

    public function fetchLocations($userId)
    {
        try {
            $client = new GoogleAuthService($userId);
            $service = new MyBusinessBusinessInformation($client->getClient());

            $locationsResponse = $service->accounts_locations->listAccountsLocations(
                auth()->user()->google_id,
                ['readMask' => self::LOCATION_FIELDS, 'pageSize' => 100]
            );

            if (!isset($locationsResponse['locations']) || !is_array($locationsResponse['locations'])) {
                return [];
            }

            return array_map(function($location) {
                return [
                    'store_code' => $location['storeCode'] ?? null,
                    'title' => $location['title'] ?? null,
                    'address' => $this->formatAddress($location['storefrontAddress'] ?? null),
                    'phone' => $location['phoneNumbers']['primaryPhone'] ?? null,
                    'category' => $location['categories']['primaryCategory']['displayName'] ?? null,
                    'raw_data' => $location // Store full data for later use
                ];
            }, $locationsResponse['locations']);
        } catch (Exception $e) {
            Log::error($e);
            return false;
        }
    }

    public function executeSelected($userId, array $selectedStoreCodes)
    {
        try {
            $locations = $this->fetchLocations($userId);
            
            if (!$locations) {
                return false;
            }

            foreach ($locations as $location) {
                if (!in_array($location['store_code'], $selectedStoreCodes)) {
                    continue;
                }

                $this->saveLocation($location['raw_data'], $userId);
            }

            return true;
        } catch (Exception $e) {
            Log::error($e);
            return false;
        }
    }

    private function saveLocation($location, $userId)
    {
        $formattedAddress = $this->formatAddress($location['storefrontAddress'] ?? null);

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

        Location::updateOrCreate(
            [
                'store_code' => $locationData['store_code'],
                'user_id' => $userId,
            ],
            $locationData
        );
    }

    private function formatAddress($storefrontAddress)
    {
        if (!$storefrontAddress) {
            return '';
        }

        return implode(', ', array_filter([
            $storefrontAddress['addressLines'][0] ?? '',
            $storefrontAddress['locality'] ?? '',
            $storefrontAddress['administrativeArea'] ?? '',
            $storefrontAddress['postalCode'] ?? '',
            $storefrontAddress['regionCode'] ?? '',
        ]));
    }
}