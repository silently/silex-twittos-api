<?php
namespace Twittos\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twittos\Entity\Tweet;
use Twittos\Controller\Utils;

class TweetController {

  public function index(Request $request, Application $app) {
    $query = $app['orm.em']
      ->getRepository('Twittos\Entity\Tweet')
      ->createQueryBuilder('t')
      ->select('t.id, t.text, t.likes, t.created_at')
      ->orderBy('t.created_at', 'DESC')
      ->setMaxResults(20)
      ->getQuery();
    $tweets = $query->getArrayResult();
    return $app->json($tweets, 200);
  }

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

  public function like(Request $request, Application $app) {
    $tweet = $app['orm.em']->getRepository('Twittos\Entity\Tweet')->findOneById($request->get('id'));
    if(null === $tweet) return new Response(404);
    $tweet->liked();
    // persists
    $app['orm.em']->persist($tweet);
    $app['orm.em']->flush();
    // Tweet created of duplicate conflict
    return new Response(null, 201);
  }
}