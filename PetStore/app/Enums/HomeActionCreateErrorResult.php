<?php

namespace PetStore\Enums;

enum HomeActionCreateErrorResult
{
    case INTERNAL_SERVER_ERROR;
    case CATEGORY_NOT_FOUND;
    case TAG_NOT_FOUND;
    case INVALID_INPUT;
    case INVALID_IMAGE_FILE;
}
