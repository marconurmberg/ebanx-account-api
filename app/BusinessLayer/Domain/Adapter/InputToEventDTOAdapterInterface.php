<?php

namespace App\BusinessLayer\Domain\Adapter;

use App\BusinessLayer\Infra\DTO\DepositOperationEventDTO;
use App\BusinessLayer\Infra\DTO\WithdrawOperationEventDTO;
use App\BusinessLayer\Infra\Exception\BadRequestException;

interface InputToEventDTOAdapterInterface
{
    /**
     * @throws BadRequestException
     */
    public function inputToDepositOperationEventDTO(array $input): DepositOperationEventDTO;

    /**
     * @throws BadRequestException
     */
    public function inputToWithdrawOperationEventDTO(array $input): WithdrawOperationEventDTO;
}