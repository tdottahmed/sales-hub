<x-data-entry.form action="{{ route('password.update') }}" model="{{ auth()->user() }}">
    <div class="row">
        <x-data-entry.input type="password" name="current_password" label="Current Password" required />
        <x-data-entry.input type="password" name="password" label="New Password" required />
        <x-data-entry.input type="password" name="password_confirmation" label="Confirm Password" required />
    </div>
</x-data-entry.form>
