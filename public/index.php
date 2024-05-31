<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Connection;
use App\Database\DB;
use DI\Container;
use DiDom\Document;
use GuzzleHttp\Client as Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use Valitron\Validator;

session_start();

try {
    Connection::get()->connect();
} catch (\PDOException $e) {
    echo $e->getMessage();
}

date_default_timezone_set("Europe/Moscow");

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

AppFactory::setContainer($container);
$app = AppFactory::create();

$router = $app->getRouteCollector()->getRouteParser();

$app->addErrorMiddleware(true, true, true);
$app->add(MethodOverrideMiddleware::class);

$app->get('/', function ($request, $response) {
    $params = [
        'errors' => []
    ];
    return $this->get('renderer')->render($response, 'new.phtml', $params);    
});

$app->post('/urls', function ($request, $response) use ($router) {
    $url = $request->getParsedBodyParam('url');
    
    $validator = new Validator($url);
    $validator->rules([
        'required' => [
            ['name']
        ],
        'lengthMax' => [
            ['name', 255]
        ],
        'url' => [
            ['name']
        ]
    ]);
    
    $name = $url['name'];
    $date = date("Y-m-d H:i:s");

    if (!$validator->validate()) {
        $errors = $validator->errors();
        $params = [
            'errors' => $errors['name'],
            'name' => $name
        ];

        return $this->get('renderer')->render($response, 'new.phtml', $params);
    }
    
    $db = new DB();

    if ($db::getRow('SELECT id, name, created_at FROM urls WHERE name = :name', [$name])) {
        $this->get('flash')->addMessage('success', 'Страница уже существует');
    } else {
        $db::save('INSERT INTO urls (name, created_at) VALUES (:name, :created_at)', [$name, $date]);
        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    }

    $data = $db::getRow('SELECT id, name, created_at FROM urls WHERE name = :name', [$name]);
    $id = $data['id'];
    
    return $response->withRedirect($router->urlFor('url', ['id' => $id]));

});

$app->get('/urls/{id}', function ($request, $response, $args) use ($router) {
    $id = $args['id'];
    $db = new DB();
    
    $url = $db::getRow('SELECT id, name, created_at 
                              FROM urls 
                              WHERE id = :id', [$id]);
    
    $checks = $db::getRows('SELECT id, created_at, status_code, h1, title, description 
                                  FROM url_checks 
                                  WHERE url_id = :url_id ORDER BY id DESC', [$id]);
    
    $messages = $this->get('flash')->getMessages();
    
    $params = [
        'checks' => $checks,
        'url' => $url,
        'flash' => $messages
    ];
    
    return $this->get('renderer')->render($response, 'show.phtml', $params);
})->setName('url');

$app->get('/urls', function ($request, $response) use ($router) {
    $db = new DB();
    
    $query = 'SELECT DISTINCT ON (urls.name)
        urls.id,
        urls.name,
        checks.created_at AS last_reg,
        checks.status_code
    FROM urls
    INNER JOIN url_checks AS checks
    ON
        urls.id = checks.url_id
    ORDER BY urls.name, last_reg DESC';
    
    $urls = $db::getRows($query);
    
    $params = [
        'urls' => $urls
    ];
    
    return $this->get('renderer')->render($response, 'index.phtml', $params);
})->setName('urls');

$app->post('/urls/{url_id}/checks', function ($request, $response, $args) use ($router) {
    $urlId = $args['url_id'];
    $date = date("Y-m-d H:i:s");
    $db = new DB();

    $url = $db::getRow('SELECT id, name, created_at FROM urls WHERE id = :id', [$urlId]);
    
    $document = new Document($url['name'], true);
    $h1 = optional($document->first('h1'))->innerHtml();
    $title = optional($document->first('title'))->innerHtml();
    $description = optional($document->first('meta[name=description]'))->attr('content');
    
    $client = new Client([
        'timeout' => 3.0
    ]);
    
    try {
        $res = $client->request('GET', $url['name']);
        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    } catch (ConnectException | ClientException | ServerException) {
        $this->get('flash')->addMessage('error', 'Произошла ошибка при проверке, не удалось подключиться');
        return $response->withRedirect($router->urlFor('url', ['id' => $urlId]));
    }
    
    $statusCode = $res->getStatusCode();
    
    $db::save('INSERT INTO url_checks (url_id, created_at, status_code, h1, title, description) 
                     VALUES (:url_id, :created_at, :status_code, :h1, :title, :description)', 
                     [$urlId, $date, $statusCode, $h1, $title, $description]);
    
    $urlForRedirect = $router->urlFor('url', ['id' => $urlId]);
    return $response->withRedirect($urlForRedirect);
});

$app->run();








