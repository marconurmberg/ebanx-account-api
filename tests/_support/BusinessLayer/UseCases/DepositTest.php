<?php

namespace Tests\Support\BusinessLayer\UseCases;

use App\BusinessLayer\Domain\Adapter\InputToEventDTOAdapterInterface;
use App\BusinessLayer\Infra\Adapter\UserAccountResponseAdapter;
use App\BusinessLayer\Infra\DTO\DepositOperationEventDTO;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use App\BusinessLayer\Infra\Entity\UserAccountEntity;
use App\BusinessLayer\Infra\Exception\BadRequestException;
use App\BusinessLayer\Infra\Exception\CouldNotPersistException;
use App\BusinessLayer\Infra\Exception\UserAccountNotFoundException;
use App\BusinessLayer\Infra\Repository\UserAccountRepository;
use App\BusinessLayer\UseCases\Deposit;
use CodeIgniter\HTTP\ResponseInterface;
use PHPUnit\Framework\TestCase;

class DepositTest extends TestCase
{
    /** @dataProvider successfulDepositDataProvider */
    public function testExecuteSuccessfulDeposit($depositAmount): void
    {
        $accountId = 12345;
        $initialBalance = 50;
        $expectedBalance = number_format($initialBalance + $depositAmount, 2);
        
        $inputData = ['destinationAccountId' => $accountId, 'amount' => $depositAmount];
        $depositEventDTO = $this->createMock(DepositOperationEventDTO::class);
        $depositEventDTO->method('getAmount')->willReturn((float)$depositAmount);

        $existingAccount = $this->createUserAccountEntity($accountId, $initialBalance);
        $userAccountWithNewBalance = clone $existingAccount;
        $userAccountWithNewBalance->setBalance($expectedBalance);

        $repository = $this->createMock(UserAccountRepository::class);
        $repository->method('getUserAccountByAccountId')->willReturn($existingAccount);
        
        $repository->method('persistUserAccount')
            ->with($userAccountWithNewBalance)
            ->willReturn(true);

        $inputAdapter = $this->createMock(InputToEventDTOAdapterInterface::class);
        $inputAdapter->method('inputToDepositOperationEventDTO')->willReturn($depositEventDTO);

        $responseBodyMock = [
            "destination" => [
                'id' => $accountId,
                'balance' => $expectedBalance,
            ]
        ];

        $responseDTOMock = $this->createMock(ResponseDTO::class);
        $responseDTOMock->method('getBody')->willReturn($responseBodyMock);
        $responseDTOMock->method('getHttpStatus')->willReturn(ResponseInterface::HTTP_CREATED);

        $responseAdapter = $this->createMock(UserAccountResponseAdapter::class);
        $responseAdapter->method('fromDepositEventUserAccountEntityToResponseDTO')
            ->willReturn($responseDTOMock);

        $deposit = new Deposit($repository, $inputAdapter, $responseAdapter);
        $response = $deposit->execute($inputData);


        $this->assertEquals(201, $response->getHttpStatus());
        $this->assertEquals($expectedBalance, $existingAccount->getBalance());
    }

    public function testExecuteCreatesAccountWhenAccountNotFound(): void
    {
        $accountId = 12345;
        $depositAmount = 50.0;
        $inputData = ['destinationAccountId' => $accountId, 'amount' => $depositAmount];

        $depositEventDTO = $this->createMock(DepositOperationEventDTO::class);
        $depositEventDTO->method('getDestinationAccountId')->willReturn($accountId);
        $depositEventDTO->method('getAmount')->willReturn($depositAmount);

        $userAccountEntity = $this->createUserAccountEntity($accountId, $depositAmount);

        $repository = $this->createMock(UserAccountRepository::class);
        $repository->method('getUserAccountByAccountId')->willThrowException(new UserAccountNotFoundException());
        $repository->expects($this->once())
            ->method('persistUserAccount')
            ->with($userAccountEntity);

        $adapter = $this->createMock(InputToEventDTOAdapterInterface::class);
        $adapter->method('inputToDepositOperationEventDTO')->willReturn($depositEventDTO);

        $responseDTO = new ResponseDTO();
        $responseDTO->setHttpStatus(ResponseInterface::HTTP_CREATED);
        $responseAdapter = $this->createMock(UserAccountResponseAdapter::class);
        $responseAdapter->method('fromDepositEventUserAccountEntityToResponseDTO')
            ->willReturn($responseDTO);


        $deposit = new Deposit($repository, $adapter, $responseAdapter);

        $response = $deposit->execute($inputData);

        $this->assertEquals(201, $response->getHttpStatus());
    }

    public function testExecuteBadInputThrowsBadRequestException(): void
    {
        $accountId = 12345;
        $depositAmount = -50;
        $inputData = ['destinationAccountId' => $accountId, 'amount' => $depositAmount];

        $repository = $this->createMock(UserAccountRepository::class);
        $adapter = $this->createMock(InputToEventDTOAdapterInterface::class);
        $adapter->method('inputToDepositOperationEventDTO')->willThrowException(new BadRequestException());

        $responseAdapter = $this->createMock(UserAccountResponseAdapter::class);
        $deposit = new Deposit($repository, $adapter, $responseAdapter);

        $response = $deposit->execute($inputData);

        $this->assertEquals(400, $response->getHttpStatus());
    }

    public function testExecuteThrowsCouldNotPersistException(): void
    {
        $accountId = 12345;
        $amount = 100;
        $inputData = ['destinationAccountId' => $accountId, 'amount' => $amount];
        $depositEventDTO = $this->createMock(DepositOperationEventDTO::class);

        $repository = $this->createMock(UserAccountRepository::class);
        $repository->method('getUserAccountByAccountId')
            ->willReturn($this->createUserAccountEntity($accountId, $amount));
        $repository->method('persistUserAccount')->willThrowException(new CouldNotPersistException());

        $adapter = $this->createMock(InputToEventDTOAdapterInterface::class);
        $adapter->method('inputToDepositOperationEventDTO')->willReturn($depositEventDTO);

        $responseAdapter = $this->createMock(UserAccountResponseAdapter::class);
        $deposit = new Deposit($repository, $adapter, $responseAdapter);

        $response = $deposit->execute($inputData);

        $this->assertEquals(500, $response->getHttpStatus());
    }

    private function createUserAccountEntity(int $accountId, float $balance): UserAccountEntity
    {
        $account = new UserAccountEntity();
        $account->setAccountId($accountId);
        $account->setBalance($balance);
        return $account;
    }

    public static function successfulDepositDataProvider(): array
    {
        return [
            "DECIMAL_DEPOSIT" => [10.48],
            "INT_DEPOSIT" => [50]
        ];
    }
}