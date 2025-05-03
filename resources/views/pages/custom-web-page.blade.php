@extends('layouts.app')

@section('title', $page->title)

@section('description', $page->description)

@isset($page->og_image_url)
    @section('og image url', $page->og_image_url)
@endisset

@section('main')
    <section class="container">
        @vite('resources/css/ckEditor.css')
        <article class="ck-content">
            {!! $page->content !!}
        </article>
    </section>
@endsection
