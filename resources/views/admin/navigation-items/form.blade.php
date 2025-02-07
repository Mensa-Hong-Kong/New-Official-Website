            @csrf
            <div class="row g-3 form-outline mb-3">
                <label for="validationMaster" class="form-label">Master</label>
                <select class="form-select" id="validationMaster" name="master_id" required>
                    <option value="" selected disabled>Please display order master</option>
                    <option value="0">root</option>
                    @include('admin.navigation-items.master-options', ['items' => $items, 'masterID' => null, 'layer' => 1])
                </select>
                <div id="masterFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="row g-3 form-outline mb-3">
                <label for="validationName" class="form-label">Name</label>
                <input type="text" name="name" class="form-control" id="validationName"
                    maxlength="255" placeholder="name..." value="{{ old('name') }}" required />
                <div id="nameFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="row g-3 form-outline mb-3">
                <label for="validationUrl" class="form-label">URL</label>
                <input type="url" name="url" class="form-control" id="validationUrl"
                    maxlength="8000" placeholder="https://google.com" value="{{ old('url') }}" />
                <div id="urlFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
            <div class="row g-3 form-outline mb-3">
                <label for="validationDisplayOrder" class="form-label">Display Order</label>
                <select class="form-select" id="validationDisplayOrder" name="display_order" disabled required>
                    <option value="" selected disabled>Please display order master</option>
                    @foreach ($displayOptions as $masterID => $array)
                        @foreach ($array as $key => $value)
                            <option value="{{ $key }}" data-masterid="{{ $masterID }}">{{ $value }}</option>
                        @endforeach
                    @endforeach
                </select>
                <div id="displayOrderFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
