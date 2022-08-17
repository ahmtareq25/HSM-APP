<?php

namespace App\Http\Base\Controllers;

use App\Http\Base\Requests\LoginRequest;
use App\Http\Base\Requests\RenewAuthTokenRequest;
use App\Http\Base\Requests\ResendOTPRequest;
use App\Http\Base\Requests\VerifyOTPRequest;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Exception;

class AuthController extends Controller
{
    public function logIn(LoginRequest $request)
    {
        $action = 'API_LOGIN';
        $request_data = $request->all();
        try {

            $auth_service = (new AuthService())->processLogin($request_data);

            appLog()->createLog([
                'action' => $action,
                'message' => $auth_service->status_message,
                'success_status' => $auth_service->is_success
            ]);

            if ($auth_service->is_success) {
                return appResponse()->success(config('status_code.success'), __($auth_service->status_message), $auth_service->status_data);
            }

            $error_message = $auth_service->status_message;

        } catch (Exception $exception) {
            appLog()->errorHandlingLog($exception, $action);
        }

        return appResponse()->failed(config('status_code.server_error'), __('Failed'), __($error_message ?? 'Login Failed'));
    }

    public function resendOTP(ResendOTPRequest $request)
    {
        $action = 'API_RESEND_OTP';
        $request_data = $request->all();
        try {

            $auth_service = (new AuthService())->resendOTP($request_data);

            appLog()->createLog([
                'action' => $action,
                'message' => $auth_service->status_message,
                'success_status' => $auth_service->is_success
            ]);

            if ($auth_service->is_success) {
                return appResponse()->success(config('status_code.success'), __($auth_service->status_message), $auth_service->status_data);
            }

            $error_message = $auth_service->status_message;

        } catch (Exception $exception) {
            appLog()->errorHandlingLog($exception, $action);
        }

        return appResponse()->failed(config('status_code.server_error'), __('Failed'), __($error_message ?? 'Resend OTP Failed'));
    }

    public function verifyOTP(VerifyOTPRequest $request)
    {
        $action = 'API_OTP_VERIFICATION';
        $request_data = $request->all();
        try {

            $auth_service = (new AuthService())->verifyOTP($request_data);

            appLog()->createLog([
                'action' => $action,
                'message' => $auth_service->status_message,
                'success_status' => $auth_service->is_success
            ]);

            if ($auth_service->is_success) {
                return appResponse()->success(config('status_code.success'), __($auth_service->status_message), $auth_service->status_data);
            }

            $error_message = $auth_service->status_message;

        } catch (Exception $exception) {
            appLog()->errorHandlingLog($exception, $action);
        }

        return appResponse()->failed(config('status_code.server_error'), __('Failed'), __($error_message ?? 'OTP Verification Failed'));
    }


    public function renewAuthToken(RenewAuthTokenRequest $request)
    {
        $action = 'API_RENEW_AUTH_TOKEN_BY_REFRESH_TOKEN';
        $request_data = $request->all();
        try {

            $auth_service = (new AuthService())->regenerateAuthTokenWithRefreshToken($request_data);

            appLog()->createLog([
                'action' => $action,
                'message' => $auth_service->status_message,
                'success_status' => $auth_service->is_success
            ]);

            if ($auth_service->is_success) {
                return appResponse()->success(config('status_code.success'), __($auth_service->status_message), $auth_service->status_data);
            }

            $error_message = $auth_service->status_message;

        } catch (Exception $exception) {
            appLog()->errorHandlingLog($exception, $action);
        }

        return appResponse()->failed(config('status_code.server_error'), __('Failed'), __($error_message ?? 'OTP Verification Failed'));
    }
}
