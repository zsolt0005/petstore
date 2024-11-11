<?php declare(strict_types=1);

namespace PetStore\Presenters\Api\Pet;

use Exception;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use PetStore\Data\JsonResponse;
use PetStore\Data\Pet;
use PetStore\Enums\CreatePetErrorResult;
use PetStore\Enums\FindPetByStatusErrorResult;
use PetStore\Enums\GetPetByIdErrorResult;
use PetStore\Enums\UpdatePetErrorResult;
use PetStore\Services\PetService;
use PetStore\Utils\RequestUtils;
use PetStore\Utils\ResponseUtils;
use PetStore\Utils\TypeUtils;

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
     * FULL Updates a Pet.
     *
     * @return never
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
     * Partially Updates a Pet.
     *
     * @param int $id
     *
     * @return never
     * @throws Exception
     */
    public function actionPartialUpdate(int $id): never
    {
        $httpRequest = $this->getHttpRequest();
        $request = $this->getRequest();

        $name = TypeUtils::convertToString($request?->getParameter('name'));
        $status = TypeUtils::convertToString($request?->getParameter('status'));

        $result = $this->service->partialUpdate($id, $name, $status);

        $this->sendResponse(
            $result->match(
                success: fn(Pet $pet) => $this->sendResponse(ResponseUtils::mapDataToResponse($httpRequest, $pet)),
                failure: fn(UpdatePetErrorResult $errorResult) => match ($errorResult)
                {
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

    /**
     * Delete the Pet by its ID.
     *
     * @param int $id
     *
     * @return never
     * @throws Exception
     */
    public function actionDeleteById(int $id): never
    {
        $result = $this->service->deleteById($id);
        if(!$result)
        {
            $this->sendResponse(new JsonResponse(null, IResponse::S400_BadRequest));
        }

        $this->sendResponse(new JsonResponse(null, IResponse::S200_OK));
    }

    /**
     * Finds all the pets with the given status.
     *
     * @return never
     * @throws Exception
     */
    public function actionFindByStatus(): never
    {
        $request = $this->getHttpRequest();

        $status = TypeUtils::convertToString($this->getRequest()?->getParameter('status')) ?? '';
        $result = $this->service->findByStatus($status);

        $this->sendResponse(
            $result->match(
                success: fn(array $pets) => $this->sendResponse(ResponseUtils::mapDataToResponse($request, $pets)),
                failure: fn(FindPetByStatusErrorResult $errorResult) => match ($errorResult)
                {
                    FindPetByStatusErrorResult::INVALID_STATUS => $this->sendResponse(new JsonResponse(null, IResponse::S400_BadRequest))
                }
            )
        );
    }

    /**
     * Finds all the pets that have at least one of the tags.
     *
     * @return never
     * @throws Exception
     */
    public function actionFindByTags(): never
    {
        $request = $this->getHttpRequest();

        $tags = TypeUtils::convertToString($this->getRequest()?->getParameter('tags')) ?? '';
        if(empty($tags))
        {
            $this->sendResponse(new JsonResponse(null, IResponse::S400_BadRequest));
        }

        $pets = $this->service->findByTags($tags);
        $this->sendResponse(ResponseUtils::mapDataToResponse($request, $pets));
    }

    /**
     * Uploads an image for a Pet.
     *
     * @param int $id
     *
     * @return never
     * @throws Exception
     */
    public function actionUploadImage(int $id): never
    {
        $request = $this->getHttpRequest();
        $filesToUpload = $request->getFiles();

        $fileUploadResponse = $this->service->uploadImagesById($id, $filesToUpload);

        $this->sendResponse(new JsonResponse($fileUploadResponse, $fileUploadResponse->code));
    }
}
