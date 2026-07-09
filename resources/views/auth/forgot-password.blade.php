<x-guest-layout>
    <div class="mb-6">
        <h2 class="font-outfit font-bold text-3xl text-charcoal mb-2">Reset Password</h2>
        <p class="text-charcoal/60 text-sm leading-relaxed">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-8 flex items-center justify-between">
            <a class="text-sm font-medium text-charcoal/60 hover:text-charcoal focus:outline-none transition-colors" href="{{ route('login') }}">
                &larr; {{ __('Back to login') }}
            </a>
            <x-primary-button>
                {{ __('Send Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
