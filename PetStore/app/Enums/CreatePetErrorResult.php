<?php

namespace PetStore\Enums;

enum CreatePetErrorResult
{
    case CATEGORY_NOT_FOUND;
    case TAG_NOT_FOUND;
    case FAILED;
    case INVALID_INPUT;
}
