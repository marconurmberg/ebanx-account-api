<?php

namespace Tests\Support\BusinessLayer\UseCases;

use App\BusinessLayer\Domain\Adapter\InputToEventDTOAdapterInterface;
use App\BusinessLayer\Domain\Adapter\UserAccountResponseAdapterInterface;
use App\BusinessLayer\Domain\Entity\UserAccountEntity;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use App\BusinessLayer\Infra\DTO\TransferOperationEventDTO;
use App\BusinessLayer\Infra\Exception\CouldNotPersistException;
use App\BusinessLayer\Infra\Exception\UserAccountNotFoundException;
use App\BusinessLayer\Infra\Repository\UserAccountRepository;
use App\BusinessLayer\UseCases\Transfer;
use CodeIgniter\HTTP\ResponseInterface;
use PHPUnit\Framework\TestCase;

class TransferTest extends TestCase
{
    /** @dataProvider successfulTransferDataProvider */
    public function testExecuteSuccessfulTransfer(
        float $originInitialBalance,
        float $destinationInitialBalance,
        float $transferAmount,
        float $expectedOriginBalance,
        float $expectedDestinationBalance
    ): void {
        $originAccountId = 12345;
        $destinationAccountId = 67890;
        
        $inputData = [
            'originAccountId' => $originAccountId,
            'destinationAccountId' => $destinationAccountId,
            'amount' => $transferAmount
        ];

        $transferEventDTO = $this->createMock(TransferOperationEventDTO::class);
        $transferEventDTO->method('getAmount')->willReturn($transferAmount);
        $transferEventDTO->method('getOriginAccountId')->willReturn($originAccountId);
        $transferEventDTO->method('getDestinationAccountId')->willReturn($destinationAccountId);

        $originAccount = $this->createUserAccountEntity($originAccountId, $originInitialBalance);
        $destinationAccount = $this->createUserAccountEntity($destinationAccountId, $destinationInitialBalance);

        $repository = $this->createMock(UserAccountRepository::class);
        $repository->method('getUserAccountByAccountId')
            ->willReturnMap([
                [$originAccountId, $originAccount],
                [$destinationAccountId, $destinationAccount]
            ]);

        $expectedOriginAccount = $this->createUserAccountEntity($originAccountId, $expectedOriginBalance);
        $expectedDestinationAccount = $this->createUserAccountEntity($destinationAccountId, $expectedDestinationBalance);

        $repository->expects($this->exactly(2))
            ->method('persistUserAccount')
            ->willReturn(true);

        $inputAdapter = $this->createMock(InputToEventDTOAdapterInterface::class);
        $inputAdapter->method('inputToTransferOperationEventDTO')->willReturn($transferEventDTO);

        $responseAdapter = $this->createMock(UserAccountResponseAdapterInterface::class);
        $responseAdapter->method('fromTransferEventAccountEntitiesToResponseDTO')
            ->willReturn(new ResponseDTO(ResponseInterface::HTTP_CREATED));

        $transfer = new Transfer($repository, $inputAdapter, $responseAdapter);
        $response = $transfer->execute($inputData);

        $this->assertEquals(ResponseInterface::HTTP_CREATED, $response->getHttpStatus());
        $this->assertEquals($expectedOriginBalance, $originAccount->getBalance());
        $this->assertEquals($expectedDestinationBalance, $destinationAccount->getBalance());
    }

    public function testExecuteWithInsufficientFunds(): void
    {
        $originAccountId = 12345;
        $destinationAccountId = 67890;
        $initialBalance = 50.0;
        $transferAmount = 100.0;
        $inputData = [
            'originAccountId' => $originAccountId,
            'destinationAccountId' => $destinationAccountId,
            'amount' => $transferAmount
        ];

        $transferEventDTO = $this->createMock(TransferOperationEventDTO::class);
        $transferEventDTO->method('getAmount')->willReturn($transferAmount);
        $transferEventDTO->method('getOriginAccountId')->willReturn($originAccountId);
        $transferEventDTO->method('getDestinationAccountId')->willReturn($destinationAccountId);

        $originAccount = $this->createUserAccountEntity($originAccountId, $initialBalance);
        $destinationAccount = $this->createUserAccountEntity($destinationAccountId, 0);

        $repository = $this->createMock(UserAccountRepository::class);
        $repository->method('getUserAccountByAccountId')
            ->willReturnMap([
                [$originAccountId, $originAccount],
                [$destinationAccountId, $destinationAccount]
            ]);
        $repository->expects($this->never())->method('persistUserAccount');

        $inputAdapter = $this->createMock(InputToEventDTOAdapterInterface::class);
        $inputAdapter->method('inputToTransferOperationEventDTO')->willReturn($transferEventDTO);

        $responseAdapter = $this->createMock(UserAccountResponseAdapterInterface::class);

        $transfer = new Transfer($repository, $inputAdapter, $responseAdapter);
        $response = $transfer->execute($inputData);

        $this->assertEquals(ResponseInterface::HTTP_BAD_REQUEST, $response->getHttpStatus());
        $this->assertEquals($initialBalance, $originAccount->getBalance());
    }

