# Auren prueba técnica

## Setup

1. Ejecutamos `docker-compose up --build`
2. Instalamos los paquetes con `docker-compose exec php-apache composer install`
3. Ejecutamos las migraciones con`docker-compose exec php-apache php bin/console doctrine:migrations:migrate`
4. Por último arrancamos symfony con `docker-compose exec php-apache symfony server:start`

Tras ejecutar estos pasos ya podemos acceder a `http://localhost`
