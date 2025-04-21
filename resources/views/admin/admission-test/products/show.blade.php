@extends('layouts.app')

@section('main')
    <section class="container">
        <article>
            <form id="form" method="POST" action="{{ route('admin.admission-test.products.update', ['product' => $product]) }}" novalidate>
                @csrf
                @method('put')
                <h3 class="fw-bold mb-2">
                    Info
                    <button class="btn btn-primary" id="savingButton" type="button" disabled hidden>
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Saving...
                    </button>
                    <button onclick="return false" class="btn btn-outline-primary" id="editButton">Edit</button>
                    <button type="submit" class="btn btn-outline-primary submitButton" id="saveButton" hidden>Save</button>
                    <button onclick="return false" class="btn btn-outline-danger" id="cancelButton" hidden>Cancel</button>
                </h3>
                <table class="table">
                    <tr>
                        <th>Name</th>
                        <td>
                            <span id="showName">{{ $product->name }}</span>
                            <input name="name" class="form-control" id="validationName" placeholder="name"
                                value="{{ $product->name }}" data-value="{{ $product->name }}" hidden required />
                            <div id="nameFeedback" class="valid-feedback">
                                Looks good!
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Minimum Age</th>
                        <td>
                            <span id="showMinimumAge">{{ $product->minimum_age }}</span>
                            <input type="number" name="minimum_age" class="form-control" id="validationMinimumAge" placeholder="minimum age"
                                value="{{ $product->minimum_age }}" data-value="{{ $product->minimum_age }}" hidden />
                            <div id="minimumAgeFeedback" class="valid-feedback">
                                Looks good!
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>Maximum Age</th>
                        <td>
                            <span id="showMaximumAge">{{ $product->maximum_age }}</span>
                            <input type="number" name="maximum_age" class="form-control" id="validationMaximumAge" placeholder="maximum age"
                                value="{{ $product->maximum_age }}" data-value="{{ $product->maximum_age }}" hidden />
                            <div id="maximumAgeFeedback" class="valid-feedback">
                                Looks good!
                            </div>
                        </td>
                    </tr>
                </table>
            </form>
        </article>
    </section>
@endsection

@push('after footer')
    @vite('resources/js/admin/admissionTest/products/show.js')
@endpush
