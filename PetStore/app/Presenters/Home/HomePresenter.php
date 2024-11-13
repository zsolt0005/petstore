<?php declare(strict_types=1);

namespace PetStore\Presenters\Home;

use InvalidArgumentException;
use Nette\Http\IResponse;
use PetStore\Data\HomeFilterData;
use PetStore\Enums\HomeActionDefaultErrorResult;
use PetStore\Presenters\APresenter;
use PetStore\Presenters\Components\Grid\Data\GridData;
use PetStore\Presenters\Components\Grid\Grid;
use PetStore\SDK\Exceptions\RequestException;
use PetStore\Services\HomeService;
use PetStore\Utils\TypeUtils;
use Tracy\Debugger;
use Tracy\ILogger;

final class HomePresenter extends APresenter
{
    /** @var string Query parameter for filtering by ID. */
    public const string QUERY_FILTER_BY_ID = 'filterById';

    /** @var string Query parameter for filtering by Status. */
    public const string QUERY_FILTER_BY_STATUS = 'filterByStatus';

    /** @var string Query parameter for filtering by Tags. */
    public const string QUERY_FILTER_BY_TAGS = 'filterByTags';

    /**
     * Constructor.
     *
     * @param HomeService $service
     */
    public function __construct(private readonly HomeService $service)
    {
        parent::__construct();
    }

    /**
     * Action: Default.
     *
     * @return void
     */
    public function actionDefault(): void
    {
        $filterData = $this->getActionDefaultFilterData();

        $template = $this->getTemplate();
        $template->fitlerData = $filterData;

        $result = $this->service->prepareGridData($filterData);

        $result->matchAll(
            success: fn (GridData $gridData) => $template->gridData = $gridData,
            failure: function (?HomeActionDefaultErrorResult $error)
            {
                switch ($error)
                {
                    case HomeActionDefaultErrorResult::NOT_FOUND:
                        $this->flashMessageWarning('No pets found for the given filter');
                        break;

                    case HomeActionDefaultErrorResult::INVALID_FILTER_VALUE:
                        $this->flashMessageWarning('Invalid filter value');
                        break;

                    case HomeActionDefaultErrorResult::INTERNAL_SERVER_ERROR:
                        $this->flashMessageError('Something went wrong');
                        break;
                }
            }
        );
    }

    /**
     * Creates the grid component.
     *
     * @return Grid
     */
    public function createComponentGrid(): Grid
    {
        return new Grid();
    }

    /**
     * Gets the filter data for action default.
     *
     * @return HomeFilterData|null
     */
    private function getActionDefaultFilterData(): ?HomeFilterData
    {
        $request = $this->getRequest();

        $filterById = $request->getParameter(self::QUERY_FILTER_BY_ID);
        $filterByStatus = $request->getParameter(self::QUERY_FILTER_BY_STATUS);
        $filterByTags = $request->getParameter(self::QUERY_FILTER_BY_TAGS);

        if(!empty($filterById))
        {
            return new HomeFilterData(id: TypeUtils::convertToInt($filterById));
        }

        if(!empty($filterByStatus))
        {
            return new HomeFilterData(status: TypeUtils::convertToString($filterByStatus));
        }

        if(!empty($filterByTags))
        {
            return new HomeFilterData(tags: TypeUtils::convertToString($filterByTags));
        }

        return null;
    }
}
