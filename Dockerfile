FROM php:8.3-apache

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo_mysql mysqli

RUN apt-get update && \
    apt-get install -y git zip unzip && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Habilitar el módulo de Apache rewrite
RUN a2enmod rewrite

# Reiniciar el servicio de Apache
RUN service apache2 restart

# Instalar Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
