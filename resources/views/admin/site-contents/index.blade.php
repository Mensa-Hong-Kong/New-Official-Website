@extends('layouts.app')

@section('main')
    <section class="container">
        <div class="accordion">
            @foreach ($pages as $page)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" aria-expanded="true"
                            data-bs-target="#page{{ $page->id }}" aria-controls="page{{ $page->id }}"
                            data-bs-toggle="collapse">
                            {{ $page->name }}
                        </button>
                    </h2>
                    <div id="page{{ $page->id }}" class="accordion-collapse collapse show">
                        <div class="accordion-body">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Control</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($page->contents as $content)
                                        <tr>
                                            <td scope="row">{{ $content->id }}</td>
                                            <td>{{ $content->name }}</td>
                                            <td>
                                                <a href="{{ route('admin.site-contents.edit', ['site_content' => $content]) }}"
                                                    class="btn btn-primary">Edit</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
@endsection
