<?php

namespace App\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'title' => $this->title,
            'status' => (bool)$this->status,
            'created_at' => $this->whenNotNull(format_datetime($this->created_at)),
            'updated_at' => $this->whenNotNull(format_datetime($this->updated_at))
        ];
    }
}
