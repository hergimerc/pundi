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

    {{-- Bottom navigation --}}
    <nav class="fixed bottom-0 inset-x-0 z-10 bg-white dark:bg-zinc-800 border-t border-zinc-200 dark:border-zinc-700">
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
<a href="{{ route('accounts.index') }}" wire:navigate
                class="flex-1 flex flex-col items-center gap-1 py-3 text-xs font-medium transition
                    {{ request()->routeIs('accounts.*') ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400 dark:text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" />
                </svg>
                Accounts
            </a>
        </div>
    </nav>

    @livewireScripts
</body>
</html>
