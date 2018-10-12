<?php

namespace CaffeineInteractive\Remzy\App\BenuTech;

use CaffeineInteractive\Remzy\App\PropLoader\PropLoaderInterface;
use CaffeineInteractive\Remzy\App\PropLoader\PropLoader;
use CaffeineInteractive\Remzy\App\Models\Property;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Cookie\CookieJar;

class Api
{    
    protected $client;

    protected $service = "webservices";

    protected $partnerKey = null;

    protected $ttbsid;

    public function __construct()
    {
        $this->partnerKey = env("BENUTECH_PARTNER_KEY");
        $this->client =  new Client([
            'base_uri' => 'https://direct.api.titletoolbox.com/'
        ]);
    }

    static function login()
    {
        $api = new Api;
        $client = $api->client;

        $payload = [
            "TbUser" =>[
                "username" => env('BENUTECH_USER'),
                "password" => env('BENUTECH_PASS')
            ]
        ];

        $response = $client->post($api->service . '/login.json', [
            'debug' => false,
            'body' => json_encode($payload),
            'headers' => [
                'cache-control' => 'no-cache',
                'content-type' => 'application/x-www-form-urlencoded',
                'partner-key' => $api->partnerKey
            ]
        ]);

        $ttbsid = explode("=", explode(";", $response->getHeaders()["Set-Cookie"][1])[0])[1];

        return $ttbsid;
    }

    /**
     * $payload:
     * - site_zip
     * - site_address
     *
     * @return void
     */
    public function globalSearch($payload, $limit = 20, $page = 1, $order = null)
    {
 
        $ttbsid = self::login();
        $this->ttbsid = $ttbsid;
        $client = $this->client;
        $endPoint = "global_search.json";
        $params = [
            "limit" => $limit,
            "page" => $page
        ];

        if($order)
        {
            $params["sort"] = $order["sort"];
            $params["direction"] = $order["direction"];      
        }

        $queryParams = http_build_query($params);
        $response = $client->post($this->service . '/' . $endPoint . '?' . $queryParams, [
            'debug' => false,
            'body' => json_encode($payload),
            'headers' => [
                'Partner-Key' => $this->partnerKey,
                'Cookie' => 'TTBSID=' . $ttbsid 
            ]
        ]);

        $contents = $response->getBody()->getContents();

        return json_decode($contents)->response;
    }

    /**
     * Populate null data in db.
     * 
     * @param  [type] $propertyId [description]
     * @param  [type] $stateFips  [description]
     * @return [type]             [description]
     */
    public static function propDetailsPopulateNull($property)
    {
        $propertyId = $property->apiId;
        $stateFips = $property->fips;
        $api = new Api;
        $apiData = $api->propertyDetails($propertyId, $stateFips);
        $halfBath = null;

        if((!$property->half_baths || $property->half_baths == 0) && isset($apiData->data->PropertyInfo))
        {
            $halfBath = (isset($apiData->data->PropertyInfo->{'Partial Bathroom(s)'}) ? $apiData->data->PropertyInfo->{'Partial Bathroom(s)'} : null);

            if( $halfBath)
            {
                $halfBath = trim(str_replace("-", "", $halfBath));
                $halfBath = $halfBath!="" ? $halfBath : null;
            }
        }

        $lotArea = null;
        
        if(isset($apiData->data->PropertyInfo->{"Lot Size"}))
        {
            $lotArea = str_replace(",", "", str_replace("-", "", $apiData->data->PropertyInfo->{"Lot Size"}));
        }

        if($lotArea && $lotArea != "")
        {
            $property->lot_area = $lotArea;
        }

        if($halfBath)
        {
            $property->half_baths = $halfBath;
        }

        if(isset($apiData->data->OwnerInfo) && $apiData->data->OwnerInfo)
        {   
           $property->owner()->delete();
           $property->owner()->create([
            'name' => $apiData->data->OwnerInfo->{"Owner Name"},
            'address' => $apiData->data->OwnerInfo->{"Mailing Address"} . ', ' . $apiData->data->OwnerInfo->{"Mailing City & State"} . ', ' . $apiData->data->OwnerInfo->{"Mailing Zip"}
           ]);
        }

        $property->save();
        return $property;
    }

    /**
     * Property Data with AVM
     * @return 
     */
    public function propertyDetails($propertyId, $stateFips)
    {
        $ttbsid = self::login();
        $this->ttbsid = $ttbsid;        
        $client = $this->client;
        $endPoint = "property_details.json";
        $body = json_encode([
            "property_id" => $propertyId,
            "state_fips" => $stateFips
        ]);

        $response = $client->get($this->service . '/' . $endPoint, [
            'debug' => false,
            'headers' => [
                'Partner-Key' => $this->partnerKey,
                'Cookie' => 'TTBSID=' . $ttbsid 
            ],
            'body' => $body
        ]);

        $contents = $response->getBody()->getContents();
        $data = json_decode($contents);

        return isset($data->response) ? $data->response : null;
    }

    public function netSheetAndDetails($propertyId, $stateFips)
    {
        /**
         * Propety Details
         */
        $detailContents = $this->propertyDetails($propertyId, $stateFips);

        /**
         * NetSheet
         */
        $client = $this->client;
        $endPoint = "get_netsheet";
        $response = $client->get($this->service . '/' . $endPoint . '/' .$propertyId . '.json', [
            'debug' => false,
            'headers' => [
                'Partner-Key' => $this->partnerKey,
                'Cookie' => 'TTBSID=' . $this->ttbsid
            ]
        ]);
        $netSheetContents = $response->getBody()->getContents();

        return [
            "netSheet" => json_decode($netSheetContents)->response->data,
            "propertyDetails" => $detailContents->data
        ];        
    }
}