<?php declare(strict_types=1);

namespace PetStore\Presenters\Home;

use InvalidArgumentException;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use PetStore\Data\HomeFilterData;
use PetStore\Data\Pet;
use PetStore\Enums\HomeActionCreateErrorResult;
use PetStore\Enums\HomeActionDefaultErrorResult;
use PetStore\Enums\HomeActionDeleteErrorResult;
use PetStore\Enums\HomeActionUpdateErrorResult;
use PetStore\Presenters\APresenter;
use PetStore\Presenters\Components\Grid\Data\GridData;
use PetStore\Presenters\Components\Grid\Grid;
use PetStore\Services\HomeService;
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
        FORM_INPUT_CREATE_IMAGES = 'images',
        FORM_SUBMIT_CREATE = 'create';

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
     * @throws InvalidArgumentException
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
     * Action: Edit.
     *
     * @param int $id
     *
     * @return void
     * @throws AbortException
     */
    public function actionEdit(int $id): void
    {
        $result = $this->service->getById($id);
        $pet = $result->match(
            success: fn (Pet $pet) => $pet,
            failure: function (HomeActionUpdateErrorResult $errorResult) use ($id): never
            {
                match ($errorResult)
                {
                    HomeActionUpdateErrorResult::PET_NOT_FOUND => $this->flashMessageWarning('Pet with id ' . $id . ' not found'),
                    default => $this->flashMessageError('Something went wrong'),
                };

                $this->redirect('Home:default');
            }
        );

        $template = $this->getTemplate();
        $template->pet = $pet;
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
        $form->addMultiUpload(self::FORM_INPUT_CREATE_IMAGES, 'Images')
            ->setHtmlAttribute('accept', 'image/*');
        $form->addSubmit(self::FORM_SUBMIT_CREATE, 'Create');

        $form->onSuccess[] = [$this, 'processCreateForm'];
        $form->onValidate[] = [$this, 'validateCreateForm'];

        return $form;
    }

    /**
     * Validates the create form.
     *
     * @param Form $form
     * @param ArrayHash<string> $values
     *
     * @return void
     */
    public function validateCreateForm(Form $form, ArrayHash $values): void
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
    }

    /**
     * Processes the create form.
     *
     * @param Form $form
     * @param ArrayHash<string> $values
     *
     * @return never
     */
    public function processCreateForm(Form $form, ArrayHash $values): never
    {
        $result = $this->service->createPet($values);
        $result->match(
            success: fn (Pet $pet) => $this->flashMessageInfo('Pet was created'),
            failure: function (HomeActionCreateErrorResult $errorResult) use ($form): void
            {
                match ($errorResult)
                {
                    HomeActionCreateErrorResult::CATEGORY_NOT_FOUND => $this->flashMessageWarning('Category not found'),
                    HomeActionCreateErrorResult::TAG_NOT_FOUND => $this->flashMessageWarning('Tag not found'),
                    HomeActionCreateErrorResult::INVALID_INPUT => $this->flashMessageWarning('Invalid values supplied'),
                    HomeActionCreateErrorResult::INTERNAL_SERVER_ERROR => $this->flashMessageError('Something went wrong'),
                    default => null
                };

                // Special case where pet was created by the iamges were failed to upload
                if($errorResult == HomeActionCreateErrorResult::INVALID_IMAGE_FILE)
                {
                    $this->flashMessageInfo('Pet was created');
                    $this->flashMessageWarning('Failed to upload pet images due to an invalid image file');
                    return;
                }

                $form->addError('Failed to create pet');
            }
        );

        $this->redirect('Home:default');
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
