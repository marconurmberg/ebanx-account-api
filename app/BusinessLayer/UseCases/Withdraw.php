<?php

namespace App\BusinessLayer\UseCases;

use App\BusinessLayer\Domain\Adapter\InputToEventDTOAdapterInterface;
use App\BusinessLayer\Domain\Adapter\UserAccountResponseAdapterInterface;
use App\BusinessLayer\Domain\Event\AccountOperationEventInterface;
use App\BusinessLayer\Domain\Repository\UserAccountRepositoryInterface;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use App\BusinessLayer\Infra\DTO\WithdrawOperationEventDTO;
use App\BusinessLayer\Infra\Entity\UserAccountEntity;
use App\BusinessLayer\Infra\Exception\BadRequestException;
use App\BusinessLayer\Infra\Exception\CouldNotPersistException;
use App\BusinessLayer\Infra\Exception\InsufficientFundsException;
use App\BusinessLayer\Infra\Exception\UserAccountNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Withdraw implements AccountOperationEventInterface
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
            $withdrawEventDTO = $this->inputToDepositEventDTO->inputToWithdrawOperationEventDTO($inputData);
            $responseDTO = $this->processWithdraw($withdrawEventDTO, $responseDTO);
        } catch (BadRequestException|InsufficientFundsException $exception) {
            $this->handleBusinessException($responseDTO, $exception);
        } catch (UserAccountNotFoundException $exception) {
            $this->handleUserNotFoundException($responseDTO);
        } catch (CouldNotPersistException|\Exception $exception) {
            $this->handleSystemException($responseDTO, $exception);
        }
        
        return $responseDTO;
    }

    /**
     * @throws InsufficientFundsException
     * @throws UserAccountNotFoundException
     * @throws CouldNotPersistException
     */
    private function processWithdraw(WithdrawOperationEventDTO $withdrawEventDTO, ResponseDTO $responseDTO): ResponseDTO
    {
        $userAccount = $this->userAccountRepository->getUserAccountByAccountId(
            $withdrawEventDTO->getOriginAccountId()
        );
        
        $this->validateWithdraw($userAccount, $withdrawEventDTO->getAmount());
        $this->executeWithdraw($userAccount, $withdrawEventDTO->getAmount());
        
        $this->userAccountRepository->persistUserAccount($userAccount);
        
        return $this->userAccountResponseAdapter->fromWithdrawEventUserAccountEntityToResponseDTO(
            $userAccount,
            $responseDTO
        );
    }

    /**
     * @throws InsufficientFundsException
     */
    private function validateWithdraw(UserAccountEntity $userAccount, float $amount): void
    {
        if ($userAccount->getBalance() < $amount) {
            throw new InsufficientFundsException();
        }
    }

    private function executeWithdraw(UserAccountEntity $userAccount, float $amount): void
    {
        $userAccount->setBalance($userAccount->getBalance() - $amount);
    }

    private function handleBusinessException(ResponseDTO $responseDTO, \Exception $exception): void
    {
        $responseDTO->setHttpStatus(ResponseInterface::HTTP_BAD_REQUEST);
        $responseDTO->setBody($exception->getMessage());
    }

    private function handleUserNotFoundException(ResponseDTO $responseDTO): void
    {
        $responseDTO->setHttpStatus(ResponseInterface::HTTP_NOT_FOUND);
        $responseDTO->setBody(0);
    }

    private function handleSystemException(ResponseDTO $responseDTO, \Exception $exception): void
    {
        $responseDTO->setHttpStatus(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        $responseDTO->setBody($exception->getMessage());
    }
}