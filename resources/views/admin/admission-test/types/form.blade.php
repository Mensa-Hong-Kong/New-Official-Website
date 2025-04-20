        @csrf
        <div class="form-outline mb-4">
            <div class="form-floating">
                <input name="name" class="form-control" id="validationName" placeholder="name"
                    maxlength="255" value="{{ old('name', $type->name ?? '') }}" required />
                <label for="validationName">Name</label>
                <div id="nameFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
        </div>
        <div class="form-outline mb-4">
            <div class="form-floating">
                <input type="number" name="interval_month" class="form-control" id="validationIntervalMonth" placeholder="interval month"
                    step="1" min="0" max="60" value="{{ old('interval_month', $type->interval_month ?? 0) }}" required />
                <label for="validationIntervalMonth">Interval Month</label>
                <div id="intervalMonthFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
        </div>
        <div class="form-outline mb-4">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="isActive" name="is_active"
                    @checked(old('is_active', $type->is_active ?? true)) />
                <label class="form-check-label" for="isActive">Is Active</label>
            </div>
        </div>
        <div class="form-outline mb-4">
            <div class="form-floating">
                <select class="form-select" id="validationDisplayOrder" name="display_order" required>
                    <option value="" @selected(old('display_order', $type->display_order ?? null) === null) disabled>Please select display order</option>
                    @foreach ($types as $key => $value)
                        <option value="{{ $key }}" @selected($key === old('display_order', $type->display_order ?? ''))>
                            {{ $value }}
                        </option>
                    @endforeach
                </select>
                <label for="validationDisplayOrder" class="form-label">Display Order</label>
                <div id="displayOrderFeedback" class="valid-feedback">
                    Looks good!
                </div>
            </div>
        </div>
