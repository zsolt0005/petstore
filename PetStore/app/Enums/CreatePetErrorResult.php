<?php

namespace PetStore\Results;

enum CreatePetErrorResult
{
    case CATEGORY_DOES_NOT_EXIST;
    case FAILED;
}
