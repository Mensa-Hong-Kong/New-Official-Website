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
                        <th>Expect End At</th>
                        <td>
                            <span id="showExpectEndAt">{{ $test->expect_end_at }}</span>
                            <input type="datetime-local" name="expect_end_at" class="form-control" id="validationExpectEndAt" placeholder="expect end at"
                                value="{{ $test->expect_end_at }}" data-value="{{ $test->expect_end_at }}" hidden required />
                            <div id="expectEndAtFeedback" class="valid-feedback">
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
                        <td id="showCurrentCandidates">{{ $test->candidates->count() }}</td>
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
            @can('Edit:Admission Test')
                <article id="proctor">
                    <h3 class="fw-bold mb-2">Proctors</h3>
                    <div class="row g-3">
                        <div class="col-md-1">User ID</div>
                        <div class="col-md-2">Name</div>
                        <div class="col-md-3">Control</div>
                    </div>
                    @foreach ($test->proctors as $proctor)
                        <div class="row g-3" id="showProctor{{ $proctor->id }}">
                            <form method="POST" id="deleteProctorForm{{ $proctor->id }}" action="{{ route('admin.admission-tests.proctors.destroy', ['admission_test' => $test, 'proctor' => $proctor]) }}" hidden>
                                @csrf
                                @method('DELETE')
                            </form>
                            <div class="col-md-1" id="showProctorId{{ $proctor->id }}">{{ $proctor->id }}</div>
                            <div class="col-md-2" id="showProctorName{{ $proctor->id }}">{{ $proctor->name }}</div>
                            <a class="btn btn-primary col-md-1" id="showProctorLink{{ $proctor->id }}"
                                href="{{ route('admin.users.show', ['user' => $proctor]) }}">Show</a>
                            <span class="spinner-border spinner-border-sm proctorLoader" id="proctorLoader{{ $proctor->id }}" role="status" aria-hidden="true"></span>
                            <button class="btn btn-primary col-md-1" id="editProctor{{ $proctor->id }}" hidden>Edit</button>
                            <button class="btn btn-danger col-md-1" id="deleteProctor{{ $proctor->id }}" form="deleteProctorForm{{ $proctor->id }}" hidden>Delete</button>
                        </div>
                        <form class="row g-3" id="editProctorForm{{ $proctor->id }}" method="POST" novalidate hidden
                            action="{{ route('admin.admission-tests.proctors.update', ['admission_test' => $test, 'proctor' => $proctor]) }}">
                            @csrf
                            @method('PUT')
                            <input type="text" id="proctorUserIdInput{{ $proctor->id }}" class="col-md-1" name="user_id" value="{{ $proctor->id }}" data-value="{{ $proctor->id }}" required />
                            <div class="col-md-2" id="proctorName{{ $proctor->id }}">{{ $proctor->name }}</div>
                            <button class="btn btn-primary col-md-1 submitButton" id="saveProctor{{ $proctor->id }}">Save</button>
                            <button class="btn btn-primary col-md-1" id="savingProctor{{ $proctor->id }}" disabled hidden>Save</button>
                            <button class="btn btn-danger col-md-1" id="cancelEditProctor{{ $proctor->id }}" onclick="return false">Cancel</button>
                        </form>
                    @endforeach
                    <form class="row g-3" id="createProctorForm" method="POST" novalidate
                        action="{{ route('admin.admission-tests.proctors.store', ['admission_test' => $test]) }}">
                        @csrf
                        <input type="text" id="proctorUserIdInput" class="col-md-1" name="user_id" required />
                        <div class="col-md-2"></div>
                        <button class="btn btn-success col-md-3 submitButton" id="addProctorButton">Add</button>
                        <button class="btn btn-success col-md-3" id="addingProctorButton" hidden disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Adding
                        </button>
                    </form>
                </article>
            @endcan
        @endcan
        @if(
            $test->inTestingTimeRange() ||
            auth()->user()->can('View:User')
        )
            <article id="candidate">
                <h3 class="fw-bold mb-2">Candidates</h3>
                <div class="row g-3">
                    <div class="col-md-1">User ID</div>
                    <div class="col-md-1">Gender</div>
                    <div class="col-md-2">Name</div>
                    <div class="col-md-2">Passport Type</div>
                    <div class="col-md-2">Passport Number</div>
                    <div class="col-md-3">Control</div>
                </div>
                @foreach ($test->candidates as $candidate)
                    <div class="row g-3">
                        <div class="col-md-1">{{ $candidate->id }}</div>
                        <div class="col-md-1">{{ $candidate->gender->name }}</div>
                        <div class="col-md-2">{{ $candidate->name }}</div>
                        <div class="col-md-2">{{ $candidate->passportType->name }}</div>
                        <div @class([
                            'col-md-2',
                            'text-warning' => $candidate->hasOtherUserSamePassportJoinedFutureTest(),
                            'text-danger' => $candidate->hasSamePassportTestedTwoTimes() ||
                                $candidate->hasSamePassportAlreadyQualificationOfMembership() ||
                                $candidate->hasSamePassportTestedWithinDateRange(
                                    $test->testing_at->subMonths(6), now()
                                ),
                        ])>{{ $candidate->passport_number }}</div>
                        @can('View:User')
                            <a class="btn btn-primary col-md-1" id="showCandidateLink{{ $candidate->id }}"
                                href="{{ route('admin.users.show', ['user' => $candidate]) }}">Show</a>
                        @else
                            <a class="btn btn-primary col-md-1" id="showCandidateLink{{ $candidate->id }}"
                                href="{{ route('admin.admission-tests.candidates.show', ['admission_test' => $test, 'candidate' => $candidate]) }}">Show</a>
                        @endcan
                    </div>
                @endforeach
                @can('Edit:Admission Test')
                    <form class="row g-3" id="createCandidateForm" method="POST" novalidate
                        action="{{ route('admin.admission-tests.candidates.store', ['admission_test' => $test]) }}">
                        @csrf
                        <input type="text" id="candidateUserIdInput" class="col-md-1" name="user_id" required />
                        <div class="col-md-7"></div>
                        <button class="btn btn-success col-md-3 submitButton" id="addCandidateButton">Add</button>
                        <button class="btn btn-success col-md-3" id="addingCandidateButton" hidden disabled>
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            Adding
                        </button>
                    </form>
                @endcan
            </article>
        @endif
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/admissionTests/show.js')
@endpush
