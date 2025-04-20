<?php

namespace App\BusinessLayer\Infra\DTO;

class WithdrawOperationEventDTO extends AbstractAccountOperationEventDTO
{
    protected int $originAccountId;

    public function getOriginAccountId(): int
    {
        return $this->originAccountId;
    }

    public function setOriginAccountId(int $originAccountId): void
    {
        $this->originAccountId = $originAccountId;
    }
}