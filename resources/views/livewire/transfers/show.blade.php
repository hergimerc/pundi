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
            <h1 class="text-lg font-semibold">Transfer Detail</h1>
        </div>
    </div>

    {{-- Amount hero --}}
    <div class="bg-white dark:bg-zinc-800 rounded-2xl px-5 py-6 mb-4 text-center">
        <p class="text-xs font-medium uppercase tracking-wide text-zinc-400 dark:text-zinc-500 mb-2">Transfer</p>
        <p class="text-3xl font-bold text-zinc-900 dark:text-zinc-100">
            Rp {{ number_format($transfer->amount, 0, ',', '.') }}
        </p>
        @if ($transfer->fee > 0)
            <p class="text-sm text-red-400 mt-1">
                +Rp {{ number_format($transfer->fee, 0, ',', '.') }} fee
            </p>
        @endif
        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-2">
            {{ $transfer->transferred_at->format('d M Y, H:i') }}
        </p>
    </div>

    {{-- Details card --}}
    <div class="bg-white dark:bg-zinc-800 rounded-2xl divide-y divide-zinc-100 dark:divide-zinc-700 mb-6">

        <div class="flex items-center justify-between px-5 py-3.5">
            <p class="text-sm text-zinc-400 dark:text-zinc-500">From</p>
            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $transfer->fromAccount->name }}</p>
        </div>

        <div class="flex items-center justify-between px-5 py-3.5">
            <p class="text-sm text-zinc-400 dark:text-zinc-500">To</p>
            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $transfer->toAccount->name }}</p>
        </div>

        @if ($transfer->fee > 0)
            <div class="flex items-center justify-between px-5 py-3.5">
                <p class="text-sm text-zinc-400 dark:text-zinc-500">Fee</p>
                <p class="text-sm font-medium text-red-500">Rp {{ number_format($transfer->fee, 0, ',', '.') }}</p>
            </div>
        @endif

        @if ($transfer->note)
            <div class="flex items-center justify-between px-5 py-3.5">
                <p class="text-sm text-zinc-400 dark:text-zinc-500">Note</p>
                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $transfer->note }}</p>
            </div>
        @endif

    </div>

    {{-- Delete --}}
    <div x-data="{ confirming: false }">
        <button type="button" x-show="!confirming" @click="confirming = true"
            class="w-full py-3 rounded-xl border border-red-200 dark:border-red-900 text-red-500 text-sm font-medium hover:bg-red-50 dark:hover:bg-red-950 transition">
            Delete Transfer
        </button>

        <div x-show="confirming" class="space-y-2">
            <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">This will reverse both account balances.</p>
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
