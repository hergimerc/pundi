<div>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold">Categories</h1>
        <a href="{{ route('categories.create') }}" wire:navigate
            class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 dark:bg-zinc-100 dark:text-zinc-900 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:hover:bg-zinc-300 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                    clip-rule="evenodd" />
            </svg>
            Add
        </a>
    </div>

    @error('delete')
        <div class="mb-4 rounded-xl bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 px-4 py-3">
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        </div>
    @enderror

    @if ($categories->isEmpty())
        <div class="text-center py-16 text-zinc-400 dark:text-zinc-500">
            <p class="text-sm">No categories yet.</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($types as $categoryType)
                @if ($categories->has($categoryType->value))
                    <div>
                        <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500 uppercase tracking-wide mb-2 px-1">
                            {{ $categoryType->label() }}
                        </p>

                        <div class="bg-white dark:bg-zinc-800 rounded-2xl divide-y divide-zinc-100 dark:divide-zinc-700">
                            @foreach ($categories[$categoryType->value] as $category)
                                {{-- Parent row --}}
                                <div wire:key="cat-{{ $category->id }}" class="flex items-center gap-3 px-4 py-3">
                                    <div class="size-8 rounded-full shrink-0"
                                        style="background-color: {{ $category->color ?? '#71717a' }}"></div>

                                    <span class="flex-1 text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                                        {{ $category->name }}
                                    </span>

                                    <div class="flex items-center gap-1 shrink-0">
                                        <a href="{{ route('categories.edit', $category) }}" wire:navigate
                                            class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                        <button type="button" wire:click="delete({{ $category->id }})"
                                            wire:confirm="Delete '{{ $category->name }}'? This cannot be undone."
                                            class="p-1.5 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Children --}}
                                @foreach ($category->children->sortBy('sort_order') as $child)
                                    <div wire:key="cat-{{ $child->id }}" class="flex items-center gap-3 px-4 py-3 pl-14">
                                        <div class="size-6 rounded-full shrink-0"
                                            style="background-color: {{ $child->color ?? '#71717a' }}"></div>

                                        <span class="flex-1 text-sm text-zinc-700 dark:text-zinc-300 truncate">
                                            {{ $child->name }}
                                        </span>

                                        <div class="flex items-center gap-1 shrink-0">
                                            <a href="{{ route('categories.edit', $child) }}" wire:navigate
                                                class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                </svg>
                                            </a>
                                            <button type="button" wire:click="delete({{ $child->id }})"
                                                wire:confirm="Delete '{{ $child->name }}'? This cannot be undone."
                                                class="p-1.5 rounded-lg text-zinc-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>
