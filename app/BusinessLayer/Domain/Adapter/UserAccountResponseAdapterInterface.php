<?php

namespace App\BusinessLayer\Domain\Adapter;

use App\BusinessLayer\Infra\DTO\ResponseDTO;
use App\BusinessLayer\Infra\Entity\UserAccountEntity;

interface UserAccountResponseAdapterInterface
{
    public function fromUserAccountEntityToResponseDTO(
        UserAccountEntity $userAccountEntity,
        ResponseDTO $responseDTO
    ): ResponseDTO;

    public function fromDepositEventUserAccountEntityToResponseDTO(
        UserAccountEntity $userAccountEntity,
        ResponseDTO $responseDTO
    ): ResponseDTO;

    public function fromWithdrawEventUserAccountEntityToResponseDTO(
        UserAccountEntity $userAccountEntity,
        ResponseDTO $responseDTO
    ): ResponseDTO;

    public function fromTransferEventAccountEntitiesToResponseDTO(
        UserAccountEntity $originUserAccount,
        UserAccountEntity $destinationUserAccount,
        ResponseDTO $responseDTO
    ): ResponseDTO;
}