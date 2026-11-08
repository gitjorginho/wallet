<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Transaction;

/**
 * Teste de integração para verificar o comportamento das transações de 
 * carteira, incluindo transferências, depósitos e estornos.
 */

class WalletTransactionTest extends TestCase
{
    use RefreshDatabase; 

    /**
     * Verifica se é possivel acessar o dashboard sem esta logado 
     */
    
    public function test_guests_cannot_access_wallet_routes(): void
    {
    // Tenta acessar o dashboard sem estar logado
    $response = $this->get(route('dashboard'));

    // Garante que o sistema redireciona para a tela de login
    $response->assertRedirect(route('login'));
    }

    /**
     * teste para verificar se o formulario de transferencia 
     * esta sendo exibido corretamente 
     */
    public function test_show_form_transfer():void{

        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 100.00]);

        // Cria outro usuário para testar a lista de carteiras
        $otherUser = User::factory()->create();
        $otherWallet = Wallet::factory()->create(['user_id' => $otherUser->id, 'balance' => 50.00]);

        $response = $this->actingAs($user)->get(route('transfer.form'));

        $response->assertStatus(200);
        $response->assertViewIs('wallet.transfer');
        $response->assertViewHas('wallet', function ($viewWallet) use ($wallet) {
            return $viewWallet->id === $wallet->id;
        });
        $response->assertViewHas('otherWallets', function ($viewOtherWallets) use ($otherWallet) {
            return $viewOtherWallets->contains($otherWallet);
        });
    }

    /**
     * Teste para verificar se o formulario de deposito é mostrado.  
     */

    public function test_show_form_deposit():void{

        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 100.00]);

        $response = $this->actingAs($user)->get(route('deposit.form'));

        $response->assertStatus(200);
        $response->assertViewIs('wallet.deposit');
        $response->assertViewHas('wallet', function ($viewWallet) use ($wallet) {
            return $viewWallet->id === $wallet->id;
        });
    }

    /**
     * Teste para verificar se o formulario de deposito é mostrado corretamente
     */
    public function test_show_form_withdraw():void{

        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 100.00]);

        $response = $this->actingAs($user)->get(route('withdraw.form'));

        $response->assertStatus(200);
        $response->assertViewIs('wallet.withdraw');
        $response->assertViewHas('wallet', function ($viewWallet) use ($wallet) {
            return $viewWallet->id === $wallet->id;
        });
    }
    
    /**
     * Teste para verificar se a tela de dashboard é mostrado corretamente
     */
    public function test_show_dashboard():void{

        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 100.00]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('wallet.index');
        $response->assertViewHas('wallet', function ($viewWallet) use ($wallet) {
            return $viewWallet->id === $wallet->id;
        });
    }
        
    /** 
     * teste de saque de uma carteira para verificar se o valor é debitado corretamente
     */
    public function test_user_can_withdraw_money(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => 100.00,
        ]);

        $response = $this->actingAs($user)->post(route('withdraw'), [
            'amount' => 50.00,
        ]);

        $response->assertRedirect(route('withdraw.form'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 50.00,
        ]);
    }


    /**
     * Teste para verifica se o valor depositado é calculado corretamente se o saldo da conta for negativo
     */
    public function test_user_can_deposit_money_and_offset_negative_balance(): void
    {
        $user = User::factory()->create();
        // Simula uma conta que ficou negativa após um estorno
        $wallet = Wallet::factory()->create([
            'user_id' => $user->id,
            'balance' => -48.23,
        ]);
        //testa o envio do formulario de deposito
        $response = $this->actingAs($user)->post(route('deposit'), [
            'amount' => 150.00,
        ]);
        //valida se o redirecionamento e a mensagem de sucesso foram retornados
        $response->assertRedirect(route('deposit.form'));
        $response->assertSessionHas('success');

        // Verifica se -48.23 + 150.00 resultou em 101.77
        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'balance' => 101.77,
        ]);
    }

    /**
     * Teste de transferencia de uma carteira para outra
     */
    public function test_user_can_transfer_money_to_another_user(): void
    {
        //cria um usuario pagador e sua carteira com saldo inicial
        $payer = User::factory()->create();
        $payerWallet = Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 200.00]);
        
        //cria um usario recebedor e sua carteira com saldo inicial
        $payee = User::factory()->create();
        $payeeWallet = Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 50.00]);

        // Simula a transferência de 80.00 do pagador para o recebedor
        $response = $this->actingAs($payer)->post(route('transfer'), [
            'payee_wallet_id' => $payeeWallet->id,
            'amount' => 80.00,
        ]);

        //verifica se o redirecionamento de sucesso foi realizado  
        $response->assertRedirect(route('transfer.form'));
        $response->assertSessionHas('success');

        // Valida os novos saldos no banco
        $this->assertDatabaseHas('wallets', ['id' => $payerWallet->id, 'balance' => 120.00]);
        $this->assertDatabaseHas('wallets', ['id' => $payeeWallet->id, 'balance' => 130.00]);

        // Valida se o histórico foi gravado
        $this->assertDatabaseHas('transactions', [
            'payer_wallet_id' => $payerWallet->id,
            'payee_wallet_id' => $payeeWallet->id,
            'amount' => 80.00,
            'type' => 'transfer',
            'status' => 'completed',
        ]);
    }

    /**
     * Teste de estono de uma transação de transferencia 
     */
    public function test_user_can_reverse_a_completed_transfer(): void
    {
        $payer = User::factory()->create();
        $payerWallet = Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 100.00]);

        $payee = User::factory()->create();
        $payeeWallet = Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 20.00]);

        // Cria uma transação concluída de R$ 50,00
        $transaction = Transaction::create([
            'payer_wallet_id' => $payerWallet->id,
            'payee_wallet_id' => $payeeWallet->id,
            'amount' => 50.00,
            'type' => 'transfer',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($payer)->post(route('transactions.reverse', $transaction->id));

        $response->assertRedirect(route('dashboard'));

        // Pagador recebe os 50 de volta (100 + 50 = 150)
        $this->assertDatabaseHas('wallets', ['id' => $payerWallet->id, 'balance' => 150.00]);
        // Recebedor tem os 50 descontados, ficando negativo (20 - 50 = -30)
        $this->assertDatabaseHas('wallets', ['id' => $payeeWallet->id, 'balance' => -30.00]);

        // Status da transação muda para reversed
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'reversed',
        ]);
    }

    /**
     * teste de saldo insuficiente para transferencia
     */
    public function test_insufficient_balance_for_transfer():void{
        
        $payer = User::factory()->create();
        $payerWallet = Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 100.00]);

        $payee = User::factory()->create();
        $payeeWallet = Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 20.00]);

        // Simula a transferência de 150.00 do pagador para o recebedor
        $response = $this->actingAs($payer)->post(route('transfer'), [
            'payee_wallet_id' => $payeeWallet->id,
            'amount' => 150.00,
        ]);

        $response->assertSessionHas(
          'error',
          'Erro ao realizar a transferência: Saldo insuficiente para realizar a transferência.'
        );

        //verifica o saldo do pagador continua o mesmo
        $this->assertDatabaseHas('wallets', [
            'id' => $payerWallet->id,
            'balance' => 100,
        ]);

        //verifica o saldo do recebedor continua o mesmo
        $this->assertDatabaseHas('wallets', [
            'id' => $payeeWallet->id,
            'balance' => 20,
        ]);

        //verifica de na tabela de trasacoes não houve inserções
        $this->assertDatabaseCount('transactions', 0);
    }

    /**
     * Teste de estorno de uma transação de transferência que já foi estornada
     */
    public function test_duplicate_refund(): void
    {
        $payer = User::factory()->create();
        $payerWallet = Wallet::factory()->create(['user_id' => $payer->id, 'balance' => 100.00]);

        $payee = User::factory()->create();
        $payeeWallet = Wallet::factory()->create(['user_id' => $payee->id, 'balance' => 20.00]);

        // Cria uma transação concluída de R$ 50,00
        $transaction = Transaction::create([
            'payer_wallet_id' => $payerWallet->id,
            'payee_wallet_id' => $payeeWallet->id,
            'amount' => 50.00,
            'type' => 'transfer',
            'status' => 'completed',
        ]);

        // Primeiro estorno
        $this->actingAs($payer)->post(route('transactions.reverse', $transaction->id));

        // Tenta estornar novamente
        $response = $this->actingAs($payer)->post(route('transactions.reverse', $transaction->id));

        // Verifica se a mensagem de erro foi retornada
        $response->assertSessionHas(
            'error',
            'Erro ao estornar a transação: Esta transação já foi estornada.'
        );

        // Verifica se os saldos permanecem corretos após a tentativa de estorno duplicado
        $this->assertDatabaseHas('wallets', ['id' => $payerWallet->id, 'balance' => 150.00]);
        $this->assertDatabaseHas('wallets', ['id' => $payeeWallet->id, 'balance' => -30.00]);

    }
}
