<?php

namespace App\BusinessLayer\Domain\Enum;

enum ResponseMessagesEnum
{
    public const RESPONSE_MESSAGE_OK = "Ok";
    public const RESPONSE_MESSAGE_ERROR = "Error";
    public const RESPONSE_MESSAGE_MISSING_PARAMETERS = "Missing required parameters";
}
