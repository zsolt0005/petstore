<?php

namespace PetStore\Enums;

enum HomeActionDefaultErrorResult
{
    case INTERNAL_SERVER_ERROR;
    case NOT_FOUND;
    case INVALID_FILTER_VALUE;
}
