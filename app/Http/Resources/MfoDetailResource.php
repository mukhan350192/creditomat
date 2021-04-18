<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MfoDetailResource extends JsonResource
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
            'description' => $this->description,
            'background_img' => $this->background_img,
            'address' => $this->address,
            'email' => $this->email,
            'phone' => $this->phone,
            'documents' => $this->documents,
            'url' => $this->url,
        ];
    }
}
