<ol @class(['root sortable ui-sortable mjs-nestedSortable-branch mjs-nestedSortable-expanded' => $id == 0])
    id="root_{{ $id }}">
    @foreach ($items as $item)
        <li class="list-group-item mjs-nestedSortable-branch mjs-nestedSortable-expanded" id="menuItem_{{ $item->id }}">
            <div class="menuDiv row">
                <div class="col">
                    <span title="Click to show/hide children" class="disclose ui-icon ui-icon-minusthick">
                        <span></span>
                    </span>
                    <span class="itemTitle">{{ $item->name }}</span>
                </div>
                <div class="col text-end">
                    @if($item->url)
                        <a href="{{ $item->url }}" class="btn button btn-secondary">Url</a>
                    @else
                        <button class="btn button btn-secondary">Url</button>
                    @endif
                </div>
            </div>
            @isset($item->children)
                @include('admin.navigation-items.navigation-items', ['items' => $item->children, 'id' => $item->id])
            @endisset
        </li>
    @endforeach
</ol>
