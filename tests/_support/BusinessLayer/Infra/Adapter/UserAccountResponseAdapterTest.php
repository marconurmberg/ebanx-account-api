<?php

namespace Tests\Support\BusinessLayer\Infra\Adapter;

use App\BusinessLayer\Domain\Entity\UserAccountEntity;
use App\BusinessLayer\Infra\Adapter\UserAccountResponseAdapter;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use PHPUnit\Framework\TestCase;

class UserAccountResponseAdapterTest extends TestCase
{
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

        $adapter = new UserAccountResponseAdapter();
        $responseDTO = new ResponseDTO();
        $result = $adapter->fromUserAccountEntityToResponseDTO($userAccountEntity, $responseDTO);

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
}