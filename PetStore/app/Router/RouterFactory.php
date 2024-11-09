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
            'methods' => [
                'POST' => 'create',
                'PUT' => 'update'
            ],
        ]));
        $router->add(new ApiRoute('/api/v1/pet/<id>', 'Api:Pet', [
            'methods' => [
                'GET' => 'getById'
            ],
        ]));

        $router->add(new ApiRoute('/api/v1/category', 'Api:Category', ['methods' => ['POST' => 'create']]));
        $router->add(new ApiRoute('/api/v1/category/<id>', 'Api:Category', ['methods' => ['DELETE' => 'delete']]));

        return $router;
	}
}
