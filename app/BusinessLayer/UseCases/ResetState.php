<?php

namespace App\BusinessLayer\UseCases;

use App\BusinessLayer\Domain\Cache\CacheServiceInterface;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use App\BusinessLayer\Infra\Enum\ResponseMessagesEnum;

class ResetState
{
    private CacheServiceInterface $cacheService;

    /**
     * @param CacheServiceInterface $cacheService
     */
    public function __construct(CacheServiceInterface $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function execute(): ResponseDTO {
        $response = new ResponseDTO();
        try {
            $cleanCacheStatus = $this->cacheService->clean();
            $response->setMessage(
                    $cleanCacheStatus ? ResponseMessagesEnum::RESPONSE_MESSAGE_OK
                        : ResponseMessagesEnum::RESPONSE_MESSAGE_ERROR
            );
        } catch (\Exception $exception) {
            $response->setHttpStatus(500);
            $response->setMessage($exception->getMessage());
        }
        return $response;
    }
}