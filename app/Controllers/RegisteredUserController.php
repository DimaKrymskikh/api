<?php

namespace App\Controllers;

use App\Models\User;

/**
 * Регистрация пользователя
 */
class RegisteredUserController 
{
    public function store(): string
    {
        return json_encode((new User)->processRegistration());
    }
}
