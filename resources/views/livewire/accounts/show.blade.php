<div>
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('accounts.index') }}" wire:navigate
                class="size-8 flex items-center justify-center rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                        clip-rule="evenodd" />
                </svg>
            </a>
            <h1 class="text-lg font-semibold truncate">{{ $account->name }}</h1>
        </div>
        <a href="{{ route('accounts.edit', $account) }}" wire:navigate
            class="px-3 py-1.5 rounded-lg text-sm font-medium text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
            Edit
        </a>
    </div>

    {{-- Account hero --}}
    <div class="rounded-2xl px-5 py-6 mb-4 text-white"
        style="background-color: {{ $account->color ?? '#71717a' }}">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-medium opacity-80">{{ $account->type->label() }}</p>
            @if (!$account->is_active)
                <span class="text-xs font-medium bg-white/20 px-2 py-0.5 rounded-full">Inactive</span>
            @endif
        </div>
        <p class="text-3xl font-bold tracking-tight">
            Rp {{ number_format($account->current_balance, 0, ',', '.') }}
        </p>
        <p class="text-xs opacity-60 mt-1">Current balance</p>
    </div>

    {{-- Income / Expense summary --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl px-4 py-4">
            <p class="text-xs text-zinc-400 dark:text-zinc-500 mb-1">Total Income</p>
            <p class="text-base font-semibold text-emerald-500">
                Rp {{ number_format($totalIncome, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-2xl px-4 py-4">
            <p class="text-xs text-zinc-400 dark:text-zinc-500 mb-1">Total Expense</p>
            <p class="text-base font-semibold text-red-500">
                Rp {{ number_format($totalExpense, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Transaction history --}}
    <h2 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300 mb-3">Transactions</h2>

    @if ($grouped->isEmpty())
        <div class="text-center py-12 text-zinc-400 dark:text-zinc-500">
            <p class="text-sm">No transactions for this account.</p>
        </div>
    @else
        <div class="space-y-5">
            @foreach ($grouped as $date => $dayTransactions)
                <div>
                    <div class="flex items-center justify-between mb-2 px-1">
                        <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wide">
                            {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                            @if (\Carbon\Carbon::parse($date)->isToday())
                                <span class="ml-1 text-zinc-300 dark:text-zinc-600">Today</span>
                            @elseif (\Carbon\Carbon::parse($date)->isYesterday())
                                <span class="ml-1 text-zinc-300 dark:text-zinc-600">Yesterday</span>
                            @endif
                        </p>
                        <div class="flex items-center gap-2 text-xs font-medium">
                            @php
                                $dayIncome = (float) $dayTransactions->where('type', \App\Enums\TransactionType::Income)->sum('amount');
                                $dayExpense = (float) $dayTransactions->where('type', \App\Enums\TransactionType::Expense)->sum('amount');
                            @endphp
                            @if ($dayIncome > 0)
                                <span class="text-emerald-500">+Rp {{ number_format($dayIncome, 0, ',', '.') }}</span>
                            @endif
                            @if ($dayExpense > 0)
                                <span class="text-red-500">-Rp {{ number_format($dayExpense, 0, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-2">
                        @foreach ($dayTransactions as $transaction)
                            <a wire:key="tx-{{ $transaction->id }}" wire:navigate
                                href="{{ route('transactions.show', $transaction) }}"
                                class="bg-white dark:bg-zinc-800 rounded-2xl px-4 py-3 flex items-center gap-3 hover:bg-zinc-50 dark:hover:bg-zinc-700/60 transition">
                                <div class="size-9 rounded-full shrink-0 flex items-center justify-center text-white text-xs font-semibold"
                                    style="background-color: {{ $transaction->category->color ?? '#71717a' }}">
                                    {{ strtoupper(substr($transaction->category->name, 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                        {{ $transaction->category->name }}
                                    </p>
                                    @if ($transaction->note)
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500 truncate mt-0.5">
                                            {{ $transaction->note->content }}
                                        </p>
                                    @endif
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-semibold {{ $transaction->type === \App\Enums\TransactionType::Income ? 'text-emerald-500' : 'text-zinc-900 dark:text-zinc-100' }}">
                                        {{ $transaction->type === \App\Enums\TransactionType::Income ? '+' : '-' }}Rp
                                        {{ number_format($transaction->amount, 0, ',', '.') }}
                                    </p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
