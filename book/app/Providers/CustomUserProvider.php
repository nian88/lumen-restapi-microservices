<?php

namespace App\Providers;

use App\Models\User;
use Throwable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;

class CustomUserProvider implements UserProvider
{
    public function retrieveByToken ($identifier, $token) {
        throw new Throwable('Method not implemented.');
    }

    public function updateRememberToken (Authenticatable $user, $token) {
        throw new Throwable('Method not implemented.');
    }

    public function retrieveById ($identifier) {
        return $this->getMemberInstance($identifier);
    }

    public function retrieveByCredentials (array $credentials) {
        return $this->getMemberInstance($credentials);
    }

    public function validateCredentials (Authenticatable $user, array $credentials) {
        return true;
    }

    private function getMemberInstance ($identifier) {
        // return $phone;
        // if (false == is_valid_phone_no($phone)) {
        //     // your custom method to validate number format.
        //     return null;
        // }

        return tap(new User(), function ($user) use ($identifier) {
            $user->id = $identifier;
            $user->phone = (string) $identifier;
            $user->custom_data = $identifier;
            // push whatever your require from user
            // Don't save the model instance here
            // As we won't use any stroage.
        });
    }
}