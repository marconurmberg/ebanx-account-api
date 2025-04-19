<?php

namespace App\BusinessLayer\Domain\Adapter;

use App\BusinessLayer\Infra\DTO\DepositOperationEventDTO;
use App\BusinessLayer\Infra\Exception\BadRequestException;

interface InputToEventDTOAdapterInterface
{
    /**
     * @throws BadRequestException
     */
    public function inputToDepositOperationEventDTO(array $input): DepositOperationEventDTO;
}