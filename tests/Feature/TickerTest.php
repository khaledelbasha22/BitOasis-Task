<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TickerTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODAwMFwvYXBpXC9sb2dpbiIsImlhdCI6MTYyNjY5ODYwNywiZXhwIjoxNjI2NzAyMjA3LCJuYmYiOjE2MjY2OTg2MDcsImp0aSI6Im9jSGo2TGlDbW1tb2NlU0kiLCJzdWIiOjEsInBydiI6IjIzYmQ1Yzg5NDlmNjAwYWRiMzllNzAxYzQwMDg3MmRiN2E1OTc2ZjcifQ.krhExA9mACXDQTv-xBqwxsCUFHdDK270QPFHlvXBWqU',
        ])->post('/api/ticker/BTC');

        print_r($response->getStatusCode());

    }
}
