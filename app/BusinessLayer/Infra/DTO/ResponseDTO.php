<?php

namespace App\BusinessLayer\Infra\DTO;

use CodeIgniter\HTTP\ResponseInterface;

class ResponseDTO
{
    private int $httpStatus = ResponseInterface::HTTP_OK;
    private array|string|object $body = "";

    public function __construct(int $httpStatus = 200, array|string|object $body = "")
    {
        $this->httpStatus = $httpStatus;
        $this->body = $body;
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    public function setHttpStatus(int $httpStatus): void
    {
        $this->httpStatus = $httpStatus;
    }

    public function getBody(): string|array|object
    {
        return $this->body;
    }

    public function setBody(string|array|object $body): void
    {
        $this->body = $body;
    }
}