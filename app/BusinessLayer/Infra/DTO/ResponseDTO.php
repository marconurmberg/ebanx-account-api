<?php

namespace App\BusinessLayer\Infra\DTO;

class ResponseDTO
{
    private int $httpStatus = 200;
    private string $message = "";

    public function __construct(int $httpStatus = 200, string $message = "")
    {
        $this->httpStatus = $httpStatus;
        $this->message = $message;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function setHttpStatus(int $httpStatus): void
    {
        $this->httpStatus = $httpStatus;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}