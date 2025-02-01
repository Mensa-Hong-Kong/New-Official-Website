@extends('layouts.app')

@section('main')
    <section class="container">
        <article>
            <form id="form" method="POST" action="{{ route('admin.admission-tests.update', ['admission_test' => $test]) }}" novalidate>
                @csrf
                @method('put')
                <h3 class="fw-bold mb-2">
                    Info
                    <button class="btn btn-primary" id="savingButton" type="button" disabled hidden>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Saving...
                    </button>
                    <button onclick="return false" class="btn btn-outline-primary" id="editButton">Edit</button>
                    <button type="submit" class="btn btn-outline-primary submitButton" id="saveButton" hidden>Save</button>
                    <button onclick="return false" class="btn btn-outline-danger" id="cancelButton" hidden>Cancel</button>
                </h3>
                <table class="table">
                    <tr>
                        <th>Testing At</th>
                        <td>
                            <span id="showTestingAt">{{ $test->testing_at }}</span>
                            <input type="datetime-local" name="testing_at" class="form-control" id="validationTestingAt" placeholder="testing at"
                                value="{{ $test->testing_at }}" data-value="{{ $test->testing_at }}" hidden required />
                            <div id="testingAtFeedback" class="valid-feedback">
                                Looks good!
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td>
                            <span id="showLocation">{{ $test->location->name }}</span>
                            <input type="text" name="location" class="form-control" id="validationLocation" maxlength="255" placeholder="location"
                                data-value="{{ $test->location->name }}" value="{{ $test->location->name }}" list="locations" hidden required />
                            <div id="locationFeedback" class="valid-feedback">
                                Looks good!
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>District</th>
                        <td>
                            <span id="showDistrict">{{ $test->address->district->area->name }}</span>
                            <select class="form-select" id="validationDistrict" name="district_id" data-value="{{ $test->address->district_id }}" hidden required>
                                <option value="" disabled>Please district</option>
                                @foreach ($districts as $area => $array)
                                    <optgroup label="{{ $area }}">
                                        @foreach ($array as $key => $value)
                                            <option value="{{ $key }}" @selected($key === $test->address->district_id)>
                                                {{ $value }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            <div id="districtFeedback" class="valid-feedback">
                                Looks good!
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td>
                            <span id="showAddress">{{ $test->address->address }}</span>
                            <input type="text" name="address" class="form-control" id="validationAddress" maxlength="255" placeholder="address"
                                data-value="{{ $test->address->address }}" value="{{ $test->address->address }}" list="addresses" required hidden />
                            <div id="addressFeedback" class="valid-feedback">
                                Looks good!
                            </div>
                            <x-datalist :id="'addresses'" :values="$addresses"></x-datalist>
                        </td>
                    </tr>
                    <tr>
                        <th>Maximum Candidates</th>
                        <td>
                            <span id="showMaximumCandidates">{{ $test->testing_at }}</span>
                            <input type="number" name="maximum_candidates" class="form-control" id="validationMaximumCandidates"
                                min="1" step="1" placeholder="maximum candidates" data-value="{{ $test->maximum_candidates }}" value="{{ $test->maximum_candidates }}" required hidden />
                            <div id="maximumCandidatesFeedback" class="valid-feedback">
                                Looks good!
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Current Candidates</th>
                        <td>0</td>
                    </tr>
                    <tr>
                        <th>Is Public</th>
                        <td>
                            <span id="showIsPublic">{{ $test->is_public ? 'Public' : 'Private' }}</span>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="isPublic" name="is_public"
                                    @checked($test->is_public) data-value="{{ $test->is_public }}" hidden />
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </article>
        @can('View:User')
            <article id="proctor">
                <h3 class="fw-bold mb-2">Proctors</h3>
                <div class="row g-3">
                    <div class="col-md-2">User ID</div>
                    <div class="col-md-4">Name</div>
                    <div class="col-md-6">Control</div>
                </div>
                <form class="row g-3" id="createProctorForm" method="POST" novalidate
                    action="{{ route('admin.admission-tests.proctors.store', ['admission_test' => $test]) }}">
                    @csrf
                    <input type="text" id="proctorUserIdInput" class="col-md-2" name="user_id" list="users" required />
                    <div class="col-md-4" id="proctorName"></div>
                    <div class="col-md-6">
                        <button class="btn btn-success form-control submitButton" id="addProctorButton">Add</button>
                        <button class="btn btn-success form-control" id="addingProctorButton" hidden disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Adding
                        </button>
                    </div>
                </form>
            </article>
            <x-datalist :id="'users'" :values="$users" isStringKey="true"></x-datalist>
        @endcan
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/admissionTests/show.js')
@endpush
