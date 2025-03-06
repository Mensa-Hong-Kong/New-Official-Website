@extends('layouts.app')

@section('main')
    <section class="container">
        <div class="accordion">
            @foreach ($pages as $page)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#page{{ $page->id }}" aria-expanded="true" aria-controls="page{{ $page->id }}">
                        Accordion Item #1
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
                                            <td></td>
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
