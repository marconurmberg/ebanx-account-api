<?php

namespace App\BusinessLayer\UseCases;

use App\BusinessLayer\Domain\Adapter\InputToEventDTOAdapterInterface;
use App\BusinessLayer\Domain\Adapter\UserAccountResponseAdapterInterface;
use App\BusinessLayer\Domain\Entity\UserAccountEntity;
use App\BusinessLayer\Domain\Event\AccountOperationEventInterface;
use App\BusinessLayer\Domain\Repository\UserAccountRepositoryInterface;
use App\BusinessLayer\Infra\DTO\DepositOperationEventDTO;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use App\BusinessLayer\Infra\Exception\BadRequestException;
use App\BusinessLayer\Infra\Exception\CouldNotPersistException;
use App\BusinessLayer\Infra\Exception\UserAccountNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Deposit implements AccountOperationEventInterface
{
    private UserAccountRepositoryInterface $userAccountRepository;
    private InputToEventDTOAdapterInterface $inputToDepositEventDTO;

    private UserAccountResponseAdapterInterface $userAccountResponseAdapter;

    public function __construct(
        UserAccountRepositoryInterface $userAccountRepository,
        InputToEventDTOAdapterInterface $inputToDepositEventDTO,
        UserAccountResponseAdapterInterface $userAccountResponseAdapter
    ){
        $this->userAccountRepository = $userAccountRepository;
        $this->inputToDepositEventDTO = $inputToDepositEventDTO;
        $this->userAccountResponseAdapter = $userAccountResponseAdapter;
    }


    public function execute(array $inputData): ResponseDTO
    {
        $responseDTO = new ResponseDTO();
        try {
            $depositEventDTO = $this->inputToDepositEventDTO->inputToDepositOperationEventDTO($inputData);
            $responseDTO = $this->processDeposit($depositEventDTO, $responseDTO);
        } catch (BadRequestException $exception) {
            $this->handleBusinessException($responseDTO, $exception);
        } catch (CouldNotPersistException|\Exception $exception) {
            $this->handleSystemException($responseDTO, $exception);
        }
        
        return $responseDTO;
    }

    /**
     * @throws CouldNotPersistException
     */
    private function processDeposit(DepositOperationEventDTO $depositEventDTO, ResponseDTO $responseDTO): ResponseDTO
    {
        try {
            $userAccount = $this->userAccountRepository->getUserAccountByAccountId(
                $depositEventDTO->getDestinationAccountId()
            );
        } catch (UserAccountNotFoundException) {
            $userAccount = $this->createNewUserAccount($depositEventDTO->getDestinationAccountId());
        }
        
        $this->executeDeposit($userAccount, $depositEventDTO->getAmount());
        
        $this->userAccountRepository->persistUserAccount($userAccount);
        $responseDTO->setHttpStatus(ResponseInterface::HTTP_CREATED);;
        
        return $this->userAccountResponseAdapter->fromDepositEventUserAccountEntityToResponseDTO(
            $userAccount,
            $responseDTO
        );
    }

    private function createNewUserAccount(int $accountId): UserAccountEntity
    {
        $userAccount = new UserAccountEntity();
        $userAccount->setAccountId($accountId);
        $userAccount->setBalance(0);
        
        return $userAccount;
    }

    private function executeDeposit(UserAccountEntity $userAccount, float $amount): void
    {
        $userAccount->setBalance($userAccount->getBalance() + $amount);
    }

    private function handleBusinessException(ResponseDTO $responseDTO, \Exception $exception): void
    {
        $responseDTO->setHttpStatus(ResponseInterface::HTTP_BAD_REQUEST);
        $responseDTO->setBody($exception->getMessage());
    }

    private function handleSystemException(ResponseDTO $responseDTO, \Exception $exception): void
    {
        $responseDTO->setHttpStatus(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        $responseDTO->setBody($exception->getMessage());
    }
}