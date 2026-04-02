@extends('layouts.app')

@section('content')
  @if (is_post_type_archive('event'))
    @include('partials.event-archive-list')
  @else
    @include('partials.page-header')

    @if (! have_posts())
      <x-alert type="warning">
        {!! __('Sorry, no results were found.', 'sage') !!}
      </x-alert>

      {!! get_search_form(false) !!}
    @endif

    @while(have_posts()) @php(the_post())
      @includeFirst(['partials.content-' . get_post_type(), 'partials.content'])
    @endwhile

    {!! get_the_posts_navigation() !!}
  @endif
@endsection

@if (! is_post_type_archive('event'))
  @section('sidebar')
    @include('sections.sidebar')
  @endsection
@endif
