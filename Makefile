.PHONY: up down bash migrate seed test npm-dev pnpm-dev

up:
	docker compose up -d

down:
	docker compose down

bash:
	docker compose exec php bash

migrate:
	docker compose exec php php artisan migrate

seed:
	docker compose exec php php artisan db:seed

test:
	docker compose exec php php artisan test

pnpm-dev:
	docker compose exec -w /var/www php pnpm run dev

pnpm-build:
	docker compose exec -w /var/www php pnpm run build

logs:
	docker compose logs -f
