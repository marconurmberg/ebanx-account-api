<?php

namespace App\BusinessLayer\Infra\Entity;

class UserAccountEntity
{
    private int $accountId;
    private int $balance;

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function setAccountId(int $accountId): void
    {
        $this->accountId = $accountId;
    }

    public function getBalance(): int
    {
        return $this->balance;
    }

    public function setBalance(int $balance): void
    {
        $this->balance = $balance;
    }
}