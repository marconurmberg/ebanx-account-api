<?php

namespace App\BusinessLayer\Domain\Entity;

class UserAccountEntity
{
    private int $accountId;
    private float $balance;

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function setAccountId(int $accountId): void
    {
        $this->accountId = $accountId;
    }

    public function getBalance(): float
    {
        return number_format($this->balance, 2);
    }

    public function setBalance(float $balance): void
    {
        $this->balance = number_format($balance, 2);
    }
}