<?php

namespace App\BusinessLayer\Domain\Enum;

enum ResponseMessagesEnum
{
    public const string RESPONSE_MESSAGE_OK = "OK";
    public const string RESPONSE_MESSAGE_ERROR = "Error";
    public const string RESPONSE_MESSAGE_MISSING_PARAMETERS = "Missing required parameters";
}
