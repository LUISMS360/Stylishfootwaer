<x-guest-layout>
    {{-- Session Status --}}
    <x-auth-session-status class="alert alert-success mb-3" :status="session('status')" />

    <div class="mb-4">
        <h2 class="h2 text-center mb-1">
            {{ __('Inicio de Sesión') }}
        </h2>

        <p class="text-secondary text-center">
            {{ __('Ingrese con sus credenciales') }}
        </p>
    </div>
    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email Address --}}
        <div class="mb-3">
            <label for="email" class="form-label">
                {{ __('Email') }}
            </label>

            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="form-control @error('email') is-invalid @enderror"
                placeholder="tu@correo.com"
                required
                autofocus
                autocomplete="username">

            <x-input-error :messages="$errors->get('email')" class="invalid-feedback d-block mt-1" />
        </div>

        {{-- Password --}}
        <div class="mb-3">
            <label for="password" class="form-label">
                {{ __('Password') }}
            </label>

            <input
                id="password"
                type="password"
                name="password"
                class="form-control @error('password') is-invalid @enderror"
                placeholder="Contraseña"
                required
                autocomplete="current-password">

            <x-input-error :messages="$errors->get('password')" class="invalid-feedback d-block mt-1" />
        </div>

        {{-- Remember Me --}}
        <div class="mb-3">
            <label class="form-check">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="form-check-input"
                    name="remember">

                <span class="form-check-label">
                    {{ __('Remember me') }}
                </span>
            </label>
        </div>

        {{-- Actions --}}
        <div class="d-flex align-items-center justify-content-between mt-4">

            @if (Route::has('password.request'))
            <a
                href="{{ route('password.request') }}"
                class="link-secondary">
                {{ __('Forgot your password?') }}
            </a>
            @endif

            <button type="submit" class="btn btn-primary">
                {{ __('Log in') }}
            </button>

        </div>
    </form>
</x-guest-layout>