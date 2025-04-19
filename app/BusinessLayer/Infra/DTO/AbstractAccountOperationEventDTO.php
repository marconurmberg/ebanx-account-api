<?php

namespace App\BusinessLayer\Infra\DTO;

abstract class AbstractAccountOperationEventDTO
{
    protected string $eventType;
    protected float $amount;

    public function getEventType(): string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): void
    {
        $this->eventType = $eventType;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }
}