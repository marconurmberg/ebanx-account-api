<?php

namespace App\BusinessLayer\Infra\Repository;

use App\BusinessLayer\Domain\Cache\CacheServiceInterface;
use App\BusinessLayer\Domain\Repository\UserAccountRepositoryInterface;
use App\BusinessLayer\Infra\Entity\UserAccountEntity;
use App\BusinessLayer\Infra\Exception\CouldNotPersistException;
use App\BusinessLayer\Infra\Exception\UserAccountNotFoundException;

class UserAccountRepository implements UserAccountRepositoryInterface
{

    private CacheServiceInterface $cacheService;

    public function __construct(CacheServiceInterface $cacheService)
    {
        $this->cacheService = $cacheService;
    }


    /**
     * @throws UserAccountNotFoundException
     */
    public function getUserAccountByAccountId(int $accountId): UserAccountEntity
    {
        $cacheKey = $this->getCacheKey($accountId);
        $userAccount = $this->cacheService->get($cacheKey);
        if (!$userAccount) {
            throw new UserAccountNotFoundException();
        }
        return $userAccount;
    }

    /**
     * @throws CouldNotPersistException
     */
    public function persistUserAccount(UserAccountEntity $userAccount): bool
    {
        $cacheKey = $this->getCacheKey($userAccount->getAccountId());
        $persistUserAccount = $this->cacheService->save($cacheKey, $userAccount);

        if (!$persistUserAccount) {
            throw new CouldNotPersistException();
        }
        return true;
    }

    private function getCacheKey($accountId)
    {
        return "account_user_id_{$accountId}";
    }
}