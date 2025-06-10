<ul class="navbar-nav me-auto">
    @foreach ($items as $id => $item)
        @isset($item['children'])
            <li class="nav-item dropdown">
                <button class="nav-link dropdown-toggle" id="dropdown{{ $id }}"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ $item['name'] }}
                </button>
                @include('components.navbar-dropdown', ['items' => $item['children'], 'id' => $id])
            </li>
        @else
            <li class="nav-item">
                <a href="{{ $item['url'] ?? '#' }}" class="nav-link">{{ $item['name'] }}</a>
            </li>
        @endisset
    @endforeach
</ul>
