# Sweepstakes Announcer

Monorepo for the Sweepstakes Announcer backend and Svelte frontend.

## Layout

- `backend/` contains the PHP bot, API, migrations, and Dockerfile.
- `frontend/` contains the Svelte/Vite configuration UI.
- `docker-compose.yml` starts Postgres, runs migrations, starts the backend API/bot, and serves the frontend.

## Local Development

Create the backend environment file:

```bash
cp backend/.env.example backend/.env
```

Set at least `API_KEY` in `backend/.env`. Compose supplies the local database connection values automatically.

Start everything from the repository root:

```bash
make dev
```

The frontend runs at http://127.0.0.1:5173 and talks to the backend API at http://127.0.0.1:8090.

Useful commands:

```bash
make up
make logs
make down
make migrate
```
