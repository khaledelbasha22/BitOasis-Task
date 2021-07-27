<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TickerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'code' => $this->code,
            'price' => $this->price,
            'volume' => $this->volume,
            'daily_change' => $this->daily_change,
            'last_updated' => $this->last_updated,
        ];
    }






}
