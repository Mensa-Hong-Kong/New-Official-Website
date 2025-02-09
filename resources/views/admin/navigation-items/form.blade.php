            @csrf
            <div class="row g-3 form-outline mb-3">
                <label for="validationMaster" class="form-label">Master</label>
                <select class="form-select" id="validationMaster" name="master_id" required>
                    <option value="" @selected(!isset($item)) disabled>Please display order master</option>
                    <option value="0" @selected(old('master_id', isset($item) && is_null($item->master_id) ? '0' : '') == "0")>root</option>
                    @include('admin.navigation-items.master-options', ['items' => $items, 'masterID' => null, 'layer' => 1, 'selected' => old('master_id', $item->master_id ?? '')])
                </select>
                <div id="masterFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="row g-3 form-outline mb-3">
                <label for="validationName" class="form-label">Name</label>
                <input type="text" name="name" class="form-control" id="validationName"
                    maxlength="255" placeholder="name..." value="{{ old('name', $item->name ?? '') }}" required />
                <div id="nameFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="row g-3 form-outline mb-3">
                <label for="validationUrl" class="form-label">URL</label>
                <input type="url" name="url" class="form-control" id="validationUrl"
                    maxlength="8000" placeholder="https://google.com" value="{{ old('url', $item->url ?? '') }}" />
                <div id="urlFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="row g-3 form-outline mb-3">
                <label for="validationDisplayOrder" class="form-label">Display Order</label>
                <select class="form-select" id="validationDisplayOrder" name="display_order" @disabled(!isset($item)) required>
                    <option value="" @selected(!isset($item)) disabled>Please display order master</option>
                    @foreach ($displayOptions as $masterID => $array)
                        @foreach ($array as $key => $value)
                            <option value="{{ $key }}" data-masterid="{{ $masterID }}"
                                @hidden($masterID != old('master_id', isset($item) && is_null($item->master_id) ? '0' : ''))
                                @selected(
                                    $masterID == old('master_id', isset($item) && is_null($item->master_id) ? '0' : '') &&
                                    $key == old('display_order', $item->display_order ?? '')
                                )>{{ $value }}</option>
                        @endforeach
                    @endforeach
                </select>
                <div id="displayOrderFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
