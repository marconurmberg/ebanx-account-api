<?php

namespace App\Controllers;

use App\BusinessLayer\UseCases\ResetState;
use CodeIgniter\HTTP\ResponseInterface;

class StateController extends BaseController
{
    public function resetState(): ResponseInterface
    {
        try {
            $useCase = new ResetState($this->cacheService);
            $resetStateResponse = $useCase->execute();
            return $this->response
                ->setStatusCode($resetStateResponse->getHttpStatus())
                ->setBody($resetStateResponse->getMessage());
        } catch (\Exception $exception) {
            return $this->response
                ->setStatusCode(500)
                ->setBody($exception->getMessage());
        }
    }
}