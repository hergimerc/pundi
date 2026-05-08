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
