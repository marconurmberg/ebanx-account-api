<?php

namespace Tests\Support\BusinessLayer\UseCases;

use App\BusinessLayer\Domain\Adapter\InputToEventDTOAdapterInterface;
use App\BusinessLayer\Domain\Adapter\UserAccountResponseAdapterInterface;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use App\BusinessLayer\Infra\DTO\WithdrawOperationEventDTO;
use App\BusinessLayer\Infra\Entity\UserAccountEntity;
use App\BusinessLayer\Infra\Exception\BadRequestException;
use App\BusinessLayer\Infra\Exception\CouldNotPersistException;
use App\BusinessLayer\Infra\Exception\InsufficientFundsException;
use App\BusinessLayer\Infra\Exception\UserAccountNotFoundException;
use App\BusinessLayer\Infra\Repository\UserAccountRepository;
use App\BusinessLayer\UseCases\Withdraw;
use CodeIgniter\HTTP\ResponseInterface;
use PHPUnit\Framework\TestCase;

class WithdrawTest extends TestCase
{
    /** @dataProvider successfulWithdrawDataProvider */
    public function testExecuteSuccessfulWithdraw(float $initialBalance, float $withdrawAmount): void
    {
        $accountId = 12345;
        $expectedBalance = $initialBalance - $withdrawAmount;
        $inputData = ['originAccountId' => $accountId, 'amount' => $withdrawAmount];
        
        $withdrawEventDTO = $this->createMock(WithdrawOperationEventDTO::class);
        $withdrawEventDTO->method('getAmount')->willReturn($withdrawAmount);
        $withdrawEventDTO->method('getOriginAccountId')->willReturn($accountId);

        $userAccount = $this->createUserAccountEntity($accountId, $initialBalance);
        $userAccountWithNewBalance = clone $userAccount;
        $userAccountWithNewBalance->setBalance($expectedBalance);

        $repository = $this->createMock(UserAccountRepository::class);
        $repository->method('getUserAccountByAccountId')->willReturn($userAccount);
        $repository->expects($this->once())
            ->method('persistUserAccount')
            ->with($userAccountWithNewBalance)
            ->willReturn(true);

        $inputAdapter = $this->createMock(InputToEventDTOAdapterInterface::class);
        $inputAdapter->method('inputToWithdrawOperationEventDTO')->willReturn($withdrawEventDTO);

        $responseAdapter = $this->createMock(UserAccountResponseAdapterInterface::class);
        $responseAdapter->method('fromWithdrawEventUserAccountEntityToResponseDTO')
            ->willReturn(new ResponseDTO(ResponseInterface::HTTP_CREATED));

        $withdraw = new Withdraw($repository, $inputAdapter, $responseAdapter);
        $response = $withdraw->execute($inputData);

        $this->assertEquals(ResponseInterface::HTTP_CREATED, $response->getHttpStatus());
        $this->assertEquals($expectedBalance, $userAccount->getBalance());
    }

    public function testExecuteWithInsufficientFunds(): void
    {
        $accountId = 12345;
        $initialBalance = 50.0;
        $withdrawAmount = 100.0;
        $inputData = ['originAccountId' => $accountId, 'amount' => $withdrawAmount];

        $withdrawEventDTO = $this->createMock(WithdrawOperationEventDTO::class);
        $withdrawEventDTO->method('getAmount')->willReturn($withdrawAmount);
        $withdrawEventDTO->method('getOriginAccountId')->willReturn($accountId);

        $userAccount = $this->createUserAccountEntity($accountId, $initialBalance);

        $repository = $this->createMock(UserAccountRepository::class);
        $repository->method('getUserAccountByAccountId')->willReturn($userAccount);
        $repository->expects($this->never())->method('persistUserAccount');

        $inputAdapter = $this->createMock(InputToEventDTOAdapterInterface::class);
        $inputAdapter->method('inputToWithdrawOperationEventDTO')->willReturn($withdrawEventDTO);

        $responseAdapter = $this->createMock(UserAccountResponseAdapterInterface::class);

        $withdraw = new Withdraw($repository, $inputAdapter, $responseAdapter);
        $response = $withdraw->execute($inputData);

        $this->assertEquals(ResponseInterface::HTTP_BAD_REQUEST, $response->getHttpStatus());
        $this->assertEquals($initialBalance, $userAccount->getBalance());
    }

    public function testExecuteAccountNotFound(): void
    {
        $accountId = 12345;
        $withdrawAmount = 50.0;
        $inputData = ['originAccountId' => $accountId, 'amount' => $withdrawAmount];

        $withdrawEventDTO = $this->createMock(WithdrawOperationEventDTO::class);
        $withdrawEventDTO->method('getAmount')->willReturn($withdrawAmount);
        $withdrawEventDTO->method('getOriginAccountId')->willReturn($accountId);

        $repository = $this->createMock(UserAccountRepository::class);
        $repository->method('getUserAccountByAccountId')
            ->willThrowException(new UserAccountNotFoundException());

        $inputAdapter = $this->createMock(InputToEventDTOAdapterInterface::class);
        $inputAdapter->method('inputToWithdrawOperationEventDTO')->willReturn($withdrawEventDTO);

        $responseAdapter = $this->createMock(UserAccountResponseAdapterInterface::class);

        $withdraw = new Withdraw($repository, $inputAdapter, $responseAdapter);
        $response = $withdraw->execute($inputData);

        $this->assertEquals(ResponseInterface::HTTP_NOT_FOUND, $response->getHttpStatus());
        $this->assertEquals(0, $response->getBody());
    }

    public function testExecuteWithPersistenceError(): void
    {
        $accountId = 12345;
        $initialBalance = 100.0;
        $withdrawAmount = 50.0;
        $inputData = ['originAccountId' => $accountId, 'amount' => $withdrawAmount];

        $withdrawEventDTO = $this->createMock(WithdrawOperationEventDTO::class);
        $withdrawEventDTO->method('getAmount')->willReturn($withdrawAmount);
        $withdrawEventDTO->method('getOriginAccountId')->willReturn($accountId);

        $userAccount = $this->createUserAccountEntity($accountId, $initialBalance);

        $repository = $this->createMock(UserAccountRepository::class);
        $repository->method('getUserAccountByAccountId')->willReturn($userAccount);
        $repository->method('persistUserAccount')
            ->willThrowException(new CouldNotPersistException());

        $inputAdapter = $this->createMock(InputToEventDTOAdapterInterface::class);
        $inputAdapter->method('inputToWithdrawOperationEventDTO')->willReturn($withdrawEventDTO);

        $responseAdapter = $this->createMock(UserAccountResponseAdapterInterface::class);

        $withdraw = new Withdraw($repository, $inputAdapter, $responseAdapter);
        $response = $withdraw->execute($inputData);

        $this->assertEquals(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR, $response->getHttpStatus());
    }

    private function createUserAccountEntity(int $accountId, float $balance): UserAccountEntity
    {
        $account = new UserAccountEntity();
        $account->setAccountId($accountId);
        $account->setBalance($balance);
        return $account;
    }

    public static function successfulWithdrawDataProvider(): array
    {
        return [
            "PARTIAL_WITHDRAW" => [100.0, 50.0],
            "FULL_WITHDRAW" => [100.0, 100.0],
            "DECIMAL_WITHDRAW" => [100.50, 50.25]
        ];
    }
}