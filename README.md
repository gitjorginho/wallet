# 💰 Carteira Digital - Desafio Técnico Full Stack

Este projeto consiste em uma plataforma de **Carteira Digital** de alta confiabilidade desenvolvida para gerenciar saldos, depósitos, saques, transferências entre usuários e estornos de transações. 

A arquitetura foi projetada seguindo os padrões recomendados para sistemas financeiros, priorizando a consistência dos dados, precisão matemática e segurança contra condições de corrida.

---

## 🚀 Decisões Técnicas & Arquitetura

### 1. Garantia ACID e Locks contra Concorrência (`lockForUpdate`)
Para evitar problemas críticos como o *Double Spending* (gasto duplo), todas as operações que envolvem alteração de saldo utilizam transações de banco de dados (`DB::transaction`). Dentro do Service, os registros das carteiras envolvidas são bloqueados utilizando o mecanismo de **Pessimistic Locking** (`lockForUpdate()`) do banco de dados:
* Isso impede que duas transferências simultâneas leiam ou alterem o mesmo saldo ao mesmo tempo, enfileirando as requisições de forma segura e garantindo consistência absoluta.

### 2. Precisão Decimal Financeira (`BCMath`)
Computadores sofrem nativamente com erros de arredondamento ao realizar cálculos matemáticos com números de ponto flutuante (`float`). Em bilhões de transações, pequenas dízimas como `.77000000000001` podem distorcer saldos.
* Toda a matemática de crédito e débito deste projeto foi blindada utilizando a extensão **BCMath** do PHP, que realiza cálculos de precisão arbitrária baseados em strings, garantindo exatidão de duas casas decimais em todas as somas (`bcadd`) e subtrações (`bcsub`).

### 3. Observabilidade e Auditoria Isolada (`transactions.log`)
Operações financeiras exigem rastreabilidade estrita. O sistema possui uma arquitetura de logs estruturados em formato JSON, gravando dados essenciais de contexto (`transaction_id`, `wallet_id`, `amount`, `new_balance`).
* **Canal Dedicado:** Foi criado um canal de log customizado chamado `transactions`. Ele isola todas as movimentações de sucesso diretamente no arquivo `storage/logs/transactions.log`, impedindo que os dados de auditoria financeira se misturem com logs comuns de erro ou depuração do sistema.

### 4. Camada de Serviço Isolada (`TransactionService`)
Toda a lógica e regras de validação financeira (bloqueio de valores negativos, validação de saldo insuficiente e impedimento de auto-transferência) foram totalmente encapsuladas na camada de serviço, mantendo os Controllers limpos e focados apenas no transporte das requisições.

---

## 🛠️ Stack Tecnológica

* **Backend:** PHP 8.3 / Laravel 11
* **Frontend:** Blade, Tailwind CSS e Alpine.js (para gerenciamento de estado e alertas fluidos)
* **Autenticação:** Laravel Breeze (Estilização nativa e limpa)
* **Ambiente:** Docker & Docker Compose (Containerização completa da aplicação)
* **Banco de Dados:** MySQL 8.0 (Produção/Local) e SQLite (Suíte de Testes Automatizados)

---

## 📦 Como Executar o Projeto com Docker

Certifique-se de ter o **Docker** e o **Docker Compose** instalados em sua máquina.

### 1. Clonar o repositório e acessar a pasta
```bash
git clone <url-do-seu-repositorio>
cd wallet
```

### 2. Preparar os arquivos de ambiente e subir os containers
```bash
cp .env.example .env
docker compose up -d
```

### 3. Instalar dependências e gerar chaves
```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
```

### 4. Executar as Migrations e compilar os Assets frontend
```bash
docker compose exec app php artisan migrate --seed
docker compose exec app npm install
docker compose exec app npm run build
```

A aplicação estará disponível em seu navegador através do endereço: **`http://localhost:8080`**

---

## 🧪 Suíte de Testes Automatizados

O projeto conta com uma cobertura rigorosa de testes, dividida entre testes de integração de ponta a ponta e testes unitários isolados da regra de negócio. Para garantir velocidade máxima e isolamento total, os testes rodam utilizando um banco de dados SQLite em memória (`:memory:`).

### Cenários Cobertos
* **Segurança de Rotas:** Bloqueio de acessos ao dashboard para usuários não autenticados.
* **Formulários e Visual:** Verificação de renderização (HTTP 200) das telas e componentes.
* **Fluxo de Depósito:** Acréscimo exato e compensação/abatimento automático de saldos negativos gerados por estornos.
* **Fluxo de Saque:** Dedução de saldo e validação estrita de saldo insuficiente.
* **Fluxo de Transferência:** Débito no pagador, crédito no recebedor e registro automático no histórico de transações.
* **Fluxo de Estorno:** Reversão segura de saldo, atualização de status do histórico para `reversed` e bloqueio de estornos duplicados da mesma transação.

### Como executar os testes no terminal:

```bash
# Executar toda a suíte de testes (Unitários + Integração)
docker compose exec app php artisan test

# Executar exclusivamente os testes de integração (Feature)
docker compose exec app php artisan test --testsuite=Feature

# Executar exclusivamente os testes de regra de negócio (Unit)
docker compose exec app php artisan test --testsuite=Unit
```
