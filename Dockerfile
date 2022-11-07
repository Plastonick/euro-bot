FROM php:8.1-cli

COPY . /app

ENTRYPOINT php /app/src/App.php
