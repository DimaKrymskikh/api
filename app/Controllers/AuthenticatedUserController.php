<?php

namespace App\Controllers;

use App\App;
use App\Models\User;

class AuthenticatedUserController 
{
    /**
     * Контроллер, выполняемый при первой загрузке клиентского приложения
     * @param string $aud Кодовое значение клиенского приложения, по которому определяется поле aud токена
     * @return string 
     * Возврящает токен неаутентифицированного пользователя и метку, что он гость.
     */
    public function index(string $aud): string
    {
        return json_encode((object)[
            'app' => (object)[
                'token' => (new User)->generateToken($aud, User::DEFAULT_USER_ID, User::TOKEN_EXP),
                'isGuest' => true,
            ]
        ]);
    }
    
    public function store(): string
    {
        return json_encode((new User)->processLogin());
    }
    
    public function destroy(): string
    {
        return json_encode((new User)->processLogout());
    }
}
