<?php

namespace App\BusinessLayer\Infra\Adapter;

use App\BusinessLayer\Domain\Adapter\UserAccountResponseAdapterInterface;
use App\BusinessLayer\Infra\DTO\ResponseDTO;
use App\BusinessLayer\Infra\Entity\UserAccountEntity;

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

        return $this->fromArrayStructureToResponseDTO($arrayStructure);
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

        return $this->fromArrayStructureToResponseDTO($arrayStructure);
    }

    private function fromArrayStructureToResponseDTO(array $arrayStructure): ResponseDTO
    {
        $responseDTO = new ResponseDTO();
        $responseDTO->setBody($arrayStructure);
        return $responseDTO;
    }
}