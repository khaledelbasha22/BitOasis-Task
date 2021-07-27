<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Response;


class ApiBaseController extends Controller
{
    public $Sorting = ["ASC", "DESC"];

    public $GetCoinsUrl = "https://api.alternative.me/v2/listings/";
    public $GetTickerUrl = "https://api.alternative.me/v2/ticker/";

    public $CoinsCacheKey = "coins";
    public $TickerCacheKey = "ticker";

    public $coinsCacheLimitTime = (60 * 60) * 24; // One day Caching
    public $tickerCacheLimitTime = (60 * 5); // 5 min Caching


    public function sendResponse($result, $message, $timestamp)
    {
        $responseData =  [
            'success'      => true,
            'message'      => $message,
            'data'         => $result,
            'timestamp'    => $timestamp,

        ];
        return Response::json($responseData);
    }

    public function sendError($error, array $data = [], $code = 404, $timestamp )
    {
        $responseData = [
            'success' => false,
            'error' => $code,
            'message' => $error,
            'timestamp' => $timestamp,
        ];

        if (!empty($data)) {
            $res['data'] = $data;
        }
        return Response::json($responseData, $code);
    }

    public function sendSuccess($message, $timestamp )
    {
        return Response::json([
            'success' => true,
            'message' => $message,
            'timestamp' => $timestamp,
        ], 200);
    }
}
