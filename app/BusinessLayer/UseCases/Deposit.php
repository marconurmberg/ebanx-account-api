<?php

namespace App\BusinessLayer\UseCases;

use App\BusinessLayer\Domain\Adapter\InputToEventDTOAdapterInterface;
use App\BusinessLayer\Domain\Adapter\UserAccountResponseAdapterInterface;
use App\BusinessLayer\Domain\Event\AccountOperationEventInterface;
use App\BusinessLayer\Domain\Repository\UserAccountRepositoryInterface;
use App\BusinessLayer\Infra\DTO\DepositOperationEventDTO;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use App\BusinessLayer\Infra\Entity\UserAccountEntity;
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
            $userAccount = $this->getUserAccountAndUpdateBalanceFromDepositEventDTO($depositEventDTO);
            $responseDTO = $this->setSuccessfulDepositToResponseDTO($userAccount, $responseDTO);
        } catch (BadRequestException $exception) {
            $responseDTO->setHttpStatus(ResponseInterface::HTTP_BAD_REQUEST);
            $responseDTO->setBody($exception->getMessage());
        } catch (UserAccountNotFoundException $exception) {
            $userAccount = $this->createUserAccountFromDepositEventDTO($depositEventDTO);
            $responseDTO = $this->setSuccessfulDepositToResponseDTO($userAccount, $responseDTO);
        } catch (CouldNotPersistException|\Exception $exception) {
            $responseDTO->setHttpStatus(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            $responseDTO->setBody($exception->getMessage());
        }

        return $responseDTO;
    }

    /**
     * @throws UserAccountNotFoundException
     * @throws CouldNotPersistException
     */
    private function getUserAccountAndUpdateBalanceFromDepositEventDTO(
        DepositOperationEventDTO $depositEventDTO
    ): UserAccountEntity {
        $userAccount = $this->userAccountRepository->getUserAccountByAccountId(
            $depositEventDTO->getDestinationAccountId()
        );

        $userAccount->setBalance($depositEventDTO->getAmount() + $userAccount->getBalance());
        $this->userAccountRepository->persistUserAccount($userAccount);

        return $userAccount;
    }

    /**
     * @throws CouldNotPersistException
     */
    private function createUserAccountFromDepositEventDTO(DepositOperationEventDTO $depositEventDTO): UserAccountEntity
    {
        $userAccount = new UserAccountEntity();
        $userAccount->setAccountId($depositEventDTO->getDestinationAccountId());
        $userAccount->setBalance($depositEventDTO->getAmount());
        $this->userAccountRepository->persistUserAccount($userAccount);
        return $userAccount;
    }

    private function setSuccessfulDepositToResponseDTO(
        UserAccountEntity $userAccount,
        ResponseDTO $responseDTO): ResponseDTO {

        $responseDTO = $this->userAccountResponseAdapter->fromDepositEventUserAccountEntityToResponseDTO(
            $userAccount,
            $responseDTO
        );
        $responseDTO->setHttpStatus(ResponseInterface::HTTP_CREATED);

        return $responseDTO;
    }
}