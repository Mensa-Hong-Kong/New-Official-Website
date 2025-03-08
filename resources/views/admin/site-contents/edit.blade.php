@extends('layouts.app')

@section('main')
    <form id="form" method="POST" action="{{ route('admin.site-contents.update', ['site_content' => $content]) }}" novalidate>
        <h2 class="fw-bold mb-2 text-uppercase">Edit Site Content - {{ $content->page->name }} - {{ $content->name }}</h2>
        @csrf
        @method('put')
        <div class="row g-3 form-outline mb-3">
            <label for="validationContent" class="form-label">Content</label>
            <textarea name="content" id="validationContent" maxlength="65535" required>
                {!! old('content', $content->content) !!}
            </textarea>
            <div id="contentFeedback" class="valid-feedback">
                Looks good!
            </div>
        </div>
        <input type="submit" id="saveButton" class="form-control btn btn-primary" value="Save">
        <button class="form-control btn btn-primary" id="savingButton" type="button" disabled hidden>
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Saving...
        </button>
    </form>
@endsection

@push('after footer')
    @vite('resources/js/admin/siteContents/edit.js')
@endpush
