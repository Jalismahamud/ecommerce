# Fresh Food Multi-Shop

Laravel 12, Inertia.js and Vue power both the Fresh Food customer storefront and shop-management dashboard. The storefront uses the supplied grocery theme and assets with custom Fresh Food branding; the authenticated dashboard uses the supplied Berry/Vuetify visual direction.

## Local setup

1. Install PHP/Composer dependencies with `composer install` and JavaScript dependencies with `npm install`.
2. Copy `.env.example` to `.env`, configure the database, and run `php artisan key:generate`.
3. Run `php artisan migrate --seed` to create the catalog tables and the `admin` / `shop-owner` roles.
4. Run `php artisan storage:link` so uploaded product images can be served.
5. Run `php artisan app:create-admin-user` to securely create the first administrator. No default administrator credentials are shipped.
6. Start the frontend build watcher with `npm run dev`, then run the Laravel app using the local PHP server.

## Local live QA fixtures

For browser testing, set `LIVE_QA_SEED_PASSWORD` in `.env` to a password of at least 16 characters, then run `php artisan db:seed --class=Database\Seeders\LiveQaSeeder`. This local-only, additive seeder creates two QA accounts, one shop, five categories, eight products, and eight shop offers. It does not reset or delete existing data.

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
