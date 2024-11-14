<?php declare(strict_types=1);

namespace PetStore\Presenters\Home;

use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use PetStore\Data\HomeFilterData;
use PetStore\Enums\HomeActionDefaultErrorResult;
use PetStore\Enums\HomeActionDeleteErrorResult;
use PetStore\Presenters\APresenter;
use PetStore\Presenters\Components\Grid\Data\GridData;
use PetStore\Presenters\Components\Grid\Grid;
use PetStore\Services\HomeService;
use PetStore\Services\PetService;
use PetStore\Utils\TypeUtils;

final class HomePresenter extends APresenter
{
    /** @var string Query parameters for filtering. */
    public const string
        QUERY_FILTER_BY_ID = 'filterById',
        QUERY_FILTER_BY_STATUS = 'filterByStatus',
        QUERY_FILTER_BY_TAGS = 'filterByTags';

    /** @var string Create pet form inputs. */
    public const string
        FORM_INPUT_CREATE_NAME = 'name',
        FORM_INPUT_CREATE_CATEGORY = 'category',
        FORM_INPUT_CREATE_TAGS = 'tags',
        FORM_INPUT_CREATE_STATUS = 'status',
        FORM_SUBMIT_CREATE = 'create';

    /**
     * Constructor.
     *
     * @param HomeService $service
     * @param PetService $petService
     */
    public function __construct(
        private readonly HomeService $service,
        private readonly PetService $petService
    )
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
            success: function (?GridData $gridData) use ($template) { $template->gridData = $gridData; },
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
     * Action: Delete.
     *
     * @param int $id
     *
     * @return void
     * @throws AbortException
     */
    public function actionDelete(int $id): void
    {
        $result = $this->service->deleteById($id);

        $result->match(
            success: fn () => $this->flashMessageInfo('Pet was deleted'),
            failure: fn (HomeActionDeleteErrorResult $errorResult) => $this->flashMessageError('Something went wrong')
        );

        $this->redirect('Home:default');
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
     * Creates the creat pet form component.
     *
     * @return Form
     */
    public function createComponentCreateForm(): Form
    {
        $form = new Form();

        $form->addText(self::FORM_INPUT_CREATE_NAME, 'Name');
        $form->addText(self::FORM_INPUT_CREATE_CATEGORY, 'Category');
        $form->addText(self::FORM_INPUT_CREATE_TAGS, 'Tags');
        $form->addText(self::FORM_INPUT_CREATE_STATUS, 'Status');

        $form ->addSubmit(self::FORM_SUBMIT_CREATE, 'Create');

        $form->onSuccess[] = function (Form $form, ArrayHash $values): void
        {
            //$result = $this->service->createPet($values);
        };

        $form->onValidate[] = function (Form $form, ArrayHash $values): void
        {
            if(empty($values[self::FORM_INPUT_CREATE_NAME]))
            {
                $form[self::FORM_INPUT_CREATE_NAME]->addError('Pet name cannot be empty');
            }

            if(empty($values[self::FORM_INPUT_CREATE_CATEGORY]))
            {
                $form[self::FORM_INPUT_CREATE_CATEGORY]->addError('Category cannot be empty');
            }

            if(empty($values[self::FORM_INPUT_CREATE_TAGS]))
            {
                $form[self::FORM_INPUT_CREATE_TAGS]->addError('Tags cannot be empty');
            }

            if(empty($values[self::FORM_INPUT_CREATE_STATUS]))
            {
                $form[self::FORM_INPUT_CREATE_STATUS]->addError('Status cannot be empty');
            }
        };

        return $form;
    }

    /**
     * Gets the filter data for action default.
     *
     * @return HomeFilterData|null
     */
    private function getActionDefaultFilterData(): ?HomeFilterData
    {
        $request = $this->getRequest();

        $filterById = $request?->getParameter(self::QUERY_FILTER_BY_ID);
        $filterByStatus = $request?->getParameter(self::QUERY_FILTER_BY_STATUS);
        $filterByTags = $request?->getParameter(self::QUERY_FILTER_BY_TAGS);

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
