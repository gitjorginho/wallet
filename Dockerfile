FROM php:8.4-fpm

# Instala todas as dependências do sistema e do frontend (Node.js e NPM) de uma só vez
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nodejs \
    npm \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala as extensões essenciais do PHP (incluindo as de precisão financeira)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Copia o binário oficial do Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho padrão
WORKDIR /var/www

# Copia e concede permissão de execução ao script de inicialização automatizado
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

# Configura as diretivas de entrada oficial do container Docker
ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
