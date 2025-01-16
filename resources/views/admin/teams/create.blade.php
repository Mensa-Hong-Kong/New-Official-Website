@extends('layouts.app')

@section('main')
    <section class="container">
        <form id="form" method="POST" action="{{ route('admin.teams.store') }}" novalidate>
            <h2 class="fw-bold mb-2 text-uppercase">Create Team</h2>
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
            <input type="submit" id="createButton" class="form-control btn btn-success" value="Create">
            <div class="alert alert-danger" id="createFeedback" role="alert" hidden></div>
            <button class="form-control btn btn-primary" id="creatingButton" type="button" disabled hidden>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Creating...
            </button>
        </form>
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/teams/create.js')
@endpush
