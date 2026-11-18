<?php

namespace App\Services;

use App\Contracts\ReversibleTransactionInterface;
use App\Exceptions\BusinessException;

class ReversalResolver
{
    public static function resolve(string $type): ReversibleTransactionInterface
    {
        return match ($type) {
            'transfer' => app(TransferService::class),
            'deposit'  => app(DepositService::class),
            'withdraw' => app(WithdrawService::class),
            default    => throw new BusinessException("Tipo de transação desconhecido."),
        };
    }
}
