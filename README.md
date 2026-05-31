# AIRO Quotation App

Simple Laravel app to calculate travel insurance quotations.

The project includes:

* JWT login
* Protected quotation API
* Basic Bootstrap frontend
* SQLite database
* Unit tests

---

## Requirements

* PHP 8.2+
* Composer
* SQLite

---

## Installation

Clone the project:

```bash
git clone https://github.com/YOUR_USERNAME/airo-quotation.git
cd airo-quotation
```

Install dependencies:

```bash
composer install
```

Create the environment file:

```bash
cp .env.example .env
```

Generate the Laravel app key:

```bash
php artisan key:generate
```

Create the SQLite database:

```bash
touch database/database.sqlite
```

Update `.env`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/airo-quotation/database/database.sqlite
```

Install and configure JWT:

```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

Run migrations and seed the demo user:

```bash
php artisan migrate
php artisan db:seed --class=UserSeeder
```

Start the server:

```bash
php artisan serve
```

Open the app:

```text
http://127.0.0.1:8000/login
```

---

## Demo Login

```text
Email: demo@airo.com
Password: password
```

After login, the app redirects to:

```text
/quotation
```

---

## Main Pages

```text
/login
/quotation
```

The frontend files are:

```text
resources/views/layouts/app.blade.php
resources/views/auth/login.blade.php
resources/views/quotation/create.blade.php
```

Bootstrap is loaded in:

```text
resources/views/layouts/app.blade.php
```

---

## API Routes

### Login

```http
POST /api/auth/login
```

Example body:

```json
{
  "email": "demo@airo.com",
  "password": "password"
}
```

Successful response:

```json
{
  "access_token": "jwt_token_here",
  "token_type": "bearer",
  "expires_in": 3600
}
```

---

### Create Quotation

```http
POST /api/quotation
```

This route requires JWT authentication.

Required headers:

```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer YOUR_TOKEN_HERE
```

Example body:

```json
{
  "age": "28,35",
  "currency_id": "EUR",
  "start_date": "2020-10-01",
  "end_date": "2020-10-30"
}
```

Example response:

```json
{
  "total": 117,
  "currency_id": "EUR",
  "quotation_id": 1
}
```

---

## Business Rule

The quotation is calculated with:

```text
3 x age load x number of days
```

Age loads:

| Age   | Load |
| ----- | ---: |
| 18-30 |  0.6 |
| 31-40 |  0.7 |
| 41-50 |  0.8 |
| 51-60 |  0.9 |
| 61-70 |  1.0 |

Example:

```text
Ages: 28,35
Dates: 2020-10-01 to 2020-10-30
Days: 30

Age 28: 3 x 0.6 x 30 = 54
Age 35: 3 x 0.7 x 30 = 63

Total = 117
```

---

## Run Tests

Run all tests:

```bash
php artisan test
```

Run only unit tests:

```bash
php artisan test tests/Unit
```

Run only feature tests:

```bash
php artisan test tests/Feature
```

Run a specific test:

```bash
php artisan test --filter=QuotationServiceTest
```

---

## Logs

To see Laravel errors:

```bash
tail -f storage/logs/laravel.log
```

If something looks cached, run:

```bash
php artisan optimize:clear
```

---

## Common Issues

### View `[layouts.app]` not found

Make sure this file exists:

```text
resources/views/layouts/app.blade.php
```

Then run:

```bash
php artisan view:clear
```

---

### JWT secret missing

Run:

```bash
php artisan jwt:secret
php artisan optimize:clear
```

---

### Demo user missing

Run:

```bash
php artisan db:seed --class=UserSeeder
```

---

## Notes

The `/quotation` page checks the token in the browser.

The real protection is in the API route:

```php
Route::middleware('auth:api')->group(function () {
    Route::post('/quotation', [QuotationController::class, 'store']);
});
```

This means users need a valid JWT token to create a quotation.
