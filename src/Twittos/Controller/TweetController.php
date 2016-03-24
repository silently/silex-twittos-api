<?php
namespace Twittos\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twittos\Entity\Tweet;
use Twittos\Controller\Utils;

class TweetController {

  public function create(Request $request, Application $app) {
    // Init tweet object
    $tweet = new Tweet(
      $request->get('currentUser'),
      $request->get('text')
    );
    // Fields validation
    $errors = Utils::formatErrors($app['validator']->validate($tweet));
    if ($errors) return $app->json($errors, 422);
    // Persists
    $app['orm.em']->persist($tweet);
    $app['orm.em']->flush();
    return new Response(null, 201);
  }

  public function destroy(Request $request, Application $app) {
    $app['session']->clear();
    return new Response(null, 200);
  }
}
