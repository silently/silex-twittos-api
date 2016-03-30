<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;

// Configures $app
$app = new Application();
$app['debug'] = true;
require_once __DIR__.'/../config/bootstrap.php';

// Parses JSON body according to Content-Type
$app->before(function (Request $request) {
  if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
    $data = json_decode($request->getContent(), true);
    $request->request->replace(is_array($data) ? $data : array());
  }
});

// Public API
$app->post('/api/sessions', 'Twittos\\Controller\\SessionController::create');
$app->post('/api/users', 'Twittos\\Controller\\UserController::create');
$app->get('/', function () use ($app) { return $app->sendFile(__DIR__.'/doc/index.html'); });//doc

// API that requires a user
$authentifiedAPI = $app['controllers_factory'];
$authentifiedAPI->before(function (Request $request, Application $app) {
  if (null === $userId = $app['session']->get('userId')) return new Response(null, 401);
  $currentUser = $app['orm.em']->getRepository('Twittos\Entity\User')->findOneById($userId);
  if(null === $currentUser) return new Response(null, 401);
  $request->attributes->set('currentUser', $currentUser);
});
$authentifiedAPI->get('/api/tweets', 'Twittos\\Controller\\TweetController::index');
$authentifiedAPI->get('/api/users/{id}/tweets', 'Twittos\\Controller\\TweetController::indexForUser');
$authentifiedAPI->post('/api/tweets', 'Twittos\\Controller\\TweetController::create');
$authentifiedAPI->get('/api/tweets/{id}', 'Twittos\\Controller\\TweetController::show');
$authentifiedAPI->post('/api/tweets/like/{id}', 'Twittos\\Controller\\TweetController::like');
$authentifiedAPI->delete('/api/tweets/{id}', 'Twittos\\Controller\\TweetController::destroy');
$authentifiedAPI->post('/api/tweets/retweet/{id}', 'Twittos\\Controller\\TweetController::retweet');
$authentifiedAPI->get('/api/users/self', 'Twittos\\Controller\\UserController::info');
$authentifiedAPI->delete('/api/sessions', 'Twittos\\Controller\\SessionController::destroy');
$app->mount('/', $authentifiedAPI);

// Preflight OPTIONS for CORS
$app->options('/api/{path}', function() {
  return new Response(null, 200, array(
    'Access-Control-Allow-Headers' => 'Content-Type'
  ));
});

// Access-Control settings for any request
$app->after(function (Request $request, Response $response) {
  // Allow-Credentials imply Allow-Origin is a whitelist rather than a * wildcard
  $response->headers->set('Access-Control-Allow-Credentials', 'true');
  $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:3000');
});

// Runs app
$app->run();
