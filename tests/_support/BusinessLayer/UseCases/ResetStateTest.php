<?php

namespace Tests\Support\BusinessLayer\UseCases;

use App\BusinessLayer\Infra\Cache\CacheService;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use App\BusinessLayer\Infra\Enum\ResponseMessagesEnum;
use App\BusinessLayer\UseCases\ResetState;
use PHPUnit\Framework\TestCase;

class ResetStateTest extends TestCase
{
    /** @dataProvider resetStateDataProvider */
    public function testResetState($shouldCleanCache, $expectedHttpStatus, $expectedMessage) {
        $cacheServiceMock = $this->createMock(CacheService::class);
        $cacheServiceMock->expects($this->once())
            ->method('clean')
            ->willReturn($shouldCleanCache);

        $useCase = new ResetState($cacheServiceMock);
        $resetStateResponse = $useCase->execute();
        $this->assertEquals($expectedHttpStatus, $resetStateResponse->getHttpStatus());
        $this->assertEquals($expectedMessage, $resetStateResponse->getMessage());
    }

    public function testRestStateException()
    {
        $cacheServiceMock = $this->createMock(CacheService::class);
        $cacheServiceMock->expects($this->once())
            ->method('clean')
            ->willThrowException(new \Exception("Test Exception"));

        $useCase = new ResetState($cacheServiceMock);
        $resetStateResponse = $useCase->execute();

        $this->assertEquals(500, $resetStateResponse->getHttpStatus());
        $this->assertEquals("Test Exception", $resetStateResponse->getMessage());
    }

    public static function resetStateDataProvider() : array
    {
        return [
            "SUCCESSFUL_RESET" => [
                true,
                200,
                ResponseMessagesEnum::RESPONSE_MESSAGE_OK
            ],
            "UNSUCCESSFUL_RESET" => [
                false,
                200,
                ResponseMessagesEnum::RESPONSE_MESSAGE_ERROR
            ]
        ];
    }
}
