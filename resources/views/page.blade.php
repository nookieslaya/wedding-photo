@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    @if (! empty($flexibleModules))
      @foreach ($flexibleModules as $module)
        @php($layout = $module['acf_fc_layout'] ?? null)

        @if ($layout)
          @includeIf("modules.{$layout}", ['module' => $module])
        @endif
      @endforeach
    @else
      @include('partials.page-header')
      @includeFirst(['partials.content-page', 'partials.content'])
    @endif
  @endwhile
@endsection
