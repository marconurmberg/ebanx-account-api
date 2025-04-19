<?php

namespace App\BusinessLayer\Infra\Exception;

class AccountOperationEventNotFoundException extends \Exception
{
    protected $message = "Event type not found";
}