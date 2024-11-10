<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new PetStore\Bootstrap;
$container = $bootstrap->bootWebApplication();
$application = $container->getByType(Nette\Application\Application::class);
$application->run();