
@foreach ($contacts as $contact)
    <div class="row g-3" id="showContactRow{{ $contact->id }}">
        <span class="col-md-3" id="contact{{ $contact->id }}">{{ $contact->contact }}</span>
        <form class="col-md-2" id="changeVerifyContactStatusForm{{ $contact->id }}" method="POST"
            action="{{ route('admin.contacts.verify', ['contact' => $contact]) }}">
            @csrf
            @method('put')
            <button id="verifyContactStatus{{ $contact->id }}"
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
        <div class="col-md-2">
            <span class="{{ $contact->type }}DefaultContact"
                id="defaultContact{{ $contact->id }}"
                @hidden(! $contact->is_default)>
                Default
            </span>
        </div>
        <div class="contactLoader col-md-1" id="contactLoader{{ $contact->id }}">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        </div>
        <button class="btn btn-primary col-md-1" id="editContact{{ $contact->id }}" hidden>Edit</button>
    </div>
    @can('Edit:User')
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
            <input type="checkbox" class="btn-check isVerifiedContactCheckbox" id="isVerifiedContactCheckbox{{ $contact->id }}" @checked($contact->isVerified())>
            <label class="btn btn-outline-success col-md-2" for="isVerifiedContactCheckbox{{ $contact->id }}">Verified</label>
            <input type="checkbox" class="btn-check isDefaultContactCheckbox" id="isDefaultContactCheckbox{{ $contact->id }}" @checked($contact->is_default) data-value="{{ $contact->is_default }}">
            <label class="btn btn-outline-success col-md-2" for="isDefaultContactCheckbox{{ $contact->id }}">Default</label>
            <button class="btn btn-primary col-md-1 submitButton" id="saveContact{{ $contact->id }}" form="editContactForm{{ $contact->id }}">Save</button>
            <button class="btn btn-danger col-md-1" id="cancelEditContact{{ $contact->id }}" onclick="return false;">Cancel</button>
            <button class="btn btn-primary col-md-2" id="savingContact{{ $contact->id }}" hidden disabled>
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Saving
            </button>
        </form>
    @endcan
@endforeach
