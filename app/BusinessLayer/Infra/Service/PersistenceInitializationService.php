<?php

namespace App\BusinessLayer\Infra\Service;

use App\BusinessLayer\Domain\Entity\UserAccountEntity;
use App\BusinessLayer\Domain\Repository\UserAccountRepositoryInterface;
use App\BusinessLayer\Infra\Exception\UserAccountNotFoundException;

class PersistenceInitializationService
{
    private UserAccountRepositoryInterface $userAccountRepository;

    public function __construct(UserAccountRepositoryInterface $userAccountRepository)
    {
        $this->userAccountRepository = $userAccountRepository;
    }

    public function init()
    {
        $jsonMock = file_get_contents(__DIR__ . "\\..\\Mocks\\UserAccountsMock.json");
        $userAccountsMock = json_decode($jsonMock, true);

        foreach ($userAccountsMock["accounts"] as $userAccount) {
            $accountId = $userAccount["account_id"];
            $balance = $userAccount["balance"];
            if (!$this->accountAlreadyExists($accountId)) {
                $userAccountEntity = new UserAccountEntity();
                $userAccountEntity->setAccountId($accountId);
                $userAccountEntity->setBalance($balance);
                $this->userAccountRepository->persistUserAccount($userAccountEntity);
            }
        }
    }

    private function accountAlreadyExists(int $accountId): bool
    {
        try {
            $this->userAccountRepository->getUserAccountByAccountId($accountId);
        } catch (UserAccountNotFoundException $exception) {
            return false;
        }
        return true;
    }
}