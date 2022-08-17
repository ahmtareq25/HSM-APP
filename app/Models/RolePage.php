<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePage extends BaseModel
{

    protected $table = 'role_pages';
    protected $guarded = [];

    public function getSelectedpages($id, $user)
    {
        $roles = [];
        $selected_pages = [];
        $selected_sub_module_list = [];

        $usertype_submodule_list = Module::join('submodules', 'submodules.module_id', 'modules.id')
            ->join('user_type_submodules', 'user_type_submodules.submodule_id', 'submodules.id')
            ->where('user_type_submodules.user_type_id', $user['user_type_id']) // here have to give user type id
            ->select(
                'modules.id as module_id',
                'submodules.id as submodule_id'
            )->get();

        $module_ids_array = $usertype_submodule_list->unique('module_id')->pluck('module_id')->toArray();
        $usertype_submodule_ids_array = $usertype_submodule_list->unique('submodule_id')->pluck('submodule_id')->toArray();

        $modules_assoc = Module::with(['submodules.pages'])
            ->with(['submodules' => function ($query) use ($usertype_submodule_ids_array) {
                $query->whereIn('id', $usertype_submodule_ids_array)->orderBy('sequence', 'ASC');
            }])
            ->whereIn('id', $module_ids_array);

        $modules_assoc = $modules_assoc->get();

        if ($id > 0) {
            $roles = RolePage::where('role_id', $id)->get();
        }

        if (!empty($roles)) {
            foreach ($roles as $role) {
                $selected_pages[] = $role->page_id;
            }
        }

        $modules_array = [];
        $module_submodule_assoc = [];
        if (!empty($modules_assoc)) {
            foreach ($modules_assoc as $module_info) {
                if (in_array($module_info->id, $module_ids_array)) {
                    $modules_array[$module_info->id] = $module_info->name;
                }
                if (!empty($module_info->submodules)) {
                    foreach ($module_info->submodules as $submodules) {
                        $module_submodule_assoc[$module_info->id][] = $submodules->id;
                        if (!empty($submodules)) {
                            $submoduleselected = 0;
                            foreach ($submodules->pages as $page) {
                                if (in_array($page->id, $selected_pages)) {
                                    $submoduleselected++;
                                }
                            }
                            $selected_sub_module_list[$submodules->id] = 0;
                            if (count($submodules->pages) == $submoduleselected) {
                                $selected_sub_module_list[$submodules->id] = 1;
                            }
                        }
                    }
                }
            }
        }
        $selected_module_list = [];
        if (!empty($module_submodule_assoc)) {
            foreach ($module_submodule_assoc as $module_id => $submodule_list) {
                $selected_module_list[$module_id] = 0;
                if (!empty($submodule_list)) {
                    $selected_module = 0;
                    foreach ($submodule_list as $submodule_id) {
                        if ($selected_sub_module_list[$submodule_id] == 1) {
                            $selected_module++;
                        }
                    }

                    if (count($submodule_list) == $selected_module) {
                        $selected_module_list[$module_id] = 1;
                    }
                }
            }
        }


        $response['modules_info'] = $modules_assoc;
        $response['selected_module_list'] = $selected_module_list;
        $response['selected_sub_module_list'] = $selected_sub_module_list;
        $response['selected_pages'] = $selected_pages;

        return $response;
    }

    public function updateData($data, $user)
    {
        $data_updated = false;

        $selected_page_id_list = $data->selected_page_ids;
        $role_id = $data->role_id;

        if ($role_id > 0 && !empty($selected_page_id_list)) {
            $insert_data = array();

            $admin_pages = [];
            $usertype_submodules = UserTypeSubmodules::with(['sub_module', 'sub_module.pages'])
                                    ->where('user_type_id', $user['user_type_id'])
                                    ->get(); //user type in place of 1

            foreach ($usertype_submodules as $sub_module) {
                if (!empty($sub_module->sub_module)) {
                    $all_pages = $sub_module->sub_module->pages;
                    if (!empty($all_pages)) {
                        foreach ($all_pages as $page) {
                            $admin_pages[] = $page->id;
                        }
                    }
                }
            }

            foreach ($selected_page_id_list as $page_id) {
                if (in_array($page_id, $admin_pages)) {
                    $insert_data[] = ['role_id' => $role_id, 'page_id' => $page_id];
                }
            }

            $roles = RolePage::where('role_id', $role_id)->delete();
            RolePage::insert($insert_data);

            $data_updated = true;

            /* $user_group_roles = UserGroupRoles::where('role_id',$role_id)->get();
            $user_group_ids = [];

            if(!empty($user_group_roles)){
                foreach ($user_group_roles as $user_group_role){
                    $user_group_ids[] = $user_group_role->usergroup_id;
                }

                $user_group_ids = array_unique($user_group_ids);
                $user_groups = [];
                if(!empty($user_group_ids)){
                    $user_groups = UserUsergroup::whereIn('usergroup_id',$user_group_ids)->get();
                }
                $user_ids = [];
                if(!empty($user_groups)){
                    foreach($user_groups as $user_group){
                        $user_ids[] = $user_group->user_id;//['id'=>$user_group->user_id];
                    }
                }

                $user_ids = array_unique($user_ids);
                if(!empty($user_ids)){
                    $user = User::whereIn('id',$user_ids)->update(['permission_version'=>DB::raw('permission_version+1')]);

                }
                \DB::commit();

                $status_code = 100;
                $status_message = 'The record has been updated successfully!';

            } */
        }

        return $data_updated;
    }

    public function getRolePagesByRoleId($role_id)
    {
        if (is_array($role_id)) {
            return self::query()->whereIn('role_id', $role_id)->get();
        }
        return $this->where('role_id', $role_id)->get();
    }

    public function getRolePagePermissionData()
    {
        return self::query()->join('usergroup_roles', 'usergroup_roles.role_id', '=', 'role_pages.role_id')
            ->join('pages', 'pages.id', '=', 'role_pages.page_id')
            ->select(['usergroup_roles.usergroup_id', 'pages.route_name'])
            ->get()->toArray();

    }
}
