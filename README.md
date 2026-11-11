# Fresh Food Multi-Shop

Laravel 12, Inertia.js and Vue power both the Fresh Food customer storefront and shop-management dashboard. The storefront uses the supplied grocery theme and assets with custom Fresh Food branding; the authenticated dashboard uses the supplied Berry/Vuetify visual direction.

## Local setup

1. Install PHP/Composer dependencies with `composer install` and JavaScript dependencies with `npm install`.
2. Copy `.env.example` to `.env`, configure the database (if using the default `sqlite` connection, create `database/database.sqlite` or confirm when prompted), and run `php artisan key:generate`.
3. Run `php artisan migrate --seed` to create the catalog tables, roles, demo accounts, and realistic sample catalog data.
4. Run `php artisan storage:link` so uploaded product images can be served.
5. Sign in with the local demo accounts below, or run `php artisan app:create-admin-user` to create a separate administrator.
6. Build frontend assets with `npm run build` (or start the Vite dev watcher with `npm run dev`), then run the Laravel app using `php artisan serve` (or your local web server).

## Local live QA fixtures

The default `.env.example` configures `LIVE_QA_SEED_PASSWORD`, so `php artisan migrate --seed` creates the following local accounts:

- Admin: `admin@freshfood.test`
- Shop owner: `owner@freshfood.test`
- Password: the value of `LIVE_QA_SEED_PASSWORD` in `.env`

For browser testing after the initial setup, you can rerun `php artisan db:seed --class=Database\Seeders\LiveQaSeeder`. This local-only, idempotent seeder creates or updates two QA accounts, one shop, five categories, eight products, and eight shop offers. It does not reset or delete existing data. The password must be at least 16 characters.

## Main areas

- `/` — Fresh Food storefront. Select an active shop to browse its active offers.
- `/cart` — Session-based cart; stock and current shop prices are checked on cart changes.
- `/login` and `/dashboard` — Inertia authentication and role-specific dashboard.
- `/admin/shops`, `/admin/shop-owners`, `/admin/categories`, `/admin/products` — Admin management.
- `/shop/products` — Shop Owner catalog assignment and shop-specific offer management.

The shared catalog is stored in `products`; each shop's price, stock, description, visibility, and thumbnail are stored in `shop_products`. Shop owners can only manage shops assigned to them. Product media uses Spatie MediaLibrary and the public storage disk.

The cart supports offers from multiple shops, but checkout, payment, and order management are not part of this implementation.

## Tests

Run `php artisan test`. PHPUnit uses an isolated in-memory SQLite database so test runs do not alter the configured application database.
