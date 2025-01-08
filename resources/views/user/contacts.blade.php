@foreach ($contacts as $contact)
    <div class="row g-3" id="contactRow{{ $contact->id }}"
        data-requset-verify-code-url="{{ route('contacts.send-verify-code', ['contact' => $contact]) }}">
        <div class="col-md-3">
            <span id="contact{{ $contact->id }}">{{ $contact->contact }}</span>
            <form id="editContactForm{{ $contact->id }}" method="POST" novalidate hidden
                action="{{ route('contacts.update', ['contact' => $contact]) }}">
                <input id="contactInput{{ $contact->id }}" class="form-control"
                    @switch($contact->type)
                        @case('email')
                            type="email" name="email" maxlength="320"
                            placeholder="dammy@example.com"
                            @break
                        @case('mobile')
                            type="tel" name="mobile" minlength="5" maxlength="15"
                            placeholder="85298765432"
                            @break
                    @endswitch
                    value="{{ $contact->contact }}"
                    data-value="{{ $contact->contact }}" required />
            </form>
        </div>
        <div class="col-md-2">
            <span class="{{ $contact->type }}DefaultContact"
                id="defaultContact{{ $contact->id }}"
                data-type="{{ $contact->type }}"
                @hidden(! $contact->is_default)>
                Default
            </span>
            <form id="setDefault{{ $contact->id }}" class="{{ $contact->type }}SetDefault" method="POST"
                action="{{ route('contacts.set-default', ['contact' => $contact]) }}" hidden>
                @csrf
                @method('put')
                <button class="btn btn-primary submitButton">Set Default</button>
            </form>
            <button class="btn btn-primary" id="settingDefault{{ $contact->id }}" disabled hidden>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Setting
            </button>
        </div>
        <div class="col-md-2">
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
        </div>
        <button id="verifyContactButton{{ $contact->id }}" hidden @class([
            'btn',
            'col-md-1',
            'btn-secondary' => $contact->isVerified(),
            'btn-primary' => !$contact->isVerified(),
            'submitButton' => !$contact->isVerified(),
        ])>{{ $contact->isVerified() ? 'Verified' : 'Verify' }}</button>
        <button class="btn btn-primary col-md-2 submitButton requestNewVerifyCodeButton" id="requestNewVerifyCode{{ $contact->id }}" hidden>
            Send New Verify Code
        </button>
        <button class="btn btn-primary col-md-4" id="requestingContactButton{{ $contact->id }}" hidden disabled>
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Requesting...
        </button>
        <button class="btn btn-primary col-md-1 submitButton" id="submitVerifyCode{{ $contact->id }}" form="verifyContactForm{{ $contact->id }}" hidden>Submit</button>
        <button class="btn btn-danger col-md-1" id="cancelVerify{{ $contact->id }}" hidden>Cancel</button>
        <button class="btn btn-danger col-md-4" id="submittingContactButton{{ $contact->id }}" hidden disabled>
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Submitting...
        </button>
        <button class="btn btn-primary col-md-1" id="editContact{{ $contact->id }}" hidden>Edit</button>
        <button class="btn btn-primary col-md-1 submitButton" id="saveContact{{ $contact->id }}" form="editContactForm{{ $contact->id }}" hidden>Save</button>
        <button class="btn btn-danger col-md-1" id="cancelEditContact{{ $contact->id }}" hidden>Cancel</button>
        <button class="btn btn-primary col-md-2" id="savingContact{{ $contact->id }}" hidden disabled>
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Saving...
        </button>
        <form id="deleteContactForm{{ $contact->id }}" method="POST" hidden
            action="{{ route('contacts.destroy', ['contact' => $contact]) }}">
            @csrf
            @method('delete')
        </form>
        <button class="btn btn-danger col-md-1 submitButton" id="deleteContact{{ $contact->id }}" form="deleteContactForm{{ $contact->id }}" hidden>Delete</button>
    </div>
@endforeach
<form class="row g-3 createContact" data-type="{{ $type }}" id="{{ $type }}CreateForm"
    action="{{ route('contacts.store') }}" method="POST" novalidate>
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">
    <div class="col-md-3">
        <input id="{{ $type }}ContactInput" class="form-control"
            @switch($type)
                @case('email')
                    type="email" maxlength="320"
                    placeholder="dammy@example.com"
                    @break
                @case('mobile')
                    type="tel" minlength="5" maxlength="15"
                    placeholder="85298765432"
                    @break
            @endswitch
            name="contact" required />
    </div>
    <div class="col-md-4"></div>
    <button class="btn btn-success col-md-4 submitButton" id="{{ $type }}CreateButtob">Create</button>
    <button class="btn btn-success col-md-4" id="{{ $type }}CreatingContact" hidden disabled>
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        Creating
    </button>
</form>
