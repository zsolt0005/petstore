<?php declare(strict_types=1);

namespace PetStore\Services;

use InvalidArgumentException;
use Nette\Http\IResponse;
use Nette\Utils\Arrays;
use PetStore\Data\HomeFilterData;
use PetStore\Data\Result;
use PetStore\Data\Tag;
use PetStore\Enums\HomeActionDefaultErrorResult;
use PetStore\Presenters\Components\Grid\Builders\GridDataBuilder;
use PetStore\Presenters\Components\Grid\Data\GridData;
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
            ->addHeader('Tags');

        try
        {
            $pets = match (true)
            {
                $filterData?->id !== null => PetStoreSdk::create()->getAllPets(), // TODO
                $filterData?->status !== null => PetStoreSdk::create()->getAllPets(), // TODO
                $filterData?->tags !== null => PetStoreSdk::create()->getAllPets(), // TODO
                default => PetStoreSdk::create()->getAllPets(),
            };

            foreach($pets as $pet)
            {
                $tags = Arrays::map($pet->tags, static fn(Tag $tag) => $tag->name);

                $dataBuilder->addRow()
                    ->addColumn((string) $pet->id)
                    ->addColumn($pet->name)
                    ->addColumn($pet->category->name)
                    ->addColumn($pet->status)
                    ->addColumn(implode(', ', $tags));
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
        catch (InvalidArgumentException $e)
        {
            return Result::of(HomeActionDefaultErrorResult::INTERNAL_SERVER_ERROR, $dataBuilder->build());
        }

        return Result::of(null, $dataBuilder->build());
    }

}