<?php

namespace App\BusinessLayer\Domain\Adapter;

use App\BusinessLayer\Domain\Entity\UserAccountEntity;
use App\BusinessLayer\Infra\DTO\ResponseDTO;

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