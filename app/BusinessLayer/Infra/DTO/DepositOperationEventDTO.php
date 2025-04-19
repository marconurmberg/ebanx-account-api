<?php

namespace App\BusinessLayer\Infra\DTO;

class DepositOperationEventDTO extends AbstractAccountOperationEventDTO
{
    protected int $destinationAccountId;

    public function getDestinationAccountId(): int
    {
        return $this->destinationAccountId;
    }

    public function setDestinationAccountId(int $destinationAccountId): void
    {
        $this->destinationAccountId = $destinationAccountId;
    }
}