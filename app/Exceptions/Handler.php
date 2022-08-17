<?php

namespace App\Exceptions;

use App\Models\AppSetting;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->renderable(function (Throwable $throwable, $request) {
            if ($request->wantsJson() and $throwable instanceof AuthenticationException) {
                return appResponse()->failed(config('status_code.validation_fail'), __('Failed'), __('Unauthenticated'));
            }else if($request->wantsJson()){
                $logData = [
                    'action' => 'EXCEPTION_FOUND',
                    'request_unique_key' => getResource(AppSetting::APP_REQUEST_UNIQUE_KEY_NAME),
                    'exception_message' => $throwable->getMessage(),
                    'exception_file' => $throwable->getFile(),
                    'exception_line' => $throwable->getLine(),
                ];
                appLog()->createLog($logData);
                return appResponse()->failed(config('status_code.validation_fail'), __('Unknown Error'));

            }
        });
    }
}
