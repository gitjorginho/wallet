<?php
namespace App\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class DepositService{
  
/**
   * Realiza o Deposito em uma carteira específica.
   * 
   * @param  int $payeeWalletId O ID da carteira que receberá o depósito.
   * @param  float $amount O valor a ser depositado.
   * @return Transaction A transação de depósito criada.
   * @throws Exception Se o valor do depósito for inválido.
   */
  public function execute(int $walletId, float $amount): Transaction
  {
    //Não permite deposito com valor 0 ou menor
    if($amount <= 0){
      throw new Exception("O valor do depósito deve ser maior que zero.");
    }

    return DB::transaction(function () use ($walletId, $amount) {
        
    //garante que nenhuma atualizacao seja feita na carteira durante a transacao evitatando 
        //problemas de concorrencias e inconsistencia do saldo
        $wallet = Wallet::where('id', $walletId)->lockForUpdate()->firstOrFail();

        //adiciona o valor na carteira
        $wallet->balance = bcadd($wallet->balance, $amount, 2);
        $wallet->save();

        //registra a trasacao de deposito com sucesso
        $transaction =  Transaction::create([
            'payer_wallet_id' => null, // Nenhum pagador para depósito
            'payee_wallet_id' => $walletId,
            'amount' => $amount,
             'type' => 'deposit',
            'status' => 'completed',
        ]);

        //resgistra o log da operação
        Log::channel('transactions')->info('Depósito realizado com sucesso', [
         'transaction_id' => $transaction->id,
         'payee_wallet_id' => $wallet->id,
         'amount' => $amount,
         'new_balance' => $wallet->balance,
        ]);
        
        return $transaction;
    });
  }

  /**
   * Methodo responsável por fazer o estono da trasação finaceira
   * @param Transaction
   * @return void
   * @throws Exception caso o estorno já tenha sido realizado
   */
  public function reverse(Transaction  $transaction){
    

    // Verifica se a transação já foi estornada
    if ($transaction->status === 'reversed') {
      throw new Exception("Esta transação já foi estornada.");
    }

    DB::transaction(function () use ($transaction) {
    $wallet = Wallet::where('id', $transaction->payee_wallet_id)->lockForUpdate()->firstOrFail();
    // Reverte o depósito
    $wallet->balance = bcsub($wallet->balance, $transaction->amount, 2);
    $wallet->save();
    
     //modifica o status da trasação
     $transaction->status = 'reversed';
     $transaction->save();
     
    //log da operacao
    Log::channel('transactions')->warning('Transação de deposito estornada', [
      'transaction_id' => $transaction->id,
      'reversed_by_wallet_id' => $wallet->id,
      'amount' => $transaction->amount,
    ]);
    });
  }
}