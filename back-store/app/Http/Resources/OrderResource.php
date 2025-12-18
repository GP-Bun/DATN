<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'status'         => $this->order_status,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'final_amount'   => $this->final_amount,
            'created_at'     => $this->created_at->toDateTimeString(),
            'address'        => [
                'receiver_name'  => $this->address->receiver_name ?? null,
                'receiver_phone' => $this->address->receiver_phone ?? null,
                'line1'          => $this->address->line1 ?? null,
                'city'           => $this->address->city ?? null,
                'province'       => $this->address->province ?? null,
            ],
            'items' => $this->items->map(function ($item) {
                return [
                    'id'           => $item->id,
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product_name,
                    'variant_id'   => $item->variant_id,
                    'quantity'     => $item->quantity,
                    'price'        => $item->price,
                    'total'        => $item->quantity * $item->price,
                ];
            }),
        ];
    }
}
