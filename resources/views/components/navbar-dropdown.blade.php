<ul class="dropdown-menu" aria-labelledby="dropdown{{ $id }}">
    @foreach($items as $id => $item)
        @isset($item['children'])
            <li class="dropdown dropend">
                <a class="dropdown-item dropdown-toggle" href="{{ $item['url'] ?? '#' }}" role="button"
                    id="dropdown{{ $id }}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    {{ $item['name'] }}
                </a>
                @include('components.navbar-dropdown', ['items' => $item['children'], 'id' => $id])
            </li>
        @else
            <li>
                <a class="dropdown-item" href="{{ $item['url'] ?? '#' }}">{{ $item['name'] }}</a>
            </li>
        @endisset
    @endforeach
</ul>
