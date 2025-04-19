<?php

namespace App\BusinessLayer\Domain\Repository;

use App\BusinessLayer\Infra\Entity\UserAccountEntity;
use App\BusinessLayer\Infra\Exception\CouldNotPersistException;
use App\BusinessLayer\Infra\Exception\UserAccountNotFoundException;

interface UserAccountRepositoryInterface
{
    /**
     * @throws UserAccountNotFoundException
     */
    public function getUserAccountByAccountId(int $accountId): UserAccountEntity;

    /**
     * @throws CouldNotPersistException
     */
    public function persistUserAccount(UserAccountEntity $userAccount): bool;
}