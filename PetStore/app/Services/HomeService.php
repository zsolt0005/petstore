<?php declare(strict_types=1);

namespace PetStore\Services;

use InvalidArgumentException;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Http\IResponse;
use Nette\Utils\Arrays;
use PetStore\Data\HomeFilterData;
use PetStore\Data\Result;
use PetStore\Data\Tag;
use PetStore\Enums\HomeActionDefaultErrorResult;
use PetStore\Presenters\Components\Grid\Builders\GridDataBuilder;
use PetStore\Presenters\Components\Grid\Data\GridColumnActionData;
use PetStore\Presenters\Components\Grid\Data\GridData;
use PetStore\Presenters\Home\HomePresenter;
use PetStore\SDK\Exceptions\RequestException;
use PetStore\SDK\PetStoreSdk;

/**
 * Class HomeService
 *
 * @package PetStore\Services
 * @author  Zsolt Döme
 * @since   2024
 */
final class HomeService
{
    /**
     * Constructor.
     *
     * @param LinkGenerator $linkGenerator
     */
    public function __construct(private LinkGenerator $linkGenerator)
    {
    }

    /**
     * Prepares the grid data.
     *
     * @param HomeFilterData|null $filterData
     *
     * @return Result<HomeActionDefaultErrorResult, GridData>
     */
    public function prepareGridData(?HomeFilterData $filterData): Result
    {
        $dataBuilder = GridDataBuilder::create()
            ->addHeader('ID')
            ->addHeader('Name')
            ->addHeader('Category')
            ->addHeader('Status')
            ->addHeader('Tags')
            ->addHeader('Actions');

        try
        {
            $pets = match (true)
            {
                $filterData?->id !== null => [PetStoreSdk::create()->getById($filterData->id)],
                $filterData?->status !== null => PetStoreSdk::create()->findByStatus($filterData->status),
                $filterData?->tags !== null => PetStoreSdk::create()->findByTags($filterData->tags),
                default => PetStoreSdk::create()->getAll(),
            };

            if(empty($pets))
            {
                return Result::of(HomeActionDefaultErrorResult::NOT_FOUND, $dataBuilder->build());
            }

            foreach($pets as $pet)
            {
                $tags = Arrays::map($pet->tags, static fn(Tag $tag) => $tag->name);

                $actions = [
                    GridColumnActionData::create('delete', 'Delete', $this->linkGenerator->link('Home:delete', ['id' => $pet->id]), 'btn-danger')
                ];

                $dataBuilder->addRow()
                    ->addColumn((string) $pet->id)
                    ->addColumn($pet->name)
                    ->addColumn($pet->category->name)
                    ->addColumn($pet->status)
                    ->addColumn(implode(', ', $tags))
                    ->addActionsColumn($actions);
            }
        }
        catch (RequestException $e)
        {
            return match ($e->getCode())
            {
                IResponse::S404_NotFound => Result::of(HomeActionDefaultErrorResult::NOT_FOUND, $dataBuilder->build()),
                IResponse::S400_BadRequest => Result::of(HomeActionDefaultErrorResult::INVALID_FILTER_VALUE, $dataBuilder->build()),
                default => Result::of(HomeActionDefaultErrorResult::INTERNAL_SERVER_ERROR, $dataBuilder->build())
            };
        }
        catch (InvalidArgumentException | InvalidLinkException $e)
        {
            return Result::of(HomeActionDefaultErrorResult::INTERNAL_SERVER_ERROR, $dataBuilder->build());
        }

        return Result::of(null, $dataBuilder->build());
    }

}