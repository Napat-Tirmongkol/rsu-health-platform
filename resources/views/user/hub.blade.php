<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'RSU Health Platform') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-100 font-sans text-gray-900 antialiased">
        <main class="mx-auto flex min-h-screen max-w-5xl flex-col px-4 py-8 sm:px-6 lg:px-8">
            <header class="flex items-center justify-between border-b border-gray-200 pb-4">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">{{ __('User Hub') }}</h1>
                    <p class="mt-1 text-sm text-gray-600">{{ auth('user')->user()->name }}</p>
                </div>

                <form method="POST" action="{{ route('user.logout') }}">
                    @csrf
                    <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
                        {{ __('Log out') }}
                    </button>
                </form>
            </header>

            <section class="py-8">
                <div class="rounded-md border border-gray-200 bg-white p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-gray-900">{{ __('Welcome back') }}</h2>
                    <p class="mt-2 text-sm text-gray-600">{{ __('Your booking and clinic services will appear here as the user portal is migrated.') }}</p>
                </div>
            </section>
        </main>
    </body>
</html>
