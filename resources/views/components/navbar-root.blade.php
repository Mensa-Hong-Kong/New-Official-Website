<ul class="navbar-nav me-auto">
    @foreach ($items as $id => $item)
        @isset($item['children'])
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="{{ $item['url'] ?? '#' }}" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    {{ $item['name'] }}
                </a>
                @include('components.navbar-dropdown', ['items' => $item['children']])
            </li>
        @else
            <li class="nav-item">
                <a href="{{ $item['url'] ?? '#' }}" class="nav-link">{{ $item['name'] }}</a>
            </li>
        @endisset
    @endforeach
</ul>
