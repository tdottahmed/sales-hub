<x-data-entry.form action="{{ route('profile.update') }}" model="{{ auth()->user() }}">
    <div class="row">
        <x-data-entry.input type="text" name="name" label="Name" value="{{ auth()->user()->name }}" required />
        <x-data-entry.input type="email" name="email" label="Email" value="{{ auth()->user()->email }}" required />
        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
            <div>
                <p class="text-sm mt-2 text-gray-800">
                    {{ __('Your email address is unverified.') }}

                    <button form="send-verification"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 font-medium text-sm text-green-600">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            </div>
        @endif
    </div>
</x-data-entry.form>
