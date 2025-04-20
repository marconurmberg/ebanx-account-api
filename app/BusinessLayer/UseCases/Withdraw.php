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
            $userAccount = $this->getUserAccountAndUpdateBalanceFromWithdrawEvent($withdrawEventDTO);
            $responseDTO = $this->userAccountResponseAdapter->fromWithdrawEventUserAccountEntityToResponseDTO(
                $userAccount,
                $responseDTO
            );
        } catch (BadRequestException|InsufficientFundsException $exception) {
            $responseDTO->setHttpStatus(ResponseInterface::HTTP_BAD_REQUEST);
            $responseDTO->setBody($exception->getMessage());
        } catch (UserAccountNotFoundException $exception) {
            $responseDTO->setHttpStatus(ResponseInterface::HTTP_NOT_FOUND);
            $responseDTO->setBody(0);
        } catch (CouldNotPersistException|\Exception $exception) {
            $responseDTO->setHttpStatus(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            $responseDTO->setBody($exception->getMessage());
        }

        return $responseDTO;
    }

    /**
     * @throws InsufficientFundsException|UserAccountNotFoundException|CouldNotPersistException
     */
    private function getUserAccountAndUpdateBalanceFromWithdrawEvent(
        WithdrawOperationEventDTO $withdrawEventDTO
    ): UserAccountEntity {
        $userAccount = $this->userAccountRepository->getUserAccountByAccountId(
            $withdrawEventDTO->getOriginAccountId()
        );

        return $this->validateAccountBalanceAndWithdraw($userAccount, $withdrawEventDTO);
    }

    /**
     * @throws InsufficientFundsException|CouldNotPersistException
     */
    private function validateAccountBalanceAndWithdraw(
        UserAccountEntity $userAccount,
        WithdrawOperationEventDTO $withdrawEventDTO
    ): UserAccountEntity {
        if (!$this->hasSufficientFunds($userAccount, $withdrawEventDTO)) {
            throw new InsufficientFundsException();
        }
        $newBalance = $userAccount->getBalance() - $withdrawEventDTO->getAmount();
        $userAccount->setBalance($newBalance);
        $this->userAccountRepository->persistUserAccount($userAccount);

        return $userAccount;
    }

    private function hasSufficientFunds(
        UserAccountEntity $userAccount,
        WithdrawOperationEventDTO $withdrawEventDTO
    ): bool {
        return $userAccount->getBalance() >= $withdrawEventDTO->getAmount();
    }
}