<?php declare(strict_types=1);

namespace PetStore\Presenters\Home;

use InvalidArgumentException;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use PetStore\Data\HomeFilterData;
use PetStore\Data\Pet;
use PetStore\Data\PetFormData;
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

    /** @var Pet|null Pet. */
    private ?Pet $pet = null;

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
                match ($error)
                {
                    HomeActionDefaultErrorResult::NOT_FOUND => $this->flashMessageWarning('home.default.errors.notFound'),
                    HomeActionDefaultErrorResult::INVALID_FILTER_VALUE => $this->flashMessageWarning('home.default.errors.invalidFilter'),
                    HomeActionDefaultErrorResult::INTERNAL_SERVER_ERROR => $this->flashMessageError('home.errors.general'),
                    null => null
                };
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
            success: fn () => $this->flashMessageInfo('home.delete.success'),
            failure: fn (HomeActionDeleteErrorResult $errorResult) => $this->flashMessageError('home.errors.general')
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
     * @throws InvalidArgumentException
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
                    HomeActionUpdateErrorResult::PET_NOT_FOUND => $this->flashMessageWarning('home.edit.errors.notFound', ['id' => $id]),
                    default => $this->flashMessageError('home.errors.general'),
                };

                $this->redirect('Home:default');
            }
        );

        $template = $this->getTemplate();
        $template->pet = $pet;
        $this->pet = $pet;
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
    public function createComponentForm(): Form
    {
        $form = new Form();

        $imagesLabel = $this->pet === null
            ? $this->translator->translate('home.form.labels.images')
            : $this->translator->translate('home.form.labels.addImages');

        $submitButtonCaption = $this->pet === null
            ? $this->translator->translate('home.form.labels.create')
            : $this->translator->translate('home.form.labels.edit');

        $form->addText(self::FORM_INPUT_CREATE_NAME, $this->translator->translate('home.form.labels.name'))
            ->setDefaultValue($this->pet?->name);
        $form->addText(self::FORM_INPUT_CREATE_CATEGORY, $this->translator->translate('home.form.labels.category'))
            ->setDefaultValue($this->pet?->category?->name);
        $form->addText(self::FORM_INPUT_CREATE_TAGS, $this->translator->translate('home.form.labels.tags'))
            ->setDefaultValue($this->pet?->getTagNames(', '));
        $form->addText(self::FORM_INPUT_CREATE_STATUS, $this->translator->translate('home.form.labels.status'))
            ->setDefaultValue($this->pet?->status);
        $form->addMultiUpload(self::FORM_INPUT_CREATE_IMAGES, $imagesLabel)
            ->setHtmlAttribute('accept', 'image/*');
        $form->addSubmit(self::FORM_SUBMIT_CREATE, $submitButtonCaption);

        $form->onValidate[] = [$this, 'validateForm'];

        if($this->pet === null)
        {
            $form->onSuccess[] = [$this, 'processCreateForm'];
        }
        else
        {
            $form->onSuccess[] = [$this, 'processUpdateForm'];
        }

        return $form;
    }

    /**
     * Validates the form.
     *
     * @param Form $form
     * @param PetFormData $data
     *
     * @return void
     */
    public function validateForm(Form $form, PetFormData $data): void
    {
        if(empty($data->name))
        {
            $form[self::FORM_INPUT_CREATE_NAME]->addError($this->translator->translate('home.form.errors.validation.nameEmpty'));
        }

        if(empty($data->category))
        {
            $form[self::FORM_INPUT_CREATE_CATEGORY]->addError($this->translator->translate('home.form.errors.validation.categoryEmpty'));
        }

        if(empty($data->tags))
        {
            $form[self::FORM_INPUT_CREATE_TAGS]->addError($this->translator->translate('home.form.errors.validation.tagEmpty'));
        }

        if(empty($data->status))
        {
            $form[self::FORM_INPUT_CREATE_STATUS]->addError($this->translator->translate('home.form.errors.validation.statusEmpty'));
        }
    }

    /**
     * Processes the create form.
     *
     * @param Form $form
     * @param PetFormData $data
     *
     * @return never
     * @throws AbortException
     * @throws InvalidArgumentException
     */
    public function processCreateForm(Form $form, PetFormData $data): never
    {
        $result = $this->service->createPet($data);
        $result->match(
            success: fn (Pet $pet) => $this->flashMessageInfo('home.create.success'),
            failure: function (HomeActionCreateErrorResult $errorResult) use ($form): void
            {
                match ($errorResult)
                {
                    HomeActionCreateErrorResult::CATEGORY_NOT_FOUND => $this->flashMessageWarning('home.form.errors.categoryNotFound'),
                    HomeActionCreateErrorResult::TAG_NOT_FOUND => $this->flashMessageWarning('home.form.errors.tagNotFound'),
                    HomeActionCreateErrorResult::INVALID_INPUT => $this->flashMessageWarning('home.form.errors.invalidValues'),
                    HomeActionCreateErrorResult::INTERNAL_SERVER_ERROR => $this->flashMessageError('home.errors.general'),
                    default => null
                };

                // Special case where pet was created by the iamges were failed to upload
                if($errorResult == HomeActionCreateErrorResult::INVALID_IMAGE_FILE)
                {
                    $this->flashMessageInfo('home.create.success');
                    $this->flashMessageWarning('home.form.errors.failedToUploadImages');
                    return;
                }

                $form->addError('Failed to create pet');
            }
        );

        $this->redirect('Home:default');
    }

    /**
     * Processes the update form.
     *
     * @param Form $form
     * @param PetFormData $data
     *
     * @return never
     * @throws AbortException
     * @throws InvalidArgumentException
     */
    public function processUpdateForm(Form $form, PetFormData $data): never
    {
        // This should never happen
        if($this->pet === null)
        {
            $this->flashMessageError('home.errors.general');
            $this->redirect('Home:default');
        }

        $result = $this->service->updatePet($this->pet, $data);
        $result->match(
            success: fn (Pet $pet) => $this->flashMessageInfo('home.edit.success'),
            failure: function (HomeActionCreateErrorResult $errorResult) use ($form): void
            {
                match ($errorResult)
                {
                    HomeActionCreateErrorResult::CATEGORY_NOT_FOUND => $this->flashMessageWarning('home.form.errors.categoryNotFound'),
                    HomeActionCreateErrorResult::TAG_NOT_FOUND => $this->flashMessageWarning('home.form.errors.tagNotFound'),
                    HomeActionCreateErrorResult::INVALID_INPUT => $this->flashMessageWarning('home.form.errors.invalidValues'),
                    HomeActionCreateErrorResult::INTERNAL_SERVER_ERROR => $this->flashMessageError('home.errors.general'),
                    default => null
                };

                // Special case where pet was created by the iamges were failed to upload
                if($errorResult == HomeActionCreateErrorResult::INVALID_IMAGE_FILE)
                {
                    $this->flashMessageInfo('home.edit.success');
                    $this->flashMessageWarning('home.form.errors.failedToUploadImages');
                    return;
                }

                $form->addError('Failed to update pet');
            }
        );

        $this->redirect('Home:edit', ['id' => $this->pet->id]);
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
