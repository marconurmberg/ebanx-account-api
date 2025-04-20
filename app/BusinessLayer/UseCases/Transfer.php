<?php

namespace App\BusinessLayer\UseCases;

use App\BusinessLayer\Domain\Adapter\InputToEventDTOAdapterInterface;
use App\BusinessLayer\Domain\Adapter\UserAccountResponseAdapterInterface;
use App\BusinessLayer\Domain\Entity\UserAccountEntity;
use App\BusinessLayer\Domain\Event\AccountOperationEventInterface;
use App\BusinessLayer\Domain\Repository\UserAccountRepositoryInterface;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use App\BusinessLayer\Infra\DTO\TransferOperationEventDTO;
use App\BusinessLayer\Infra\Exception\BadRequestException;
use App\BusinessLayer\Infra\Exception\CouldNotPersistException;
use App\BusinessLayer\Infra\Exception\InsufficientFundsException;
use App\BusinessLayer\Infra\Exception\UserAccountNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Transfer implements AccountOperationEventInterface
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
            $transferEventDTO = $this->inputToDepositEventDTO->inputToTransferOperationEventDTO($inputData);
            $responseDTO = $this->processTransfer($transferEventDTO, $responseDTO);
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
     * @throws CouldNotPersistException
     * @throws UserAccountNotFoundException
     * @throws InsufficientFundsException
     * @throws BadRequestException
     */
    private function processTransfer(TransferOperationEventDTO $transferEventDTO, ResponseDTO $responseDTO): ResponseDTO
    {
        $originAccount = $this->userAccountRepository->getUserAccountByAccountId(
            $transferEventDTO->getOriginAccountId()
        );
        
        $destinationAccount = $this->userAccountRepository->getUserAccountByAccountId(
            $transferEventDTO->getDestinationAccountId()
        );
        
        $this->validateTransfer($originAccount, $destinationAccount, $transferEventDTO->getAmount());
        $this->executeTransfer($originAccount, $destinationAccount, $transferEventDTO->getAmount());
        
        $this->userAccountRepository->persistUserAccount($originAccount);
        $this->userAccountRepository->persistUserAccount($destinationAccount);
        
        return $this->userAccountResponseAdapter->fromTransferEventAccountEntitiesToResponseDTO(
            $originAccount,
            $destinationAccount,
            $responseDTO
        );
    }

    /**
     * @throws BadRequestException
     * @throws InsufficientFundsException
     */
    private function validateTransfer(
        UserAccountEntity $originAccount,
        UserAccountEntity $destinationAccount,
        float $amount
    ): void {
        if ($originAccount->getAccountId() === $destinationAccount->getAccountId()) {
            throw new BadRequestException("Can't Transfer to the same account.");
        }
        
        if ($originAccount->getBalance() < $amount) {
            throw new InsufficientFundsException();
        }
    }

    private function executeTransfer(
        UserAccountEntity $originAccount,
        UserAccountEntity $destinationAccount,
        float $amount
    ): void {
        $originAccount->setBalance($originAccount->getBalance() - $amount);
        $destinationAccount->setBalance($destinationAccount->getBalance() + $amount);
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