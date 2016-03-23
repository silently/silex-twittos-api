<?php
use Symfony\Component\Yaml\Yaml;

$app['settings'] = Yaml::parse(file_get_contents(__DIR__.'/../config/settings.yml'));
// Database abstraction layer
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
  'db.options' => $app['settings']['db']
));
// ORM
$app->register(new Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider, array(
  'orm.proxies_dir' => __DIR__.'/../src/Twittos/Proxy',
  'orm.em.options' => array(
    'mappings' => array(
      array(
        'type' => 'annotation',
        'namespace' => 'Twittos\Entity',
        'path' => __DIR__.'/../src/'
      )
    )
  )
));
// Validator
$app->register(new Silex\Provider\ValidatorServiceProvider());
// Sessions
$app->register(new Silex\Provider\SessionServiceProvider(), array(
  'session.storage.options' => array(
    'cookie_secure' => false,//needs https
    'cookie_httponly' => true
  )
));
