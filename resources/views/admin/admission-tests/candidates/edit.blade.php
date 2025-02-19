@extends('layouts.app')

@section('main')
    <section class="container">
        <form id="form" method="POST" novalidate
            action="{{ route('admin.admission-tests.candidates.update', ['admission_test' => $test, 'candidate' => $user]) }}">
            @csrf
            @method('put')
            <h2 class="fw-bold mb-2">Edit Candidate</h2>
            <div data-mdb-input-init class="form-outline mb-4">
                <div class="form-floating">
                    <input type="text" name="family_name" class="form-control" id="validationFamilyName" maxlength="255" placeholder="family name" value="{{ old('family_name', $user->family_name) }}" required />
                    <label for="validationFamilyName">Family Name</label>
                    <div id="familyNameFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div data-mdb-input-init class="form-outline mb-4">
                <div class="form-floating">
                    <input type="text" name="middle_name" class="form-control" id="validationMiddleName" maxlength="255" placeholder="middle name" value="{{ old('middle_name', $user->middle_name) }}" />
                    <label for="validationMiddleName">Middle Name</label>
                    <div id="middleNameFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div data-mdb-input-init class="form-outline mb-4">
                <div class="form-floating">
                    <input type="text" name="given_name" class="form-control" id="validationGivenName" maxlength="255" placeholder="given name" required value="{{ old('given_name', $user->given_name) }}" required />
                    <label for="validationGivenName">Given Name</label>
                    <div id="givenNameFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div data-mdb-input-init class="form-outline mb-4">
                <div class="form-floating">
                    <input type="text" name="gender" class="form-control" id="validationGender" list="genders" maxlength="255" placeholder="gender" required value="{{ old('gender', $user->gender->name) }}" required />
                    <label for="validationGender">Gender</label>
                    <div id="genderFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <x-datalist :id="'genders'" :values="$genders"></x-datalist>
            <div data-mdb-input-init class="form-outline mb-4">
                <div class="form-floating">
                    <select name="passport_type_id" class="form-select" id="validationPassportType" required>
                        @foreach ($passportTypes as $key => $value)
                            <option value="{{ $key }}" @selected($key == old('passport_type_id', $user->passport_type_id))>{{ $value }}</option>
                        @endforeach
                    </select>
                    <label for="validationPassportType">Passport Type</label>
                    <div id="passportTypeFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div data-mdb-input-init class="form-outline mb-4">
                <div class="form-floating">
                    <input type="text" name="passport_number" minlength="8" maxlength="18" class="form-control" id="validationPassportNumber" maxlength="255" placeholder="passport number" required value="{{ old('passport_number', $user->passport_number) }}" required />
                    <label for="validationPassportNumber">Passport Number</label>
                    <div id="passportNumberFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </div>
            <div class="mb-4">
                <input type="submit" id="saveButton" class="form-control btn btn-primary" value="Save">
                <button class="form-control btn btn-primary" id="savingButton" type="button" disabled hidden>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Saving...
                </button>
            </div>
        </form>
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/admissionTests/candidate/edit.js')
@endpush
