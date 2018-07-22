<li class="nav-item {{ $class ?? '' }}">
    @if(empty($route) || '-' === $route)
        <span class="nav-link {{ $contentClass ?? '' }}" {{ $options ?? '' }}>
            {{ $slot }}
        </span>
    @else
        <a class="nav-link @if(Request::fullUrl() == $route) active @endif {{ $contentClass or '' }}" href="{{ $route }}" {{ $options ?? '' }}>
            {{ $slot }}
        </a>
    @endif
    {{ $body or '' }}
</li>