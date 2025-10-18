FROM php:8.2-apache

# Instala extensões necessárias do Laravel
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# Copia os arquivos do projeto
COPY . /var/www/html

# Define o diretório de trabalho
WORKDIR /var/www/html

# Instala Composer e dependências do Laravel
RUN apt-get update && apt-get install -y unzip git \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader

# Permissões para o Apache acessar os arquivos
RUN chown -R www-data:www-data /var/www/html

# Expõe a porta 8080 (Fly usa 8080 por padrão)
EXPOSE 8080

# Ajusta o Apache para rodar na porta 8080
RUN sed -i 's/80/8080/g' /etc/apache2/sites-available/000-default.conf
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf

# Comando final que inicia o servidor
CMD ["apache2-foreground"]
