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
                            <td>{{ $test->candidates()->count() }}/{{ $test->maximum_candidates }}</td>
                            <td>{{ $test->is_public ? 'Public' : 'Private' }}</td>
                            <td>
                                @if(
                                    $test->inTestingTimeRange() ||
                                    auth()->user()->can('Edit:Admission Test')
                                )
                                    <a href="{{ route('admin.admission-tests.show', ['admission_test' => $test]) }}"
                                        class="btn btn-primary showTest">Show</a>
                                    <button class="btn btn-secondary disabledShowTest" hidden>Show</button>
                                    <form id="deleteTestForm{{ $test->id }}" hidden method="POST"
                                        action="{{ route('admin.admission-tests.destroy', ['admission_test' => $test]) }}">
                                        @csrf
                                        @method("delete")
                                    </form>
                                    <span class="spinner-border spinner-border-sm testLoader" id="testLoader{{ $test->id }}" role="status" aria-hidden="true"></span>
                                    <button class="btn btn-danger submitButton" id="deleteTest{{ $test->id }}" form="deleteTestForm{{ $test->id }}" hidden
                                        data-location="{{ $test->location->name }}" data-testingat>Delete</button>
                                @else
                                    <button class="btn btn-secondary">Show</button>
                                @endif
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

@push('after footer')
    @vite('resources/js/admin/admissionTests/index.js')
@endpush
