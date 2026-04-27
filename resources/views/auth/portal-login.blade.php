<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <div class="mb-6 text-center">
            <h1 class="text-lg font-semibold text-gray-900">{{ __('Portal Login') }}</h1>
        </div>

        <form method="POST" action="{{ route('portal.login.store') }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="mt-4 block">
                <label for="remember_portal" class="flex items-center">
                    <x-checkbox id="remember_portal" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="mt-4 flex justify-end">
                <x-button>{{ __('Log in') }}</x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
