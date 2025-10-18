# Usa a imagem oficial do PHP 8.2 com Apache
FROM php:8.2-apache

# Instala dependências do sistema e extensões do PHP necessárias para o Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    zip \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd bcmath exif pcntl mbstring zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia o código do projeto
COPY . .

# Instala o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala as dependências do Laravel (sem dependências de dev)
RUN composer install --no-dev --optimize-autoloader

# Gera a APP_KEY automaticamente (se não existir)
RUN php artisan key:generate --ansi || true

# Dá permissão para o Apache acessar os arquivos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Muda a porta do Apache para 8080 (padrão da Sevalla e Fly.io)
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's/80/8080/g' /etc/apache2/ports.conf

# Expõe a porta 8080
EXPOSE 8080

# Inicia o servidor Apache
CMD ["apache2-foreground"]
