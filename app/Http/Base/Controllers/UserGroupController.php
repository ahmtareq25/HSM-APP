<?php

namespace App\Http\Base\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserGroup;
use App\Http\Base\Requests\UserGroupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserGroupController extends Controller
{
     public function index()
     {
          $log_action = 'GET_USER_GROUP_DATA';
          try{
               $data = (new UserGroup)->getAllUserGroup();

               $logData = [
                    'action' => $log_action,
                    'data' => $data,
               ];
               appLog()->createLog($logData);
               return appResponse()->success(config('status_code.success'), 'Success', $data);
          }catch(\Exception $e){
               appLog()->errorHandlingLog($e, $log_action);
               return appResponse()->failed(config('status_code.server_error'), 'failed', []);
          }

     }

    public function list(Request $request)
    {
        $company_id = $request->company_id ?? 0;
        $log_action = 'GET_USER_GROUP_DATA_WITH_COMPANY_ID';
        try {
            $user_groups = (new UserGroup())->getUserGroupsByCompanyId($company_id);
            $logData = [
                'action' => $log_action,
                'data' => $user_groups,
            ];
            appLog()->createLog($logData);
            return appResponse()->success(config('status_code.success'), __('Success'), $user_groups);
        } catch (\Throwable $th) {
            appLog()->errorHandlingLog($th, $log_action);
            return appResponse()->failed(config('status_code.server_error'), __('failed'), $th->getMessage());
        }

    }

     public function create(UserGroupRequest $request){

          $log_action = 'CREATE_USER_GROUP_DATA';
          try{
               $insert_data = [
                    'name' => $request->name,
               ];

               $data = (new UserGroup)->createData($insert_data);

               $logData = [
                    'action' => $log_action,
                    'data' => $data,
               ];

               appLog()->createLog($logData);

               return appResponse()->success(config('status_code.success'), 'Success', $data);
          }catch(\Exception $e){
               appLog()->errorHandlingLog($e, $log_action);
               return appResponse()->failed(config('status_code.server_error'), 'failed', []);
          }
     }

     public function update(UserGroupRequest $request, $id){

          $log_action = 'UPDATE_USER_GROUP_DATA';
          try{
               $insert_data = [
                    'name' => $request->name,
               ];

              $data = $insert_data;
              $data['id'] = $id;

               if((new UserGroup)->updateData($insert_data, $id)){
                   $response = appResponse()->success(config('status_code.success'), 'Success', $data);
               }else{
                   $data['update_status'] = false;
                   $response = appResponse()->failed(config('status_code.failed'), 'failed', []);
               }

               $logData = [
                    'action' => $log_action,
                    'data' => $data,
               ];

               appLog()->createLog($logData);

               return $response;
          }catch(\Exception $e){
               appLog()->errorHandlingLog($e, $log_action);
               return appResponse()->failed(config('status_code.server_error'), 'failed', []);
          }
     }

     public function destroy($id){

          $log_action = 'DELETE_USER_GROUP_DATA';
          try{

               $data = (new UserGroup)->deleteData($id);

               $logData = [
                    'action' => $log_action,
                    'data' => $data,
               ];

               appLog()->createLog($logData);

               return appResponse()->success(config('status_code.success'), 'Success', $data);
          }catch(\Exception $e){
               appLog()->errorHandlingLog($e, $log_action);
               return appResponse()->failed(config('status_code.server_error'), 'failed', []);
          }
     }

     public function show($id){
          $log_action = 'SHOW_USER_GROUP_DATA';
          try{
              $filters = [
                  'company_id' => Auth::user()->company_id
              ];
               $data = (new UserGroup)->getUserGroupById($id, $filters);

               $logData = [
                    'action' => $log_action,
                    'data' => $data,
               ];

               appLog()->createLog($logData);

               return appResponse()->success(config('status_code.success'), 'Success', $data);
          }catch(\Exception $e){
               appLog()->errorHandlingLog($e, $log_action);
               return appResponse()->failed(config('status_code.server_error'), 'failed', []);
          }
     }

}
