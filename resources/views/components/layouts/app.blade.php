<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Pundi') }}</title>
    {{-- Apply saved theme before paint and after every wire:navigate --}}
    <script>
        function applyTheme() {
            if (localStorage.theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
        applyTheme();
        document.addEventListener('livewire:navigated', applyTheme);
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-zinc-100 dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100 antialiased">

    <header class="sticky top-0 z-10 bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-700">
        <div class="max-w-2xl mx-auto px-4 h-14 flex items-center justify-between">
            <span class="font-semibold text-base tracking-tight">{{ config('app.name', 'Pundi') }}</span>

            <div class="flex items-center gap-3">
                {{-- Dark / light toggle --}}
                <button
                    type="button"
                    onclick="
                        const isDark = document.documentElement.classList.toggle('dark');
                        localStorage.theme = isDark ? 'dark' : 'light';
                    "
                    class="size-8 flex items-center justify-center rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition cursor-pointer"
                    aria-label="Toggle dark mode"
                >
                    {{-- Sun (shown in dark mode) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 hidden dark:block" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4.22 1.78a1 1 0 011.42 1.42l-.71.7a1 1 0 11-1.41-1.41l.7-.71zM18 9a1 1 0 110 2h-1a1 1 0 110-2h1zM4.93 14.36a1 1 0 011.41 1.41l-.7.71a1 1 0 01-1.42-1.42l.71-.7zM10 16a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zm-5.66-2.34a1 1 0 010 1.41l-.7.71a1 1 0 01-1.42-1.42l.71-.7a1 1 0 011.41 0zM3 10a1 1 0 110-2H2a1 1 0 100 2h1zm11.36-4.93a1 1 0 010 1.41l-.7.71a1 1 0 01-1.42-1.42l.71-.7a1 1 0 011.41 0zM10 6a4 4 0 100 8 4 4 0 000-8z" clip-rule="evenodd"/>
                    </svg>
                    {{-- Moon (shown in light mode) --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 block dark:hidden" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                    </svg>
                </button>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-100 transition cursor-pointer">
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-4 py-6 pb-24">
        {{ $slot }}
    </main>

    {{-- Settings backdrop --}}
    <div id="settings-backdrop"
        onclick="closeSettings()"
        class="fixed inset-0 z-40 bg-black/40 hidden transition-opacity duration-200"></div>

    {{-- Settings sheet --}}
    <div id="settings-sheet"
        class="fixed bottom-16 inset-x-0 z-50 hidden bg-white dark:bg-zinc-800 rounded-t-2xl border-t border-zinc-200 dark:border-zinc-700 shadow-xl">
        <div class="max-w-2xl mx-auto px-4 pt-4 pb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold">Settings</h2>
                <button type="button" onclick="closeSettings()"
                    class="size-8 flex items-center justify-center rounded-lg text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div class="bg-zinc-100 dark:bg-zinc-700/50 rounded-2xl divide-y divide-zinc-200 dark:divide-zinc-600">
                <a href="{{ route('reports.index') }}" wire:navigate onclick="closeSettings()"
                    class="flex items-center gap-3 px-4 py-3.5 rounded-t-2xl hover:bg-zinc-200/60 dark:hover:bg-zinc-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-zinc-500 dark:text-zinc-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                        <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
                    </svg>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Reports</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-zinc-400 dark:text-zinc-500 ml-auto" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
                <a href="{{ route('budgets.index') }}" wire:navigate onclick="closeSettings()"
                    class="flex items-center gap-3 px-4 py-3.5 rounded-b-2xl hover:bg-zinc-200/60 dark:hover:bg-zinc-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-zinc-500 dark:text-zinc-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                    </svg>
                    <span class="text-sm font-medium text-zinc-900 dark:text-zinc-100">Budgets</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-zinc-400 dark:text-zinc-500 ml-auto" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <script>
        function openSettings() {
            document.getElementById('settings-sheet').classList.remove('hidden');
            document.getElementById('settings-backdrop').classList.remove('hidden');
        }
        function closeSettings() {
            document.getElementById('settings-sheet').classList.add('hidden');
            document.getElementById('settings-backdrop').classList.add('hidden');
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeSettings();
        });
        document.addEventListener('livewire:navigated', closeSettings);
    </script>

    {{-- Bottom navigation --}}
    <nav class="fixed bottom-0 inset-x-0 z-50 bg-white dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700">
        <div class="max-w-2xl mx-auto px-4 flex">
            <a href="{{ route('transactions.index') }}" wire:navigate
                class="flex-1 flex flex-col items-center gap-1 py-3 text-xs font-medium transition
                    {{ request()->routeIs('transactions.*') ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                        clip-rule="evenodd" />
                </svg>
                Transactions
            </a>
            <a href="{{ route('categories.index') }}" wire:navigate
                class="flex-1 flex flex-col items-center gap-1 py-3 text-xs font-medium transition
                    {{ request()->routeIs('categories.*') ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M5 3a1 1 0 000 2c5.523 0 10 4.477 10 10a1 1 0 102 0C17 8.373 11.627 3 5 3z" />
                    <path d="M4 9a1 1 0 011-1 7 7 0 017 7 1 1 0 11-2 0 5 5 0 00-5-5 1 1 0 01-1-1zM3 15a2 2 0 114 0 2 2 0 01-4 0z" />
                </svg>
                Categories
            </a>
            <a href="{{ route('accounts.index') }}" wire:navigate
                class="flex-1 flex flex-col items-center gap-1 py-3 text-xs font-medium transition
                    {{ request()->routeIs('accounts.*') ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" />
                </svg>
                Accounts
            </a>
            <button type="button" onclick="openSettings()"
                class="flex-1 flex flex-col items-center gap-1 py-3 text-xs font-medium transition
                    {{ request()->routeIs('budgets.*') ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                </svg>
                More
            </button>
        </div>
    </nav>

    {{-- Flash toast --}}
    <div id="flash-toast"
        class="fixed top-16 inset-x-0 z-50 flex justify-center pointer-events-none px-4 transition-all duration-300"
        style="opacity:0;transform:translateY(-8px)">
        <div class="bg-zinc-900 dark:bg-zinc-100 text-zinc-100 dark:text-zinc-900 text-sm font-medium px-4 py-2.5 rounded-xl shadow-lg max-w-sm w-full text-center">
            <span id="flash-toast-message"></span>
        </div>
    </div>

    <script>
        (function () {
            let _flashTimer = null;

            function showFlash(message) {
                const toast = document.getElementById('flash-toast');
                const msg = document.getElementById('flash-toast-message');
                if (!toast || !msg) return;
                msg.textContent = message;
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
                clearTimeout(_flashTimer);
                _flashTimer = setTimeout(function () {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-8px)';
                }, 2500);
            }

            window.addEventListener('flash', function (e) {
                showFlash(e.detail.message ?? e.detail[0]?.message ?? '');
            });
        })();
    </script>

    @livewireScripts
</body>
</html>
