FROM php:8.2-apache

# Dependências básicas do sistema
RUN apt-get update -y \
    && apt-get install -y --no-install-recommends \
       ca-certificates \
       git \
       unzip \
       curl \
    && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Habilita mod_rewrite do Apache
RUN a2enmod rewrite

# Instalador de extensões do PHP (resolve libs automaticamente)
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd pdo_mysql bcmath exif mbstring zip

# Composer (binário oficial)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Define o diretório de trabalho e copia o app
WORKDIR /var/www/html
COPY . /var/www/html

# Instala dependências do Laravel (production)
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader

# Permissões de escrita
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Configura o Apache para servir o diretório "public" (porta padrão 80)
RUN printf '%s\n' \
    'Listen 8080' \
    > /etc/apache2/ports.conf \
    && printf '%s\n' \
    '<VirtualHost *:8080>' \
    '    DocumentRoot /var/www/html/public' \
    '    <Directory /var/www/html/public>' \
    '        AllowOverride All' \
    '        Require all granted' \
    '    </Directory>' \
    '</VirtualHost>' \
    > /etc/apache2/sites-available/000-default.conf

# Define ServerName para evitar warnings
RUN printf 'ServerName localhost\n' > /etc/apache2/conf-available/servername.conf && a2enconf servername

EXPOSE 8080

CMD ["apache2-foreground"]
