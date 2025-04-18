<?php

namespace App\BusinessLayer\Infra\Exception;

class UserAccountNotFoundException extends \Exception
{

    /**
     * @var string
     */
    protected $message = 'User account not found';
}