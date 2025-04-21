<?php

namespace Tests\Support\BusinessLayer\Infra\Repository;

use App\BusinessLayer\Domain\Cache\CacheServiceInterface;
use App\BusinessLayer\Domain\Entity\UserAccountEntity;
use App\BusinessLayer\Infra\Exception\CouldNotPersistException;
use App\BusinessLayer\Infra\Exception\UserAccountNotFoundException;
use App\BusinessLayer\Infra\Repository\UserAccountRepository;
use PHPUnit\Framework\TestCase;

class UserAccountRepositoryTest extends TestCase
{
    private UserAccountRepository $repository;
    private CacheServiceInterface $cacheServiceMock;

    protected function setUp(): void
    {
        $this->cacheServiceMock = $this->createMock(CacheServiceInterface::class);
        $this->repository = new UserAccountRepository($this->cacheServiceMock);
    }

    public function testGetUserAccountByAccountIdSuccess(): void
    {
        $accountId = 100;
        $balance = 100.00;
        $expectedAccount = $this->createUserAccountEntity($accountId, $balance);
        $expectedCacheKey = "account_user_id_{$accountId}";

        $this->cacheServiceMock->expects($this->once())
            ->method('get')
            ->with($expectedCacheKey)
            ->willReturn($expectedAccount);

        $result = $this->repository->getUserAccountByAccountId($accountId);

        $this->assertEquals($expectedAccount, $result);
        $this->assertEquals($accountId, $result->getAccountId());
        $this->assertEquals($balance, $result->getBalance());
    }

    public function testGetUserAccountByAccountIdThrowsNotFoundException(): void
    {
        $accountId = 100;
        $expectedCacheKey = "account_user_id_{$accountId}";

        $this->cacheServiceMock->expects($this->once())
            ->method('get')
            ->with($expectedCacheKey)
            ->willReturn(null);

        $this->expectException(UserAccountNotFoundException::class);

        $this->repository->getUserAccountByAccountId($accountId);
    }

    public function testPersistUserAccountSuccess(): void
    {
        $accountId = 100;
        $balance = 100.00;
        $account = $this->createUserAccountEntity($accountId, $balance);
        $expectedCacheKey = "account_user_id_{$accountId}";

        $this->cacheServiceMock->expects($this->once())
            ->method('save')
            ->with($expectedCacheKey, $account)
            ->willReturn(true);

        $result = $this->repository->persistUserAccount($account);

        $this->assertTrue($result);
    }

    public function testPersistUserAccountThrowsPersistException(): void
    {
        $accountId = 100;
        $balance = 100.00;
        $account = $this->createUserAccountEntity($accountId, $balance);
        $expectedCacheKey = "account_user_id_{$accountId}";

        $this->cacheServiceMock->expects($this->once())
            ->method('save')
            ->with($expectedCacheKey, $account)
            ->willReturn(false);

        $this->expectException(CouldNotPersistException::class);

        $this->repository->persistUserAccount($account);
    }

    private function createUserAccountEntity(int $accountId, float $balance): UserAccountEntity
    {
        $account = new UserAccountEntity();
        $account->setAccountId($accountId);
        $account->setBalance($balance);
        return $account;
    }
}