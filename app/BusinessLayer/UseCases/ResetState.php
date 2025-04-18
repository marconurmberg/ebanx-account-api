<?php

namespace App\BusinessLayer\UseCases;

use App\BusinessLayer\Domain\Cache\CacheServiceInterface;
use App\BusinessLayer\Domain\Enum\ResponseMessagesEnum;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use CodeIgniter\HTTP\ResponseInterface;

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
            $response->setBody(
                    $cleanCacheStatus ? ResponseMessagesEnum::RESPONSE_MESSAGE_OK
                        : ResponseMessagesEnum::RESPONSE_MESSAGE_ERROR
            );
        } catch (\Exception $exception) {
            $response->setHttpStatus(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            $response->setBody($exception->getMessage());
        }
        return $response;
    }
}