<?php declare(strict_types=1);

namespace PetStore\Presenters\Api\Category;

use Exception;
use Nette\Application\UI\Presenter;
use Nette\Http\IResponse;
use PetStore\Data\Category;
use PetStore\Data\JsonResponse;
use PetStore\Enums\CreateCategoryErrorResult;
use PetStore\Services\CategoryService;
use PetStore\Utils\RequestUtils;
use PetStore\Utils\ResponseUtils;

/**
 * Class CategoryPresenter
 *
 * @package PetStore\Presenters\Api\Pet
 * @author  Zsolt Döme
 * @since   2024
 */
final class CategoryPresenter extends Presenter
{
    /**
     * Constructor.
     *
     * @param CategoryService $service
     */
    public function __construct(private readonly CategoryService $service)
    {
        parent::__construct();
    }

    /**
     * Creates a new Category.
     *
     * @return never
     * @throws Exception
     */
    public function actionCreate(): never
    {
        $request = $this->getHttpRequest();

        $categoryData = RequestUtils::mapRequestToData($request, Category::class)
            ?? $this->sendResponse(new JsonResponse(null, IResponse::S400_BadRequest));

        $result = $this->service->create($categoryData);

        $this->sendResponse(
            $result->match(
                success: fn(Category $pet) => $this->sendResponse(ResponseUtils::mapDataToResponse($request, $pet)),
                failure: fn(CreateCategoryErrorResult $errorResult) => $this->sendResponse(new JsonResponse(null, IResponse::S400_BadRequest))
            )
        );
    }

    /**
     * Deletes a Category.
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