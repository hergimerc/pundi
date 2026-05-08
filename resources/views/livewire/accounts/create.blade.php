<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('accounts.index') }}" wire:navigate
            class="size-8 flex items-center justify-center rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                    clip-rule="evenodd" />
            </svg>
        </a>
        <h1 class="text-lg font-semibold">New Account</h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- Name --}}
        <div>
            <label for="name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                Account name
            </label>
            <input
                id="name"
                type="text"
                wire:model="name"
                placeholder="e.g. BCA Savings"
                autofocus
                class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-800 placeholder-zinc-400 dark:placeholder-zinc-500 outline-none transition
                    {{ $errors->has('name') ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:focus:ring-red-800' : 'border-zinc-300 dark:border-zinc-600 focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200 dark:focus:ring-zinc-600' }}"
            >
            @error('name')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Type --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                Account type
            </label>
            <div class="grid grid-cols-3 gap-2">
                @foreach ($types as $accountType)
                    <label
                        class="flex flex-col items-center justify-center gap-1 rounded-xl border px-3 py-3 cursor-pointer text-center transition
                            {{ $type === $accountType->value
                                ? 'border-zinc-900 dark:border-zinc-100 bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900'
                                : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:border-zinc-400 dark:hover:border-zinc-500' }}"
                    >
                        <input type="radio" wire:model.live="type" value="{{ $accountType->value }}" class="sr-only">
                        <span class="text-xs font-medium">{{ $accountType->label() }}</span>
                    </label>
                @endforeach
            </div>
            @error('type')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Initial balance --}}
        <div>
            <label for="initial_balance" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                Initial balance
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm text-zinc-400 dark:text-zinc-500 pointer-events-none">
                    Rp
                </span>
                <input
                    id="initial_balance"
                    type="number"
                    step="1"
                    wire:model="initial_balance"
                    class="w-full rounded-lg border pl-9 pr-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-800 outline-none transition
                        {{ $errors->has('initial_balance') ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:focus:ring-red-800' : 'border-zinc-300 dark:border-zinc-600 focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200 dark:focus:ring-zinc-600' }}"
                >
            </div>
            @error('initial_balance')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Color --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                Color
            </label>
            <div class="flex flex-wrap gap-2">
                @foreach ([
                    '#ef4444', '#f97316', '#f59e0b', '#84cc16',
                    '#22c55e', '#10b981', '#06b6d4', '#3b82f6',
                    '#8b5cf6', '#ec4899', '#f43f5e', '#71717a',
                ] as $swatch)
                    <button
                        type="button"
                        wire:click="$set('color', '{{ $swatch }}')"
                        title="{{ $swatch }}"
                        class="size-8 rounded-full transition ring-offset-2 ring-offset-white dark:ring-offset-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-400
                            {{ $color === $swatch ? 'ring-2 ring-zinc-500 scale-110' : 'hover:scale-110' }}"
                        style="background-color: {{ $swatch }}"
                    >
                        @if ($color === $swatch)
                            <svg class="size-4 mx-auto text-white drop-shadow" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </button>
                @endforeach
            </div>
            @error('color')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="pt-2">
            <button
                type="submit"
                class="w-full rounded-lg bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-700 dark:hover:bg-zinc-300 transition cursor-pointer"
            >
                Save account
            </button>
        </div>

    </form>
</div>
