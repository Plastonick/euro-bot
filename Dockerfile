FROM php:8.1-cli

RUN apt-get update && apt-get install -y libpq-dev && docker-php-ext-install pdo pdo_pgsql && rm -rf /var/lib/apt/lists/*

COPY . /app

ENTRYPOINT php /app/src/App.php
