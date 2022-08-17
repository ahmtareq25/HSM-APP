<?php

namespace App\Passport;

use App\Models\User;
use Psr\Http\Message\ServerRequestInterface;
use imrancse\passportgrant\UserProvider;

class OTPGrantProvider extends UserProvider
{
    /**
     * {@inheritdoc}
     */
    public function validate(ServerRequestInterface $request)
    {
        // It is not necessary to validate the "grant_type", "client_id",
        // "client_secret" and "scope" expected parameters because it is
        // already validated internally.

        $this->validateRequest($request, [
            'email' => ['required', 'email']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function retrieve(ServerRequestInterface $request)
    {
        $inputs = $this->only($request, ['email']);

        // Here insert your logic to retrieve user entity instance

        return User::where('email', $inputs['email'])->first();
    }
}
