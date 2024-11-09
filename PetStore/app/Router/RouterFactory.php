<?php declare(strict_types=1);

namespace PetStore\Router;

use Contributte\ApiRouter\ApiRoute;
use Nette;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;

        // Web
        $router->addRoute('<presenter>/<action>', 'Home:default');

        // API
        $router->add(new ApiRoute('/api/v1/pet', 'Api:Pet', [
            'methods' => ['POST' => 'create'],
        ]));

        return $router;
	}
}
