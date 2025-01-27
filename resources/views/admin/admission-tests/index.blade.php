@extends('layouts.app')

@section('main')
    <section class="container">
        <h2 class="fw-bold mb-2 text-uppercase">Admission Tests</h2>
        @if(count($tests))
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">@sortablelink('id', '#')</th>
                        <th scope="col">@sortablelink('testing_at', 'Testing At')</th>
                        <th scope="col">Location</th>
                        <th scope="col">Candidates</th>
                        <th scope="col">Is Public</th>
                        <th scope="col">Control</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tests as $test)
                        <tr>
                            <th scope="row">{{ $test->id }}</th>
                            <td>{{ $test->testing_at }}</td>
                            <td>{{ $test->location->name }}</td>
                            <td>0/{{ $test->maximum_candidates }}</td>
                            <td>{{ $test->is_public ? 'Public' : 'Private' }}</td>
                            <td>
                                <a href="{{ route('admin.admission-tests.show', ['admission_test' => $test]) }}"
                                    class="btn btn-primary">Show</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $tests->onEachSide(4)->links() }}
        @else
            <div class="alert alert-danger" role="alert">
                No Result
            </div>
        @endif
    </section>
@endsection
