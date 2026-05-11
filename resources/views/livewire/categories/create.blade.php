<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('categories.index') }}" wire:navigate
            class="size-8 flex items-center justify-center rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                    clip-rule="evenodd" />
            </svg>
        </a>
        <h1 class="text-lg font-semibold">New Category</h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- Type --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Type</label>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($types as $categoryType)
                    <label
                        class="flex items-center justify-center rounded-xl border px-3 py-3 cursor-pointer text-center transition
                            {{ $type === $categoryType->value
                                ? 'border-zinc-900 dark:border-zinc-100 bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900'
                                : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:border-zinc-400 dark:hover:border-zinc-500' }}">
                        <input type="radio" wire:model.live="type" value="{{ $categoryType->value }}" class="sr-only">
                        <span class="text-sm font-medium">{{ $categoryType->label() }}</span>
                    </label>
                @endforeach
            </div>
            @error('type')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Name --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Name</label>
            <input type="text" wire:model="name" autofocus maxlength="255"
                placeholder="e.g. Food & Drink"
                class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-800 placeholder-zinc-400 dark:placeholder-zinc-500 outline-none transition
                    {{ $errors->has('name') ? 'border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200 dark:focus:ring-red-800' : 'border-zinc-300 dark:border-zinc-600 focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200 dark:focus:ring-zinc-600' }}" />
            @error('name')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Parent (optional) --}}
        @if ($parents->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                    Parent category
                    <span class="text-zinc-400 dark:text-zinc-500 font-normal">(optional)</span>
                </label>
                <select wire:model="parent_id"
                    class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 px-3.5 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 bg-white dark:bg-zinc-800 outline-none focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200 dark:focus:ring-zinc-600 transition">
                    <option value="">None (top-level)</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        @endif

        {{-- Color --}}
        <div>
            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">Color</label>
            <div class="flex flex-wrap gap-2">
                @foreach ([
                    '#ef4444', '#f97316', '#f59e0b', '#84cc16',
                    '#22c55e', '#10b981', '#06b6d4', '#3b82f6',
                    '#8b5cf6', '#ec4899', '#f43f5e', '#71717a',
                ] as $swatch)
                    <button type="button" wire:click="$set('color', '{{ $swatch }}')"
                        class="size-8 rounded-full transition ring-offset-2 ring-offset-white dark:ring-offset-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-400
                            {{ $color === $swatch ? 'ring-2 ring-zinc-500 scale-110' : 'hover:scale-110' }}"
                        style="background-color: {{ $swatch }}">
                        @if ($color === $swatch)
                            <svg class="size-4 mx-auto text-white drop-shadow" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        @endif
                    </button>
                @endforeach
            </div>
            @error('color')
                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-2">
            <button type="submit"
                class="w-full rounded-lg bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 px-4 py-2.5 text-sm font-medium text-white hover:bg-zinc-700 dark:hover:bg-zinc-300 transition">
                <span wire:loading.remove>Save Category</span>
                <span wire:loading>Saving…</span>
            </button>
        </div>

    </form>
</div>
