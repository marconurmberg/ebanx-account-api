<?php

namespace Tests\Support\BusinessLayer\Infra\Adapter;

use App\BusinessLayer\Domain\Entity\UserAccountEntity;
use App\BusinessLayer\Infra\Adapter\UserAccountResponseAdapter;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use PHPUnit\Framework\TestCase;

class UserAccountResponseAdapterTest extends TestCase
{
    private UserAccountResponseAdapter $adapter;
    private ResponseDTO $responseDTO;

    protected function setUp(): void
    {
        $this->adapter = new UserAccountResponseAdapter();
        $this->responseDTO = new ResponseDTO();
    }

    /** @dataProvider balanceResponseDataProvider */
    public function testFromUserAccountEntityToResponseDTO($initialBalance, $expectedBalance): void
    {
        $accountId = 1;
        $userAccountEntity = $this->createUserAccountEntity($accountId, $initialBalance);

        $expectedBody = [
            "id" => (string)$accountId,
            "balance" => $expectedBalance
        ];
        $expectedStatus = 201;

        $result = $this->adapter->fromUserAccountEntityToResponseDTO($userAccountEntity, $this->responseDTO);

        $this->assertEquals($expectedBody, $result->getBody());
        $this->assertEquals($expectedStatus, $result->getHttpStatus());
    }

    /** @dataProvider depositEventDataProvider */
    public function testFromDepositEventUserAccountEntityToResponseDTO($initialBalance, $expectedBalance): void
    {
        $accountId = 1;
        $userAccountEntity = $this->createUserAccountEntity($accountId, $initialBalance);

        $expectedBody = [
            "destination" => [
                "id" => (string)$accountId,
                "balance" => $expectedBalance
            ]
        ];
        $expectedStatus = 201;

        $result = $this->adapter->fromDepositEventUserAccountEntityToResponseDTO(
            $userAccountEntity,
            $this->responseDTO
        );

        $this->assertEquals($expectedBody, $result->getBody());
        $this->assertEquals($expectedStatus, $result->getHttpStatus());
    }

    /** @dataProvider withdrawEventDataProvider */
    public function testFromWithdrawEventUserAccountEntityToResponseDTO($initialBalance, $expectedBalance): void
    {
        $accountId = 1;
        $userAccountEntity = $this->createUserAccountEntity($accountId, $initialBalance);

        $expectedBody = [
            "origin" => [
                "id" => (string)$accountId,
                "balance" => $expectedBalance
            ]
        ];
        $expectedStatus = 201;

        $result = $this->adapter->fromWithdrawEventUserAccountEntityToResponseDTO(
            $userAccountEntity,
            $this->responseDTO
        );

        $this->assertEquals($expectedBody, $result->getBody());
        $this->assertEquals($expectedStatus, $result->getHttpStatus());
    }

    /** @dataProvider transferEventDataProvider */
    public function testFromTransferEventAccountEntitiesToResponseDTO(
        $originBalance,
        $destinationBalance,
        $expectedOriginBalance,
        $expectedDestinationBalance
    ): void {
        $originAccountId = 1;
        $destinationAccountId = 2;
        
        $originAccount = $this->createUserAccountEntity($originAccountId, $originBalance);
        $destinationAccount = $this->createUserAccountEntity($destinationAccountId, $destinationBalance);

        $expectedBody = [
            "origin" => [
                "id" => (string)$originAccountId,
                "balance" => $expectedOriginBalance
            ],
            "destination" => [
                "id" => (string)$destinationAccountId,
                "balance" => $expectedDestinationBalance
            ]
        ];
        $expectedStatus = 201;

        $result = $this->adapter->fromTransferEventAccountEntitiesToResponseDTO(
            $originAccount,
            $destinationAccount,
            $this->responseDTO
        );

        $this->assertEquals($expectedBody, $result->getBody());
        $this->assertEquals($expectedStatus, $result->getHttpStatus());
    }

    private function createUserAccountEntity(int $accountId, float $balance): UserAccountEntity
    {
        $account = new UserAccountEntity();
        $account->setAccountId($accountId);
        $account->setBalance($balance);
        return $account;
    }

    public static function balanceResponseDataProvider(): array
    {
        return [
            "INTEGER_BALANCE" => [100, 100.00],
            "TWO_DECIMAL_PLACES" => [150.75, 150.75],
            "MULTIPLE_DECIMAL_PLACES" => [200.12345, 200.12],
            "ZERO_BALANCE" => [0, 0.00],
            "ROUND_UP" => [100.995, 101.00],
            "ROUND_DOWN" => [100.994, 100.99],
        ];
    }

    public static function depositEventDataProvider(): array
    {
        return [
            "DEPOSIT_INTEGER" => [100, 100.00],
            "DEPOSIT_DECIMAL" => [150.75, 150.75],
            "DEPOSIT_MULTIPLE_DECIMALS" => [200.12345, 200.12],
            "DEPOSIT_ROUND_UP" => [100.995, 101.00],
            "DEPOSIT_ROUND_DOWN" => [100.994, 100.99]
        ];
    }

    public static function withdrawEventDataProvider(): array
    {
        return [
            "WITHDRAW_INTEGER" => [100, 100.00],
            "WITHDRAW_DECIMAL" => [150.75, 150.75],
            "WITHDRAW_MULTIPLE_DECIMALS" => [200.12345, 200.12],
            "WITHDRAW_ROUND_UP" => [100.995, 101.00],
            "WITHDRAW_ROUND_DOWN" => [100.994, 100.99]
        ];
    }

    public static function transferEventDataProvider(): array
    {
        return [
            "TRANSFER_INTEGER" => [
                100, 50, 100.00, 50.00
            ],
            "TRANSFER_DECIMAL" => [
                150.75, 200.25, 150.75, 200.25
            ],
            "TRANSFER_MULTIPLE_DECIMALS" => [
                200.12345, 300.98765, 200.12, 300.99
            ],
            "TRANSFER_ROUND_BOTH" => [
                100.995, 200.994, 101.00, 200.99
            ]
        ];
    }
}