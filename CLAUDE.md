# CLAUDE.md — Personal Expense Tracker

## Project overview

A single-user personal expense and budgeting app. There is **no authentication system** — the app is designed for one user only. Focus is on correctness of financial data, not user management.

## Tech stack

- **Laravel 12**
- **Livewire** — all interactive UI (forms, tables, charts)
- **TailwindCSS** — styling
- **MySQL / MariaDB** — database
- **PHP backed enums** — for all constrained string values

---

## Common commands

```bash
php artisan serve                  # start dev server
php artisan migrate                # run migrations
php artisan migrate:fresh --seed   # reset and re-seed
php artisan test                   # run test suite
php artisan make:model Foo -mf     # model + migration + factory
php artisan make:livewire Foo/Bar  # Livewire component
php artisan make:observer FooObserver --model=Foo
```

---

## Directory structure (key paths)

```
app/
  Enums/              # AccountType, TransactionType, CategoryType
  Models/             # Eloquent models
  Observers/          # Model observers (balance side effects)
  Services/           # Business logic (TransferService, ReportService, etc.)
  Livewire/
    Accounts/
    Transactions/
    Transfers/
    Budgets/
    Categories/
    Reports/
database/
  migrations/
  seeders/
  factories/
```

---

## Models and relationships

### Account

```
accounts
  id, name, type (enum), initial_balance, current_balance
  color, icon, is_active (bool), deleted_at, timestamps
```

- `hasMany(Transaction::class)`
- `hasMany(Transfer::class, 'from_account_id')`
- `hasMany(Transfer::class, 'to_account_id')`
- Uses `SoftDeletes`
- **Never hard-delete** an account that has transactions — use `is_active = false` instead

### Category

```
categories
  id, parent_id (nullable FK → categories.id), name, type (enum)
  color, icon, sort_order, deleted_at, timestamps
```

- `belongsTo(Category::class, 'parent_id')` — parent
- `hasMany(Category::class, 'parent_id')` — children
- `hasMany(Transaction::class)`
- `belongsToMany(Budget::class, 'budget_category')`
- Uses `SoftDeletes`
- `allDescendantIds(): array` — recursively collects all child IDs (used in budget calculations)
- Scope `scopeRoots()` — returns only top-level categories
- Scope `scopeOfType(CategoryType $type)` — filters by expense/income

### Transaction

```
transactions
  id, type (enum), account_id (FK), category_id (FK), note_id (nullable FK)
  amount (decimal 15,2), description (text), transacted_at (datetime)
  deleted_at, timestamps
```

- `belongsTo(Account::class)`
- `belongsTo(Category::class)`
- `belongsTo(Note::class)` — nullable
- `hasMany(Attachment::class)`
- Uses `SoftDeletes`
- **Balance updates happen via `TransactionObserver`, not in controllers or components**

### Transfer

```
transfers
  id, from_account_id (FK), to_account_id (FK)
  amount (decimal 15,2), fee (decimal 15,2, default 0)
  note (text), transferred_at (datetime), deleted_at, timestamps
```

- `belongsTo(Account::class, 'from_account_id')`
- `belongsTo(Account::class, 'to_account_id')`
- Uses `SoftDeletes`
- **Always use `TransferService` — never mutate balances directly**

### Note

```
notes
  id, content (string, unique), timestamps
```

- `hasMany(Transaction::class)`
- No soft deletes — notes are reusable references
- Always use `Note::firstOrCreate(['content' => $input])` — never `create()`

### Attachment

```
attachments
  id, transaction_id (FK cascade), file_path, original_name, mime_type, size_bytes
  timestamps
```

- `belongsTo(Transaction::class)`
- No soft deletes — deleted with the transaction via cascade

### Budget

```
budgets
  id, name, amount (decimal 15,2), month (tinyint 1–12), year (smallint)
  deleted_at, timestamps
  UNIQUE (name, month, year)
```

- `belongsToMany(Category::class, 'budget_category')`
- Uses `SoftDeletes`
- `spentAmount(): float` — sums expense transactions for covered categories in the budget's month/year
- `remainingAmount(): float`
- `progressPercent(): float`

### BudgetCategory (pivot — no model needed)

