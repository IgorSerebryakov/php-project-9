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

$app->get('/', function ($request, $response) {
    return $this->get('renderer')->render($response, 'index.phtml');    
});

$app->post('/urls', function ($request, $response) use ($pdo) {
    $url = $request->getParsedBodyParam('url');
    $dateOfCreation = date("Y-m-d H:i:s");
    
    $db = new Database($pdo);
    $db->insertUrl($url['name'], $dateOfCreation);
    
    $html = var_export($url['name'], true);
    $response->getBody()->write($html);
    
    return $response;
});

$app->run();








// psql -a -d $DATABASE_URL -f database.sql
// export DATABASE_URL=postgres://xen:J6jwrmQ7rpFX0ivayzuUhW4c8ZbR7XM1@dpg-cp2entv79t8c73fsjoag-a.oregon-postgres.render.com/urls_gacn