#!/bin/bash

# 2. Instala as dependências do Laravel 
if [ ! -d "/var/www/vendor" ]; then
    echo "[2] - Instalando dependências com o composer..."
    composer install --no-interaction --prefer-dist
fi

# 3. Garante a existência imediata do arquivo .env
if [ ! -f "/var/www/.env" ] && [ -f "/var/www/.env.example" ]; then
    echo "[3] - Criando arquivo .env a partir do .env.example"
    cp "/var/www/.env.example" "/var/www/.env"
fi

# 4. Gera a chave de segurança se necessário
if ! grep -q "APP_KEY=base64" "/var/www/.env"; then
    echo "[4] - Gerando APP_KEY do Laravel"
    php artisan key:generate
fi

# 5. Executa as tabelas e alimenta o banco com usuários de teste
echo "[5] - Executando migrations "
php artisan migrate --force 

# 6. Instala e compila o Frontend (Tailwind/Vite) se não estiver compilado
if [ ! -d "/var/www/node_modules" ]; then
    echo "[6] - Instalando pacotes do NPM"
    npm install
fi

echo "[7] - Compilando assets "
npm run build

# 7. Força a limpeza e sincronização de todos os caches de configuração
echo "[8] - Limpando cache, route e config"
php artisan config:clear
php artisan route:clear
php artisan cache:clear


# 9. Garante que as pastas de cache e storage existam e pertençam ao servidor web
echo "[9] -  Criando permissoes nas pastas de cache e storage"
mkdir -p /var/www/storage /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache


echo -e "✅ Sistema pronto! Acesse http://localhost:8080"

# Executa o comando principal do container (que é o php-fpm)
exec "$@"
