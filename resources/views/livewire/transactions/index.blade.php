<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Transactions</h1>
        <a href="{{ route('transactions.create') }}" wire:navigate
            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:hover:bg-zinc-300 transition cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                    clip-rule="evenodd" />
            </svg>
            Add
        </a>
    </div>

    {{-- Month navigator --}}
    <div class="flex items-center justify-between mb-4">
        <button wire:click="previousMonth"
            class="p-2 rounded-lg text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
            {{ $month->format('F Y') }}
        </p>

        <button wire:click="nextMonth"
            class="p-2 rounded-lg text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl px-4 py-4">
            <p class="text-xs text-zinc-400 dark:text-zinc-500 mb-1">Income</p>
            <p class="text-base font-semibold text-emerald-500">
                Rp {{ number_format($totalIncome, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-2xl px-4 py-4">
            <p class="text-xs text-zinc-400 dark:text-zinc-500 mb-1">Expense</p>
            <p class="text-base font-semibold text-red-500">
                Rp {{ number_format($totalExpense, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Transactions grouped by date --}}
    @if ($grouped->isEmpty())
        <div class="text-center py-16 text-zinc-400 dark:text-zinc-500">
            <p class="text-sm">No transactions this month.</p>
        </div>
    @else
        <div class="space-y-5">
            @foreach ($grouped as $date => $dayTransactions)
                <div>
                    {{-- Date label --}}
                    <div class="flex items-center justify-between mb-2 px-1">
                        <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wide">
                            {{ \Carbon\Carbon::parse($date)->format('d M') }}
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

                    {{-- Transaction rows --}}
                    <div class="space-y-2">
                        @foreach ($dayTransactions as $transaction)
                            <a wire:key="tx-{{ $transaction->id }}" wire:navigate
                                href="{{ route('transactions.edit', $transaction) }}"
                                class="bg-white dark:bg-zinc-800 rounded-2xl px-4 py-3 flex items-center gap-3 hover:bg-zinc-50 dark:hover:bg-zinc-700/60 transition">
                                {{-- Category dot --}}
                                <div class="size-9 rounded-full shrink-0 flex items-center justify-center text-white text-xs font-semibold"
                                    style="background-color: {{ $transaction->category->color ?? '#71717a' }}">
                                    {{ strtoupper(substr($transaction->category->name, 0, 1)) }}
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                        {{ $transaction->category->name }}
                                    </p>
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500 truncate mt-0.5">
                                        {{ $transaction->account->name }}
                                        @if ($transaction->note)
                                            · {{ $transaction->note->content }}
                                        @endif
                                    </p>
                                </div>

                                {{-- Amount --}}
                                <div class="shrink-0 text-right">
                                    <p
                                        class="text-sm font-semibold {{ $transaction->type === \App\Enums\TransactionType::Income ? 'text-emerald-500' : 'text-zinc-900 dark:text-zinc-100' }}">
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
