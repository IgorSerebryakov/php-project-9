<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Urls;
use App\Database\UrlChecks;
use App\Parser;
use App\Url;
use DI\Container;
use GuzzleHttp\Client as Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use Dotenv\Dotenv;

session_start();

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

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$urls = new Urls();
$urlChecks = new UrlChecks();

$app->get('/', function ($request, $response) {
    $params = [
        'errors' => []
    ];
    return $this->get('renderer')->render($response, 'new.phtml', $params);
});

$app->post('/urls', function ($request, $response) use ($router, $urls) {
    $url = new Url($request->getParsedBodyParam('url'));

    if (!$url->isValid()) {
        $errors = $url->getErrors();
        $params = [
            'errors' => $errors,
            'name' => $url->getName()
        ];

        return $this->get('renderer')->render($response->withStatus(422), 'new.phtml', $params);
    }

    $urls->save($url);

    if ($url->isNew()) {
        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    } else {
        $this->get('flash')->addMessage('success', 'Страница уже существует');
    }

    return $response->withRedirect($router->urlFor('url', ['id' => $url->getId()]));
});

$app->get('/urls/{id}', function ($request, $response, $args) use ($urls, $urlChecks) {
    $url = $urls->getUrlById($args['id']);

    $checks = $urlChecks->getChecksById($args['id']);

    $messages = $this->get('flash')->getMessages();

    $params = [
        'checks' => $checks,
        'url' => $url,
        'flash' => $messages
    ];

    return $this->get('renderer')->render($response, 'show.phtml', $params);
})->setName('url');

$app->get('/urls', function ($request, $response) use ($urls) {
    $urls = $urls->getAll();

    $params = [
        'urls' => $urls
    ];

    return $this->get('renderer')->render($response, 'index.phtml', $params);
})->setName('urls');

$app->post('/urls/{url_id}/checks', function ($request, $response, $args) use ($router, $urls, $urlChecks) {
    $url = new Url($urls->getUrlById($args['url_id']));

    $client = new Client(
        [
            'timeout' => 2.0
        ]
    );
    try {
        $clientResponse = $client->request('GET', $url->getName());
        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    } catch (ClientException $e) {
        $clientResponse = $e->getResponse();
        $this->get('flash')->addMessage('warning', 'Проверка была выполнена успешно, но сервер ответил с ошибкой!');
    } catch (ConnectException) {
        $this->get('flash')->addMessage('danger', 'Произошла ошибка при проверке, не удалось подключиться');
        return $response->withRedirect($router->urlFor('url', ['id' => $args['url_id']]));
    }

    $parser = new Parser($clientResponse);
    $check = $parser->getHtmlParams();
    $check->setStatusCode($clientResponse->getStatusCode());
    $check->setUrlId($args['url_id']);

    $urlChecks->save($check);

    return $response->withRedirect($router->urlFor('url', ['id' => $args['url_id']]));
});

$app->run();
