@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">Custom Pages</h2>
        @if(count($pages))
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">@sortablelink('pathname', 'Pathname')</th>
                        <th scope="col">@sortablelink('title', 'Title')</th>
                        <th scope="col">@sortablelink('created_at', 'Created At')</th>
                        <th scope="col">@sortablelink('updated_at', 'Updated At')</th>
                        <th scope="col">Control</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pages as $page)
                        <tr id="row{{ $page->id }}">
                            <th scope="row">{{ $page->pathname }}</th>
                            <td>{{ $page->title }}</td>
                            <td>{{ $page->created_at }}</td>
                            <td>{{ $page->updated_at }}</td>
                            <td>
                                <form id="deleteForm{{ $page->id }}" method="POST" hidden
                                    action="{{ route('admin.custom-pages.destroy', ['custom_page' => $page]) }}">
                                    @csrf
                                    @method('DELETE')
                                </form>
                                <a href="{{ route('custom-page', ['pathname' => $page->pathname]) }}"
                                    class="btn btn-primary">Show</a>
                                <a href="{{ route('admin.custom-pages.edit', ['custom_page' => $page]) }}"
                                    class="btn btn-primary">Edit</a>
                                <span class="spinner-border spinner-border-sm pageLoader" id="pageLoader{{ $page->id }}" role="status" aria-hidden="true"></span>
                                <button form="deleteForm{{ $page->id }}" id="delete{{ $page->id }}" class="btn btn-danger submitButton"
                                    data-title="{{ $page->title }}" hidden>Delete</button>
                                <button class="btn btn-danger" id="deleting{{ $page->id }}" hidden disabled>
                                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Deleting...
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-danger" role="alert">
                No Result
            </div>
        @endif
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/custom-pages/index.js')
@endpush
