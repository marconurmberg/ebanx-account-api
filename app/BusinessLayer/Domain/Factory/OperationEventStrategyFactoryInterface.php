<?php

namespace App\BusinessLayer\Domain\Factory;

use App\BusinessLayer\Domain\Event\AccountOperationEventInterface;

interface OperationEventStrategyFactoryInterface
{
    public function create(string $eventMethod): AccountOperationEventInterface;
}