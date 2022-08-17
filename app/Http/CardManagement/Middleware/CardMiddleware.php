<?php

namespace App\Http\CardManagement\Middleware;

use App\Models\AppSetting;
use App\Services\ApplicationCode;
use Closure;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CardMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        $appSetting = new AppSetting();
        $appSetting->validateRequestAuthentication($request->client_id,$request->client_secret);
        if ($appSetting->application_code == ApplicationCode::APP_PROCESS_SUCCESSFUL){
            return $next($request);
        }

        return appResponse()->failed($appSetting->application_code, (new ApplicationCode())->getMessageByCode($appSetting->application_code));
    }
}
