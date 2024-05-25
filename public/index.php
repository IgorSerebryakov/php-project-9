<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PostgreSQL\Connection;
use PostgreSQL\DB;
use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;
use GuzzleHttp\Client;

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
    $validator = new \Valitron\Validator($url);
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
    
    if ($validator->validate()) {
        $db = new DB();
        if ($db::getRow('SELECT id, name, created_at FROM urls WHERE name = :name', [$name])) {
            $this->get('flash')->addMessage('success', 'Страница уже существует');
        } else {
            $db::save('INSERT INTO urls (name, created_at) VALUES (:name, :created_at)', [$name, $date]);
            $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
        }
        
        $data = DB::getRow('SELECT id, name, created_at FROM urls WHERE name = :name', [$name]);
        $id = $data['id'];
        
        $urlForRedirect = $router->urlFor('url', ['id' => $id]);
        return $response->withRedirect($urlForRedirect);
    } else {
        $errors = $validator->errors();
        $params = [
            'errors' => $errors['name'],
            'name' => $url['name']
        ];
    }
    
    return $this->get('renderer')->render($response, 'new.phtml', $params);
});

$app->get('/urls/{id}', function ($request, $response, $args) use ($router) {
    $db = new DB();
    $id = $args['id'];
    $url = $db::getRow('SELECT id, name, created_at FROM urls WHERE id = :id', [$id]);
    $check = $db::getRow('SELECT id, created_at FROM url_checks WHERE url_id = :url_id', [$id]);
    
    $messages = $this->get('flash')->getMessages();
    
    $params = [
        'checks' => $check,
        'url' => $url,
        'flash' => $messages
    ];
    
    return $this->get('renderer')->render($response, 'show.phtml', $params);
})->setName('url');

$app->get('/urls', function ($request, $response) use ($router) {
    $db = new DB();
    $query = 'SELECT urls.id, name, MAX(checks.created_at) AS last_reg 
            FROM urls
            INNER JOIN url_checks AS checks
            ON 
                        urls.id = checks.url_id
                GROUP BY name, urls.id
                ORDER BY urls.id DESC';
    $urls = $db::getRows($query);
    
    $params = [
        'urls' => $urls
    ];
    
    return $this->get('renderer')->render($response, 'index.phtml', $params);
})->setName('urls');

$app->post('/urls/{url_id}/checks', function ($request, $response, $args) use ($database, $checksDatabase, $router) {
    $urlId = $args['url_id'];
    $checksDatabase->save($urlId);
    $url = $database->getById($urlId); 

    $client = new GuzzleHttp\Client();
    $res = $client->request('GET', $url['name']);
    $statusCode = $res->getStatusCode();
    
    
    
    $urlForRedirect = $router->urlFor('url', ['id' => $urlId]);
    return $response->withRedirect($urlForRedirect);
});

$app->run();








// psql -a -d $DATABASE_URL -f database.sql
// export DATABASE_URL=postgres://xen:J6jwrmQ7rpFX0ivayzuUhW4c8ZbR7XM1@dpg-cp2entv79t8c73fsjoag-a.oregon-postgres.render.com/urls_gacn