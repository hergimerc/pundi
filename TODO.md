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
- [ ] accounts.show — account detail with its transaction history

### Transfers
- [ ] Transfer model, migration, seeder
- [ ] TransferService — execute() and reverse(), always inside DB::transaction()
  - Deduct amount + fee from from_account
  - Add amount (no fee) to to_account
- [ ] transfers.index — list of transfers
- [ ] transfers.create — from/to account picker, amount, fee, date, note
- [ ] Delete transfer (reverse both balances)

### Categories
- [ ] categories.index — list with parent/child tree structure
- [ ] categories.create — name, type, color, parent (optional)
- [ ] categories.edit
- [ ] Delete category (soft delete; historical transactions keep the reference)

### Budgets
- [ ] Budget model, migration, pivot table (budget_category)
- [ ] Budget::spentAmount(), remainingAmount(), progressPercent()
  - Must use Category::allDescendantIds() to include subcategory spending
- [ ] budgets.index — list with progress bars
- [ ] budgets.create — name, amount, month/year, category picker (multi-select)
- [ ] budgets.edit
- [ ] Delete budget (soft delete)

### Reports
- [ ] ReportService — monthly/annual summaries by category, account, note
- [ ] BudgetReportService — spending vs budget per month
- [ ] reports.index — charts: spending by category, income vs expense over time
  - Consider using a JS charting library (Chart.js or ApexCharts via CDN)

### Services (backend only, no UI yet)
- [ ] TransactionService — create/update/delete with validation
- [ ] TransferService — (see Transfers above)
- [ ] ReportService
- [ ] BudgetReportService

### Polish
- [ ] Attachment support — file upload on transaction create/edit
  - Attachment model, migration (transaction_id cascade, file_path, mime_type, size_bytes)
- [ ] Pagination or infinite scroll on transactions.index for large months
- [ ] Empty states with helpful CTAs (e.g. "No accounts yet — add one")
- [ ] Flash messages on successful save/delete
- [ ] Confirm dialog before deleting any record
