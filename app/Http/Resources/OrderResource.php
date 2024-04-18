<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            
            "material" => [
                "id" => $this->material_id,
                "code" => $this->material_code,
                "name" => $this->material_name,
            ],

            "category" => [
                "id" => $this->category_id,
                "name" => $this->category_name,
            ],
            "uom" => [
                "id" => $this->uom_id,
                "code" => $this->uom_code,
            ],

            "quantity" => $this->quantity,
            "remarks" => $this->remarks,
            "deleted_at" => $this->deleted_at,
        ];
    }
}
