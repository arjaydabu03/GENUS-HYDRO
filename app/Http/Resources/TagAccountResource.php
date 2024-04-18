<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TagAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            "id" => $this->location_id,
            "code" => $this->location_code,
        ];
    }
}
