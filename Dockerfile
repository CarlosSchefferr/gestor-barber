# Usa a imagem oficial do PHP 8.2 com Apache
FROM php:8.2-apache

# Instala dependências do sistema e extensões do PHP necessárias pro Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    git \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd bcmath exif pcntl mbstring

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia o código do projeto
COPY . /var/www/html

# Instala o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instala dependências do Laravel (sem dependências de dev)
RUN composer install --no-dev --optimize-autoloader

# Corrige permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Configura o Apache para servir o diretório "public"
RUN echo '<VirtualHost *:8080>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf \
    && sed -i 's/80/8080/g' /etc/apache2/ports.conf \
    && a2enmod rewrite

# Expondo a porta 8080
EXPOSE 8080

# Inicia o servidor Apache
CMD ["apache2-foreground"]
