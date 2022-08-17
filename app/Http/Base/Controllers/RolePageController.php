<?php

namespace App\Http\Base\Controllers;

use App\Http\Base\Requests\UpdateRolePageRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRolePageRequest;
use App\Models\RolePage;
use App\Services\PermissionManagement;

class RolePageController extends Controller
{
    public function index()
    {
        //
    }

    public function list($id)
    {
        $log_action = 'GET_ROLE_PAGE_DATA';

        $user['user_type_id'] = 1;

        try{
            $data = (new RolePage)->getSelectedpages($id,$user);

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

    public function update(UpdateRolePageRequest $request)
    {
        $log_action = 'UPDATE_ROLE_PAGE_DATA';

        $user['user_type_id'] = 1;

        try{
            $logData = [
                'action' => $log_action,
                'request' => $request,
            ];

            $data = (new RolePage())->updateData($request,$user);
            //call reset permission
            (new PermissionManagement())->resetAccessPermission();
            //
            appLog()->createLog($logData);
            return appResponse()->success(config('status_code.success'), __('Success'), []);
        }catch(\Exception $e){
            appLog()->errorHandlingLog($e, $log_action);
            return appResponse()->failed(config('status_code.server_error'), __('failed'), $e->getMessage());
        }
    }

}
