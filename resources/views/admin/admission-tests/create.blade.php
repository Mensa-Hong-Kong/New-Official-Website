@extends('layouts.app')

@section('main')
    <section class="container">
        <form id="form" method="POST" action="{{ route('admin.admission-tests.store') }}" novalidate>
            <h2 class="fw-bold mb-2 text-uppercase">Create Admission Test</h2>
            @csrf
            <div class="form-outline mb-4">
                <div class="form-floating">
                    <input type="datetime-local" name="testing_at" class="form-control" id="validationTestingAt" placeholder="testing at"
                        value="{{ old('testing_at', date('Y-m-d\TH:i')) }}" required />
                    <label for="validationTestingAt">Testing At</label>
                    <div id="testingAtFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div class="form-outline mb-4">
                <div class="form-floating">
                    <input type="text" name="location" class="form-control" id="validationLocation" maxlength="255"
                        placeholder="location" value="{{ old('location') }}" list="locations" required />
                    <label for="validationLocation">Location</label>
                    <div id="locationFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                    <x-datalist :id="'locations'" :values="$locations"></x-datalist>
                </div>
            </div>
            <div class="form-outline mb-4">
                <div class="form-floating">
                    <select class="form-select" id="validationDistrict" name="district_id" required>
                        <option value="" @selected(old('district_id') === null) disabled>Please district</option>
                        @foreach ($districts as $area => $array)
                            <optgroup label="{{ $area }}">
                                @foreach ($array as $key => $value)
                                    <option value="{{ $key }}" @selected($key === old('district_id'))>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <label for="validationDistrict" class="form-label">District</label>
                    <div id="districtFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div class="form-outline mb-4">
                <div class="form-floating">
                    <input type="text" name="address" class="form-control" id="validationAddress" maxlength="255"
                        placeholder="address" value="{{ old('address') }}" list="addresses" required />
                    <label for="validationAddress">Address</label>
                    <div id="addressFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                    <x-datalist :id="'addresses'" :values="$addresses"></x-datalist>
                </div>
            </div>
            <div class="form-outline mb-4">
                <div class="form-floating">
                    <input type="number" name="maximum_candidates" class="form-control" id="validationMaximumCandidates"
                        min="1" step="1" placeholder="maximum candidates" value="{{ old('maximum_candidates', 1) }}" required />
                    <label for="validationMaximumCandidates">Maximum Candidates</label>
                    <div id="maximumCandidatesFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div class="form-outline mb-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="isPublic" name="is_public"
                        @checked(old('is_public', true)) />
                    <label class="form-check-label" for="isPublic">Is Public</label>
                </div>
            </div>
            <input type="submit" id="createButton" class="form-control btn btn-success" value="Create">
            <button class="form-control btn btn-success" id="creatingButton" type="button" disabled hidden>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Creating...
            </button>
        </form>
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/admissionTests/create.js')
@endpush
