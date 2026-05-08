<div>
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('transactions.index') }}" wire:navigate
                class="p-2 rounded-lg text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </a>
            <h1 class="text-lg font-semibold">Transaction Detail</h1>
        </div>
        <a href="{{ route('transactions.edit', $transaction) }}" wire:navigate
            class="px-3 py-1.5 rounded-lg text-sm font-medium text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
            Edit
        </a>
    </div>

    {{-- Amount hero --}}
    <div class="bg-white dark:bg-zinc-800 rounded-2xl px-5 py-6 mb-4 text-center">
        <p class="text-xs font-medium uppercase tracking-wide mb-2
            {{ $transaction->type === \App\Enums\TransactionType::Income ? 'text-emerald-500' : 'text-red-500' }}">
            {{ $transaction->type === \App\Enums\TransactionType::Income ? 'Income' : 'Expense' }}
        </p>
        <p class="text-3xl font-bold {{ $transaction->type === \App\Enums\TransactionType::Income ? 'text-emerald-500' : 'text-zinc-900 dark:text-zinc-100' }}">
            {{ $transaction->type === \App\Enums\TransactionType::Income ? '+' : '-' }}Rp
            {{ number_format($transaction->amount, 0, ',', '.') }}
        </p>
        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-2">
            {{ $transaction->transacted_at->format('d M Y, H:i') }}
        </p>
    </div>

    {{-- Details card --}}
    <div class="bg-white dark:bg-zinc-800 rounded-2xl divide-y divide-zinc-100 dark:divide-zinc-700 mb-6">

        <div class="flex items-center justify-between px-5 py-3.5">
            <p class="text-sm text-zinc-400 dark:text-zinc-500">Category</p>
            <div class="flex items-center gap-2">
                <span class="size-5 rounded-full inline-block shrink-0"
                    style="background-color: {{ $transaction->category->color ?? '#71717a' }}"></span>
                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $transaction->category->name }}</p>
            </div>
        </div>

        <div class="flex items-center justify-between px-5 py-3.5">
            <p class="text-sm text-zinc-400 dark:text-zinc-500">Account</p>
            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $transaction->account->name }}</p>
        </div>

        @if ($transaction->note)
            <div class="flex items-center justify-between px-5 py-3.5">
                <p class="text-sm text-zinc-400 dark:text-zinc-500">Note</p>
                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $transaction->note->content }}</p>
            </div>
        @endif

        @if ($transaction->description)
            <div class="px-5 py-3.5">
                <p class="text-sm text-zinc-400 dark:text-zinc-500 mb-1">Description</p>
                <p class="text-sm text-zinc-900 dark:text-zinc-100">{{ $transaction->description }}</p>
            </div>
        @endif

    </div>

    {{-- Delete --}}
    <div x-data="{ confirming: false }">
        <button type="button" x-show="!confirming" @click="confirming = true"
            class="w-full py-3 rounded-xl border border-red-200 dark:border-red-900 text-red-500 text-sm font-medium hover:bg-red-50 dark:hover:bg-red-950 transition">
            Delete Transaction
        </button>

        <div x-show="confirming" class="space-y-2">
            <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">Are you sure? This cannot be undone.</p>
            <div class="flex gap-2">
                <button type="button" @click="confirming = false"
                    class="flex-1 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 text-sm font-medium text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                    Cancel
                </button>
                <button type="button" wire:click="delete" wire:loading.attr="disabled"
                    class="flex-1 py-3 rounded-xl bg-red-500 text-white text-sm font-medium hover:bg-red-600 transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="delete">Yes, Delete</span>
                    <span wire:loading wire:target="delete">Deleting…</span>
                </button>
            </div>
        </div>
    </div>
</div>
