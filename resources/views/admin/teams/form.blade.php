            @csrf
            <div class="form-outline mb-4">
                <div class="form-floating">
                    <input type="text" name="name" class="form-control" id="validationName"
                        minlength="1" maxlength="255" pattern="(?!.*:).*" placeholder="name" required />
                    <label for="validationName">Name</label>
                    <div id="nameFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div class="form-outline mb-4">
                <div class="form-floating">
                    <select class="form-select" id="validationType" name="type_id" required>
                        <option value="" selected disabled>Please select type</option>
                        @foreach ($types as $key => $value)
                            <option value="{{ $key }}" @selected($key == old('type_id'))>{{ $value }}</option>
                        @endforeach
                    </select>
                    <label for="validationType" class="form-label">Type</label>
                    <div id="typeFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div class="form-outline mb-4">
                <div class="form-floating">
                    <select class="form-select" id="validationDisplayOrder" name="display_order"
                        @disabled(!old('type_id')) required>
                        <option value="" selected disabled>Please display order type</option>
                        @foreach ($displayOptions as $typeID => $array)
                            @foreach ($array as $key => $value)
                                <option value="{{ $key }}" data-typeid="{{ $typeID }}"
                                    @hidden(!(
                                        old('type_id') == $typeID &&
                                        $key == old('display_order')
                                    ))
                                    @selected(
                                        old('type_id') == $typeID &&
                                        $key == old('display_order')
                                    )>{{ $value }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    <label for="validationDisplayOrder" class="form-label">Display Order</label>
                    <div id="displayOrderFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
