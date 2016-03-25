<?php
namespace Twittos\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twittos\Entity\Tweet;
use Twittos\Controller\Utils;

class TweetController {

  public function index(Request $request, Application $app) {
    $maxResults = 2;
    $page = $request->get('page') ? $request->get('page') : 0;
    $firstResult = $page * $maxResults;
    $query = $app['orm.em']
      ->getRepository('Twittos\Entity\Tweet')
      ->createQueryBuilder('t')
      ->select('t.id, t.text, author.login AS authorLogin, t.likes, t.retweets, t.createdAt')
      ->where('t.isRetweet = false')
      ->innerJoin('t.author',  'author')
      ->orderBy('t.createdAt', 'DESC')
      ->setFirstResult($firstResult)
      ->setMaxResults($maxResults)
      ->getQuery();

    $apiRoot = $app['settings']['api']['root'];
    $tweets = array_map(function($t) use ($apiRoot) {
      $t['URI'] = $apiRoot.'/tweets/'.$t['id'];
      $t['authorURI'] = $apiRoot.'/users/'.$t['authorLogin'];
      $t['createdAt'] = $t['createdAt']->format('Y-m-d H:i:s');
      return $t;
    }, $query->getArrayResult());
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
    // Checks that tweets exists
    $tweet = $app['orm.em']->getRepository('Twittos\Entity\Tweet')->findOneById($request->get('id'));
    if(null === $tweet) return new Response(404);

    $user = $request->get('currentUser');
    // Checks user has not already liked the tweet
    if($user->getLikes()->contains($tweet)) { return new Response(null, 409); }
    // Let's like it
    $tweet->liked();
    $user->getLikes()->add($tweet);
    // persists
    $app['orm.em']->persist($tweet);
    $app['orm.em']->persist($user);
    $app['orm.em']->flush();
    return new Response(null, 201);
  }

  public function retweet(Request $request, Application $app) {
    // Checks that tweets exists
    $tweet = $app['orm.em']->getRepository('Twittos\Entity\Tweet')->findOneById($request->get('id'));
    if(null === $tweet) return new Response(404);

    $user = $request->get('currentUser');
    // Checks user has not already liked the tweet
    if($user->getRetweets()->contains($tweet)) { return new Response(null, 409); }
    // Let's like it
    $tweet->retweeted();
    $user->getRetweets()->add($tweet);
    // persists
    $app['orm.em']->persist($tweet);
    $app['orm.em']->persist($user);
    $app['orm.em']->flush();
    return new Response(null, 201);
  }

  public function destroy(Request $request, Application $app) {
    // Checks that tweets exists
    $tweet = $app['orm.em']->getRepository('Twittos\Entity\Tweet')->findOneBy(array(
      'id' => $request->get('id'),
      'author' => $request->get('currentUser')
    ));
    if(null === $tweet) return new Response(null, 401);
    $app['orm.em']->remove($tweet);
    $app['orm.em']->flush();
    return new Response(null, 201);
  }
}
