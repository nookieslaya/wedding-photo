@php
  $sectionTitle = $module['section_title'] ?? null;
  $heading = $module['heading'] ?? null;
  $description = $module['description'] ?? null;
  $image = $module['image'] ?? null;
  $stats = $module['stats'] ?? [];
  $button = $module['button'] ?? null;
@endphp

<section class="about-us-module py-16 md:py-24">
  <div class="grid gap-10 md:grid-cols-2 md:items-start">
    <div class="space-y-6">
      @if ($sectionTitle)
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-stone-500">
          {{ $sectionTitle }}
        </p>
      @endif

      @if ($heading)
        <h2 class="max-w-xl text-3xl font-semibold leading-tight text-stone-900 md:text-5xl">
          {{ $heading }}
        </h2>
      @endif

      @if ($description)
        <div class="max-w-lg text-base leading-relaxed text-stone-700 md:text-lg">
          {!! nl2br(e($description)) !!}
        </div>
      @endif

      @if (! empty($stats))
        <ul class="grid max-w-lg grid-cols-2 gap-4">
          @foreach ($stats as $stat)
            <li class="rounded-2xl border border-stone-200 bg-white p-4">
              <p class="text-xs uppercase tracking-[0.12em] text-stone-500">
                {{ $stat['label'] ?? '' }}
              </p>
              <p class="mt-2 text-2xl font-semibold text-stone-900">
                {{ $stat['value'] ?? '' }}
              </p>
            </li>
          @endforeach
        </ul>
      @endif

      @if (! empty($button['url']) && ! empty($button['title']))
        <a
          href="{{ $button['url'] }}"
          target="{{ $button['target'] ?? '_self' }}"
          class="inline-flex items-center gap-2 rounded-full border border-stone-900 px-6 py-3 text-sm font-semibold text-stone-900 transition hover:bg-stone-900 hover:text-white"
        >
          {{ $button['title'] }}
        </a>
      @endif
    </div>

    @if (! empty($image['ID']))
      <div class="overflow-hidden rounded-2xl">
        {!! wp_get_attachment_image($image['ID'], 'large', false, ['class' => 'h-full w-full object-cover']) !!}
      </div>
    @endif
  </div>
</section>
