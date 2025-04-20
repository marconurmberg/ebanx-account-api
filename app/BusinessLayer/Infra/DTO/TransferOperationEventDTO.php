<?php

namespace App\BusinessLayer\Infra\DTO;

class TransferOperationEventDTO extends AbstractAccountOperationEventDTO
{
    protected int $originAccountId;
    protected int $destinationAccountId;

    public function getOriginAccountId(): int
    {
        return $this->originAccountId;
    }

    public function setOriginAccountId(int $originAccountId): void
    {
        $this->originAccountId = $originAccountId;
    }

    public function getDestinationAccountId(): int
    {
        return $this->destinationAccountId;
    }

    public function setDestinationAccountId(int $destinationAccountId): void
    {
        $this->destinationAccountId = $destinationAccountId;
    }
}