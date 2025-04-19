<?php

namespace App\BusinessLayer\Infra\Exception;

class BadRequestException extends \Exception
{
    /**
     * @var string
     */
    protected $message = "Bad Request";
}