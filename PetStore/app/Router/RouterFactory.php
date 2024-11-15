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

        # [API] Pets
        $router->add(new ApiRoute('/api/v1/pet', 'Api:Pet', [
            'methods' => [
                'GET' => 'getAll',
                'POST' => 'create',
                'PUT' => 'update'
            ]
        ]));

        $router->add(new ApiRoute('/api/v1/pet/findByStatus', 'Api:Pet', [
            'methods' => [
                'GET' => 'findByStatus'
            ]
        ]));

        $router->add(new ApiRoute('/api/v1/pet/findByTags', 'Api:Pet', [
            'methods' => [
                'GET' => 'findByTags'
            ]
        ]));

        $router->add(new ApiRoute('/api/v1/pet/<id>/uploadImage', 'Api:Pet', [
            'methods' => [
                'POST' => 'uploadImage'
            ]
        ]));

        $router->add(new ApiRoute('/api/v1/pet/<id>', 'Api:Pet', [
            'methods' => [
                'GET' => 'getById',
                'DELETE' => 'deleteById',
                'POST' => 'partialUpdate'
            ]
        ]));

        # [API] Categories
        $router->add(new ApiRoute('/api/v1/category', 'Api:Category', ['methods' => ['POST' => 'create']]));
        $router->add(new ApiRoute('/api/v1/category/<id>', 'Api:Category', ['methods' => ['DELETE' => 'delete']]));

        # [API] Tags
        $router->add(new ApiRoute('/api/v1/tag', 'Api:Tag', ['methods' => ['POST' => 'create']]));
        $router->add(new ApiRoute('/api/v1/tag/<id>', 'Api:Tag', ['methods' => ['DELETE' => 'delete']]));

        // [Web]
        $router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');

        return $router;
	}
}
