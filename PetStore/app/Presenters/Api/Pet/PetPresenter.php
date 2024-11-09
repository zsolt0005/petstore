<?php declare(strict_types=1);

namespace PetStore\Presenters\Api\Pet;

use Exception;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use PetStore\Data\JsonResponse;
use PetStore\Data\Pet;
use PetStore\Results\CreatePetErrorResult;
use PetStore\Results\GetPetByIdErrorResult;
use PetStore\Results\UpdatePetErrorResult;
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
     * @throws Exception
     */
    public function actionCreate(): never
    {
        $request = $this->getHttpRequest();

        $petData = RequestUtils::mapRequestToData($request, Pet::class)
            ?? $this->sendResponse(new JsonResponse(null, IResponse::S400_BadRequest));

        $result = $this->service->create($petData);

        $this->sendResponse(
            $result->match(
               success: fn(Pet $pet) => $this->sendResponse(ResponseUtils::mapDataToResponse($request, $pet)),
               failure: fn(CreatePetErrorResult $errorResult) => $this->sendResponse(new JsonResponse(null, IResponse::S405_MethodNotAllowed))
            )
        );
    }

    /**
     * Creates a new Pet.
     *
     * @return never
     *
     * @throws Exception
     */
    public function actionUpdate(): never
    {
        $request = $this->getHttpRequest();

        $petData = RequestUtils::mapRequestToData($request, Pet::class)
            ?? $this->sendResponse(new JsonResponse(null, IResponse::S405_MethodNotAllowed));

        $result = $this->service->update($petData);

        $this->sendResponse(
            $result->match(
                success: fn(Pet $pet) => $this->sendResponse(ResponseUtils::mapDataToResponse($request, $pet)),
                failure: fn(UpdatePetErrorResult $errorResult) => match ($errorResult)
                {
                    UpdatePetErrorResult::INVALID_ID => $this->sendResponse(new JsonResponse(null, IResponse::S400_BadRequest)),
                    UpdatePetErrorResult::PET_NOT_FOUND => $this->sendResponse(new JsonResponse(null, IResponse::S404_NotFound)),
                    default => $this->sendResponse(new JsonResponse(null, IResponse::S405_MethodNotAllowed))
                }
            )
        );
    }

    /**
     * Get the Pet by its ID.
     *
     * @param int $id
     *
     * @return never
     *
     * @throws Exception
     */
    public function actionGetById(int $id): never
    {
        $request = $this->getHttpRequest();

        $result = $this->service->getById($id);

        $this->sendResponse(
            $result->match(
                success: fn(Pet $pet) => $this->sendResponse(ResponseUtils::mapDataToResponse($request, $pet)),
                failure: fn(GetPetByIdErrorResult $errorResult) => match ($errorResult)
                {
                    GetPetByIdErrorResult::INVALID_ID => $this->sendResponse(new JsonResponse(null, IResponse::S400_BadRequest)),
                    GetPetByIdErrorResult::PET_NOT_FOUND => $this->sendResponse(new JsonResponse(null, IResponse::S404_NotFound))
                }
            )
        );
    }
}
