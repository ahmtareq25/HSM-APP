<?php

namespace App\Http\Base\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    private mixed $token_data;
    private mixed $permissions;

    public function __construct($resource, mixed $permissions = null, mixed $token_data = null)
    {
        parent::__construct($resource);
        $this->permissions = $permissions;
        $this->token_data = $token_data;
    }

    public function toArray($request): array
    {
        $resource = [];

        if (! empty($this->token_data)) {
            $resource = array_merge($resource, [
                'token_type' => $this->token_data['token_type'],
                'expires_in' => $this->token_data['expires_in'],
                'access_token' => $this->token_data['access_token'],
                'refresh_token' => $this->token_data['refresh_token']
            ]);
        }

        $resource = array_merge($resource, [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'email' => $this->email,
            'language' => $this->language,
            'created_at' => format_datetime($this->created_at),
            'updated_at' => format_datetime($this->updated_at)
        ]);

        if (! empty($this->permissions)) {
            $resource = array_merge($resource, [
                'permissions' => $this->permissions
            ]);
        }

        return $resource;
    }
}
