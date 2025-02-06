@foreach ($items as $item)
    @if($item->master_id == $masterID)
        <option value="{{ $item->id }}">{!! str_repeat('&nbsp;', 4 * $layer).$item->name !!}</option>
        @include('admin.navigation-items.master-option', ['items' => $items, 'masterID' => $item->id, 'layer' => $layer + 1])
    @endif
@endforeach
