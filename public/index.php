<?php

// Подключение автозагрузки через composer
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;

$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];

// helpers
function filterUsersByName($users, $search)
{
  $filteredUsersByName = array_filter($users, function ($userName) use ($search) {
    return strpos($userName, $search) !== false;
  });

  return $filteredUsersByName;
}

function validate ($user)
{
  $errors = [];

  if (empty($user['nickname'])) {
    $errors['nickname'] = "Field can not be blank";
  }

  if (empty($user['email'])) {
    $errors['nickname'] = "Field can not be blank";
  }

  return $errors;
}

function saveData($data)
{ 
  $fileName = '/home/downtempa/Hexlet/slim-example/data/usersList.txt';
  $dataAsJson = json_encode($data);
  $f = file_put_contents($fileName, $dataAsJson . PHP_EOL, FILE_APPEND);
}

function counter ($start)
{
  $tick = $start;

  return function() use (&$tick)
  {
    $tick += 1;

    return $tick;
  };
}

$getNextId = counter(0);

$container = new Container();

$container->set('renderer', function () {
  return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

AppFactory::setContainer($container);

$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

// main page
$app->get('/', function ($request, $response) {
  return $this->get('renderer')->render($response, 'index.phtml');
});

// courses
$app->get('/courses', function ($request, $response) {
  return $this->get('renderer')->render($response, 'courses/index.phtml');
});

$app->get('/courses/{id}', function ($request, $response, array $args) {
  $courseId = $args['id'];
  return $response->write("Course id: {$courseId}");
});

// users
$app->get('/users', function ($request, $response) use ($users) {
  $search = $request->getQueryParam('search');
  $userList = isset($search) ? filterUsersByName($users, $search) : $users;

  $params = [
    'users' => $userList,
  ];

  return $this->get('renderer')->render($response, 'users/list.phtml', $params);
});

$app->get('/users/show/{id}', function ($request, $response, $args) {
  $params = [
    'id' => $args['id'],
    'nickname' => 'user-' . $args['id']
  ];
  // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
  return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$app->post('/users', function ($request, $response) use ($getNextId) {
  $user = $request->getParsedBodyParam('user');
  $errors = validate($user);

  if (count($errors) === 0) {
    $user['id'] = $getNextId();
    saveData($user);
    
    return $response->withHeader('Location', '/users')->withStatus(302);
  }

  $params = [
    'user' => $user,
    'errors' => $errors
  ];

  return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->get('/users/new', function ($request, $response) {
  $user = $request->getParsedBody();

  $params = [
    'user' => ['nickname' => '', 'email' => '', 'id' => ''],
    'errors' => [],
  ];

  return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->run();


