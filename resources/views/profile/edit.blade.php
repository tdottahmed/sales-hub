<x-layouts.admin.master>
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xxl-3">
                    <div class="card mt-n5">
                        <div class="card-body p-4">
                            <div class="text-center">
                                <div class="profile-user position-relative d-inline-block mx-auto  mb-4">
                                    <img src="{{ getFilePath(auth()->user()->image) }}"
                                         class="rounded-circle avatar-xl img-thumbnail user-profile-image material-shadow"
                                         alt="user-profile-image">
                                </div>
                                <h5 class="fs-16 mb-1">{{ Auth::user()->name }}</h5>
                                <p class="text-muted mb-0">{{ Auth::user()->email }}</p>
                            </div>
                            <x-data-entry.form action="{{ route('profile.update-image') }}"
                                               model="{{ auth()->user() }}">
                                <x-data-entry.uploader-filepond name="image" label="Image" />
                            </x-data-entry.form>
                        </div>
                    </div>
                    <!--end card-->
                </div>
                <!--end col-->
                <div class="col-xxl-9">
                    <div class="card mt-xxl-n5">
                        <div class="card-header">
                            <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails"
                                       role="tab">
                                        <i class="fas fa-home"></i> {{ __('Personal Details') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab">
                                        <i class="far fa-user"></i> {{ __('Change Password') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#deleteProfile" role="tab">
                                        <i class="far fa-envelope"></i> {{ __('Delete Profile') }}
                                    </a>
                                </li>

                            </ul>
                        </div>
                        <div class="card-body p-4">
                            <div class="tab-content">
                                <div class="tab-pane active" id="personalDetails" role="tabpanel">
                                    @include('profile.partials.update-profile-information-form')
                                </div>
                                <!--end tab-pane-->
                                <div class="tab-pane" id="changePassword" role="tabpanel">
                                    @include('profile.partials.update-password-form')
                                </div>
                                <div class="tab-pane" id="deleteProfile" role="tabpanel">
                                    @include('profile.partials.delete-user-form')
                                </div>
                                <!--end tab-pane-->
                            </div>
                        </div>
                    </div>
                </div>
                <!--end col-->
            </div>
        </div>
    </div>
</x-layouts.admin.master>
