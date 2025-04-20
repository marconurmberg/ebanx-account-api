<?php

namespace App\BusinessLayer\Domain\Enum;

enum AccountOperationEventEnum
{
    const string EVENT_DEPOSIT = "deposit";
    const string EVENT_WITHDRAW = "withdraw";
    const string EVENT_TRANSFER = "transfer";

}
