# Pundi — Todo List

## Done

- [x] Login page (single-user, no register)
- [x] Account model, migration, factory, seeder
- [x] Category model, migration, factory, seeder
- [x] Note model, migration
- [x] Transaction model, migration, factory, seeder
- [x] TransactionObserver (balance side-effects on create/update/delete/restore)
- [x] accounts.index — grouped by type, per-group balance sum, total balance card
- [x] accounts.create — type pill selector, color swatches
- [x] transactions.index — monthly view, month navigator, income/expense summary, grouped by date
- [x] transactions.create — type toggle, formatted amount input, category/account dropdowns, note autocomplete
- [x] Bottom nav (Transactions / Accounts)
- [x] Dark / light mode toggle

---

## Remaining

### Transactions
- [x] transactions.edit — edit existing transaction (amount, type, category, account, date, note)
- [x] transactions.show — transaction detail view with delete action
- [x] Delete transaction (soft delete with balance reversal via observer)

### Accounts
- [x] accounts.edit — edit name, type, color, is_active
- [x] accounts.show — account detail with its transaction history

### Transfers
- [x] Transfer model, migration, seeder
- [x] TransferService — execute() and reverse(), always inside DB::transaction()
  - Deduct amount + fee from from_account
  - Add amount (no fee) to to_account
- [x] transfers shown in transactions.index (merged, gray amount)
- [x] transfers.create merged into transactions.create as a third tab
- [x] transfers.show — detail with delete action
- [x] Delete transfer (reverse both balances)

### Categories
- [x] categories.index — list with parent/child tree structure
- [x] categories.create — name, type, color, parent (optional)
- [x] categories.edit
- [x] Delete category (soft delete; historical transactions keep the reference)

### Budgets
- [x] Budget model, migration, pivot table (budget_category)
- [x] Budget::spentAmount(), remainingAmount(), progressPercent()
  - Must use Category::allDescendantIds() to include subcategory spending
- [x] budgets.index — list with progress bars, month navigator
- [x] budgets.create — name, amount, month/year, category picker (multi-select)
- [x] budgets.edit
- [x] Delete budget (soft delete)
- [x] "More" nav button → Settings sheet (Budgets link)

### Reports
- [x] ReportService — monthly/annual summaries by category, account, note
- [x] reports.index — monthly summary (income/expense/net), category breakdown, annual trend

### Services (backend only, no UI yet)
- [x] TransactionService — create/update/delete with validation
- [x] TransferService — (see Transfers above)
- [x] ReportService

### Polish
- [x] Attachment support — file upload on transaction create/edit
  - Attachment model, migration (transaction_id cascade, file_path, mime_type, size_bytes)
- [x] Pagination or infinite scroll on transactions.index for large months
- [x] Empty states with helpful CTAs (e.g. "No accounts yet — add one")
- [ ] Flash messages on successful save/delete
- [ ] Confirm dialog before deleting any record
