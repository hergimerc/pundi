# Pundi

Personal expense and budget tracker. Single-user, no registration. Built to keep track of daily spending, account balances, and monthly budgets.

## Stack

- **PHP 8.3** / **Laravel 13**
- **Livewire 4** — reactive UI without writing JavaScript
- **Tailwind CSS v4** — utility-first styling with dark mode support
- **MySQL / MariaDB**

## Features

- Multiple accounts (cash, bank, credit card) with running balances
- Income and expense transactions with categories
- Account-to-account transfers with optional fees
- Monthly budgets per category (subcategory spending included)
- Hierarchical categories (parent → children)
- Reusable transaction notes
- File attachments on transactions
- Dark / light mode toggle
- Mobile-first design

## Requirements

- PHP >= 8.3
- Composer
- Node.js & npm
- MySQL or MariaDB

## Setup

```bash
git clone https://github.com/hergimerc/pundi.git
cd pundi

composer install
cp .env.example .env
php artisan key:generate

# Configure DB_* in .env, then:
php artisan migrate --seed

npm install
npm run build
```

The seeder creates one user account. Update the credentials in `database/seeders/DatabaseSeeder.php` before seeding, or change the password afterward via Tinker:

```bash
php artisan tinker
> User::first()->update(['password' => bcrypt('your-password')]);
```

## Development

```bash
composer dev   # starts server, queue, log watcher, and Vite in parallel
```

Or individually:

```bash
php artisan serve
npm run dev
```

## Testing

```bash
php artisan test
```
