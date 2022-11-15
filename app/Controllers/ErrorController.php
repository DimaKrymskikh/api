<?php

namespace App\Controllers;

use App\Tools\ResponseException;
use App\App;

class ErrorController 
{
    public function index()
    {
        http_response_code(403);
        throw new ResponseException((object)['message' => 'Страница не найдена']);
    }
}
