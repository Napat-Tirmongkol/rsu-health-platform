<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <div class="mb-6 text-center">
            <h1 class="text-lg font-semibold text-gray-900">{{ __('Admin Login') }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ __('Use your approved Google account.') }}</p>
        </div>

        <a href="{{ route('auth.google') }}" class="inline-flex w-full items-center justify-center rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2">
            {{ __('Continue with Google') }}
        </a>
    </x-authentication-card>
</x-guest-layout>
