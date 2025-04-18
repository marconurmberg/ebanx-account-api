<?php

namespace App\BusinessLayer\Domain\Event;

use App\BusinessLayer\Infra\DTO\ResponseDTO;

interface AccountOperationEventInterface
{
    public function execute(array $inputData): ResponseDTO;
}