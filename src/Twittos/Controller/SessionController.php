<?php
namespace Twittos\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionController {

  public function create(Request $request, Application $app) {
    // Authenticates
    $repo = $app['orm.em']->getRepository('Twittos\Entity\User');
    // TODO login does not exists
    $user = $repo->findOneByLogin($request->get('login'));

    if ($user && $user->authenticate($request->get('password'))) {
        $userId = $repo->findOneByLogin($request->get('login'))->getId();
        $app['session']->set('userId', $userId);
        return new Response(null, 201);
    } else {
        return new Response(null, 401);
    }
  }
}
