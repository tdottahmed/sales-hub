<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ getFilePath(getSetting('app_favicon')) }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ getFilePath(getSetting('app_logo')) }}" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ getFilePath(getSetting('app_favicon')) }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ getFilePath(getSetting('app_logo')) }}" alt="" height="17">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>
    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                <x-layouts.admin.partials.sidebar-menu-item route="dashboard" icon="ri-dashboard-line"
                    label="Dashboard" />

                <x-layouts.admin.partials.sidebar-menu-item route="driffleProducts.index" icon="ri-store-line"
                                                            label="Driffle Products" :dropdown-routes="[
                        'driffleProducts.index' => 'Driffle Products',
                        'driffleProducts.mapProducts' => 'Mapped Products',
                        'driffleProducts.manualMap' => 'Manual Map',
                    ]"/>
                <x-layouts.admin.partials.sidebar-menu-item route="supplierProducts.index" icon="ri ri-store-3-line"
                                                            label="Supplier Products" />
                <x-layouts.admin.partials.sidebar-menu-item route="offers.create" icon="ri ri-award-line"
                                                            label="Offer Management" :dropdown-routes="[
                        'offers.create' => 'Create Driffle Offer',
                        'offers.index' => 'Existing Offers',
                    ]" />

                <x-layouts.admin.partials.sidebar-menu-item route="applicationSetup.index" icon="ri-settings-3-line"
                    label="Settings" />

            </ul>
        </div>
    </div>
    <div class="sidebar-background"></div>
</div>
