@extends('layouts.app')

@section('content')
  @if (is_post_type_archive('event') || request()->path() === 'wydarzenia')
    @include('partials.event-archive-list')
  @else
    @include('partials.post-archive-list')
  @endif
@endsection
