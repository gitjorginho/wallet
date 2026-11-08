<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Transaction extends Model
{
    protected $fillable = [
        'payer_wallet_id',
        'payee_wallet_id',
        'amount',
        'type',
        'status',
    ];

    /**
     * Relacionamento: A transação pertence a uma carteira pagadora (quem enviou).
     */
    public function payerWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'payer_wallet_id');
    }

    /**
     * Relacionamento: A transação pertence a uma carteira recebedora (quem recebeu).
     */
    public function payeeWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'payee_wallet_id');
    }
}
