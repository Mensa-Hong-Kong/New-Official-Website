@foreach ($contacts as $contact)
    <div class="row g-4" id="contactRow{{ $contact->id }}"
        @if(! $contact->isVerified())
            data-requsetVerifyCodeUrl="{{ route('contacts.send-verify-code', ['contact' => $contact]) }}"
        @endif
        >
        <div class="col-md-3">{{ $contact->contact }}</div>
        <div class="col-md-2">
            <span
                class="{{ $contact->type }}DefaultContact"
                id="defaultContact{{ $contact->id }}"
                data-type="{{ $contact->type }}"
                @hidden(! $contact->is_default)>
                Default
            </span>
            <form id="setDefault{{ $contact->id }}" class="{{ $contact->type }}SetDefault" method="POST"
                action="{{ route('contacts.default', ['contact' => $contact]) }}" hidden>
                @csrf
                @method('put')
                <button class="btn btn-primary submitButton">Set Default</button>
            </form>
            <button class="btn btn-primary" id="settingDefault{{ $contact->id }}" disabled hidden>Setting</button>
        </div>
        <div class="col-md-2">
            @if(! $contact->isVerified())
                <div class="contactLoader" id="contactLoader{{ $contact->id }}">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                </div>
                <form id="verifyContactForm{{ $contact->id }}" hidden novalidate
                    action="{{ route('contacts.verify', ['contact' => $contact]) }}"
                    method="POST">
                    @csrf
                    <input type="text" name="code" class="form-control" id="verifyCodeInput{{ $contact->id }}"
                        minlength="6" maxlength="6" pattern="[A-Za-z0-9]{6}" required
                        autocomplete="off" placeholder="Verify Code" />
                </form>
            @endif
        </div>
        <button id="verifyContactButton{{ $contact->id }}" hidden @class([
            'btn',
            'col-md-1',
            'btn-secondary' => $contact->isVerified(),
            'btn-primary' => !$contact->isVerified(),
            'submitButton' => !$contact->isVerified(),
        ])>{{ $contact->isVerified() ? 'Verified' : 'Verify' }}</button>
        @if(!$contact->isVerified())
            <button class="btn btn-primary col-md-2 submitButton requestNewVerifyCodeButton" id="requestNewVerifyCode{{ $contact->id }}" hidden>
                Send New Verify Code
            </button>
            <button class="btn btn-primary col-md-4" id="requestingContactButton{{ $contact->id }}" hidden disabled>Requesting</button>
            <button class="btn btn-primary col-md-1 submitButton" id="submitVerifyCode{{ $contact->id }}" form="verifyContactForm{{ $contact->id }}" hidden>Submit</button>
            <button class="btn btn-danger col-md-1" id="cancelVerify{{ $contact->id }}" hidden>Cancel</button>
            <button class="btn btn-danger col-md-4" id="submittingContactButton{{ $contact->id }}" hidden disabled>Submitting</button>
        @endif
    </div>
@endforeach
