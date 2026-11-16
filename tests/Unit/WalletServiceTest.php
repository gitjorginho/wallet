<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TransferService;
use App\Services\DepositService;
use App\Services\WithdrawService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Wallet;
use Exception;
use App\Models\Transaction;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransferService $transferService;
    private DepositService $depositService;
    private WithdrawService $withdrawService;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->transferService = new TransferService();
        $this->depositService = new DepositService();
        $this->withdrawService = new WithdrawService();
    }

    /**
     * Teste de deposito caso o saldo da conta esteja menor q zero
     */
    public function test_deposito_caso_saldo_esteja_negativo()
    {
       //cria um usuario com carteira    
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => -30.50]);

        //realiza um deposito 
        $transaction = $this->depositService->execute($wallet->id, 100.75);

        //faz assecoes com os valores atualizados
        $this->assertEquals('70.25', $wallet->fresh()->balance);
        $this->assertEquals('deposit', $transaction->fresh()->type);
        $this->assertEquals('completed', $transaction->fresh()->status);
    }

    /**
    * Teste de deposito com valor invalido ou abaixo de 0,01
    * deve retorna um exception   
    */
    public function test_deposito_com_valor_invalido()
    {
        //cria um usuario com carteira 
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 10.50]);

        //prepara o teste para receber um exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('O valor do depósito deve ser maior que zero.');

        //faz um deposito com valor menor q zero para gerar o exception  
        $this->depositService->execute($wallet->id, -10.00);
    }

     /**
     * estorno de deposito
     */
    public function test_estorno_deposito(){

      //cria um usuario com carteira 
      $user = User::factory()->create();
      $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 20.00]);

      //registra uma trasação para fazer a reversão  
      $transaction = Transaction::create([
        'payer_wallet_id' => null,
        'payee_wallet_id' => $wallet->id,
        'amount' => 80.00,
        'type' => 'deposit',
        'status' => 'completed',
      ]);
      //realiza a operacao de estorno
      $this->depositService->reverse($transaction);

      $this->assertEquals('-60', $wallet->fresh()->balance);
      $this->assertEquals('deposit', $transaction->fresh()->type);
      $this->assertEquals('reversed', $transaction->fresh()->status);

    }

    /**
     * Teste de saque 
     */
    public function test_saque()
    {
        //cria um usuario com carteira 
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 150.00]);

        //realiza uma operacao de saque 
        $transaction = $this->withdrawService->execute($wallet->id, 45.25);

        //faz as assercoes do balanço e transacao
        $this->assertEquals('104.75', $wallet->fresh()->balance);
        $this->assertEquals('withdraw', $transaction->type);
        $this->assertEquals('completed', $transaction->status);
    }

    /**
     * estorno de saque
     */
    public function test_estorno_saque(){

      //cria um usuario com carteira 
      $user = User::factory()->create();
      $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 20.00]);

      //registra uma trasação para fazer a reversão  
      $transaction = Transaction::create([
        'payer_wallet_id' => $wallet->id,
        'payee_wallet_id' => null,
        'amount' => 80.00,
        'type' => 'withdraw',
        'status' => 'completed',
      ]);
     
      $this->withdrawService->reverse($transaction); 
      $this->assertEquals('100.00', $wallet->fresh()->balance);
      $this->assertEquals('withdraw', $transaction->fresh()->type);
      $this->assertEquals('reversed', $transaction->fresh()->status);

    }

    /**
     * teste de saque quando o saldo é insuficiente 
     *
     */
    public function test_saque_com_saldo_insuficiente()
    {
        //cria um usuario com carteira 
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 20.00]);

        //realiza uma operacao de saque 
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Saldo insuficiente');

        //realiza um saque com valor superior ao saldo 
        //gerando um exception    
        $this->withdrawService->execute($wallet->id, 50.00);
    }

    /**
     * Teste de tranferencia 
     */
    public function test_tranferencia()
    {
        //cria um usuario com carteira 
        $payer = User::factory()->create();
        $payerWallet = Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 200.00]);
        
        //cria um usuario com carteira 
        $payee = User::factory()->create();
        $payeeWallet = Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 10.50]);

        //realiza uma operação de transferencia 
        $transaction = $this->transferService->execute($payerWallet->id, $payeeWallet->id, 75.33);

        //faz assercões do saldo da conta das duas carteira   
        $this->assertEquals('124.67', $payer->wallet->fresh()->balance);
        $this->assertEquals('85.83', $payee->wallet->fresh()->balance);
        $this->assertEquals('transfer', $transaction->type);
    }

    /**
     * Teste para verifica o erro caso o a transferencia seja feita para a 
     * mesma carteira q esta enviando o valor
     */
    public function test_tranferindo_valores_para_a_mesma_carteira_emissora()
    {
        //cria um usuario com carteira 
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 100.00]);

        //prepara o exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Não é possível fazer traferência para a mesma carteira');
        
        //realiza uma transferencia para a mesma carteira
        $this->transferService->execute($wallet->id, $wallet->id, 50.00);
    }

    /**
     * Teste de estorno deixando a cateira negativa 
     */
    public function test_estorno_de_valores_deixando_a_carteira_negativa ()
    {
        //cria um usuario pagador com carteira 
        $payer = User::factory()->create();
        $payerWallet = Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 50.00]);
        
        //cria um usuario recebedor com carteira 
        $payee = User::factory()->create();
        $payeeWallet = Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 20.00]);

        //registra uma trasação para fazer a reversão  
        $transaction = Transaction::create([
            'payer_wallet_id' => $payerWallet->id,
            'payee_wallet_id' => $payeeWallet->id,
            'amount' => 80.00,
            'type' => 'transfer',
            'status' => 'completed',
        ]);
        
        //realiza um estorno 
        $this->transferService->reverse($transaction);

        //faz assercoes da carteirra pagadora e recebedora 
        $this->assertEquals('130.00', $payerWallet->fresh()->balance);
        $this->assertEquals('-60.00', $payeeWallet->fresh()->balance);
        $this->assertEquals('reversed', $transaction->fresh()->status);
    }
    
    /**
     * Teste um operacao de estorno caso o estorno já tenhas sido realizado
     */
    public function test_estorno_de_valores_estornados()
    {
        //cria um usuario pagador com carteira 
        $payer = User::factory()->create();
        $payerWallet = Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 50.00]);
        
        //cria um usuario recebedor com carteira 
        $payee = User::factory()->create();
        $payeeWallet = Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 20.00]);

        //cria uma trasacao ja estonada 
        $transaction = Transaction::create([
            'payer_wallet_id' => $payerWallet->id,
            'payee_wallet_id' => $payeeWallet->id,
            'amount' => 50.00,
            'type' => 'transfer',
            'status' => 'reversed',
        ]);

        //prepara o exception q seŕa lançado
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Esta transação já foi estornada.');
        
        //realiza um a trasacao de estorno para gerar o exception 
        $this->transferService->reverse($transaction);
    }




    
}
