<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Configures $app
$app = new Silex\Application();
$app['debug'] = true;
require_once __DIR__.'/../config/bootstrap.php';

// Parse JSON body according to Content-Type
$app->before(function (Request $request) {
  if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
    $data = json_decode($request->getContent(), true);
    $request->request->replace(is_array($data) ? $data : array());
  }
});

// TEST
$app->get('/blog/{id}', function ($id) use ($app) {
    $sql = "SELECT * FROM tweets WHERE id = ?";
    $post = $app['db']->fetchAssoc($sql, array((int) $id));

    return  "<h1>text</h1>";
})
->assert('id', '\d+');

// Serves API
$app->post('/api/sessions', 'Twittos\\Controller\\SessionController::create');
//$app->delete('/api/sessions', 'Twittos\\Controller\\SessionController::delete');
$app->get('/api/users/self', 'Twittos\\Controller\\UserController::info');
//$app->put('/api/users/self', 'Twittos\\Controller\\UserController::update');
$app->post('/api/users', 'Twittos\\Controller\\UserController::create');

// Serves documentation
$app->get('/', function () use ($app) {
  return $app->sendFile(__DIR__.'/doc/index.html');
});

// Run app
$app->run();
