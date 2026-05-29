<div>
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('transactions.index') }}" wire:navigate
            class="p-2 rounded-lg text-zinc-500 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                    clip-rule="evenodd" />
            </svg>
        </a>
        <h1 class="text-lg font-semibold">Edit Transaction</h1>
    </div>

    <form wire:submit="save" class="space-y-5">

        {{-- Type toggle --}}
        <div class="flex gap-2 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-xl">
            <button type="button" wire:click="$set('type', 'expense')"
                class="flex-1 py-2 rounded-lg text-sm font-medium transition
                    {{ $type === 'expense'
                        ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-sm'
                        : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                Expense
            </button>
            <button type="button" wire:click="$set('type', 'income')"
                class="flex-1 py-2 rounded-lg text-sm font-medium transition
                    {{ $type === 'income'
                        ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-sm'
                        : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                Income
            </button>
        </div>

        {{-- Amount --}}
        <div>
            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Amount</label>
            <div class="relative" x-data="{
                init() {
                    const digits = @js($amount);
                    if (digits) {
                        this.$el.querySelector('input').value = String(digits).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    }
                },
                update(e) {
                    const digits = e.target.value.replace(/\D/g, '');
                    e.target.value = digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
                    $wire.$set('amount', digits);
                }
            }">
                <span
                    class="absolute inset-y-0 left-4 flex items-center text-sm font-medium text-zinc-400 dark:text-zinc-500 pointer-events-none">Rp</span>
                <input type="text" inputmode="numeric" @input="update($event)"
                    class="w-full pl-10 pr-4 py-3.5 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500 placeholder-zinc-300 dark:placeholder-zinc-600"
                    placeholder="0" />
            </div>
            @error('amount')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Category --}}
        <div>
            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Category</label>
            <select wire:model="category_id"
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500">
                <option value="">Select category</option>
                @foreach ($categories as $parent)
                    @if ($parent->children->isNotEmpty())
                        <optgroup label="{{ $parent->name }}">
                            @foreach ($parent->children as $child)
                                <option value="{{ $child->id }}">{{ $child->name }}</option>
                            @endforeach
                        </optgroup>
                    @else
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @endif
                @endforeach
            </select>
            @error('category_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Account --}}
        <div>
            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Account</label>
            <select wire:model="account_id"
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500">
                <option value="">Select account</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
            @error('account_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Date & Time --}}
        <div>
            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Date & Time</label>
            <input type="datetime-local" wire:model="transacted_at"
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500" />
            @error('transacted_at')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Note --}}
        <div>
            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Note
                <span class="text-zinc-300 dark:text-zinc-600 font-normal">(optional)</span>
            </label>
            <div x-data="{ open: false }" class="relative">
                <input type="text" wire:model.live.debounce.300ms="note" maxlength="255"
                    @focus="open = true"
                    @blur="setTimeout(() => open = false, 150)"
                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500 placeholder-zinc-300 dark:placeholder-zinc-600"
                    placeholder="e.g. Monthly salary" />

                @if ($noteSuggestions->isNotEmpty())
                    <ul x-show="open"
                        class="absolute z-20 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl shadow-lg overflow-hidden">
                        @foreach ($noteSuggestions as $suggestion)
                            <li>
                                <button type="button"
                                    @mousedown.prevent="$wire.$set('note', @js($suggestion)); open = false"
                                    class="w-full text-left px-4 py-2.5 text-sm text-zinc-900 dark:text-zinc-100 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition">
                                    {{ $suggestion }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
            @error('note')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Description
                <span class="text-zinc-300 dark:text-zinc-600 font-normal">(optional)</span>
            </label>
            <textarea wire:model="description" rows="2" maxlength="1000"
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500 placeholder-zinc-300 dark:placeholder-zinc-600 resize-none"
                placeholder="Any extra details…"></textarea>
            @error('description')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Attachments --}}
        <div>
            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Attachments</label>

            @if ($transaction->attachments->isNotEmpty())
                <div class="bg-white dark:bg-zinc-800 rounded-xl divide-y divide-zinc-100 dark:divide-zinc-700 mb-2">
                    @foreach ($transaction->attachments as $attachment)
                        <div class="flex items-center gap-3 px-3 py-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-zinc-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1 text-sm text-zinc-700 dark:text-zinc-300 truncate">{{ $attachment->original_name }}</span>
                            <button type="button" wire:click="removeAttachment({{ $attachment->id }})"
                                class="text-red-400 hover:text-red-600 transition text-xs shrink-0">Remove</button>
                        </div>
                    @endforeach
                </div>
            @endif

            <input type="file" wire:model="newAttachments" multiple accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
                class="w-full text-sm text-zinc-500 dark:text-zinc-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-zinc-100 dark:file:bg-zinc-700 file:text-zinc-700 dark:file:text-zinc-300 hover:file:bg-zinc-200 dark:hover:file:bg-zinc-600 cursor-pointer" />
            @error('newAttachments.*')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Submit --}}
        <button type="submit"
            class="w-full py-3.5 rounded-xl bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-300 transition">
            <span wire:loading.remove>Save Changes</span>
            <span wire:loading>Saving…</span>
        </button>

    </form>
</div>
