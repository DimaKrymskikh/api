<?php

namespace App;

use Base\DBQuery;
use Base\Router;
use Base\Jwt\JwtHelper;
use App\Tools\ResponseException;
use App\Controllers\ErrorController;
use App\Models\User;

/**
 * Основной класс приложения
 */
class App
{
    use \App\Tools\ProcessRequest;
    
    public static DBQuery $db;
    public static object $data;
    public static ?object $request;
    public static int $userId;

    public function __construct(object $config) 
    {
        // Создаём соединение с базой
        self::$db = new DBQuery($config->db);
        // Сохраняем данные конфигурации
        self::$data = $config->data;
    }
    
    /**
     * Обрабатывает запрос клиентского приложения
     * @return void
     */
    public function run(): void
    {
        // Можно раскомитить sleep(1), чтобы был лучше виден спиннер в клиенском приложении
//        sleep(1);
        
        try {
            $this->router();
        } catch (ResponseException $e) {
            echo $e->getResponse();
        }
    }

    /**
     * Задаёт маршрутизацию и проверяет токен
     * @return void
     */
    private function router(): void
    {
        // Задаём заголовки
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Credentials: true');
        header('Content-Type: application/json');
        
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
        // Если метод запроса "OPTIONS", то ничего не делаем
        // (Браузеры отправляют предзапрос с методом "OPTIONS", если запрос не является простым, т.е. нарушено одно из правил простого запроса.
        // См. https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
        // Например, тип заголовка "Content-Type: application/json" не допустим для простого запроса)
        if (mb_strtolower($requestMethod) === 'options') {
            exit();
        }
        
        $truncatedUri = trim(parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_PATH), '/');
        
        self::$request = json_decode(file_get_contents("php://input"));
        
        // Проверка токена
        if (self::$request) {
            $resultToken = JwtHelper::getResultToken(self::$request->token);
            // Если получен невалидный токен, то отправляем ответ, который потребует перезагрузки клиентского приложения
            $this->checkTokenValidity($resultToken);
            // Если получен валидный токен, то извлекаем из него id пользователя
            self::$userId = $resultToken->claims()->get('uid');
            
            if (self::$userId !== User::DEFAULT_USER_ID) {
                $this->checkTokenDate($resultToken);
            }
        }
        
        // Создаём роутер
        $router = new Router((object) [
            'controller' => ErrorController::class,
            'action' => 'index'
        ]);
        // Формируем массив маршрутов
        require_once __DIR__ . '/../routes/web.php';
        // Находим и выполняем экшен
        $router->run($requestMethod, $truncatedUri);
        
    }
}
