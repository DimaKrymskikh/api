<?php

namespace App\Controllers;

use App\Models\User;

class RegisteredUserController 
{
    public function store(): string
    {
        return json_encode((new User)->processRegistration());
    }
}
