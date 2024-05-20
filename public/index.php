<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PostgreSQL\Connection;
use PostgreSQL\Database;
use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;

session_start();

try {
    $pdo = Connection::get()->connect();
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

$database = new Database($pdo);

$app->get('/', function ($request, $response) {
    $params = [
        'errors' => []
    ];
    return $this->get('renderer')->render($response, 'index.phtml', $params);    
});

$app->post('/urls', function ($request, $response) use ($database) {
    $urlParams = $request->getParsedBodyParam('url');
    $validator = new \Valitron\Validator($urlParams);
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
    
    if ($validator->validate()) {
        $database->save($urlParams['name'], date("Y-m-d H:i:s"));
        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
        return $response;
    } else {
        $errors = $validator->errors();
        $params = [
            'errors' => $errors['name'],
            'name' => $urlParams['name']
        ];
    }
    
    return $this->get('renderer')->render($response, 'index.phtml', $params);
});

$app->get('/urls/{id}', function ($request, $response, $args) use ($database, $router) {
    $id = $args['id'];
    $dataUrl = $database->find($id);
    $params = [
        'data' => $dataUrl
    ];
    
    return $this->get('renderer')->render($response, 'show.phtml', $params);
});

$app->run();








// psql -a -d $DATABASE_URL -f database.sql
// export DATABASE_URL=postgres://xen:J6jwrmQ7rpFX0ivayzuUhW4c8ZbR7XM1@dpg-cp2entv79t8c73fsjoag-a.oregon-postgres.render.com/urls_gacn