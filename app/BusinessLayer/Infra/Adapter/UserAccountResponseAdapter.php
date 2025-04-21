<?php

namespace App\BusinessLayer\Infra\Adapter;

use App\BusinessLayer\Domain\Adapter\UserAccountResponseAdapterInterface;
use App\BusinessLayer\Domain\Entity\UserAccountEntity;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use CodeIgniter\HTTP\ResponseInterface;

//TODO RESPONSE ADAPTER PER EVENT
class UserAccountResponseAdapter implements UserAccountResponseAdapterInterface
{
    public function fromUserAccountEntityToResponseDTO(
        UserAccountEntity $userAccountEntity,
        ResponseDTO $responseDTO
    ): ResponseDTO {
        $arrayStructure = [
            "id" => (string) $userAccountEntity->getAccountId(),
            "balance" => $userAccountEntity->getBalance(),
        ];

        return $this->fromArrayStructureToResponseDTO($arrayStructure, $responseDTO);
    }

    public function fromDepositEventUserAccountEntityToResponseDTO(
        UserAccountEntity $userAccountEntity,
        ResponseDTO $responseDTO
    ): ResponseDTO {
        $arrayStructure = [
            "destination" => [
                "id" => (string) $userAccountEntity->getAccountId(),
                "balance" => $userAccountEntity->getBalance(),
            ]
        ];

        return $this->fromArrayStructureToResponseDTO($arrayStructure, $responseDTO);
    }

    public function fromWithdrawEventUserAccountEntityToResponseDTO(
        UserAccountEntity $userAccountEntity,
        ResponseDTO $responseDTO
    ): ResponseDTO {
        $arrayStructure = [
            "origin" => [
                "id" => (string) $userAccountEntity->getAccountId(),
                "balance" => $userAccountEntity->getBalance(),
            ]
        ];

        return $this->fromArrayStructureToResponseDTO($arrayStructure, $responseDTO);
    }

    public function fromTransferEventAccountEntitiesToResponseDTO(
        UserAccountEntity $originUserAccount,
        UserAccountEntity $destinationUserAccount,
        ResponseDTO $responseDTO
    ): ResponseDTO {
        $arrayStructure = [
            "origin" => [
                "id" => (string) $originUserAccount->getAccountId(),
                "balance" => $originUserAccount->getBalance(),
            ],
            "destination" => [
                "id" => (string) $destinationUserAccount->getAccountId(),
                "balance" => $destinationUserAccount->getBalance(),
            ]
        ];

        return $this->fromArrayStructureToResponseDTO($arrayStructure, $responseDTO);
    }

    private function fromArrayStructureToResponseDTO(array $arrayStructure, ResponseDTO $responseDTO): ResponseDTO
    {
        $responseDTO->setBody($arrayStructure);
        $responseDTO->setHttpStatus(ResponseInterface::HTTP_CREATED);
        return $responseDTO;
    }
}