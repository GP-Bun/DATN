<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'receiver_name'  => $this->receiver_name,
            'receiver_phone' => $this->receiver_phone,
            'line1'          => $this->line1,
            'zip'            => $this->zip,
            'is_default'     => (bool) $this->is_default,
            'full_address'   => $this->full_address,
            'province'       => [
                'id'   => $this->province?->id,
                'name' => $this->province?->name,
            ],
            'district'       => [
                'id'   => $this->district?->id,
                'name' => $this->district?->name,
            ],
            'ward'           => [
                'id'   => $this->ward?->id,
                'name' => $this->ward?->name,
            ],
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
