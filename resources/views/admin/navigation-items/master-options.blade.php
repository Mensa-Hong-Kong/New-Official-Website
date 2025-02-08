@foreach ($items as $item)
    @if($item->master_id == $masterID)
        <option value="{{ $item->id }}" @selected($selected == $item->id)>{!! str_repeat('&nbsp;', 4 * $layer).$item->name !!}</option>
        @include('admin.navigation-items.master-options', ['items' => $items, 'masterID' => $item->id, 'layer' => $layer + 1])
    @endif
@endforeach
