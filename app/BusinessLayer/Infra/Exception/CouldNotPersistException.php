<?php

namespace App\BusinessLayer\Infra\Exception;

class CouldNotPersistException extends \Exception
{
    /**
     * @var string
     */
    protected $message = "Could not persist to the database";
}