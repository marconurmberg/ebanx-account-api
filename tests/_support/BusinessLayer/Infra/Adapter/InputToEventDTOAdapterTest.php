<?php

namespace Tests\Support\BusinessLayer\Infra\Adapter;

use App\BusinessLayer\Infra\Adapter\InputToEventDTOAdapter;
use App\BusinessLayer\Infra\Exception\BadRequestException;
use PHPUnit\Framework\TestCase;

class InputToEventDTOAdapterTest extends TestCase
{
    private InputToEventDTOAdapter $adapter;

    protected function setUp(): void
    {
        $this->adapter = new InputToEventDTOAdapter();
    }

    /** @dataProvider validDepositInputDataProvider */
    public function testInputToDepositOperationEventDTOSuccess(array $input, float $expectedAmount): void
    {
        $result = $this->adapter->inputToDepositOperationEventDTO($input);

        $this->assertEquals($expectedAmount, $result->getAmount());
        $this->assertEquals($input['destination'], $result->getDestinationAccountId());
        $this->assertEquals($input['type'], $result->getEventType());
    }

    /** @dataProvider validWithdrawInputDataProvider */
    public function testInputToWithdrawOperationEventDTOSuccess(array $input, float $expectedAmount): void
    {
        $result = $this->adapter->inputToWithdrawOperationEventDTO($input);

        $this->assertEquals($expectedAmount, $result->getAmount());
        $this->assertEquals($input['origin'], $result->getOriginAccountId());
        $this->assertEquals($input['type'], $result->getEventType());
    }

    /** @dataProvider validTransferInputDataProvider */
    public function testInputToTransferOperationEventDTOSuccess(array $input, float $expectedAmount): void
    {
        $result = $this->adapter->inputToTransferOperationEventDTO($input);

        $this->assertEquals($expectedAmount, $result->getAmount());
        $this->assertEquals($input['origin'], $result->getOriginAccountId());
        $this->assertEquals($input['destination'], $result->getDestinationAccountId());
        $this->assertEquals($input['type'], $result->getEventType());
    }

    /** @dataProvider invalidDepositInputDataProvider */
    public function testInputToDepositOperationEventDTOThrowsException(array $invalidInput): void
    {
        $this->expectException(BadRequestException::class);
        $this->adapter->inputToDepositOperationEventDTO($invalidInput);
    }

    /** @dataProvider invalidWithdrawInputDataProvider */
    public function testInputToWithdrawOperationEventDTOThrowsException(array $invalidInput): void
    {
        $this->expectException(BadRequestException::class);
        $this->adapter->inputToWithdrawOperationEventDTO($invalidInput);
    }

    /** @dataProvider invalidTransferInputDataProvider */
    public function testInputToTransferOperationEventDTO_ThrowsException(array $invalidInput): void
    {
        $this->expectException(BadRequestException::class);
        $this->adapter->inputToTransferOperationEventDTO($invalidInput);
    }

    public static function validDepositInputDataProvider(): array
    {
        return [
            "DECIMAL_AMOUNT" => [
                [
                    "type" => "deposit",
                    "destination" => "100",
                    "amount" => 10.50
                ],
                10.50
            ],
            "INTEGER_AMOUNT" => [
                [
                    "type" => "deposit",
                    "destination" => "100",
                    "amount" => 50
                ],
                50.0
            ]
        ];
    }

    public static function validWithdrawInputDataProvider(): array
    {
        return [
            "DECIMAL_AMOUNT" => [
                [
                    "type" => "withdraw",
                    "origin" => "100",
                    "amount" => 10.50
                ],
                10.50
            ],
            "INTEGER_AMOUNT" => [
                [
                    "type" => "withdraw",
                    "origin" => "100",
                    "amount" => 50
                ],
                50.0
            ]
        ];
    }

    public static function validTransferInputDataProvider(): array
    {
        return [
            "DECIMAL_AMOUNT" => [
                [
                    "type" => "transfer",
                    "origin" => "100",
                    "destination" => "200",
                    "amount" => 10.50
                ],
                10.50
            ],
            "INTEGER_AMOUNT" => [
                [
                    "type" => "transfer",
                    "origin" => "100",
                    "destination" => "200",
                    "amount" => 50
                ],
                50.0
            ]
        ];
    }

    public static function invalidDepositInputDataProvider(): array
    {
        return [
            "NEGATIVE_AMOUNT" => [[
                "type" => "deposit",
                "destination" => "100",
                "amount" => -50
            ]],
            "MISSING_DESTINATION" => [[
                "type" => "deposit",
                "amount" => 50
            ]],
            "MISSING_AMOUNT" => [[
                "type" => "deposit",
                "destination" => "100"
            ]],
            "ZERO_AMOUNT" => [[
                "type" => "deposit",
                "destination" => "100",
                "amount" => 0
            ]]
        ];
    }

    public static function invalidWithdrawInputDataProvider(): array
    {
        return [
            "NEGATIVE_AMOUNT" => [[
                "type" => "withdraw",
                "origin" => "100",
                "amount" => -50
            ]],
            "MISSING_ORIGIN" => [[
                "type" => "withdraw",
                "amount" => 50
            ]],
            "MISSING_AMOUNT" => [[
                "type" => "withdraw",
                "origin" => "100"
            ]],
            "ZERO_AMOUNT" => [[
                "type" => "withdraw",
                "origin" => "100",
                "amount" => 0
            ]]
        ];
    }

    public static function invalidTransferInputDataProvider(): array
    {
        return [
            "NEGATIVE_AMOUNT" => [[
                "type" => "transfer",
                "origin" => "100",
                "destination" => "200",
                "amount" => -50
            ]],
            "MISSING_ORIGIN" => [[
                "type" => "transfer",
                "destination" => "200",
                "amount" => 50
            ]],
            "MISSING_DESTINATION" => [[
                "type" => "transfer",
                "origin" => "100",
                "amount" => 50
            ]],
            "MISSING_AMOUNT" => [[
                "type" => "transfer",
                "origin" => "100",
                "destination" => "200"
            ]],
            "ZERO_AMOUNT" => [[
                "type" => "transfer",
                "origin" => "100",
                "destination" => "200",
                "amount" => 0
            ]]
        ];
    }
}