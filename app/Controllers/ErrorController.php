<?php

namespace App\Controllers;

use App\Tools\ResponseException;

/**
 * Дефолтный контроллер
 */
class ErrorController 
{
    /**
     * Экшен, который выполняется, когда роутер по uri не находит нужный экшен
     * @throws ResponseException
     */
    public function index()
    {
        http_response_code(403);
        throw new ResponseException((object)['message' => 'Страница не найдена']);
    }
}
