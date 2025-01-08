@extends('layouts.app')

@section('main')
    <section class="container">
        <article>
            <form method="POST" class="row g-3" id="form" novalidate>
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
                <div class="col-md-4">
                    <label for="validationUsername" class="form-label">Usermame</label>
                    <div id="showUsername" class="showInfo">{{ $user->username }}</div>
                    <input type="text" class="form-control" id="validationUsername" minlength="8" maxlength="16" value="{{ old('username', $user->username) }}" placeholder="username" required hidden />
                    <div id="usernameFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="validationPassword" class="form-label">Password</label>
                    <div id="showPassword">********</div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <label for="validationFamilyName" class="form-label">Family Name</label>
                    <div id="showFamilyName">{{ $user->family_name }}</div>
                    <input type="text" class="form-control" id="validationFamilyName" maxlength="255" value="{{ old('family_name', $user->family_name) }}" placeholder="family name" name="family_name" required hidden />
                    <div id="familyNameFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="validationMiddleName" class="form-label">Middle Name</label>
                    <div id="showMiddleName">{{ $user->middle_name }}</div>
                    <input type="text" class="form-control" id="validationMiddleName" maxlength="255" value="{{ old('middle_name', $user->middle_name) }}" placeholder="middle name" name="middle_name" hidden />
                    <div id="middleNameFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="validationGivenName" class="form-label">Given Name</label>
                    <div id="showGivenName">{{ $user->given_name }}</div>
                    <input type="text" class="form-control" id="validationGivenName" maxlength="255" value="{{ old('given_name', $user->family_name) }}" placeholder="given name" name="given_name" required hidden />
                    <div id="givenNameFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="validationPassportType" class="form-label">Passport Type</label>
                    <div id="showPassportType">{{ $user->passportType->name }}</div>
                    <select class="form-select" id="validationPassportType" name="passport_type_id" required hidden>
                        @foreach ($passportTypes as $key => $value)
                            <option value="{{ $key }}" @selected($key == old('passport_type_id', $user->passport_type_id))>{{ $value }}</option>
                        @endforeach
                    </select>
                    <div id="passportTypeFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="validationPassportNumber">Passport Number</label>
                    <div id="showPassportNumber">{{ $user->passport_number }}</div>
                    <input type="text" class="form-control" id="validationPassportNumber" minlength="8" maxlength="18" value="{{ old('passport_number', $user->passport_number) }}" placeholder="passport_number" name="passport_number" required hidden />
                    <div id="passportNumberFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <label for="validationGender" class="form-label">Gender</label>
                    <div id="showGender">{{ $user->gender->name }}</div>
                    <input type="text" class="form-control" id="validationGender" list="genders" maxlength="255" value="{{ old('genders', $user->gender->name) }}" placeholder="genders" name="genders" required hidden />
                    <div id="genderFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
                <x-datalist :id="'genders'" :values="$genders"></x-datalist>
                <div class="col-md-4">
                    <label for="validationBirthday">Date of Birth</label>
                    <div id="showBirthday">{{ $user->birthday }}</div>
                    <input type="date" class="form-control" id="validationBirthday" name="birthday" max="{{ $maxBirthday }}" value="{{ old('birthday', $user->birthday) }}" required hidden />
                    <div id="birthdayFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
            </form>
        </article>
        <article id="email">
            <h3 class="fw-bold mb-2"><i class="bi bi-envelope"></i> Email</h3>
            @include('admin.users.contacts', ['contacts' => $user->emails, 'type' => 'email', 'userID' => $user->id])
        </article>
        <article id="mobile">
            <h3 class="fw-bold mb-2"><i class="bi bi-phone"></i> Mobile</h3>
            @include('admin.users.contacts', ['contacts' => $user->mobiles, 'type' => 'mobile', 'userID' => $user->id])
        </article>
    </section>
@endsection
@can('Edit:User')
    @push('after footer')
        @vite('resources/js/admin/users/show.js')
    @endpush
@endcan
