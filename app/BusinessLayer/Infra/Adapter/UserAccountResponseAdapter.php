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

        $responseDTO = new ResponseDTO();
        $responseDTO->setBody($arrayStructure);
        return $responseDTO;
    }

}