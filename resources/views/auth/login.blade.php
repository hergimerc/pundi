<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In — {{ config('app.name', 'Pundi') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-50 flex items-center justify-center p-4">

    <div class="w-full max-w-sm">

        <div class="mb-8 text-center">
            <h1 class="text-2xl font-semibold text-zinc-900 tracking-tight">{{ config('app.name', 'Pundi') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">Personal expense tracker</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xs border border-zinc-200 p-8">

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                <div class="space-y-5">

                    <div>
                        <label for="email" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Email
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            autocomplete="email"
                            autofocus
                            value="{{ old('email') }}"
                            class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition
                                {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-200' : 'border-zinc-300 bg-white focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200' }}"
                            placeholder="you@example.com"
                        >
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Password
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            class="w-full rounded-lg border px-3.5 py-2.5 text-sm text-zinc-900 placeholder-zinc-400 outline-none transition
                                {{ $errors->has('password') ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-2 focus:ring-red-200' : 'border-zinc-300 bg-white focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200' }}"
                            placeholder="••••••••"
                        >
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-zinc-300 text-zinc-900 accent-zinc-800 cursor-pointer"
                        >
                        <label for="remember" class="text-sm text-zinc-600 cursor-pointer select-none">
                            Remember me
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-lg bg-zinc-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-zinc-700 active:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-500 focus:ring-offset-2 cursor-pointer"
                    >
                        Sign in
                    </button>

                </div>
            </form>

        </div>

    </div>

</body>
</html>