    public function testExecuteWithSameAccount(): void
    {
        $accountId = 12345;
        $transferAmount = 50.0;
        $inputData = [
            'originAccountId' => $accountId,
            'destinationAccountId' => $accountId,
            'amount' => $transferAmount
        ];

        $transferEventDTO = $this->createMock(TransferOperationEventDTO::class);
        $transferEventDTO->method('getAmount')->willReturn($transferAmount);
        $transferEventDTO->method('getOriginAccountId')->willReturn($accountId);
        $transferEventDTO->method('getDestinationAccountId')->willReturn($accountId);

        $account = $this->createUserAccountEntity($accountId, 100.0);

        $repository = $this->createMock(UserAccountRepository::class);
        $repository->method('getUserAccountByAccountId')->willReturn($account);
        $repository->expects($this->never())->method('persistUserAccount');

        $inputAdapter = $this->createMock(InputToEventDTOAdapterInterface::class);
        $inputAdapter->method('inputToTransferOperationEventDTO')->willReturn($transferEventDTO);

        $responseAdapter = $this->createMock(UserAccountResponseAdapterInterface::class);

        $transfer = new Transfer($repository, $inputAdapter, $responseAdapter);
        $response = $transfer->execute($inputData);

        $this->assertEquals(ResponseInterface::HTTP_BAD_REQUEST, $response->getHttpStatus());
    }

    public function testExecuteAccountNotFound(): void
    {
        $originAccountId = 12345;
        $destinationAccountId = 67890;
        $transferAmount = 50.0;
        $inputData = [
            'originAccountId' => $originAccountId,
            'destinationAccountId' => $destinationAccountId,
            'amount' => $transferAmount
        ];

        $transferEventDTO = $this->createMock(TransferOperationEventDTO::class);
        $transferEventDTO->method('getAmount')->willReturn($transferAmount);
        $transferEventDTO->method('getOriginAccountId')->willReturn($originAccountId);

        $repository = $this->createMock(UserAccountRepository::class);
        $repository->method('getUserAccountByAccountId')
            ->willThrowException(new UserAccountNotFoundException());

        $inputAdapter = $this->createMock(InputToEventDTOAdapterInterface::class);
        $inputAdapter->method('inputToTransferOperationEventDTO')->willReturn($transferEventDTO);

        $responseAdapter = $this->createMock(UserAccountResponseAdapterInterface::class);

        $transfer = new Transfer($repository, $inputAdapter, $responseAdapter);
        $response = $transfer->execute($inputData);

        $this->assertEquals(ResponseInterface::HTTP_NOT_FOUND, $response->getHttpStatus());
        $this->assertEquals(0, $response->getBody());
    }

    public function testExecuteWithPersistenceError(): void
    {
        $originAccountId = 12345;
        $destinationAccountId = 67890;
        $transferAmount = 50.0;
        $inputData = [
            'originAccountId' => $originAccountId,
            'destinationAccountId' => $destinationAccountId,
            'amount' => $transferAmount
        ];

        $transferEventDTO = $this->createMock(TransferOperationEventDTO::class);
        $transferEventDTO->method('getAmount')->willReturn($transferAmount);
        $transferEventDTO->method('getOriginAccountId')->willReturn($originAccountId);
        $transferEventDTO->method('getDestinationAccountId')->willReturn($destinationAccountId);

        $originAccount = $this->createUserAccountEntity($originAccountId, 100.0);
        $destinationAccount = $this->createUserAccountEntity($destinationAccountId, 0);

        $repository = $this->createMock(UserAccountRepository::class);
        $repository->method('getUserAccountByAccountId')
            ->willReturnMap([
                [$originAccountId, $originAccount],
                [$destinationAccountId, $destinationAccount]
            ]);
        $repository->method('persistUserAccount')
            ->willThrowException(new CouldNotPersistException());

        $inputAdapter = $this->createMock(InputToEventDTOAdapterInterface::class);
        $inputAdapter->method('inputToTransferOperationEventDTO')->willReturn($transferEventDTO);

        $responseAdapter = $this->createMock(UserAccountResponseAdapterInterface::class);

        $transfer = new Transfer($repository, $inputAdapter, $responseAdapter);
        $response = $transfer->execute($inputData);

        $this->assertEquals(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR, $response->getHttpStatus());
    }

    private function createUserAccountEntity(int $accountId, float $balance): UserAccountEntity
    {
        $account = new UserAccountEntity();
        $account->setAccountId($accountId);
        $account->setBalance($balance);
        return $account;
    }

    public static function successfulTransferDataProvider(): array
    {
        return [
            "TRANSFER_TO_EMPTY_ACCOUNT" => [
                'originInitialBalance' => 100.0,
                'destinationInitialBalance' => 0.0,
                'transferAmount' => 50.0,
                'expectedOriginBalance' => 50.0,
                'expectedDestinationBalance' => 50.0
            ],
            "TRANSFER_TO_EXISTING_BALANCE" => [
                'originInitialBalance' => 100.0,
                'destinationInitialBalance' => 25.0,
                'transferAmount' => 50.0,
                'expectedOriginBalance' => 50.0,
                'expectedDestinationBalance' => 75.0
            ],
            "FULL_BALANCE_TRANSFER" => [
                'originInitialBalance' => 100.0,
                'destinationInitialBalance' => 75.0,
                'transferAmount' => 100.0,
                'expectedOriginBalance' => 0.0,
                'expectedDestinationBalance' => 175.0
            ],
            "DECIMAL_TRANSFER" => [
                'originInitialBalance' => 100.50,
                'destinationInitialBalance' => 25.75,
                'transferAmount' => 50.25,
                'expectedOriginBalance' => 50.25,
                'expectedDestinationBalance' => 76.00
            ]
        ];
    }
}