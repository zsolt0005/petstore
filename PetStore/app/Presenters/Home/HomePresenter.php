<?php declare(strict_types=1);

namespace PetStore\Presenters\Home;

use PetStore\Presenters\APresenter;

final class HomePresenter extends APresenter
{
    public function actionDefault(): void
    {
        $this->flashMessageInfo('Welcome to PetStore');
        $this->flashMessageWarning('Welcome to PetStore');
        $this->flashMessageError('Welcome to PetStore');
    }
}
