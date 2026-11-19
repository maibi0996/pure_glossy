<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Correo" class="text-base" style="color: #8F5578;" />
            <x-text-input id="email" class="block mt-1 w-full rounded-xl" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" style="background-color: #FFFFFF; border: 1px solid #F38ECA; color: #3B2635;" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Contraseña" class="text-base" style="color: #8F5578;" />

            <x-text-input id="password" class="block mt-1 w-full rounded-xl"
                type="password"
                name="password"
                required autocomplete="current-password"
                style="background-color: #FFFFFF; border: 1px solid #F38ECA; color: #3B2635;" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded-md border-pink-300 text-pink-500 shadow-sm focus:ring-pink-400" name="remember">
                <span class="ms-2 text-base" style="color: #70405D;">Recuérdame</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline rounded-md focus:outline-none focus:ring-2"
                    href="{{ route('password.request') }}"
                    style="color: #70405D; font-size: 14px;"
                    onmouseover="this.style.color='#E879B8'"
                    onmouseout="this.style.color='#70405D'">
                        Olvidé mi contraseña
                </a>
            @endif

            <x-primary-button
                class="ms-3 rounded-xl px-6 py-3"
                style="background-color: #F38ECA; color: #3B2635; border: 1px solid #E879B8; font-family: 'Courier Prime', monospace;"
            >
                {{ __('Ingresar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
