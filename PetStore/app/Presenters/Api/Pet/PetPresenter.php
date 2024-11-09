<?php declare(strict_types=1);

namespace PetStore\Presenters\Api\Pet;

use Nette\Application\AbortException;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use PetStore\Data\JsonResponse;
use PetStore\Data\Pet;
use PetStore\Services\PetService;
use PetStore\Utils\RequestUtils;
use PetStore\Utils\ResponseUtils;

/**
 * Class PetController
 *
 * @package PetStore\Controllers
 * @author  Zsolt Döme
 * @since   2024
 */
final class PetPresenter extends Presenter
{
    /**
     * Constructor.
     *
     * @param PetService $service
     */
    public function __construct(private readonly PetService $service)
    {
        parent::__construct();
    }

    /**
     * Creates a new Pet.
     *
     * @return never
     *
     * @throws AbortException
     */
    public function actionCreate(): never
    {
        $request = $this->getHttpRequest();

        $petData = RequestUtils::mapRequestToData($request, Pet::class)
            ?? $this->sendResponse(new JsonResponse(null, IResponse::S400_BadRequest));

        $createdPetData = $this->service->create($petData);
        if($createdPetData === null)
        {
            $this->sendResponse(new JsonResponse(null, IResponse::S405_MethodNotAllowed));
        }

        $this->sendResponse(ResponseUtils::mapDataToResponse($request, $createdPetData));
    }
}
