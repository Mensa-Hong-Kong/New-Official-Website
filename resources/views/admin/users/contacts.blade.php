
@foreach ($contacts as $contact)
    @can('Edit:User')
        <div class="row g-3" id="showContactRow{{ $contact->id }}">
            <span class="col-md-3" id="contact{{ $contact->id }}">{{ $contact->contact }}</span>
            <form class="col-md-2" id="changeVerifyContactStatusForm{{ $contact->id }}" method="POST"
                action="{{ route('admin.contacts.verify', ['contact' => $contact]) }}">
                @csrf
                @method('put')
                <button id="verifyContactStatus{{ $contact->id }}" hidden
                    name="status" value="{{ (int) ! $contact->isVerified() }}"
                    @class([
                        'btn',
                        'form-control',
                        'btn-success' => $contact->isVerified(),
                        'btn-danger' => ! $contact->isVerified(),
                        'submitButton',
                    ])>
                    {{ $contact->isVerified() ? 'Verified' : 'Not Verified'}}
                </button>
                <button class="btn btn-primary form-control" id="changingVerifyContactStatus{{ $contact->id }}" hidden disabled>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Changing...
                </button>
            </form>
            <form class="col-md-2" id="changeContactDefaultStatusForm{{ $contact->id }}" method="POST"
                action="{{ route('admin.contacts.default', ['contact' => $contact]) }}">
                @csrf
                @method('put')
                <button id="contactDefaultStatus{{ $contact->id }}" hidden
                    name="status" value="{{ (int) ! $contact->is_default }}"
                    @class([
                        'btn',
                        'form-control',
                        'btn-success' => $contact->is_default,
                        'btn-danger' => ! $contact->is_default,
                        'submitButton',
                        '{{ $contact->type }}DefaultContact',
                    ])>
                    {{ $contact->is_default ? 'Default' : 'Non Default'}}
                </button>
                <button class="btn btn-primary form-control" id="changingContactDefaultStatus{{ $contact->id }}" hidden disabled>
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Changing...
                </button>
            </form>
            <div class="contactLoader col-md-1" id="contactLoader{{ $contact->id }}">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            </div>
            <button class="btn btn-primary col-md-1" id="editContact{{ $contact->id }}" hidden>Edit</button>
            <form id="deleteContactForm{{ $contact->id }}" method="POST" hidden
                action="{{ route('admin.contacts.destroy', ['contact' => $contact]) }}">
                @csrf
                @method('delete')
            </form>
            <button class="btn btn-danger col-md-1 submitButton" id="deleteContact{{ $contact->id }}" form="deleteContactForm{{ $contact->id }}" hidden>Delete</button>
        </div>
        <form class="row g-3" id="editContactForm{{ $contact->id }}" method="POST" hidden
            action="{{ route('admin.contacts.update', ['contact' => $contact]) }}">
            @csrf
            @method('put')
            <input id="contactInput{{ $contact->id }}" class="col-md-3"
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
            <div class=" col-md-2">
                <input type="checkbox" class="btn-check" id="isVerifiedContactCheckbox{{ $contact->id }}" @checked($contact->isVerified())>
                <label class="form-control btn btn-outline-success" for="isVerifiedContactCheckbox{{ $contact->id }}">Verified</label>
            </div>
            <div class=" col-md-2">
                <input type="checkbox" class="btn-check {{ $contact->type }}DefaultContactCheckbox" id="isDefaultContactCheckbox{{ $contact->id }}" @checked($contact->is_default)>
                <label class="form-control btn btn-outline-success" for="isDefaultContactCheckbox{{ $contact->id }}">Default</label>
            </div>
            <button class="btn btn-primary col-md-1 submitButton" id="saveContact{{ $contact->id }}">Save</button>
            <button class="btn btn-danger col-md-1" id="cancelEditContact{{ $contact->id }}" onclick="return false;">Cancel</button>
            <button class="btn btn-primary col-md-2" id="savingContact{{ $contact->id }}" hidden disabled>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Saving
            </button>
        </form>
    @else
        <div class="row g-3">
            <span class="col-md-3">{{ $contact->contact }}</span>
        </div>
    @endcan
@endforeach
@can('Edit:User')
    <form class="row g-3 createContact" data-type="{{ $type }}" id="{{ $type }}CreateForm"
        action="{{ route('admin.contacts.store') }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="user_id" value="{{ $userID }}">
        <input type="hidden" name="type" value="{{ $type }}">
        <input id="{{ $type }}ContactInput" class="col-md-3"
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
        <div class=" col-md-2">
            <input type="checkbox" class="btn-check" id="{{ $type }}IsVerifiedCheckbox">
            <label class="form-control btn btn-outline-success" for="{{ $type }}IsVerifiedCheckbox">Verified</label>
        </div>
        <div class=" col-md-2">
            <input type="checkbox" class="btn-check" id="{{ $type }}IsDefaultCheckbox">
            <label class="form-control btn btn-outline-success" for="{{ $type }}IsDefaultCheckbox">Default</label>
        </div>
        <button class="btn btn-success col-md-2 submitButton" id="{{ $type }}CreateButton">Create</button>
        <button class="btn btn-success col-md-2" id="{{ $type }}CreatingContact" hidden disabled>
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Creating
        </button>
    </form>
@endcan
