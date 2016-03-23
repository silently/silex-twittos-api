<?php
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\Console\Application AS ConsoleApplication;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\HelperSet;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

// Configures $app
$app = new Silex\Application();
require_once __DIR__.'/../config/bootstrap.php';

// Creates $console
$console = new ConsoleApplication('Silex - Rest API Edition', '1.0');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));

// Configures Doctrine CLI
$helperSet = new HelperSet(array(
  'db' => new ConnectionHelper($app['orm.em']->getConnection()),
  'em' => new EntityManagerHelper($app['orm.em'])
));
$console->setHelperSet($helperSet);

// Runs
ConsoleRunner::addCommands($console);
$console->run();
