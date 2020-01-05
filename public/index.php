<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$container = new Container();
$container->set('renderer', function () {
  return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$app->get('/users/{id}', function ($request, $response, $args) {
  $params = [
    'id' => $args['id'],
    'nickname' => 'user-' . $args['id']
  ];
  // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
  return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});


$app->get('/', function ($request, $response) {
    return $response->write('Welcome to Slim!');
});

$app->get('/users', function ($request, $response) {
  return $response->write('GET /users');
});

$app->post('/users', function ($request, $response) {
  return $response->withStatus(302);
});

$app->get('/courses/{id}', function ($request, $response, array $args) {
  $courseId = $args['id'];
  return $response->write("Course id: {$courseId}");
});

$app->run();