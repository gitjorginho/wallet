<?php
namespace App\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;


class WithdrawService {
  /**
   * Realiza o Saque de uma carteira específica.
   * 
   * @param  int $payerWalletId O ID da carteira que realizará o saque.
   * @param  float $amount O valor a ser sacado.
   * @return Transaction A transação de saque criada.
   * @throws Exception Se o valor do saque for inválido ou se o saldo for insuficiente.
   */
  public function execute(int $walletId, float $amount): Transaction
  {
    //Não permite saque com valor 0 ou menor
    if($amount <= 0){
      throw new Exception("O valor do saque deve ser maior que zero."); 
    }

    return DB::transaction(function () use ($walletId, $amount) {
        
        //garante que nenhuma atualizacao seja feita na carteira durante a transacao evitatando 
        //problemas de concorrencias e inconsistencia do saldo
        $wallet = Wallet::where('id', $walletId)->lockForUpdate()->firstOrFail();

        // verifica se o saldo da carteira é suficiente 
        if ($wallet->balance < $amount) {
            throw new Exception("Saldo insuficiente para realizar o saque.");
        }    

        //deduz o valor da carteira
        $wallet->balance = bcsub($wallet->balance, $amount, 2);
        $wallet->save();

        //registra a trasacao de saque com sucesso
        $transaction = Transaction::create([
            'payer_wallet_id' => $wallet->id,
            'payee_wallet_id' => null, // Nenhum recebedor para saque
            'amount' => $amount,
            'type' => 'withdraw',
            'status' => 'completed',
        ]);
        
        //log da operacao
        Log::channel('transactions')->info('Saque realizado com sucesso', [
          'transaction_id' => $transaction->id,
          'wallet_id' => $wallet->id,
          'amount' => $transaction->amount,
          'new_balance' => $wallet->balance
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

  public function reverse(Transaction $transaction){
        
    if ($transaction->status === 'reversed') {
        throw new Exception("Esta transação já foi estornada.");
    }
    DB::transaction(function () use ($transaction) {
       $wallet = Wallet::where('id', $transaction->payer_wallet_id)->lockForUpdate()->firstOrFail();
       
       // Reverte o saque
       $wallet->balance = bcadd($wallet->balance, $transaction->amount, 2);
       $wallet->save();
        //modifica o status da trasação
       $transaction->status = 'reversed';
       $transaction->save();
       
       //log da operacao
      Log::channel('transactions')->warning('Transação de saque estornada', [
          'transaction_id' => $transaction->id,
          'reversed_by_wallet_id' => $wallet->id,
          'amount' => $transaction->amount,
        ]);
    });
  }  
}