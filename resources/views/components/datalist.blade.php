<datalist id="{{ $id }}">
    @if ($isStringKey ?? false)
        @foreach ($values as $key => $value)
            <option value="{{ $key }}">{{ $value }}</option>
        @endforeach
    @else
        @foreach ($values as $value)
            <option value="{{ $value }}"></option>
        @endforeach
    @endif
</datalist>
