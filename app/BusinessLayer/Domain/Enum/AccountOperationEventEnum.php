<?php

namespace App\BusinessLayer\Domain\Enum;

enum AccountOperationEventEnum
{
    const EVENT_DEPOSIT = "deposit";
    const EVENT_WITHDRAW = "withdraw";
    const EVENT_TRANSFER = "transfer";

}
