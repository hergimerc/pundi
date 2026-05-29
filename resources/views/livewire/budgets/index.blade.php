<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold">Budgets</h1>
        <a href="{{ route('budgets.create') }}" wire:navigate
            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:hover:bg-zinc-300 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                    clip-rule="evenodd" />
            </svg>
            Add
        </a>
    </div>

    {{-- Month navigator --}}
    <div class="flex items-center justify-between mb-4 bg-white dark:bg-zinc-800 rounded-2xl px-4 py-3">
        <button type="button" wire:click="previousMonth"
            class="size-8 flex items-center justify-center rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                    clip-rule="evenodd" />
            </svg>
        </button>

        <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
            {{ \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y') }}
        </span>

        <button type="button" wire:click="nextMonth"
            class="size-8 flex items-center justify-center rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    @if ($budgets->isEmpty())
        <div class="text-center py-16">
            <p class="text-sm text-zinc-400 dark:text-zinc-500 mb-4">No budgets for this month.</p>
            <a href="{{ route('budgets.create') }}" wire:navigate
                class="inline-flex items-center gap-1.5 rounded-xl bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-700 dark:hover:bg-zinc-300 transition">
                Add a budget
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($budgets as $budget)
                @php
                    $spent = $budget->spentAmount();
                    $progress = $budget->progressPercent();
                    $isOver = $spent > (float) $budget->amount;
                @endphp

                <div wire:key="budget-{{ $budget->id }}"
                    class="bg-white dark:bg-zinc-800 rounded-2xl px-4 py-4">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                {{ $budget->name }}
                            </p>
                            <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                                {{ $budget->categories->pluck('name')->join(', ') ?: 'No categories' }}
                            </p>
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            <a href="{{ route('budgets.edit', $budget) }}" wire:navigate
                                class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                            </a>
                            <button type="button" wire:click="delete({{ $budget->id }})"
                                wire:confirm="Delete '{{ $budget->name }}'? This cannot be undone."
                                class="p-1.5 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="w-full bg-zinc-100 dark:bg-zinc-700 rounded-full h-2 mb-2">
                        <div class="h-2 rounded-full transition-all {{ $isOver ? 'bg-red-500' : 'bg-emerald-500' }}"
                            style="width: {{ $progress }}%"></div>
                    </div>

                    {{-- Amounts --}}
                    <div class="flex items-center justify-between">
                        <span class="text-xs {{ $isOver ? 'text-red-500' : 'text-zinc-500 dark:text-zinc-400' }}">
                            Rp {{ number_format($spent, 0, ',', '.') }} spent
                        </span>
                        <span class="text-xs font-medium text-zinc-700 dark:text-zinc-300">
                            Rp {{ number_format((float) $budget->amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
