<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Wallet extends Model
{
  use HasFactory;

   // Campos que podem ser preenchidos de forma massiva
    protected $fillable = [
        'user_id',
        'balance',
    ];

    /**
     * Relacionamento: A carteira pertence a um usuário.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento: Histórico de transações enviadas por esta carteira.
     */
    public function outgoingTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payer_wallet_id');
    }

    /**
     * Relacionamento: Histórico de transações recebidas por esta carteira.
     */
    public function incomingTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payee_wallet_id');
    }
}
