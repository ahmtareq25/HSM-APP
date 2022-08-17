<?php

namespace App\Http\Base\Controllers;

use App\Http\Base\Requests\UpdateUserGroupRolesRequest;
use App\Http\Controllers\Controller;
use App\Models\UserGroupRoles;

class UserGroupRolesController extends Controller
{

    public function index()
    {
        //
    }

    public function list($id)
    {
        $log_action = 'GET_USER_GROUP_ROLES_DATA';
        try{
            $data = (new UserGroupRoles)->getUserGroupRolesByUserGroupId($id);

            $logData = [
                'action' => $log_action,
                'data' => $data,
            ];
            appLog()->createLog($logData);
            return appResponse()->success(config('status_code.success'), __('Success'), $data);
        }catch(\Exception $e){
            appLog()->errorHandlingLog($e, $log_action);
            return appResponse()->failed(config('status_code.server_error'), __('failed'), $e->getMessage());
        }
    }

    public function update(UpdateUserGroupRolesRequest $request,$id)
    {
        $log_action = 'UPDATE_USER_GROUP_ROLES_DATA';
        try{
            $logData = [
                'action' => $log_action,
                'request' => $request,
            ];

            $data = (new UserGroupRoles)->updateData($request);

            appLog()->createLog($logData);
            return appResponse()->success(config('status_code.success'), __('Success'), []);
        }catch(\Exception $e){
            appLog()->errorHandlingLog($e, $log_action);
            return appResponse()->failed(config('status_code.server_error'), __('failed'), $e->getMessage());
        }
    }

}
