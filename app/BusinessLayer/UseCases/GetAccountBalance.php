<?php

namespace App\BusinessLayer\UseCases;

use App\BusinessLayer\Domain\Adapter\UserAccountResponseAdapterInterface;
use App\BusinessLayer\Domain\Cache\CacheServiceInterface;
use App\BusinessLayer\Domain\Repository\UserAccountRepositoryInterface;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use App\BusinessLayer\Infra\Exception\UserAccountNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class GetAccountBalance
{
    private UserAccountRepositoryInterface $userAccountRepository;

    public function __construct(UserAccountRepositoryInterface $userAccountRepository)
    {
        $this->userAccountRepository = $userAccountRepository;
    }

    public function execute(int $accountId): ResponseDTO
    {
        $responseDTO = new ResponseDTO();
        try {
            $userAccount = $this->userAccountRepository->getUserAccountByAccountId($accountId);
            $responseDTO->setBody($userAccount->getBalance());
        } catch (UserAccountNotFoundException $exception) {
            $responseDTO->setHttpStatus(ResponseInterface::HTTP_NOT_FOUND);
            $responseDTO->setBody(0);
        } catch (\Exception $exception) {
            $responseDTO->setHttpStatus(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            $responseDTO->setBody($exception->getMessage());
        }
        return $responseDTO;
    }
}