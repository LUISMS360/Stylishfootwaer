<x-guest-layout>

    <div class="mb-4">
        <h2 class="h2 text-center mb-1">
            {{ __('Crear Cuenta') }}
        </h2>

        <p class="text-secondary text-center">
            {{ __('complete sus datos para el registro') }}
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Name --}}
        <div class="mb-3">
            <label for="name" class="form-label">
                {{ __('Name') }}
            </label>

            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                class="form-control @error('name') is-invalid @enderror"
                placeholder="Your full name"
                required
                autofocus
                autocomplete="name">

            <x-input-error
                :messages="$errors->get('name')"
                class="invalid-feedback d-block mt-1" />
        </div>

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
                placeholder="example@email.com"
                required
                autocomplete="username">

            <x-input-error
                :messages="$errors->get('email')"
                class="invalid-feedback d-block mt-1" />
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
                placeholder="Create a password"
                required
                autocomplete="new-password">

            <x-input-error
                :messages="$errors->get('password')"
                class="invalid-feedback d-block mt-1" />
        </div>

        {{-- Confirm Password --}}
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">
                {{ __('Confirm Password') }}
            </label>

            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="form-control @error('password_confirmation') is-invalid @enderror"
                placeholder="Repeat your password"
                required
                autocomplete="new-password">

            <x-input-error
                :messages="$errors->get('password_confirmation')"
                class="invalid-feedback d-block mt-1" />
        </div>

        {{-- Confirm Password --}}
        <div class="mb-3">
            <label for="role_user" class="form-label">
                {{ __('Role Usuario') }}
            </label>

            <select
                class="form-select form-select-md"
                name="role"
                id="role_user">
                <option value="">Seleccione</option>
                <option value="admin">Admin</option>
                <option value="customer">Cliente</option>
            </select>

            <x-input-error
                :messages="$errors->get('role_user')"
                class="invalid-feedback d-block mt-1" />
        </div>

        {{-- Actions --}}
        <div class="form-footer mt-4">
            <button type="submit" class="btn btn-primary w-100">
                {{ __('Register') }}
            </button>
        </div>

        <div class="text-center text-secondary mt-3">
            {{ __('Already registered?') }}

            <a href="{{ route('login') }}" class="link-primary">
                {{ __('Log in') }}
            </a>
        </div>

    </form>

</x-guest-layout>