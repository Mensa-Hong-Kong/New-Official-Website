@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">Users</h2>
        <div class="accordion">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button @class([
                        'accordion-button',
                        'collapsed' => !$isSearch,
                    ]) type="button" data-bs-toggle="collapse" data-bs-target="#search" aria-expanded="true" aria-controls="search">
                        Search
                    </button>
                </h2>
                <div id="search" @class([
                    'accordion-collapse',
                    'collapse',
                    'show' => $isSearch,
                ])>
                    <div class="accordion-body">
                        <form id="form" class="row g-3" novalidate>
                            <div class="col-md-4">
                                <label for="validationFamilyName" class="form-label">Family Name</label>
                                <input type="text" class="form-control" id="validationFamilyName" maxlength="255" value="{{ $append['family_name'] ?? '' }}" placeholder="family name" name="family_name" autocomplete="off" />
                                <div id="familyNameFeedback" class="valid-feedback">
                                    Looks good!
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="validationMiddleName" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="validationMiddleName" maxlength="255" value="{{ $append['middle_name'] ?? '' }}" placeholder="middle name" name="middle_name" autocomplete="off" />
                                <div id="middleNameFeedback" class="valid-feedback">
                                    Looks good!
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="validationGivenName" class="form-label">Given Name</label>
                                <input type="text" class="form-control" id="validationGivenName" maxlength="255" value="{{ $append['given_name'] ?? '' }}" placeholder="given name" name="given_name" autocomplete="off" />
                                <div id="givenNameFeedback" class="valid-feedback">
                                    Looks good!
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="validationPassportType" class="form-label">Passport Type</label>
                                <select class="form-select" id="validationPassportType" name="passport_type_id">
                                    <option value="" @selected(!isset($append['passport_type_id']))>Please select passport type</option>
                                    @foreach ($passportTypes as $key => $value)
                                        <option value="{{ $key }}" @selected($key == ($append['passport_type_id'] ?? ''))>{{ $value }}</option>
                                    @endforeach
                                </select>
                                <div id="passportTypeFeedback" class="valid-feedback">
                                    Looks good!
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="validationPassportNumber">Passport Number</label>
                                <input type="text" class="form-control" id="validationPassportNumber" minlength="8" maxlength="18" value="{{ $append['passport_number'] ?? '' }}" placeholder="passport_number" name="passport_number" autocomplete="off" />
                                <div id="passportNumberFeedback" class="valid-feedback"></div>
                            </div>
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <label for="validationGender" class="form-label">Gender</label>
                                <select class="form-select" id="validationGender" name="gender_id">
                                    <option value="" @selected(!isset($append['gender_id']))>Please select gender</option>
                                    @foreach ($genders as $genderID => $gender)
                                        <option value="{{ $genderID }}" @selected($genderID == ($append['gender_id'] ?? ''))>{{ $gender }}</option>
                                    @endforeach
                                </select>
                                <div id="genderFeedback" class="valid-feedback">
                                    Looks good!
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="validationBirthday">Date of Birth</label>
                                <input type="date" class="form-control" id="validationBirthday" name="birthday" max="{{ $maxBirthday }}" value="{{ $append['gender_id'] ?? '' }}" />
                                <div id="birthdayFeedback" class="valid-feedback"></div>
                            </div>
                            <div class="col-md-4"></div>
                            <div class="col-md-4">
                                <label for="validationEmail">Email</label>
                                <input type="email" class="form-control" id="validationEmail" maxlength="320" placeholder="dammy@example.com" name="email" value="{{ old('email') }}" autocomplete="off" />
                                <div id="emailFeedback" class="valid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="validationMobile">Mobile</label>
                                <input type="tel" class="form-control" id="validationMobile" minlength="5" maxlength="15" placeholder="85298765432" name="mobile" value="{{ old('mobile') }}" autocomplete="off" />
                                <div id="mobileFeedback" class="valid-feedback"></div>
                            </div>
                            <input type="submit" id="submitButton" class="form-control btn btn-primary" value="Search">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @if(count($users))
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Gender</th>
                        <th scope="col">@sortablelink('created_at', 'Created At')</th>
                        <th scope="col">@sortablelink('updated_at', 'Updated At')</th>
                        <th scope="col">@sortablelink('lastLoginLogs.created_at', 'Last Login Time')</th>
                        <th scope="col">Control</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <th scope="row">{{ $user->id }}</th>
                            <td>
                                @if(strlen($user->name) > 32)
                                    {{ substr($user->name, 0, 29) }}...
                                @else
                                    {{ $user->name }}
                                @endif
                            </td>
                            <td>
                                @if(strlen($user->gender->name) > 32)
                                    {{ substr($user->gender->name, 0, 29) }}...
                                @else
                                    {{ $user->gender->name }}
                                @endif
                            </td>
                            <td>{{ $user->created_at }}</td>
                            <td>{{ $user->updated_at }}</td>
                            <td>{{ $user->lastLoginLogs->created_at ?? '--' }}</td>
                            <td>
                                <a href="{{ route('admin.users.show', ['user' => $user]) }}">
                                    <button class="btn btn-primary">Show</button>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $users->onEachSide(4)->links() }}
        @else
            <div class="alert alert-danger" role="alert">
                No Result
            </div>
        @endif
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/users/index.js')
@endpush
