<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold">Reports</h1>
    </div>

    {{-- Month navigator --}}
    <div class="flex items-center justify-between mb-4 bg-white dark:bg-zinc-800 rounded-2xl px-4 py-3">
        <button type="button" wire:click="previousMonth"
            class="size-8 flex items-center justify-center rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
        </button>
        <span class="text-sm font-medium">{{ $month->format('F Y') }}</span>
        <button type="button" wire:click="nextMonth"
            class="size-8 flex items-center justify-center rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white dark:bg-zinc-800 rounded-2xl px-3 py-4">
            <p class="text-xs text-zinc-400 dark:text-zinc-500 mb-1">Income</p>
            <p class="text-sm font-semibold text-emerald-500">Rp {{ number_format($summary['income'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-2xl px-3 py-4">
            <p class="text-xs text-zinc-400 dark:text-zinc-500 mb-1">Expense</p>
            <p class="text-sm font-semibold text-red-500">Rp {{ number_format($summary['expense'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-2xl px-3 py-4">
            <p class="text-xs text-zinc-400 dark:text-zinc-500 mb-1">Net</p>
            <p class="text-sm font-semibold {{ $summary['net'] >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                Rp {{ number_format(abs($summary['net']), 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Expense breakdown --}}
    @if ($expenseBreakdown->isNotEmpty())
        <div class="mb-6">
            <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wide mb-2 px-1">Expenses by Category</p>
            <div class="bg-white dark:bg-zinc-800 rounded-2xl divide-y divide-zinc-100 dark:divide-zinc-700">
                @foreach ($expenseBreakdown as $row)
                    @php $pct = $summary['expense'] > 0 ? ($row['amount'] / $summary['expense']) * 100 : 0; @endphp
                    <div class="px-4 py-3">
                        <div class="flex items-center gap-3 mb-1.5">
                            <div class="size-3 rounded-full shrink-0" style="background-color: {{ $row['color'] ?? '#71717a' }}"></div>
                            <span class="flex-1 text-sm text-zinc-800 dark:text-zinc-200 truncate">{{ $row['name'] }}</span>
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($row['amount'], 0, ',', '.') }}</span>
                            <span class="text-xs text-zinc-400 dark:text-zinc-500 w-10 text-right">{{ number_format($pct, 0) }}%</span>
                        </div>
                        <div class="w-full bg-zinc-100 dark:bg-zinc-700 rounded-full h-1">
                            <div class="h-1 rounded-full bg-red-400" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Income breakdown --}}
    @if ($incomeBreakdown->isNotEmpty())
        <div class="mb-6">
            <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wide mb-2 px-1">Income by Category</p>
            <div class="bg-white dark:bg-zinc-800 rounded-2xl divide-y divide-zinc-100 dark:divide-zinc-700">
                @foreach ($incomeBreakdown as $row)
                    @php $pct = $summary['income'] > 0 ? ($row['amount'] / $summary['income']) * 100 : 0; @endphp
                    <div class="px-4 py-3">
                        <div class="flex items-center gap-3 mb-1.5">
                            <div class="size-3 rounded-full shrink-0" style="background-color: {{ $row['color'] ?? '#71717a' }}"></div>
                            <span class="flex-1 text-sm text-zinc-800 dark:text-zinc-200 truncate">{{ $row['name'] }}</span>
                            <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Rp {{ number_format($row['amount'], 0, ',', '.') }}</span>
                            <span class="text-xs text-zinc-400 dark:text-zinc-500 w-10 text-right">{{ number_format($pct, 0) }}%</span>
                        </div>
                        <div class="w-full bg-zinc-100 dark:bg-zinc-700 rounded-full h-1">
                            <div class="h-1 rounded-full bg-emerald-400" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($expenseBreakdown->isEmpty() && $incomeBreakdown->isEmpty())
        <div class="text-center py-10 text-zinc-400 dark:text-zinc-500">
            <p class="text-sm">No transactions this month.</p>
        </div>
    @endif

    {{-- Annual trend --}}
    <div class="mb-6">
        <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wide mb-2 px-1">{{ $month->year }} Overview</p>
        <div class="bg-white dark:bg-zinc-800 rounded-2xl divide-y divide-zinc-100 dark:divide-zinc-700">
            @php $maxAmount = $trend->max(fn($r) => max($r['income'], $r['expense'])); @endphp
            @foreach ($trend as $row)
                @if ($row['income'] > 0 || $row['expense'] > 0)
                    <div class="px-4 py-3">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-xs text-zinc-500 dark:text-zinc-400 w-8 shrink-0">
                                {{ \Carbon\Carbon::createFromDate($month->year, $row['month'], 1)->format('M') }}
                            </span>
                            <div class="flex-1 space-y-1">
                                @if ($row['income'] > 0)
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-zinc-100 dark:bg-zinc-700 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full bg-emerald-400"
                                                style="width: {{ $maxAmount > 0 ? ($row['income'] / $maxAmount) * 100 : 0 }}%"></div>
                                        </div>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400 w-24 text-right shrink-0">
                                            Rp {{ number_format($row['income'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endif
                                @if ($row['expense'] > 0)
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-zinc-100 dark:bg-zinc-700 rounded-full h-1.5">
                                            <div class="h-1.5 rounded-full bg-red-400"
                                                style="width: {{ $maxAmount > 0 ? ($row['expense'] / $maxAmount) * 100 : 0 }}%"></div>
                                        </div>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400 w-24 text-right shrink-0">
                                            Rp {{ number_format($row['expense'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            @if ($trend->every(fn($r) => $r['income'] === 0.0 && $r['expense'] === 0.0))
                <div class="px-4 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500">
                    No data for {{ $month->year }}.
                </div>
            @endif
        </div>
    </div>
</div>
