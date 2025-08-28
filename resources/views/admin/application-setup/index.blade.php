<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">{{ __('Application Setup') }}</h5>
            </div>
        </x-slot>
        <x-data-entry.form action="{{ route('applicationSetup.update') }}">
            <x-data-entry.input type="text" name="app_name" label="Organization Name" placeholder="App Name"
                value="{{ $applicationSetup->where('type', 'app_name')->first()->value ?? '' }}" required />
            <x-data-entry.input type="text" name="app_email" label="Organization Email" placeholder="App Email"
                value="{{ $applicationSetup->where('type', 'app_email')->first()->value ?? '' }}" required />
            <x-data-entry.input type="tel" name="app_phone" label="Organization Phone" placeholder="App Phone"
                value="{{ $applicationSetup->where('type', 'app_phone')->first()->value ?? '' }}" required />
            <x-data-entry.text-area name="app_address" label="Organization Address" placeholder="App Address"
                value="{{ $applicationSetup->where('type', 'app_address')->first()->value ?? '' }}"></x-data-entry.text-area>
            <div class="img-fluid">
                <img src="{{ $applicationSetup->where('type', 'app_logo')->first()->value ?? '' }}" alt="">
            </div>
            <img class="img-thumbnail" alt="Logo" width="200"
                src="{{ getFilePath($applicationSetup->where('type', 'app_logo')->first()->value ?? '') }}"
                data-holder-rendered="true">
            <x-data-entry.uploader-filepond name="app_logo" label="Organization Logo" />
            <img class="img-thumbnail" alt="Favicon" width="80"
                src="{{ getFilePath($applicationSetup->where('type', 'app_favicon')->first()->value ?? '') }}"
                data-holder-rendered="true">
            <x-data-entry.uploader-filepond name="app_favicon" label="Organization Favicon" />
            <img class="img-thumbnail" alt="Login Banner" width="200"
                src="{{ getFilePath($applicationSetup->where('type', 'login_banner')->first()->value ?? '') }}"
                data-holder-rendered="true">
            <x-data-entry.uploader-filepond name="login_banner" label="Organization Login Banner" />
        </x-data-entry.form>
    </x-data-display.card>
</x-layouts.admin.master>
