<?php

namespace Tests\Support\BusinessLayer\UseCases;

use App\BusinessLayer\Domain\Entity\UserAccountEntity;
use App\BusinessLayer\Infra\Exception\UserAccountNotFoundException;
use App\BusinessLayer\Infra\Repository\UserAccountRepository;
use App\BusinessLayer\UseCases\GetAccountBalance;
use CodeIgniter\HTTP\ResponseInterface;
use PHPUnit\Framework\TestCase;

class GetAccountBalanceTest extends TestCase
{
    public function testGetAccountBalance()
    {
        $userAccountId = 1234;
        $userAccountBalance = 10.0;

        $userAccountRepositoryMock = $this->createMock(UserAccountRepository::class);
        $userAccountEntityMock = $this->createMock(UserAccountEntity::class);

        $userAccountEntityMock->expects($this->never())
            ->method('getAccountId');
        $userAccountEntityMock->expects($this->once())
            ->method('getBalance')
            ->willReturn($userAccountBalance);

        $userAccountRepositoryMock->expects($this->once())
            ->method('getUserAccountByAccountId')
            ->with($userAccountId)
            ->willReturn($userAccountEntityMock);

        $useCase = new GetAccountBalance($userAccountRepositoryMock);
        $getAccountBalanceResponse = $useCase->execute($userAccountId);

        $this->assertEquals($userAccountBalance, $getAccountBalanceResponse->getBody());
        $this->assertEquals(ResponseInterface::HTTP_OK, $getAccountBalanceResponse->getHttpStatus());
    }

    /** @dataProvider accountBalanceExceptionDataProvider */
    public function testGetAccountBalanceUserNotFoundException($exceptionToBeThrown, $expectedBody, $expectedHttpStatus)
    {
        $userAccountId = 12345;
        $userAccountRepositoryMock = $this->createMock(UserAccountRepository::class);
        $userAccountEntityMock = $this->createMock(UserAccountEntity::class);

        $userAccountEntityMock->expects($this->never())
            ->method('getAccountId');
        $userAccountEntityMock->expects($this->never())
            ->method('getBalance');

        $userAccountRepositoryMock->expects($this->once())
            ->method('getUserAccountByAccountId')
            ->with($userAccountId)
            ->willThrowException($exceptionToBeThrown);

        $useCase = new GetAccountBalance($userAccountRepositoryMock);
        $getAccountBalanceResponse = $useCase->execute($userAccountId);

        $this->assertEquals($expectedBody, $getAccountBalanceResponse->getBody());
        $this->assertEquals($expectedHttpStatus, $getAccountBalanceResponse->getHttpStatus());
    }

    public static function accountBalanceExceptionDataProvider(): array
    {
        return [
            "USER_NOT_FOUND_EXCEPTION" => [
                new UserAccountNotFoundException(),
                0,
                ResponseInterface::HTTP_NOT_FOUND,
            ],
            "UNEXPECTED_EXCEPTION" => [
                new \Exception("Unexpected Exception"),
                "Unexpected Exception",
                ResponseInterface::HTTP_INTERNAL_SERVER_ERROR,
            ]
        ];
    }
}
