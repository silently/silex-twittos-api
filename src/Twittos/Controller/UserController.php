<?php
namespace Twittos\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Twittos\Entity\User;
use Twittos\Controller\Utils;

class UserController {

  public function info(Request $request, Application $app) {
    error_log(print_r($app['session']->all(), 1));
    if (null === $userId = $app['session']->get('userId')) {
      return new Response(null, 401);
    }
    // Retrieves user info in DB
    $repo = $app['orm.em']->getRepository('Twittos\Entity\User');
    $info = $repo->findOneById($userId)->getInfo();

    return $app->json($info, 200);
  }

  public function create(Request $request, Application $app) {
    // Init user object
    $user = new User(
      $request->get('login'),
      $request->get('password'),
      $request->get('email')
    );
    // Fields validation
    $errors = Utils::formatErrors($app['validator']->validate($user));
    if ($errors) return $app->json($errors, 422);
    // Uniqueness validation
    try {
      $app['orm.em']->persist($user);
      $app['orm.em']->flush();
      // Success
      return new Response(null, 201);
    }
    catch (UniqueConstraintViolationException $e){
      return new Response(null, 409);
    }
  }
}
