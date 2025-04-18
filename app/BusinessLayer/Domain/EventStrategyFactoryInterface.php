<?php

namespace App\BusinessLayer\Domain;

interface EventStrategyFactoryInterface
{
    public function create($eventMethod) {
        switch ($eventMethod) {

        }
    }
}