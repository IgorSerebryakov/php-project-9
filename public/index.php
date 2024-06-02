<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\DB;
use App\Database\Urls;
use App\Database\UrlChecks;
use App\Url;
use DI\Container;
use DiDom\Document;
use GuzzleHttp\Client as Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;

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

        return $this->get('renderer')->render($response, 'new.phtml', $params);
    }
    
    $urls->save($url);
    
    if ($url->isNew()) {
        $this->get('flash')->addMessage('success', 'Страница успешно добавлена');
    } else {
        $this->get('flash')->addMessage('success', 'Страница уже существует');
    }
    
    return $response->withRedirect($router->urlFor('url', ['id' => $url->getId()]));
});

$app->get('/urls/{id}', function ($request, $response, $args) use ($router, $urls, $urlChecks) {
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

$app->get('/urls', function ($request, $response) use ($router, $urls) {
    $urls = $urls->getAll();
    
    $params = [
        'urls' => $urls
    ];
    
    return $this->get('renderer')->render($response, 'index.phtml', $params);
})->setName('urls');

$app->post('/urls/{url_id}/checks', function ($request, $response, $args) use ($router, $urls) {
    $date = date("Y-m-d H:i:s");

    $url = new Url($urls->getUrlById($args['url_id']));
    
    $htmlParser = new Parser($url);
    
    
    
    $document = new Document($url->getName(), true);
    $h1 = optional($document->first('h1'));
    $title = optional($document->first('title'))->innerHtml();
    $description = optional($document->first('meta[name=description]'))->attr('content');
    
    $client = new Client([
        'timeout' => 2.0
    ]);
    try {
        $res = $client->request('GET', $url->getName());
        $this->get('flash')->addMessage('success', 'Страница успешно проверена');
    } catch (ConnectException | ClientException | ServerException) {
        $this->get('flash')->addMessage('error', 'Произошла ошибка при проверке, не удалось подключиться');
        return $response->withRedirect($router->urlFor('url', ['id' => $args['url_id']]));
    }
    
    $statusCode = $res->getStatusCode();
    
    $db::save('INSERT INTO url_checks (url_id, created_at, status_code, h1, title, description) 
                     VALUES (:url_id, :created_at, :status_code, :h1, :title, :description)', 
                     [$urlId, $date, $statusCode, $h1, $title, $description]);
    
    return $response->withRedirect($router->urlFor('url', ['id' => $urlId]));
});

$app->run();








