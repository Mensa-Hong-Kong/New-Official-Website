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
                        <tr>
                            <th scope="row">{{ $page->pathname }}</th>
                            <td>{{ $page->title }}</td>
                            <td>{{ $page->created_at }}</td>
                            <td>{{ $page->updated_at }}</td>
                            <td>
                                <a href="{{ route('custom-page', ['pathname' => $page->pathname]) }}"
                                    class="btn btn-primary">Show</a>
                                <a href="{{ route('admin.custom-pages.edit', ['custom_page' => $page]) }}"
                                    class="btn btn-primary">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $pages->onEachSide(4)->links() }}
        @else
            <div class="alert alert-danger" role="alert">
                No Result
            </div>
        @endif
    </section>
@endsection
