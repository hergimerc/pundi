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
        <h1 class="text-lg font-semibold">New Transaction</h1>
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
            <button type="button" wire:click="$set('type', 'transfer')"
                class="flex-1 py-2 rounded-lg text-sm font-medium transition
                    {{ $type === 'transfer'
                        ? 'bg-white dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100 shadow-sm'
                        : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                Transfer
            </button>
        </div>

        {{-- Amount (shared) --}}
        <div>
            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Amount</label>
            <div class="relative" x-data="{
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

        @if ($type === 'transfer')
            {{-- From account --}}
            <div>
                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">From</label>
                <select wire:model="from_account_id"
                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500">
                    <option value="">Select account</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
                @error('from_account_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- To account --}}
            <div>
                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">To</label>
                <select wire:model="to_account_id"
                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500">
                    <option value="">Select account</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </select>
                @error('to_account_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Fee --}}
            <div>
                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Fee
                    <span class="text-zinc-300 dark:text-zinc-600 font-normal">(optional)</span>
                </label>
                <div class="relative" x-data="{
                    update(e) {
                        const digits = e.target.value.replace(/\D/g, '');
                        e.target.value = digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
                        $wire.$set('fee', digits || '0');
                    }
                }">
                    <span
                        class="absolute inset-y-0 left-4 flex items-center text-sm font-medium text-zinc-400 dark:text-zinc-500 pointer-events-none">Rp</span>
                    <input type="text" inputmode="numeric" @input="update($event)"
                        class="w-full pl-10 pr-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500 placeholder-zinc-300 dark:placeholder-zinc-600"
                        placeholder="0" />
                </div>
                @error('fee')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        @else
            {{-- Category --}}
            @php
                $selectedCategoryName = null;
                if ($category_id !== '') {
                    foreach ($categories as $_cat) {
                        if ((string) $_cat->id === $category_id) {
                            $selectedCategoryName = $_cat->name;
                            break;
                        }
                        foreach ($_cat->children as $_child) {
                            if ((string) $_child->id === $category_id) {
                                $selectedCategoryName = $_child->name;
                                break 2;
                            }
                        }
                    }
                }
            @endphp

            <div x-data="{ showPicker: false, expandedId: null }"
                x-on:category-created.window="showPicker = false; expandedId = null">

                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Category</label>

                {{-- Trigger --}}
                <button type="button" @click="showPicker = true"
                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-sm text-left flex items-center justify-between gap-2 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500">
                    <span class="{{ $selectedCategoryName ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400 dark:text-zinc-500' }}">
                        {{ $selectedCategoryName ?? 'Select category' }}
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-zinc-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>

                @error('category_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror

                {{-- Half-page bottom sheet picker --}}
                <div x-show="showPicker" style="display:none" class="fixed inset-0 z-50">
                    {{-- Backdrop --}}
                    <div class="absolute inset-0 bg-black/40" @click="showPicker = false; expandedId = null"></div>

                    {{-- Sheet --}}
                    <div class="absolute bottom-0 left-0 right-0 h-1/2 flex flex-col bg-zinc-50 dark:bg-zinc-950 rounded-t-2xl overflow-hidden shadow-2xl">

                    {{-- Header --}}
                    <div class="flex items-center justify-between px-5 py-4 bg-zinc-900 dark:bg-black shrink-0">
                        <span class="text-base font-semibold text-white">Category</span>
                        <div class="flex items-center gap-5">
                            <button type="button" wire:click="openCategoryModal" title="New category"
                                class="text-white/70 hover:text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                            </button>
                            <button type="button" @click="showPicker = false; expandedId = null"
                                class="text-white/70 hover:text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Category grid --}}
                    <div class="flex-1 overflow-y-auto">
                        <div class="grid grid-cols-3 border-t border-l border-zinc-200 dark:border-zinc-800">
                            @foreach ($categories->chunk(3) as $rowCats)
                                {{-- Parent cells --}}
                                @foreach ($rowCats as $cat)
                                    <div
                                        @if ($cat->children->isNotEmpty())
                                            @click="expandedId = expandedId === {{ $cat->id }} ? null : {{ $cat->id }}"
                                        @else
                                            @click="$wire.set('category_id', '{{ $cat->id }}'); showPicker = false; expandedId = null"
                                        @endif
                                        class="flex flex-col items-center justify-center py-6 px-3 cursor-pointer text-center border-r border-b border-zinc-200 dark:border-zinc-800 select-none active:bg-zinc-100 dark:active:bg-zinc-800 transition-colors">
                                        <span class="text-sm font-medium leading-tight"
                                            :class="expandedId === {{ $cat->id }} ? 'text-orange-400' : 'text-zinc-800 dark:text-zinc-200'">
                                            {{ $cat->name }}
                                        </span>
                                        @if ($cat->children->isNotEmpty())
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="size-3 mt-2 text-zinc-400 transition-transform duration-150"
                                                :class="expandedId === {{ $cat->id }} ? '-rotate-180' : ''"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </div>
                                @endforeach

                                {{-- Pad row to 3 columns --}}
                                @for ($pad = $rowCats->count(); $pad < 3; $pad++)
                                    <div class="border-r border-b border-zinc-200 dark:border-zinc-800"></div>
                                @endfor

                                {{-- Children of each parent in this row --}}
                                @foreach ($rowCats as $cat)
                                    @if ($cat->children->isNotEmpty())
                                        @foreach ($cat->children->chunk(3) as $childRow)
                                            @foreach ($childRow as $child)
                                                <div x-show="expandedId === {{ $cat->id }}" style="display:none"
                                                    @click="$wire.set('category_id', '{{ $child->id }}'); showPicker = false; expandedId = null"
                                                    class="flex items-center justify-center py-5 px-3 cursor-pointer text-sm text-center border-r border-b border-zinc-200 dark:border-zinc-800 select-none text-zinc-500 dark:text-zinc-400 active:bg-zinc-100 dark:active:bg-zinc-800 transition-colors">
                                                    {{ $child->name }}
                                                </div>
                                            @endforeach
                                            @for ($childPad = $childRow->count(); $childPad < 3; $childPad++)
                                                <div x-show="expandedId === {{ $cat->id }}" style="display:none"
                                                    class="border-r border-b border-zinc-200 dark:border-zinc-800"></div>
                                            @endfor
                                        @endforeach
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    </div>{{-- /sheet --}}
                </div>{{-- /backdrop+sheet wrapper --}}
            </div>

            {{-- Quick-create category modal (overlays the picker) --}}
            @if ($showCategoryModal)
                <div class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-4"
                    x-data x-init="$el.querySelector('[data-modal]').focus()">
                    <div class="absolute inset-0 bg-black/40" wire:click="$set('showCategoryModal', false)"></div>
                    <div data-modal tabindex="-1"
                        class="relative w-full max-w-sm bg-white dark:bg-zinc-900 rounded-2xl shadow-xl p-6 space-y-4">
                        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">New Category</h2>

                        <div>
                            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Name</label>
                            <input type="text" wire:model="newCategoryName" maxlength="255" autofocus
                                class="w-full px-4 py-3 rounded-xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500"
                                placeholder="e.g. Transport" />
                            @error('newCategoryName')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">
                                Parent <span class="text-zinc-300 dark:text-zinc-600 font-normal">(optional)</span>
                            </label>
                            <select wire:model="newCategoryParentId"
                                class="w-full px-4 py-3 rounded-xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500">
                                <option value="">No parent (top-level)</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('newCategoryParentId')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3 pt-1">
                            <button type="button" wire:click="$set('showCategoryModal', false)"
                                class="flex-1 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 text-sm text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                                Cancel
                            </button>
                            <button type="button" wire:click="createCategory"
                                class="flex-1 py-2.5 rounded-xl bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-300 transition">
                                <span wire:loading.remove wire:target="createCategory">Save</span>
                                <span wire:loading wire:target="createCategory">Saving…</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

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
        @endif

        {{-- Date & Time (shared) --}}
        <div>
            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Date & Time</label>
            <input type="datetime-local" wire:model="transacted_at"
                class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500" />
            @error('transacted_at')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Note (shared, autocomplete only for expense/income) --}}
        <div>
            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Note
                <span class="text-zinc-300 dark:text-zinc-600 font-normal">(optional)</span>
            </label>

            @if ($type === 'transfer')
                <input type="text" wire:model="note" maxlength="500"
                    class="w-full px-4 py-3 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-zinc-100 text-sm focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:focus:ring-zinc-500 placeholder-zinc-300 dark:placeholder-zinc-600"
                    placeholder="e.g. Monthly savings" />
            @else
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
            @endif

            @error('note')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Description (expense/income only) --}}
        @if ($type !== 'transfer')
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
        @endif

        {{-- Attachments (expense/income only) --}}
        @if ($type !== 'transfer')
            <div>
                <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">Attachments
                    <span class="text-zinc-300 dark:text-zinc-600 font-normal">(optional)</span>
                </label>
                <input type="file" wire:model="attachments" multiple accept="image/*,application/pdf,.doc,.docx,.xls,.xlsx"
                    class="w-full text-sm text-zinc-500 dark:text-zinc-400 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-zinc-100 dark:file:bg-zinc-700 file:text-zinc-700 dark:file:text-zinc-300 hover:file:bg-zinc-200 dark:hover:file:bg-zinc-600 cursor-pointer" />
                @error('attachments.*')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        @endif

        {{-- Submit --}}
        <button type="submit"
            class="w-full py-3.5 rounded-xl bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-sm font-semibold hover:bg-zinc-700 dark:hover:bg-zinc-300 transition">
            <span wire:loading.remove>{{ $type === 'transfer' ? 'Save Transfer' : 'Save Transaction' }}</span>
            <span wire:loading>Saving…</span>
        </button>

    </form>
</div>
