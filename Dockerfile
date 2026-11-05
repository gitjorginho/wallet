FROM php:8.4-fpm

# Instala dependências do sistema e ferramentas necessárias
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev

# Limpa o cache do gerenciador de pacotes para reduzir o tamanho da imagem
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala extensões do PHP essenciais para o Laravel e sistemas financeiros (bcmath)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Copia o Composer oficial diretamente da imagem do Docker Hub
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho dentro do container
WORKDIR /var/www

# Expõe a porta do PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
