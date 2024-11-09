<?php

namespace PetStore\Results;

enum CreatePetErrorResult
{
    case CATEGORY_NOT_FOUND;
    case FAILED;
    case INVALID_INPUT;
}
