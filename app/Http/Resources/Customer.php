<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Customer extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray([
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'addition' => $this->addition,
            'street' => $this->street,
        ]);
    }
}
