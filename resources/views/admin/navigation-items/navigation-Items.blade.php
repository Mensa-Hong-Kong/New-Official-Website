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
                    <form method="POST" id="deleteItemForm{{ $item->id }}" hidden
                        action="{{ route('admin.navigation-items.destroy', ['navigation_item' => $item]) }}">
                        @csrf
                        @method('delete')
                    </form>
                    @if($item->url)
                        <a href="{{ $item->url }}" class="btn button btn-primary showUrl">Url</a>
                        <button class="btn button btn-primary disabledUrl" hidden>Url</button>
                    @else
                        <button class="btn button btn-secondary">Url</button>
                    @endif
                    <a href="{{ route('admin.navigation-items.edit', ['navigation_item' => $item]) }}" class="btn button btn-primary showEdit">Edit</a>
                    <button class="btn button btn-secondary disabledEdit" hidden>Edit</button>
                    <span class="spinner-border spinner-border-sm itemLoader" id="itemLoader{{ $item->id }}" role="status" aria-hidden="true"></span>
                    <button class="btn btn-danger submitButton" form="deleteItemForm{{ $item->id }}" id="deleteItem{{ $item->id }}" data-name="{{ $item->name }}" hidden>Delete</button>
                    <button class="btn btn-danger" id="deletingItem{{ $item->id }}" hidden disabled>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Deleting...
                    </button>
                </div>
            </div>
            @isset($item->children)
                @include('admin.navigation-items.navigation-items', ['items' => $item->children, 'id' => $item->id])
            @endisset
        </li>
    @endforeach
</ol>
