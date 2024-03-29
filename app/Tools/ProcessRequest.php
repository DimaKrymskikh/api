<?php

namespace App\Tools;

use Base\Jwt\JwtHelper;
use Lcobucci\JWT\Token as TokenInterface;
use App\App;
use App\Tools\ResponseException;
use App\Models\User;

/**
 * Обслуживание токена
 */
trait ProcessRequest
{
    /**
     * Генерирует токен
     * @param string $aud - Код клиентского приложения
     * @param int $uid - id пользователя или дефолтное значение
     * @param string $exp - Время жизни токена
     * @return string
     * Возвращает токен в виде строки
     */
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
        if ($resultToken->claims()->get('exp') < new \DateTimeImmutable()) {
            http_response_code(401);
            throw new ResponseException((object)['message' => 'Время токена истекло. Выполните вход заново.']);
        }
    }
}
