# AGENTS.md

## Project: Despensa Orders

Laravel 13 + Inertia.js (Vue 3) + Tailwind CSS 4 app for generating and managing food-supply (despensa) purchase orders from Excel uploads.

## Quick start

```bash
make up                 # docker compose up -d
make bash               # docker compose exec php bash
make migrate            # run migrations
make seed               # db:seed (categories)
make test               # php artisan test
make pnpm-dev           # pnpm run dev (Vite HMR)
make pnpm-build         # pnpm run build
```

## Architecture

- Source lives under `src/`, mounted at `/var/www` in the `php` container.
- All commands run inside the PHP container (user `dev`, UID 1000).
- Entrypoint: `public/index.php` → `bootstrap/app.php` (standard Laravel).
- Frontend SPA entry: `resources/js/app.js` → Inertia.js + Vue 3 + Ziggy routes.
- Pages auto-resolved from `resources/js/Pages/{Name}.vue`.

## Domain (Spanish)

| Model       | Purpose                                     |
|-------------|---------------------------------------------|
| `Category`  | Category (nombre, orden, aplica_iva)        |
| `Item`      | Product (codigo_item, descripcion, precios) |
| `Order`     | Purchase order (remision, sede, fecha, totals) |
| `OrderItem` | Line item (quantity, prices per unit/pack)  |

**IVA (19%)** is applied per-item based on `Category.aplica_iva`.

## Core workflow

1. Upload `.xlsx`/`.xls` → `ExcelParser` parses rows.
2. `OrderGenerator` maps items to DB records, calculates subtotal/IVA/total.
3. Store order → redirect to show page with XLSX/PDF export links.

## Testing

```bash
make test    # runs PHPUnit via artisan
```

- Uses SQLite `:memory:` (phpunit.xml). No external DB needed for tests.
- Feature tests in `tests/Feature/`, Unit in `tests/Unit/`.

## Services (Docker)

| Service    | Port (host) | Notes                         |
|------------|-------------|-------------------------------|
| nginx      | 8080        | Proxies to php-fpm:9000       |
| php-fpm    | 9000        | PHP 8.3, also runs Vite:5173  |
| mysql      | 3307        | DB `despensa`, user/pass `despensa` |
| phpmyadmin | 8081        |                                |

## Environment quirks

- `.env` uses MySQL for local dev; tests override to SQLite automatically.
- Sessions, cache, and queue all use the `database` driver (no Redis required).
- Node 22 + pnpm (via corepack) are pre-installed in the PHP image.

## Available agent skills

`skills-lock.json` enables `accessibility` and `frontend-design` skills.

## Available seeders

- `DatabaseSeeder` calls `CategorySeeder` which creates 9 food categories.
- Items are not seeded — typically imported via Excel or admin CRUD.
