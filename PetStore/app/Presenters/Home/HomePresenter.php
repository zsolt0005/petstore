<?php declare(strict_types=1);

namespace PetStore\Presenters\Home;

use InvalidArgumentException;
use Nette\Application\AbortException;
use Nette\Http\IResponse;
use Nette\Utils\Arrays;
use PetStore\Data\JsonResponse;
use PetStore\Data\Tag;
use PetStore\Presenters\APresenter;
use PetStore\Presenters\Components\Grid\Builders\GridDataBuilder;
use PetStore\Presenters\Components\Grid\Grid;
use PetStore\SDK\Exceptions\RequestException;
use PetStore\SDK\PetStoreSdk;

final class HomePresenter extends APresenter
{
    /** @var string Snippet id for the grid component. */
    public const string SNIPPET_GRID = 'snippet-grid';

    /**
     * Creates the grid component.
     *
     * @return Grid
     */
    public function createComponentGrid(): Grid
    {
        $data = GridDataBuilder::create()
            ->addHeader('ID')
            ->addHeader('Name')
            ->addHeader('Category')
            ->addHeader('Status')
            ->addHeader('Tags')
            ->build();

        return new Grid($data);
    }

    /**
     * Handles the load of the initial data of the grid.
     *
     * @return void
     *
     * @throws AbortException
     */
    public function handleLoadAllPets(): void
    {
        $gridComponent = $this->getTypedComponent('grid', Grid::class);
        if($gridComponent === null)
        {
            return;
        }

        try
        {
            $pets = PetStoreSdk::create()->getAllPets();
        }
        catch (RequestException $e)
        {
            $this->sendResponse(new JsonResponse(null, $e->httpStatusCode));
        }
        catch (InvalidArgumentException $e)
        {
            $this->sendResponse(new JsonResponse(null, IResponse::S500_InternalServerError));
        }

        $dataBuilder = $gridComponent->getDataBuilder()->clearRows();

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

        $gridComponent->setData($dataBuilder->build());
        $this->redrawControl(self::SNIPPET_GRID);
    }
}
