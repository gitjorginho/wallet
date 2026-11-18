<?php

namespace App\Contracts;
use App\Models\Transaction;

interface ReversibleTransactionInterface
{
    public function reverse(Transaction $transaction): void;
}
