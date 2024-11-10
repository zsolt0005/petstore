<?php

namespace PetStore\Enums;

enum UpdatePetErrorResult
{
    case CATEGORY_NOT_FOUND;
    case PET_NOT_FOUND;
    case INVALID_ID;
    case INVALID_INPUT;
}
