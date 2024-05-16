<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PostgreSQL\Connection;
use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;

try {
    Connection::get()->connect();
} catch (\PDOException $e) {
    echo $e->getMessage();
}

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});
$app = AppFactory::createFromContainer($container);

$router = $app->getRouteCollector()->getRouteParser();

$app->addErrorMiddleware(true, true, true);
$app->add(MethodOverrideMiddleware::class);











// psql -a -d $DATABASE_URL -f database.sql
// export DATABASE_URL=postgres://xen:J6jwrmQ7rpFX0ivayzuUhW4c8ZbR7XM1@dpg-cp2entv79t8c73fsjoag-a.oregon-postgres.render.com/urls_gacn