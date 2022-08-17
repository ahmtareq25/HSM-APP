<?php

namespace App\Models;


class UserGroup extends BaseModel
{

    protected $table = 'usergroups';
    protected $guarded = [];

    public function getUserGroupsByCompanyId($company_id)
    {
        return $this->where('company_id', $company_id)->get(['id', 'name']);
    }

    public function getAllUserGroup($search = [], $panination = 10)
    {
        $query = $this->query();
        return $query->paginate($panination);
    }

    public function getUserGroupById($id, array $filters = [])
    {
        $query = $this->getAllUserGroup();
        return $query->where('id', $id)
            ->when(array_key_exists('company_id', $filters), function ($query) use ($filters) {
                return $query->where('company_id', $filters['company_id']);
            })
            ->first();
    }

    public function createData($data)
    {
        return $this->create($data);
    }

    public function updateData($data, $id, array $filters = [])
    {
        return $this->where('id', $id)
            /*->when(array_key_exists('company_id', $filters), function ($query) use ($filters) {
                return $query->where('company_id', $filters['company_id']);
            })*/
            ->update($data);
    }

    public function deleteData($id, array $filters = [])
    {
        return $this->where('id', $id)
            ->when(array_key_exists('company_id', $filters), function ($query) use ($filters) {
                return $query->where('company_id', $filters['company_id']);
            })
            ->delete();
    }

    public function getById($id)
    {
        return $this->findOrFail($id);
    }
}
