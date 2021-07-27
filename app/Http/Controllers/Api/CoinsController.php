<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Cache;
use App\Http\Resources\CoinsResource;
use Illuminate\Http\Request;
use stdClass;


class CoinsController extends ApiBaseController
{


    /**
     * Create a new CoinsController instance.
     *
     * @return void
     */

    public function __construct() {
        $this->middleware('auth:api', ['except' => ['coins']]);
    }


    /**
     * Get a Coins with sorting.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function coins(Request $request){
        $RequestTimestamp = time();

        $Sort = $this->Sorting[0];

        /**
         * Check if Request sorting exist
         * if Exist and in sorting array
         * Change the Sort value with Request Sort Value
         */

        if($request->input('sort')){
            $RequestSort = $request->input('sort');
            if (in_array($RequestSort, $this->Sorting)) {
                $Sort = $RequestSort;
            }
        }

        /**
         * Check if Coins Data from http://alternative.me in Caching
         */

        $CoinSortCacheKey = $this->CoinsCacheKey . "@" . $Sort;
        if (Cache::has($CoinSortCacheKey)) {
            $CoinsDataCached = Cache::get($CoinSortCacheKey);
            if(isset($CoinsDataCached)){
                return $this->sendResponse(CoinsResource::collection($CoinsDataCached), 'Coins retrieved successfully from cache', $RequestTimestamp);
            }
        }




        $cURL = curl_init();

        curl_setopt_array($cURL, array(
            CURLOPT_URL => $this->GetCoinsUrl . "?sort=" . $Sort ,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $alternativeResponse = curl_exec($cURL);
        $alternativeErr = curl_error($cURL);
        curl_close ($cURL);


        // Check if Curl response Error
        if($alternativeErr){
            return $this->sendError("Error from alternative.me", [],500, $RequestTimestamp);
        }

        // Convert response to json
        $decodedData = json_decode($alternativeResponse);


        // Check if Response data not exist
        if(!isset($decodedData->data)){
            return $this->sendError("Error in Data from  alternative.me", [],500, $RequestTimestamp);
        }

        // check if response data not an array
        if(!is_array($decodedData->data)){
            return $this->sendError("Error in Data array from  alternative.me", [],500, $RequestTimestamp);
        }


        /**
         * handling response data in array with Keys = code
         * Saving data in cache with time limit
         */
        $CoinsResponseArr = [];
        foreach ($decodedData->data as $CoinResponse){
            $Coin = new stdClass;
            $Coin->id = $CoinResponse->id;
            $Coin->code = $CoinResponse->symbol;
            $Coin->name = $CoinResponse->name;
            array_push($CoinsResponseArr,$Coin);
        }
        Cache::put($this->CoinsCacheKey . "@" . $Sort, $CoinsResponseArr, $this->coinsCacheLimitTime);

        return $this->sendResponse(CoinsResource::collection($CoinsResponseArr), 'Coins retrieved successfully from alternative.me', $RequestTimestamp);


    }
}
