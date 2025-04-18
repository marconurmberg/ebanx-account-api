<?php

namespace App\BusinessLayer\Domain\Repository;

use App\BusinessLayer\Infra\Entity\UserAccountEntity;
use App\BusinessLayer\Infra\Exception\UserAccountNotFoundException;

interface UserAccountRepositoryInterface
{
    /**
     * @throws UserAccountNotFoundException
     */
    public function getUserAccountByAccountId(int $accountId): UserAccountEntity;
    public function persistUserAccount(UserAccountEntity $userAccount): bool;
}