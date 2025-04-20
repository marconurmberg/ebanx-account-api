<?php

namespace App\BusinessLayer\Infra\Factory;

use App\BusinessLayer\Domain\Adapter\InputToEventDTOAdapterInterface;
use App\BusinessLayer\Domain\Adapter\UserAccountResponseAdapterInterface;
use App\BusinessLayer\Domain\Enum\AccountOperationEventEnum;
use App\BusinessLayer\Domain\Event\AccountOperationEventInterface;
use App\BusinessLayer\Domain\Factory\OperationEventStrategyFactoryInterface;
use App\BusinessLayer\Domain\Repository\UserAccountRepositoryInterface;
use App\BusinessLayer\Infra\Adapter\InputToEventDTOAdapter;
use App\BusinessLayer\Infra\Adapter\UserAccountResponseAdapter;
use App\BusinessLayer\Infra\Exception\AccountOperationEventNotFoundException;
use App\BusinessLayer\UseCases\Deposit;
use App\BusinessLayer\UseCases\Transfer;
use App\BusinessLayer\UseCases\Withdraw;

class OperationEventStrategyFactory implements OperationEventStrategyFactoryInterface
{
    private UserAccountRepositoryInterface $userAccountRepository;
    private InputToEventDTOAdapterInterface $inputToEventDTOAdapter;

    private UserAccountResponseAdapterInterface $userAccountResponseAdapter;

    public function __construct(
        UserAccountRepositoryInterface $userAccountRepository)
    {
        $this->userAccountRepository = $userAccountRepository;
        $this->inputToEventDTOAdapter = new InputToEventDTOAdapter();
        $this->userAccountResponseAdapter = new UserAccountResponseAdapter();
    }

    /**
     * @throws AccountOperationEventNotFoundException
     */
    public function create(string $eventMethod): AccountOperationEventInterface
    {
        switch ($eventMethod) {
            case AccountOperationEventEnum::EVENT_DEPOSIT:
                return new Deposit(
                    $this->userAccountRepository,
                    $this->inputToEventDTOAdapter,
                    $this->userAccountResponseAdapter
                );
            case AccountOperationEventEnum::EVENT_WITHDRAW:
                return new Withdraw(
                    $this->userAccountRepository,
                    $this->inputToEventDTOAdapter,
                    $this->userAccountResponseAdapter
                );
            case AccountOperationEventEnum::EVENT_TRANSFER:
                return new Transfer(
                    $this->userAccountRepository,
                    $this->inputToEventDTOAdapter,
                    $this->userAccountResponseAdapter
                );
            default:
                throw new AccountOperationEventNotFoundException();
        }
        // TODO: Implement create() method.
    }

}