```
budget_category
  budget_id (FK cascade), category_id (FK cascade)
  PRIMARY KEY (budget_id, category_id)
```

---

## Enums

All enums live in `app/Enums/` and are **PHP backed string enums**.

```php
// app/Enums/AccountType.php
enum AccountType: string {
    case Cash        = 'cash';
    case BankAccount = 'bank_account';
    case CreditCard  = 'credit_card';
}

// app/Enums/TransactionType.php
enum TransactionType: string {
    case Expense = 'expense';
    case Income  = 'income';
}

// app/Enums/CategoryType.php
enum CategoryType: string {
    case Expense = 'expense';
    case Income  = 'income';
}
```

Always cast enums in models: `protected $casts = ['type' => TransactionType::class]`.

---

## Business rules

### Account balances

- **Expense** transactions **decrease** `current_balance`
- **Income** transactions **increase** `current_balance`
- Balance changes are handled **exclusively** by `TransactionObserver`
- When a transaction is updated, the observer reverses the old effect and applies the new one
- When a transaction is soft-deleted, the observer reverses its balance effect

### Transfers

- Deduct `amount + fee` from `from_account`
- Add `amount` (not including fee) to `to_account`
- Always executed inside `DB::transaction()` via `TransferService::execute()`
- Reversing a transfer restores both balances and soft-deletes the record

### Budget spending

- A budget covers one or more categories
- **Parent category budgets include all subcategory spending** — use `Category::allDescendantIds()` to resolve the full set of category IDs before summing
- Budget period is always the full calendar month (`month` + `year` columns)

### Categories

- Expense and income categories are separate (`type` column)
- When populating a transaction form, **always filter categories by `type`** matching the transaction type
- Soft-deleted categories must remain visible in historical transaction records — use `withTrashed()` in report queries

### Notes

- Notes are shared/reusable across transactions
- Always use `Note::firstOrCreate(['content' => $input])` — duplicate note strings should be collapsed into one record

---

## Key indexes

```php
// transactions
['account_id', 'transacted_at']
['category_id', 'transacted_at']
['type', 'transacted_at']
['transacted_at']

// transfers
['from_account_id', 'transferred_at']
['to_account_id', 'transferred_at']

// categories
['parent_id', 'type']

// budgets
['month', 'year']
```

---

## Service classes

| Class                 | Responsibility                                      |
| --------------------- | --------------------------------------------------- |
| `TransferService`     | Execute and reverse transfers, balance mutations    |
| `TransactionService`  | Create/update/delete transactions with validation   |
| `BudgetReportService` | Aggregate spending vs. budget per month             |
| `ReportService`       | Monthly/annual summaries by category, account, note |

Services are injected into Livewire components. **No business logic in components.**

---

## Livewire conventions

- Components live under `app/Livewire/{Domain}/`
- Forms use Livewire `Form` objects (`php artisan make:livewire-form`)
- Use `#[Validate]` attributes for field-level validation
- Monetary input fields: always cast to `float` before saving, display formatted with `number_format()`
- Date/time fields: use `datetime-local` HTML input, store as `Y-m-d H:i:s`, default to `now()`

---

## Financial data conventions

- All money columns: `DECIMAL(15, 2)` — never `FLOAT`
- Always sum at the database level (`->sum('amount')`) — never accumulate in PHP loops
- Display formatting: `'Rp ' . number_format($amount, 0, ',', '.')` (Indonesian Rupiah)
- Negative balance is valid for credit cards — do not add DB constraints preventing it

---

## Soft delete strategy

Models with `SoftDeletes`: `Account`, `Category`, `Transaction`, `Transfer`, `Budget`

- Soft-deleted accounts: exclude from account pickers, but **include** in transaction history queries
- Soft-deleted categories: exclude from category pickers, but **include** in reports (`withTrashed()`)
- Soft-deleted transactions: excluded from all balance and report calculations automatically
- Never offer a "permanently delete" action in the UI

---

## Testing notes

- Use `RefreshDatabase` trait in all feature tests
- Use factories for all model creation in tests — never `Model::create([...])` inline
- Observer tests: assert `current_balance` changes after transaction create/update/delete
- Transfer tests: always assert **both** account balances within a single test
- Budget tests: seed a category tree (parent + children) and assert subcategory spending is included

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
