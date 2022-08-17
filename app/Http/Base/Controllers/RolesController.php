<?php

namespace App\Http\Base\Controllers;

use App\Http\Base\Requests\Roles\RoleCreateRequest;
use App\Http\Base\Requests\Roles\RoleUpdateRequest;
use App\Http\Base\Resources\RoleResource;
use App\Http\Controllers\Controller;
use App\Models\Role;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class RolesController extends Controller
{
    public function index()
    {
        try {
            $action = 'ROLE-INDEX-API';

            $filters = request()->query();

            $roles = RoleResource::collection(
                (new Role())->getRoles(
                    $filters,
                    [
                        'id', 'title', 'company_id', 'status', 'created_at', 'updated_at'
                    ],
                    true
                )
            );

            appLog()->createLog([
                'action' => $action,
                'filters' => $filters
            ]);

            return appResponse()->success(config('status_code.success'), __('Success.'), $roles);
        } catch (Exception $exception) {
            appLog()->errorHandlingLog($exception, [
                'action' => $action,
                'request_data' => request()->all()
            ]);

            return appResponse()->failed(config('status_code.server_error'), __('Failed.'));
        }
    }

    public function create(RoleCreateRequest $request)
    {
        $requestData = $request->all();

        try {
            $action = 'ROLE-CREATE-API';

            $role_data = new RoleResource(
                (new Role())->createNewRole(
                    $requestData
                )
            );

            appLog()->createLog([
                'action' => $action,
                'role_data' => $role_data
            ]);

            return appResponse()->success(config('status_code.success'), __('Role successfully created.'), $role_data);
        } catch (Exception $exception) {
            appLog()->errorHandlingLog($exception, [
                'action' => $action,
                'request_data' => $requestData
            ]);
            return appResponse()->failed(config('status_code.server_error'), __('Failed to create role.'));
        }
    }

    public function update(RoleUpdateRequest $request, $role_id)
    {
        try {
            $action = 'ROLE-UPDATE-API';
            $requestData = $request->all();

            $update_status = (new Role())->updateRole(
                $role_id,
                $requestData
            );

            if (!$update_status) {
                throw new Exception();
            }

            appLog()->createLog([
                'action' => $action,
                'request_data' => $requestData
            ]);

            return appResponse()->success(config('status_code.success'), __('Role successfully updated.'));
        } catch (Exception $exception) {
            $error_code = config('status_code.server_error');
            $error_message = 'Failed to update role.';
            appLog()->errorHandlingLog($exception, [
                'action' => $action,
                'error_message' => $error_message,
                'request_data' => $request->all()
            ]);
        }

        return appResponse()->failed($error_code, __($error_message));
    }

    public function destroy($role_id)
    {
        try {
            $action = 'ROLE-DELETE-API';

            $role_model_instance = new Role();
            $role_model_instance->findById($role_id);

            $status = $role_model_instance->deleteRole($role_id);

            appLog()->createLog([
                'action' => $action,
                'delete_status' => $status
            ]);

            return appResponse()->success(config('status_code.success'), __('Role successfully deleted.'));
        } catch (ModelNotFoundException $exception) {
            $error_code = config('status_code.validation_fail');
            $error_message = 'Role not found.';
        } catch (Exception $exception) {
            $error_code = config('status_code.server_error');
            $error_message = 'Failed to delete role.';
        }
        appLog()->errorHandlingLog($exception, [
            'action' => $action,
            'error_message' => $error_message,
            'role_id' => $role_id
        ]);
        return appResponse()->failed($error_code, __($error_message));
    }
}
