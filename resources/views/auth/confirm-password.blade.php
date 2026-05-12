<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <div class="mm-password-field mt-1">
                <x-text-input id="password" class="block w-full mm-form-input-password"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
                <button type="button" class="mm-password-toggle" data-toggle-password="password"
                    aria-label="Mostrar senha" aria-controls="password" aria-pressed="false">
                    <svg class="mm-password-icon mm-password-icon-show" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M1 12s4-8 11-8s11 8 11 8s-4 8-11 8s-11-8-11-8" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg class="mm-password-icon mm-password-icon-hide" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M17.94 17.94A10.95 10.95 0 0 1 12 20c-7 0-11-8-11-8a21.76 21.76 0 0 1 5.06-6.94" />
                        <path d="M9.9 4.24A10.93 10.93 0 0 1 12 4c7 0 11 8 11 8a21.8 21.8 0 0 1-3.17 4.69" />
                        <path d="M14.12 14.12a3 3 0 1 1-4.24-4.24" />
                        <path d="M1 1l22 22" />
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
