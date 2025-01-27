            @csrf
            <div class="form-outline mb-4">
                <div class="form-floating">
                    <input type="text" name="name" class="form-control" id="validationName"
                        minlength="1" maxlength="170" pattern="(?!.*:).*" placeholder="name"
                        value="{{ old('name', $role['name'] ?? '') }}" list="roles" required />
                    <label for="validationName">Name</label>
                    <div id="nameFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                    <x-datalist :id="'roles'" :values="$roles"></x-datalist>
                </div>
            </div>
            <div class="form-outline mb-4">
                <div class="form-floating">
                    <select class="form-select" id="validationDisplayOrder" name="display_order"
                        @disabled(!old('type_id', $team->type_id ?? '')) required>
                        <option value="" @selected(old('display_order', $role->pivot->display_order ?? null) === null) disabled>Please display order type</option>
                        @foreach ($displayOptions as $key => $value)
                            <option value="{{ $key }}" @selected($key === old('display_order', $role->pivot->display_order ?? ''))>
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
            <div class="form-outline mb-4">
                <label class="form-label">Permissions</label>
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th></th>
                            @foreach ($permissions as $permission)
                                <th>{{ $permission->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($modules as $module)
                            <tr>
                                <th>{{ $module->name }}</th>
                                @foreach ($permissions as $permission)
                                    @isset($modulePermissions[$module->id][$permission->id])
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" name="module_permissions[]"
                                                    value="{{ $modulePermissions[$module->id][$permission->id] }}"
                                                    class="form-check-input permission"
                                                    @checked($roleHasModulePermissions[$modulePermissions[$module->id][$permission->id]] ?? false) />
                                            </div>
                                        </td>
                                    @else
                                        <td></td>
                                    @endisset
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
