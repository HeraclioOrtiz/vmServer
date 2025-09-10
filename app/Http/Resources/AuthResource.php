<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->resource['token'],
            'user' => new UserResource($this->resource['user']),
            'fetched_from_api' => $this->resource['fetched_from_api'],
            'refreshed' => $this->when(
                isset($this->resource['refreshed']), 
                $this->resource['refreshed']
            ),
        ];
    }
}
