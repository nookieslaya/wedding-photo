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

      {{-- Jeśli strona ma ręcznie wpisaną treść (np. shortcode), pokaż ją pod modułami ACF. --}}
      @if (trim((string) get_post_field('post_content', get_the_ID())) !== '')
        @includeFirst(['partials.content-page', 'partials.content'])
      @endif
    @else
      @include('partials.page-header')
      @includeFirst(['partials.content-page', 'partials.content'])
    @endif
  @endwhile
@endsection
