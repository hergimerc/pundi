<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('budgets.index') }}" wire:navigate
            class="size-8 flex items-center justify-center rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                    clip-rule="evenodd" />
            </svg>
        </a>
        <h1 class="text-lg font-semibold">Edit Budget</h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- Name --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Name</label>
            <input type="text" wire:model="name" maxlength="255"
                class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-800 placeholder-zinc-400 dark:placeholder-zinc-500 outline-none transition
                    {{ $errors->has('name') ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:focus:ring-red-800' : 'border-zinc-300 dark:border-zinc-600 focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200 dark:focus:ring-zinc-600' }}" />
            @error('name')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Amount --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Amount (Rp)</label>
            <input type="number" wire:model="amount" min="0" step="1000"
                class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-800 placeholder-zinc-400 dark:placeholder-zinc-500 outline-none transition
                    {{ $errors->has('amount') ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:focus:ring-red-800' : 'border-zinc-300 dark:border-zinc-600 focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200 dark:focus:ring-zinc-600' }}" />
            @error('amount')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Month / Year --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Month</label>
                <select wire:model="month"
                    class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-800 outline-none focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200 dark:focus:ring-zinc-600 transition">
                    @foreach ($months as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::createFromDate(2000, $m, 1)->format('F') }}</option>
                    @endforeach
                </select>
                @error('month')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Year</label>
                <select wire:model="year"
                    class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-800 outline-none focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200 dark:focus:ring-zinc-600 transition">
                    @foreach ($years as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
                @error('year')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Categories --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                Categories
                <span class="text-zinc-400 dark:text-zinc-500 font-normal">(expense only)</span>
            </label>

            @if ($categories->isEmpty())
                <p class="text-sm text-zinc-400 dark:text-zinc-500">No expense categories yet.</p>
            @else
                <div class="bg-white dark:bg-zinc-800 rounded-xl border border-zinc-200 dark:border-zinc-700 divide-y divide-zinc-100 dark:divide-zinc-700">
                    @foreach ($categories as $category)
                        <label wire:key="cat-{{ $category->id }}" class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition">
                            <input type="checkbox" wire:model="categoryIds" value="{{ $category->id }}"
                                class="rounded border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 focus:ring-zinc-500" />
                            <div class="size-5 rounded-full shrink-0" style="background-color: {{ $category->color ?? '#71717a' }}"></div>
                            <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ $category->name }}</span>
                        </label>

                        @foreach ($category->children->sortBy('sort_order') as $child)
                            <label wire:key="cat-{{ $child->id }}" class="flex items-center gap-3 px-4 py-3 pl-12 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition">
                                <input type="checkbox" wire:model="categoryIds" value="{{ $child->id }}"
                                    class="rounded border-zinc-300 dark:border-zinc-600 text-zinc-900 dark:text-zinc-100 focus:ring-zinc-500" />
                                <div class="size-4 rounded-full shrink-0" style="background-color: {{ $child->color ?? '#71717a' }}"></div>
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $child->name }}</span>
                            </label>
                        @endforeach
                    @endforeach
                </div>
            @endif
            @error('categoryIds')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-2">
            <button type="submit"
                class="w-full rounded-lg bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-700 dark:hover:bg-zinc-300 transition">
                <span wire:loading.remove>Save Budget</span>
                <span wire:loading>Saving…</span>
            </button>
        </div>

    </form>
</div>
