<?php

namespace PetStore\Results;

enum GetPetByIdErrorResult
{
    case PET_NOT_FOUND;
    case INVALID_ID;
}
