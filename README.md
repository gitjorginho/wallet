# Carteira Digital - Desafio Técnico Full Stack

Este projeto consiste em uma plataforma de **Carteira Digital** de alta confiabilidade desenvolvida para gerenciar saldos, depósitos, saques, transferências entre usuários e estornos de transações. 

A arquitetura foi projetada seguindo os padrões recomendados para sistemas financeiros, priorizando a consistência dos dados, precisão matemática e segurança contra condições de concorrência.

---

## Decisões Técnicas & Arquitetura

### 1. Garantia ACID e Locks contra Concorrência (`lockForUpdate`)
Para evitar que duas transferências simultâneas leiam ou alterem o mesmo saldo ao mesmo tempo foi ultilizado o (`lockForUpdate()`). garantido consistencia absoluta dos saldos.  

### 2. Precisão Decimal Financeira (`BCMath`)
Para evitar que variáveis do tipo float tenha dizimas como 1,770000000001, foi usado a funções nativas do php (bcsub,bcadd) para calculos monetarios. evitando que alguns centavos apareção de forma imprevisível. 

### 3. Observabilidade e Auditoria Isolada (`transactions.log`)
Operações financeiras exigem rastreabilidade estrita. O sistema possui uma arquitetura de logs estruturados em formato JSON, gravando dados essenciais de contexto (`transaction_id`, `wallet_id`, `amount`, `new_balance`).
foi criado um sistema de log q fica salvo em `storage/logs/transactions.log`, impedindo que os dados de auditoria financeira se misturem com logs comuns de erro ou depuração do sistema.

### 4. Camada de Serviço Isolada (`TransferService,DepositService,WithdrawService`)
Toda a lógica e regras de validação financeira (bloqueio de valores negativos, validação de saldo insuficiente e impedimento de auto-transferência) foram totalmente encapsuladas na camada de serviço, mantendo os Controllers limpos e focados apenas no transporte das requisições.
Atendendo o principio de reponsabilidade unica.

---

##  Stack Tecnológica

* **Backend:** PHP 8.4 / Laravel 13
* **Frontend:** Blade, Tailwind CSS 
* **Autenticação:** Laravel Breeze 
* **Ambiente:** Docker & Docker Compose 
* **Banco de Dados:** MySQL 8.0 (Produção/Local) e SQLite (Suíte de Testes Automatizados)

---

##  Como Executar o Projeto com Docker

Certifique-se de ter o **Docker** e o **Docker Compose** instalados em sua máquina.

### 1. Clonar o repositório
https://github.com/gitjorginho/wallet


### 2. Preparar os arquivos de ambiente e subir os containers
```bash
docker compose up
```

A aplicação estará disponível em seu navegador através do endereço: **`http://localhost:8080`**

---

## Suíte de Testes Automatizados

O projeto conta com uma cobertura rigorosa de testes, dividida entre testes de integração de ponta a ponta e testes unitários isolados da regra de negócio. Para garantir velocidade máxima e isolamento total, os testes rodam utilizando um banco de dados SQLite em memória (`:memory:`).

### Cenários Cobertos
* **Segurança de Rotas:** Bloqueio de acessos ao dashboard para usuários não autenticados.
* **Fluxo de Depósito:** Acréscimo exato e compensação/abatimento automático de saldos negativos gerados por estornos.
* **Fluxo de Saque:** Dedução de saldo e validação estrita de saldo insuficiente.
* **Fluxo de Transferência:** Débito no pagador, crédito no recebedor e registro automático no histórico de transações.
* **Fluxo de Estorno:** Reversão segura de saldo, atualização de status do histórico para `reversed` e bloqueio de estornos duplicados da mesma transação.

### Como executar os testes no terminal:

```bash
# Executar toda a suíte de testes (Unitários + Integração)
docker exec -i wallet-app php artisan test

# Executar exclusivamente os testes de integração (Feature)
docker exec -i wallet-app php artisan test --testsuite=Feature

# Executar exclusivamente os testes de regra de negócio (Unit)
docker exec -i wallet-app php artisan test --testsuite=Unit
```
