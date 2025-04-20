<?php

namespace App\BusinessLayer\Infra\Adapter;

use App\BusinessLayer\Domain\Adapter\UserAccountResponseAdapterInterface;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use App\BusinessLayer\Infra\Entity\UserAccountEntity;

//TODO RESPONSE ADAPTER PER EVENT
class UserAccountResponseAdapter implements UserAccountResponseAdapterInterface
{
    public function fromUserAccountEntityToResponseDTO(
        UserAccountEntity $userAccountEntity,
        ResponseDTO $responseDTO
    ): ResponseDTO {
        $arrayStructure = [
            "id" => $userAccountEntity->getAccountId(),
            "balance" => $userAccountEntity->getBalance(),
        ];

        return $this->fromArrayStructureToResponseDTO($arrayStructure);
    }

    public function fromDepositEventUserAccountEntityToResponseDTO(
        UserAccountEntity $userAccountEntity,
        ResponseDTO $responseDTO
    ): ResponseDTO {
        $arrayStructure = [
            "destination" => [
                "id" => $userAccountEntity->getAccountId(),
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
                "id" => $userAccountEntity->getAccountId(),
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
                "id" => $originUserAccount->getAccountId(),
                "balance" => $originUserAccount->getBalance(),
            ],
            "destination" => [
                "id" => $destinationUserAccount->getAccountId(),
                "balance" => $destinationUserAccount->getBalance(),
            ]
        ];

        return $this->fromArrayStructureToResponseDTO($arrayStructure, $responseDTO);
    }

    private function fromArrayStructureToResponseDTO(array $arrayStructure, ResponseDTO $responseDTO): ResponseDTO
    {
        $responseDTO->setBody($arrayStructure);
        return $responseDTO;
    }
}