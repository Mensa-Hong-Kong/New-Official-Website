<ul class="dropdown-menu">
    @foreach($items as $id => $item)
        @isset($item['children'])
            <li class="nav-item dropend">
                <a class="nav-link dropdown-toggle" href="{{ $item['url'] ?? '#' }}" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false" id="navbarDropdown{{ $id }}">
                    {{ $item['name'] }}
                </a>
                @include('components.navbar-dropdown', ['items' => $item['children']])
            </li>
        @else
            <li>
                <a class="dropdown-item" href="{{ $item['url'] ?? '#' }}">{{ $item['name'] }}</a>
            </li>
        @endisset
    @endforeach
</ul>
