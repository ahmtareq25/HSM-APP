<?php

namespace App\Services;

use App\Models\RolePage;
use App\Models\User;

class PermissionManagement
{
    public function checkPermissionByAuth(User $user, $current_route){
        if (!empty($user) && !empty($user->usergroup_ids)){
            $group_ids = explode(',', $user->usergroup_ids);

            foreach ($group_ids as $group_id) {
                if ($this->checkPermission($current_route, $group_id)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function checkPermission($route_name, $user_group_id){
        $permissionArray = $this->getPermissionFromCache();
        //if (empty($permissionArray)){
        //    $permissionArray =  $this->resetAccessPermission();
        //}

       /*return (
           isset($permissionArray[$route_name])
           && in_array($user_group_id, $permissionArray[$route_name])
       );*/

        return (
            isset($permissionArray[$user_group_id])
            && in_array($route_name, $permissionArray[$user_group_id])
        );
    }

    public function resetAccessPermission(){
        $permissionArray = $this->preparePermissionArray();
        $this->setPermissionToCache($permissionArray);

        return $permissionArray;
    }

    private function preparePermissionArray(){
        $rows =  (new RolePage())->getRolePagePermissionData();
        $permissionArray  = [];
        foreach ($rows as $value) {
//            $permissionArray[$value['route_name']][] = $value['usergroup_id'];
            $permissionArray[$value['usergroup_id']][] = $value['route_name'];
        }
        return $permissionArray;

    }

    private function setPermissionToCache($permissionArray){
        if (!empty($permissionArray)){
            CacheManagement::setSystemCache(CacheManagement::PERMISSION_GROUP_NAME,$permissionArray);
        }
    }

    public function getPermissionFromCache(){
       return CacheManagement::getSystemCache(CacheManagement::PERMISSION_GROUP_NAME);
    }

}
