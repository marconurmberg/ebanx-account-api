<?php

namespace App\BusinessLayer\Infra\Adapter;

use App\BusinessLayer\Domain\Adapter\InputToEventDTOAdapterInterface;
use App\BusinessLayer\Infra\DTO\DepositOperationEventDTO;
use App\BusinessLayer\Infra\Exception\BadRequestException;

class InputToEventDTOAdapter implements InputToEventDTOAdapterInterface
{
    /**
     * @throws BadRequestException
     */
    public function inputToDepositOperationEventDTO(array $input): DepositOperationEventDTO
    {
        $this->validateInputParameters($input);
        $depositEventDTO = new DepositOperationEventDTO();
        $depositEventDTO->setEventType($input["type"]);
        $depositEventDTO->setAmount($input["amount"]);
        $depositEventDTO->setDestinationAccountId($input["destination"]);
        return $depositEventDTO;
    }

    /**
     * @throws BadRequestException
     */
    private function validateInputParameters(array $input): void
    {
        if (!$this->isValidAmount($input) || !$this->isValidDestinationAccountId($input)) {
            throw new BadRequestException();
        }
    }

    private function isValidAmount(array $input): bool
    {
        return !empty($input["amount"]) && is_numeric($input["amount"]) && $input["amount"] > 0;
    }

    private function isValidDestinationAccountId(array $input): bool
    {
        return !empty($input["destination"]) && is_numeric($input["destination"]);
    }

}