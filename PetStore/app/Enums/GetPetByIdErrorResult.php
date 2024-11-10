<?php

namespace PetStore\Enums;

enum GetPetByIdErrorResult
{
    case PET_NOT_FOUND;
    case INVALID_ID;
}
