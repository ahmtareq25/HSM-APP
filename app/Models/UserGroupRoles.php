<?php

namespace App\Models;


class UserGroupRoles extends BaseModel
{

    protected $table = 'usergroup_roles';
    protected $guarded = [];

    public function getUserGroupRolesByUserGroupId($usergroup_id)
    {
        return $this->where('usergroup_id', $usergroup_id)->get();
    }

    public function updateData($data)
    {
        $data_updated = false;

        $usergroup_id = $data['usergroup_id'];
        $selected_roles = $data['selected_roles'];

        $usergroup_id = $data->usergroup_id;
        $usergroup = $this->where('usergroup_id', '=', $usergroup_id)->delete();

        if (!empty($selected_roles)) {
            $insert_data = [];
            for ($i = 0; $i < sizeof($selected_roles); $i++) {
                $insert_data[] = [
                    'usergroup_id' => $usergroup_id,
                    'role_id' => $selected_roles[$i]
                ];
            }

            $this->insert($insert_data);
            $data_updated = true;
        }

        return $data_updated;
    }


    public function getAll(){
        return $this->all();
    }
}
