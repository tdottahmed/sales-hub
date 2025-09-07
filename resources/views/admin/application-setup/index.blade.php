<x-layouts.admin.master>
    <x-data-display.card>
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">{{ __('Settings') }}</h5>
            </div>
        </x-slot>

        <ul class="nav nav-pills gap-2 mb-3 flex-wrap" role="tablist">
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link active d-flex align-items-center gap-2 px-3 py-2 rounded-3 shadow-sm"
                    id="tab-app-info-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#tab-app-info"
                    type="button"
                    role="tab"
                    aria-controls="tab-app-info"
                    aria-selected="true"
                >
                    <i class="ri-information-line fs-5"></i>
                    <span class="text-start">
                        <span class="fw-semibold d-block">{{ __('Application Info') }}</span>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link d-flex align-items-center gap-2 px-3 py-2 rounded-3"
                    id="tab-api-setup-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#tab-api-setup"
                    type="button"
                    role="tab"
                    aria-controls="tab-api-setup"
                    aria-selected="false"
                >
                    <i class="ri-plug-line fs-5"></i>
                    <span class="text-start">
                        <span class="fw-semibold d-block">{{ __('API Setup') }}</span>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link d-flex align-items-center gap-2 px-3 py-2 rounded-3"
                    id="tab-smtp-setup-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#tab-smtp-setup"
                    type="button"
                    role="tab"
                    aria-controls="tab-smtp-setup"
                    aria-selected="false"
                >
                    <i class="ri-mail-settings-line fs-5"></i>
                    <span class="text-start">
                        <span class="fw-semibold d-block">{{ __('SMTP Setup') }}</span>
                    </span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button
                    class="nav-link d-flex align-items-center gap-2 px-3 py-2 rounded-3"
                    id="tab-other-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#tab-other"
                    type="button"
                    role="tab"
                    aria-controls="tab-other"
                    aria-selected="false"
                >
                    <i class="ri-more-2-line fs-5"></i>
                    <span class="text-start">
                        <span class="fw-semibold d-block">{{ __('Other') }}</span>
                    </span>
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-app-info" role="tabpanel" aria-labelledby="tab-app-info-tab" tabindex="0">
                <x-data-entry.form action="{{ route('applicationSetup.update') }}" method="post">
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

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-3-line me-1"></i> {{ __('Save Application Info') }}
                        </button>
                    </div>
                </x-data-entry.form>
            </div>

            <div class="tab-pane fade" id="tab-api-setup" role="tabpanel" aria-labelledby="tab-api-setup-tab" tabindex="0">
                <x-data-entry.form action="{{ route('settings.updateEnv') }}" method="post">
                    <x-data-entry.input
                        type="url"
                        name="SUPPLIER_BASE_URL"
                        label="{{ __('Supplier Base URL') }}"
                        placeholder="https://Business.ozchest.com/v1/getProducts"
                        value="{{ config('credentials.supplier.base_url') }}"
                        required
                    />
                    <x-data-entry.input
                        type="text"
                        name="SUPPLIER_API_KEY"
                        label="{{ __('Supplier API Key') }}"
                        placeholder="Your supplier API key"
                        value="{{ config('credentials.supplier.api_key') }}"
                        required
                    />

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-3-line me-1"></i> {{ __('Save API Settings') }}
                        </button>
                        <button type="button" class="btn btn-soft-secondary" onclick="document.getElementById('tab-api-setup-tab').click()">
                            <i class="ri-arrow-go-back-line me-1"></i> {{ __('Back') }}
                        </button>
                    </div>
                </x-data-entry.form>
            </div>

            <div class="tab-pane fade" id="tab-smtp-setup" role="tabpanel" aria-labelledby="tab-smtp-setup-tab" tabindex="0">
                <x-data-entry.form action="{{ route('settings.updateEnv') }}" method="post">
                    <x-data-entry.input type="text" name="MAIL_MAILER" label="Mailer" placeholder="smtp"
                        value="{{ config('mail.mailer') ?? 'log' }}" required />
                    <x-data-entry.input type="text" name="MAIL_HOST" label="Host" placeholder="127.0.0.1"
                        value="{{ config('mail.host') ?? '127.0.0.1' }}" required />
                    <x-data-entry.input type="number" name="MAIL_PORT" label="Port" placeholder="2525"
                        value="{{ config('mail.port') ?? 2525 }}" required />
                    <x-data-entry.input type="text" name="MAIL_USERNAME" label="Username" placeholder="null"
                        value="{{ config('mail.username') }}" />
                    <x-data-entry.input type="password" name="MAIL_PASSWORD" label="Password" placeholder="null"
                        value="{{ config('mail.password') }}" />
                    <x-data-entry.input type="text" name="MAIL_ENCRYPTION" label="Encryption" placeholder="tls/ssl"
                        value="{{ config('mail.encryption') }}" />
                    <x-data-entry.input type="email" name="MAIL_FROM_ADDRESS" label="From Address" placeholder="hello@example.com"
                        value="{{ config('mail.from.address') }}" />
                    <x-data-entry.input type="text" name="MAIL_FROM_NAME" label="From Name" placeholder="{{ config('app.name') }}"
                        value="{{ config('mail.from.name') }}" />

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-3-line me-1"></i> {{ __('Save SMTP Settings') }}
                        </button>
                        <button type="button" class="btn btn-soft-secondary" onclick="document.getElementById('tab-smtp-setup-tab').click()">
                            <i class="ri-arrow-go-back-line me-1"></i> {{ __('Back') }}
                        </button>
                    </div>
                </x-data-entry.form>
            </div>

            <div class="tab-pane fade" id="tab-other" role="tabpanel" aria-labelledby="tab-other-tab" tabindex="0">
                {{-- Add your other miscellaneous settings here --}}
            </div>
        </div>
    </x-data-display.card>
</x-layouts.admin.master>
