.PHONY: dev build up down logs migrate

dev:
	docker compose up --build

build:
	docker compose build

up:
	docker compose up -d --build

down:
	docker compose down

logs:
	docker compose logs -f

migrate:
	docker compose run --rm migrate
