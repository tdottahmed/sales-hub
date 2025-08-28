<li class="nav-item">
    @php
        $isDropdownActive = false;
        $isActive =
            request()->routeIs($route) ||
            request()->routeIs(implode('.', array_slice(explode('.', $route), 0, 1)) . '.*');
        if (!empty($dropdownRoutes)) {
            foreach ($dropdownRoutes as $route => $subLabel) {
                if (request()->routeIs($route) || request()->routeIs($route . '.*')) {
                    $isDropdownActive = true;
                    break;
                }
            }
        }
    @endphp
    <a class="nav-link menu-link {{ $isActive ? 'active' : '' }}"
        @if (!empty($dropdownRoutes)) href="#{{ Str::slug($label) }}" 
            data-bs-toggle="collapse" 
        @else 
            href="{{ route($route) }}" @endif
        role="button" aria-expanded="{{ $isDropdownActive ? 'true' : 'false' }}" aria-controls="{{ Str::slug($label) }}">
        <i class="{{ $icon }}"></i>
        <span data-key="t-{{ Str::slug($label) }}">{{ $label }}</span>
    </a>

    @if (!empty($dropdownRoutes))
        <div class="collapse menu-dropdown {{ $isDropdownActive ? 'show' : '' }}" id="{{ Str::slug($label) }}">
            <ul class="nav nav-sm flex-column">
                @foreach ($dropdownRoutes as $route => $subLabel)
                    <li class="nav-item">
                        <a href="{{ route($route) }}"
                            class="nav-link {{ str_contains(request()->route()->uri, $route) ? 'active' : '' }}"
                            data-key="t-{{ Str::slug($subLabel) }}">
                            {{ $subLabel }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</li>
