<?php

namespace App\BusinessLayer\Infra\Exception;

class InsufficientFundsException extends \Exception
{
    protected $message = "Insufficient funds to complete operation";
}