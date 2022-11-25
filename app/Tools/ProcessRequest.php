<?php

namespace App\Tools;

use Base\Jwt\JwtHelper;
use Lcobucci\JWT\Token as TokenInterface;
use App\App;
use App\Tools\ResponseException;
use App\Models\User;

trait ProcessRequest 
{
    public function generateToken(string $aud, int $uid, string $exp): string
    {
        return (JwtHelper::generateToken(App::$data->secretKey, App::$data->domain, App::$data->aud[$aud], null, $uid, null, $exp))->toString();
    }
    
    public function checkTokenValidity(TokenInterface $resultToken): void
    {
        // Проверяем валидность токена по секретному ключу, 
        // а также сравниваем aud в токене и в данных запроса
        if (!JwtHelper::isValidToken($resultToken, App::$data->secretKey) || !in_array(App::$data->aud[App::$request->aud], $resultToken->claims()->get('aud'))) {
            http_response_code(403);
            throw new ResponseException((object)['message' => 'Сервер отказывается дать надлежащий ответ']);
        }
    }
    
    public function checkTokenDate(TokenInterface $resultToken): void
    {        
        if ($resultToken->claims()->get('exp') > new \DateTimeImmutable()) {
            return;
        }

        http_response_code(401);
        $requestObject = (object)[
            'app' => (object)[
                'token' => $this->generateToken(App::$request->aud, User::DEFAULT_USER_ID, User::TOKEN_EXP),
                'isGuest' => true,
            ],
            'user' => (object)[
                'login' => ''
            ]
        ];
        
        echo json_encode($requestObject);
        exit;
    }
}
