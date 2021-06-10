FROM php:7.4-cli

COPY . /app

ENTRYPOINT php /app/src/App.php
