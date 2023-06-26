<?php

namespace App;

use App\Tools\ResponseException;
use App\Models\User;
use Base\Container\Container;
use Base\DBQuery;
use Base\Registration\BaseRegistration;
use Base\Registration\ModuleRegistration;
use Base\Registration\RequestRegistration;
use Base\Router;
use Base\Jwt\JwtHelper;

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
    
    private string|false $response;
    private Container $container;

    public function __construct(
            private object $config
    ) 
    {
        $this->container = new Container();
        new BaseRegistration($this->container, $this->config);
        
        // Создаём соединение с базой
        self::$db = new DBQuery($this->config->db);
        // Сохраняем данные конфигурации
        self::$data = $this->config->data;
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
            ob_start();
            $this->router();
            $this->response = ob_get_clean();
            $this->writeLogs('access.log');
        } catch (ResponseException $e) {
            $this->response = $e->getResponse();
            $this->writeLogs('errors.log');
        } finally {
            echo $this->response;
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
        
        $requestUri = trim(parse_url(filter_input(INPUT_SERVER, 'REQUEST_URI'), PHP_URL_PATH), '/');
        
        new RequestRegistration($this->container, (object) [
            'method' => $requestMethod,
            'uri' => $requestUri
        ]);
        new ModuleRegistration($this->container, $this->config);
        
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
        $router = new Router($this->container);
        // Формируем массив маршрутов
        require_once __DIR__ . '/../routes/web.php';
        // Находим и выполняем экшен
        $router->run();
        
    }
    
    private function writeLogs(string $file): void
    {
        $log = date('Y-m-d H:i:s') . PHP_EOL;
        foreach (getallheaders() as $name => $value) {
            $log .= "$name: $value\n";
        }
        $log .= "Метод запроса: {$this->container->get('request')->method}\n";
        $log .= "Uri запроса: {$this->container->get('request')->uri}\n";
        $log .= "Получены данные:" . PHP_EOL . print_r(self::$request, true);
        $log .= "Отправлены данные:" . PHP_EOL . print_r(json_decode($this->response), true);

        file_put_contents(dirname(__DIR__) . "/storage/logs/$file", $log . PHP_EOL, FILE_APPEND);
    }
}
