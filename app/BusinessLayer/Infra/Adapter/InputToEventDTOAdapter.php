<?php

namespace App\BusinessLayer\Infra\Adapter;

use App\BusinessLayer\Domain\Adapter\InputToEventDTOAdapterInterface;
use App\BusinessLayer\Infra\DTO\DepositOperationEventDTO;
use App\BusinessLayer\Infra\DTO\TransferOperationEventDTO;
use App\BusinessLayer\Infra\DTO\WithdrawOperationEventDTO;
use App\BusinessLayer\Infra\Exception\BadRequestException;

//TODO SPECIALIZED ADAPTER PER EVENT TYPE
//TODO TESTS
class InputToEventDTOAdapter implements InputToEventDTOAdapterInterface
{
    /**
     * @throws BadRequestException
     */
    public function inputToDepositOperationEventDTO(array $input): DepositOperationEventDTO
    {
        $this->validateDepositInputParameters($input);
        $depositEventDTO = new DepositOperationEventDTO();
        $depositEventDTO->setEventType($input["type"]);
        $depositEventDTO->setAmount($input["amount"]);
        $depositEventDTO->setDestinationAccountId($input["destination"]);
        return $depositEventDTO;
    }

    /**
     * @throws BadRequestException
     */
    public function inputToWithdrawOperationEventDTO(array $input): WithdrawOperationEventDTO
    {
        $this->validateWithdrawInputParameters($input);
        $withdrawEventDTO = new WithdrawOperationEventDTO();
        $withdrawEventDTO->setEventType($input["type"]);
        $withdrawEventDTO->setAmount($input["amount"]);
        $withdrawEventDTO->setOriginAccountId($input["origin"]);
        return $withdrawEventDTO;
    }

    public function inputToTransferOperationEventDTO(array $input): TransferOperationEventDTO
    {
        $this->validateTransferInputParameters($input);
        $transferEventDTO = new TransferOperationEventDTO();
        $transferEventDTO->setEventType($input["type"]);
        $transferEventDTO->setAmount($input["amount"]);
        $transferEventDTO->setOriginAccountId($input["origin"]);
        $transferEventDTO->setDestinationAccountId($input["destination"]);
        return $transferEventDTO;
    }

    /**
     * @throws BadRequestException
     */
    private function validateDepositInputParameters(array $input): void
    {
        if (!$this->isValidAmount($input) || !$this->isValidDestinationAccountId($input)) {
            throw new BadRequestException();
        }
    }

    /**
     * @throws BadRequestException
     */
    private function validateWithdrawInputParameters(array $input): void
    {
        if (!$this->isValidAmount($input) || !$this->isValidOriginAccountId($input)) {
            throw new BadRequestException();
        }
    }

    private function validateTransferInputParameters(array $input): void
    {
        if (!$this->isValidAmount($input) ||
            !$this->isValidDestinationAccountId($input) ||
            !$this->isValidOriginAccountId($input)) {
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

    private function isValidOriginAccountId(array $input): bool
    {
        return !empty($input["origin"]) && is_numeric($input["origin"]);
    }

}