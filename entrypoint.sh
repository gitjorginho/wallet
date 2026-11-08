#!/bin/bash

# ---------------------------------------------------------
# Automação de Inicialização e Preparação do Laravel
# ---------------------------------------------------------
echo "🚀 Iniciando automação do ecossistema Laravel..."

echo "🔑 Permissoes de cache e storage"
# Garante que as pastas de cache e storage existam e pertençam ao servidor web
mkdir -p /var/www/storage /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache



# 1. Instala as dependências do PHP caso não existam
if [ ! -d "/var/www/vendor" ]; then
    echo "📦 Instalando dependências do Composer..."
    composer install --no-interaction --prefer-dist
fi

# 2. Garante a existência do arquivo .env
if [ ! -f "/var/www/.env" ] && [ -f "/var/www/.env.example" ]; then
    echo "📄 Criando arquivo .env a partir do .env.example..."
    cp "/var/www/.env.example" "/var/www/.env"
fi

# 3. Gera a chave de segurança se necessário
if ! grep -q "APP_KEY=base64" "/var/www/.env"; then
    echo "🔑 Gerando APP_KEY do Laravel..."
    php artisan key:generate
fi

# 4. Aguarda o MySQL estar totalmente online (evita falha na migration)
echo "⏳ Aguardando banco de dados responder..."
until php artisan db:monitor > /dev/null 2>&1; do
    sleep 2
done

# 5. Executa as tabelas e alimenta o banco com usuários de teste
echo "🗄️ Executando migrations e seeders..."
php artisan migrate --force --seed

# 6. Instala e compila o Frontend (Tailwind/Vite) se não estiver compilado
if [ ! -d "/var/www/node_modules" ]; then
    echo "📦 Instalando pacotes do NPM..."
    npm install
fi

echo "🎨 Compilando assets com o Vite..."
npm run build

# 7. Força a limpeza e sincronização de todos os caches de configuração
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Re-aplica permissões nos arquivos recém-criados pelo build
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "✅ Sistema pronto! Acesse http://localhost:8080"
# ---------------------------------------------------------

# Executa o comando principal do container (que é o php-fpm)
exec "$@"