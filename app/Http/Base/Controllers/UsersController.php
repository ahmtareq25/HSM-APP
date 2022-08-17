<?php

namespace App\Http\Base\Controllers;

use App\Http\Base\Requests\Users\UserCreateRequest;
use App\Http\Base\Requests\Users\UserUpdateRequest;
use App\Http\Base\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function index()
    {
        try {
            $action = 'USER-INDEX-API';

            $filters = request()->query();
            $filters['company_id'] = Auth::user()->company_id;

            $roles = UserResource::collection(
                (new User())->getUsers(
                    $filters,
                    [
                        'id', 'company_id', 'name', 'email', 'language', 'created_at', 'updated_at'
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

    public function create(UserCreateRequest $request)
    {
        $requestData = $request->all();
        try {
            $action = 'USER-CREATE-API';

            $requestData['company_id'] = Auth::user()->company_id;
            $requestData['language'] = $requestData['language'] ?? Auth::user()->language;
            $user_data = new UserResource(
                (new User())->createNewuser($requestData)
            );

            appLog()->createLog([
                'action' => $action,
                'user_data' => $user_data
            ]);

            return appResponse()->success(config('status_code.success'), __('User successfully created.'), $user_data);
        } catch (Exception $exception) {
            appLog()->errorHandlingLog($exception, [
                'action' => $action,
                'request_data' => $requestData
            ]);
            return appResponse()->failed(config('status_code.server_error'), __('Failed to create role.'));
        }
    }


    public function update(UserUpdateRequest $request, $user_id)
    {
        $requestData = $request->all();
        try {
            $action = 'USER-UPDATE-API';

            $filters = [
                'company_id' => Auth::user()->company_id
            ];
            $update_status = (new User())->updateUser(
                $requestData,
                $user_id,
                $filters
            );

            if (! $update_status) {
                throw new Exception();
            }

            appLog()->createLog([
                'action' => $action,
                'request_data' => $requestData
            ]);

            return appResponse()->success(config('status_code.success'), __('User successfully updated.'));
        } catch (Exception $exception) {
            $error_code = config('status_code.server_error');
            $error_message = 'Failed to update user.';
            appLog()->errorHandlingLog($exception, [
                'action' => $action,
                'error_message' => $error_message,
                'request_data' => $requestData
            ]);
        }

        return appResponse()->failed($error_code, __($error_message));
    }

    public function destroy($user_id)
    {
        try {
            $action = 'USER-DELETE-API';

            $filters = [
                'company_id' => Auth::user()->company_id
            ];
            $status = (new User())->deleteUser($user_id, $filters);

            appLog()->createLog([
                'action' => $action,
                'delete_status' => $status
            ]);

            return appResponse()->success(config('status_code.success'), __('User successfully deleted.'));
        } catch (Exception $exception) {
            $error_code = config('status_code.server_error');
            $error_message = 'Failed to delete user.';
        }
        appLog()->errorHandlingLog($exception, [
            'action' => $action,
            'error_message' => $error_message,
            'user_id' => $user_id
        ]);
        return appResponse()->failed($error_code, __($error_message));
    }
}
