<?php

namespace App\Services;

use App\Http\Base\Resources\UserResource;
use App\Models\OauthClient;
use App\Models\User;

class AuthService
{
    private bool $is_otp_enable;
    private null|string $client_id;
    private null|string $client_secret;
    private null|string $token_url;

    public bool $is_success = false;
    public string $status_message;
    public mixed $status_data;

    public function __construct()
    {
        $this->is_otp_enable = config('auth.is_otp_enable');
        $this->token_url = config('app.url') . '/oauth/token';
    }

    public function processLogin(array $credentials): static
    {
        $user_email = array_get($credentials, 'email');

        $user = (new User())->findByEmail($user_email);

        if (is_null($user)) {
            return $this->status(false, "Credentials do not matched");
        }

        if ($this->isOTPEnable()) {
            $otp_service = new OTPService($user->id, $user->email, $user->phone);
            if ($otp_service->processOTP("Login OTP")) {
                return $this->status(true, "OTP successfully send", new UserResource($user));
            }

            return $this->status(false, "OTP send failed");
        }

        return $this->sendLoginResponse($user);
    }

    public function resendOTP(array $user_info): static
    {
        $user_id = array_get($user_info, 'user_id');

        $user = (new User())->findById($user_id);

        if (is_null($user)) {
            return $this->status(false, "Invalid user information");
        }

        if ($this->isOTPEnable()) {
            $otp_service = new OTPService($user->id, $user->email, $user->phone);
            if ($otp_service->processOTP("Resend Login OTP")) {
                return $this->status(true, "OTP successfully send");
            }
        }

        return $this->status(false, "OTP send failed");
    }

    public function verifyOTP(array $otp_information): static
    {

        $otp = array_get($otp_information, 'otp');
        $user_id = array_get($otp_information, 'user_id');

        $user = (new User())->findById($user_id);

        if (is_null($user)) {
            return $this->status(false, "User not found");
        }

        $otp_service = new OTPService($user->id, $user->email, $user->phone);
        if ($otp_service->matchOTP($otp)) {
            return $this->sendLoginResponse($user);
        }

        return $this->status(false, "OTP not match or expired.");
    }

    public function regenerateAuthTokenWithRefreshToken(array $tokens): static
    {
        $refresh_token = array_get($tokens, 'refresh_token');

        $token_data = $this->generateOAuthTokenByRefreshToken($refresh_token);

        if (! $token_data) {
            return $this->status(false, "Failed to generate token");
        }

        return $this->status(true, "Auth token successfully renewed", $token_data);
    }

    private function isOTPEnable()
    {
        return $this->is_otp_enable;
    }

    private function generateOAuthTokenByEmail(string $email): bool|array
    {
        $this->setClientIdClientSecret();
        return $this->generateOAuthToken([
            'grant_type' => 'otp_grant',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'email' => $email,
            'scope' => ''
        ]);
    }

    private function generateOAuthTokenByRefreshToken(string $refresh_token): bool|array
    {
        $this->setClientIdClientSecret();
        return $this->generateOAuthToken([
            'grant_type' => 'refresh_token',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'refresh_token' => $refresh_token,
            'scope' => ''
        ]);
    }

    private function generateOAuthToken(array $token_config_data): bool|array
    {
        //$token_config_data['client_secret'] = (new OauthClient())->getClientSecretByClientId($this->client_id);
        $response = (new HttpRequest)->postAsFrom($this->token_url, $token_config_data);

        if ($response->is_success) {
            return $response->body;
        }
        return false;
    }

    private function status(bool $status = false, string $message = "", mixed $data = ''): static
    {
        $this->is_success = $status;
        $this->status_message = $message;
        $this->status_data = $data;
        return $this;
    }

    private function sendLoginResponse($user): static
    {
        $token_data = $this->generateOAuthTokenByEmail($user->email);

        if (! $token_data) {
            return $this->status(false, "Failed to generate token");
        }

        $permissions = $this->getPermissions($user);

        return $this->status(true, "Login successful", new UserResource($user, $permissions, $token_data));
    }

    private function setClientIdClientSecret(): void
    {
        $this->client_id = request()->header('client_id');
        $this->client_secret = (new OauthClient())->getClientSecretByClientId(
            $this->client_id
        );
    }

    private function getPermissions($user): array
    {
        $sql = "SELECT distinct pages.id,
                    users.id                     AS user_id,
                    users.permission_version,
                    pages.id                     AS page_id,
                    pages.name                   AS page_name,
                    user_usergroups.usergroup_id AS usergroup_id,
                    modules.id                   AS module_id,
                    modules.name                 AS module_name,
                    submodules.name              AS submodule_name,
                    submodules.id                AS submodule_id
                FROM users
                 INNER JOIN user_usergroups ON users.id = user_usergroups.user_id
                 INNER JOIN usergroups ON user_usergroups.usergroup_id = usergroups.id
                 INNER JOIN usergroup_roles ON usergroups.id = usergroup_roles.usergroup_id
                 INNER JOIN roles ON usergroup_roles.role_id = roles.id
                 INNER JOIN role_pages ON roles.id = role_pages.role_id
                 INNER JOIN pages ON role_pages.page_id = pages.id
                 INNER JOIN submodules ON pages.submodule_id = submodules.id
                 INNER JOIN modules ON submodules.module_id = modules.id
                 WHERE users.id = " . $user->id;
        $permissions = \DB::select($sql);

        $permissions_array = [];
        foreach ($permissions as $permission) {
            $permissions_array[$permission->module_name][$permission->submodule_name][] = $permission->page_name;
        }

        return $permissions_array;
    }
}
