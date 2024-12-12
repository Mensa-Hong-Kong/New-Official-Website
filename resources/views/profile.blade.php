@extends('layouts.app')

@section('main')
    <section class="container">
        <article>
            <h2 class="fw-bold mb-2 text-uppercase">Profile</h2>
            <form method="POST" class="row g-3" id="form" novalidate>
                <h3 class="fw-bold mb-2">
                    Info
                    <button class="btn btn-primary" id="savingButton" type="button" disabled hidden>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Saving...
                    </button>
                    <button onclick="return false" class="btn btn-outline-primary" id="editButton">Edit</button>
                    <button type="submit" class="btn btn-outline-primary" id="saveButton" hidden>Save</button>
                    <button onclick="return false" class="btn btn-outline-danger" id="cancelButton" hidden>Cancel</button>
                </h3>
                <div class="alert alert-primary" id="editInfoRemind" role="alert" hidden>
                    <ol>
                        <li>Password only require when you change the username or password</li>
                        <li>New password and confirm password is not require unless you want to change a new password</li>
                    </ol>
                </div>
                @csrf
                @method('put')
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
                    <input type="password" class="form-control" id="validationPassword" minlength="8" maxlength="16" placeholder="password" name="password" required hidden />
                    <div id="passwordFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
                <div class="col-md-4"></div>
                <div class="col-md-4 newPasswordColumn" hidden>
                    <label for="validationNewPassword" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="validationNewPassword" minlength="8" maxlength="16" placeholder="New password" name="new_password" />
                    <div id="newPasswordFeedback" class="valid-feedback">
                        Looks good!
                    </div>
                </div>
                <div class="col-md-4 newPasswordColumn" hidden>
                    <label for="confirmNewPassword" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirmNewPassword" minlength="8" maxlength="16" placeholder="confirm new password" name="new_password_confirmation" />
                </div>
                <div class="col-md-4 newPasswordColumn" hidden></div>
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
                    <input type="text" class="form-control" id="validationMiddleName" maxlength="255" value="{{ old('middle_name', $user->family_name) }}" placeholder="middle name" name="middle_name" hidden />
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
                    <div id="passportNumberFeedback" class="valid-feedback"></div>
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
                    <div id="birthdayFeedback" class="valid-feedback"></div>
                </div>
            </form>
        </article>
        <article>
            <h3 class="fw-bold mb-2"><i class="bi bi-envelope"></i> Email</h3>
            @foreach ($contacts['emails'] ?? [] as $email)
                <div class="row">
                    <div class="col">{{ $email->contact }}</div>
                </div>
            @endforeach
        </article>
        <article>
            <h3 class="fw-bold mb-2"><i class="bi bi-phone"></i> Mobile</h3>
            @foreach ($contacts['mobiles'] ?? [] as $mobile)
                <div class="row">
                    <div class="col">{{ $mobile->contact }}</div>
                </div>
            @endforeach
        </article>
    </section>
@endsection

@push('after footer')
    @vite('resources/js/profile.js')
@endpush
