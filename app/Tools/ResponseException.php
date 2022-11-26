<?php

namespace App\Tools;

/**
 * Исключение, которое выбрасывается при ошибках запроса от клиентского приложения
 */
class ResponseException extends \Exception
{
    private object $response;
    
    public function __construct(object $response, string $message = "", int $code = 0, ?Throwable $previous = null) 
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    public function getResponse(): string
    {
        return json_encode($this->response);
    }
}
