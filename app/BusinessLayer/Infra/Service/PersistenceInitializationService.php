<?php

namespace App\BusinessLayer\Infra\Service;

use App\BusinessLayer\Domain\Repository\UserAccountRepositoryInterface;
use App\BusinessLayer\Infra\Entity\UserAccountEntity;

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
            $userAccountEntity = new UserAccountEntity();
            $userAccountEntity->setAccountId($userAccount["account_id"]);
            $userAccountEntity->setBalance($userAccount["balance"]);
            $this->userAccountRepository->persistUserAccount($userAccountEntity);
        }
    }
}