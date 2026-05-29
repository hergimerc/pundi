<div>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-lg font-semibold">Accounts</h1>
        <a href="{{ route('accounts.create') }}" wire:navigate
            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:hover:bg-zinc-300 transition cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                    clip-rule="evenodd" />
            </svg>
            Add
        </a>
    </div>

    @php
        $allAccounts = $accounts->flatten();
        $activeAccounts = $allAccounts->where('is_active', true);
        $totalBalance = $activeAccounts->sum('current_balance');
    @endphp

    <div class="rounded-2xl bg-zinc-900 dark:bg-zinc-700 text-white px-5 py-5 mb-6">
        <p class="text-xs text-zinc-400 dark:text-zinc-300 uppercase tracking-wide mb-1">Total Balance</p>
        <p class="text-2xl font-semibold tracking-tight">
            Rp {{ number_format($totalBalance, 0, ',', '.') }}
        </p>
        <p class="text-xs text-zinc-400 dark:text-zinc-300 mt-1">
            {{ $activeAccounts->count() }} active account{{ $activeAccounts->count() === 1 ? '' : 's' }}
        </p>
    </div>

    @if ($accounts->isEmpty())
        <div class="text-center py-16">
            <p class="text-sm text-zinc-400 dark:text-zinc-500 mb-4">No accounts yet.</p>
            <a href="{{ route('accounts.create') }}" wire:navigate
                class="inline-flex items-center gap-1.5 rounded-xl bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-700 dark:hover:bg-zinc-300 transition">
                Add your first account
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($accounts as $typeValue => $group)
                <div>
                    <div class="flex items-center justify-between mb-2 px-1">
                        <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wide">
                            {{ $group->first()->type->label() }}
                        </p>
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                            Rp
                            {{ number_format($group->where('is_active', true)->sum('current_balance'), 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        @foreach ($group->sortByDesc('is_active') as $account)
                            <a wire:key="account-{{ $account->id }}" wire:navigate
                                href="{{ route('accounts.show', $account) }}"
                                class="bg-white dark:bg-zinc-800 rounded-2xl px-4 py-4 flex items-center gap-4 hover:bg-zinc-50 dark:hover:bg-zinc-700/60 transition {{ $account->is_active ? '' : 'opacity-50' }}">
                                <div class="size-10 rounded-full shrink-0 flex items-center justify-center text-white text-sm font-semibold"
                                    style="background-color: {{ $account->color ?? '#71717a' }}">
                                    {{ strtoupper(substr($account->name, 0, 1)) }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                        {{ $account->name }}</p>
                                    @if (!$account->is_active)
                                        <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">Inactive</p>
                                    @endif
                                </div>

                                <div class="text-right shrink-0">
                                    <p
                                        class="text-sm font-semibold {{ $account->current_balance < 0 ? 'text-red-500' : 'text-zinc-900 dark:text-zinc-100' }}">
                                        Rp {{ number_format($account->current_balance, 0, ',', '.') }}
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
