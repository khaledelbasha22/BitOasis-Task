<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CoinsResource;
use App\Http\Resources\TickerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use function PHPUnit\Framework\isEmpty;
use stdClass;

class TickerController extends ApiBaseController
{



    /**
     * Create a new TickerController instance.
     *
     * @return void
     */


    public function __construct() {
        $this->middleware('auth:api', ['except' => []]);
    }


    public function ticker($coin_code, Request $request){
        $RequestTimestamp = time();
        /**
         * Check if Coin Ticker Data from this Code http://alternative.me in Caching
         */

        $CoinTickerCacheKey = $this->TickerCacheKey . "@" . $coin_code ;
        if (Cache::has($CoinTickerCacheKey)) {
            $tickerDataCached = Cache::get($CoinTickerCacheKey);
            if(isset($tickerDataCached)){

                return $this->sendResponse(new TickerResource($tickerDataCached), 'Ticker retrieved successfully from cache', $RequestTimestamp);
            }
        }

        $CoinSortCacheKey = "";
        foreach ($this->Sorting as $Sort){
            $tempCoinSortCacheKey = $this->CoinsCacheKey . "@" . $Sort;
            if (Cache::has($tempCoinSortCacheKey)) {
                $CoinSortCacheKey = $tempCoinSortCacheKey;
                break;
            }
        }

        if($CoinSortCacheKey == ""){
            $CoinsController  = new CoinsController();
            $CoinsResponse = $CoinsController->coins($request);
            if(!$CoinsResponse->getData()->success){
                return $CoinsResponse;
            }
        }
        $CoinSortCacheKey = $this->CoinsCacheKey . "@" . $this->Sorting[0];

        if (!Cache::has($CoinSortCacheKey)) {
            return $this->sendError("Error in Server caching", [],500, $RequestTimestamp);
        }


        $CoinID = $this->GetCoinID($coin_code,$CoinSortCacheKey);

        if($CoinID === ""){
            return $this->sendError("Coin code not exist", [],500, $RequestTimestamp);
        }



        /**
         * Check if Coin ticker Data from this Code http://alternative.me in Caching
         */



        $cURL = curl_init();

        $tickerUrl = $this->GetTickerUrl . $CoinID . "/";

        curl_setopt_array($cURL, array(
            CURLOPT_URL => $tickerUrl ,
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
            return $this->sendError("no data for this code from  alternative.me", [],500, $RequestTimestamp);
        }


        /**
         * handling response data in array with Keys = code
         * Saving data in cache with time limit
         */

        foreach ($decodedData->data as $TickerResponse){

            $TickerData = new stdClass;
            $TickerData->id = $TickerResponse->id;
            $TickerData->code = $TickerResponse->symbol;
            $TickerData->price = $TickerResponse->quotes->USD->price;
            $TickerData->volume = $TickerResponse->quotes->USD->volume_24h;
            $TickerData->daily_change = $TickerResponse->quotes->USD->percentage_change_24h;
            $TickerData->last_updated = $TickerResponse->last_updated;
        }
        Cache::put($CoinTickerCacheKey, $TickerData, $this->tickerCacheLimitTime);

        return $this->sendResponse(new TickerResource($TickerData), 'Ticker retrieved successfully from alternative.me', $RequestTimestamp);


    }


    private function GetCoinID($coin_code, $CoinSortCacheKey){
        $CoinID = "";
        $CoinsDataCached = Cache::get($CoinSortCacheKey);

        foreach ($CoinsDataCached as $CoinData){
            $lowerCoinCode = strtolower($coin_code);
            $lowerCoinDataCode = strtolower($CoinData->code);
            if($lowerCoinCode === $lowerCoinDataCode){
                $CoinID = $CoinData->id;
                break;
            }
        }
        return $CoinID;
    }
}
