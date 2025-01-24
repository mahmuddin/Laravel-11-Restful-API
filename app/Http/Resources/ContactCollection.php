<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactCollection extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource instanceof LengthAwarePaginator) {
            return [
                'data' => ContactResource::collection($this->resource),
                'meta' => [
                    'total' => $this->resource->total(),
                    'current_page' => $this->resource->currentPage(),
                    'last_page' => $this->resource->lastPage(),
                    'per_page' => $this->resource->perPage(),
                    'next_page_url' => $this->resource->nextPageUrl(),
                    'prev_page_url' => $this->resource->previousPageUrl(),
                    'from' => $this->resource->firstItem(),
                ],
            ];
        } else {
            // Handle the case where $this->resource is not an instance of LengthAwarePaginator
            return [];
        }
    }
}
