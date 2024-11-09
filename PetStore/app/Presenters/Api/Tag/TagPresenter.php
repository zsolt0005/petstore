<?php declare(strict_types=1);

namespace PetStore\Presenters\Api\Tag;

use Exception;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use PetStore\Data\JsonResponse;
use PetStore\Data\Tag;
use PetStore\Results\CreateTagErrorResult;
use PetStore\Services\TagService;
use PetStore\Utils\RequestUtils;
use PetStore\Utils\ResponseUtils;

/**
 * Class TagPresenter
 *
 * @package PetStore\Presenters\Api\Pet
 * @author  Zsolt Döme
 * @since   2024
 */
final class TagPresenter extends Presenter
{
    /**
     * Constructor.
     *
     * @param TagService $service
     */
    public function __construct(private readonly TagService $service)
    {
        parent::__construct();
    }

    /**
     * Creates a new Tag.
     *
     * @return never
     * @throws Exception
     */
    public function actionCreate(): never
    {
        $request = $this->getHttpRequest();

        $categoryData = RequestUtils::mapRequestToData($request, Tag::class)
            ?? $this->sendResponse(new JsonResponse(null, IResponse::S400_BadRequest));

        $result = $this->service->create($categoryData);

        $this->sendResponse(
            $result->match(
                success: fn(Tag $pet) => $this->sendResponse(ResponseUtils::mapDataToResponse($request, $pet)),
                failure: fn(CreateTagErrorResult $errorResult) => $this->sendResponse(new JsonResponse(null, IResponse::S400_BadRequest))
            )
        );
    }

    /**
     * Deletes a Tag.
     *
     * @param int $id
     *
     * @return never
     * @throws Exception
     */
    public function actionDelete(int $id): never
    {
        $this->service->delete($id);
        $this->sendResponse(new JsonResponse(null, IResponse::S200_OK));
    }
}