<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class Role extends BaseModel
{

    protected $guarded = [];


    public function getRoles(array $filters = [], array $columns = ['*'], bool $pagination = false): array|Collection|LengthAwarePaginator
    {
        $roles = $this->query();

        if (array_key_exists('company_id', $filters)) {
            $roles->where('company_id', $filters['company_id']);
        }

        if (array_key_exists('title', $filters)) {
            $roles->where('title', 'like', "%{$filters['title']}%");
        }

        if (array_key_exists('status', $filters)) {
            $roles->where('status', $filters['status']);
        }

        $roles->orderBy($filters['order_by'] ?? 'updated_at', $filters['order_by_direction'] ?? 'DESC');

        $roles->select($columns);

        if ($pagination) {
            return $roles
                ->paginate(
                    $this->getItemPerPage($filters['item_per_page'] ?? null)
                )
                ->withQueryString();
        }

        return $roles->get();
    }

    public function createNewRole(array $data): Role
    {
        return Role::create(
            $this->prepareRoleData($data)
        );
    }

    public function findById(int $role_id): Role
    {
        return Role::findOrFail($role_id);
    }

    public function updateRole($role_id, array $data, array $filters = []): bool|int
    {
        return Role::where('id', $role_id)
            /*->when(array_key_exists('company_id', $filters), function ($query) use ($filters) {
                return $query->where('company_id', $filters['company_id']);
            })*/
            ->update(
                $this->prepareRoleData($data)
            );
    }

    public function deleteRole(int $role_id, array $filters = []): bool
    {
        return $this->query()
            ->where('id', $role_id)
            ->when(array_key_exists('company_id', $filters), function ($query) use ($filters) {
                return $query->where('company_id', $filters['company_id']);
            })
            ->delete();
    }

    private function prepareRoleData(array $data): array
    {
        $role_data = [];

        if (array_key_exists('company_id', $data)) {
            $role_data['company_id'] = $data['company_id'];
        }

        if (array_key_exists('title', $data)) {
            $role_data['title'] = $data['title'];
        }

        if (array_key_exists('status', $data)) {
            $role_data['status'] = $data['status'];
        }

        return $role_data;
    }
}
