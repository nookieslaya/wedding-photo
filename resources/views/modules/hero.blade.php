@php
  $eyebrow = $module['eyebrow'] ?? null;
  $heading = $module['heading'] ?? null;
  $description = $module['description'] ?? null;
  $backgroundImage = $module['background_image'] ?? null;
  $mobileImage = $module['mobile_image'] ?? null;
  $button = $module['button'] ?? null;
  $showScrollHint = ! empty($module['show_scroll_hint']);
@endphp

@if (! empty($backgroundImage['ID']) && $heading)
  <section class="hero-module relative z-0 isolate min-h-[70svh] w-[100dvw] overflow-hidden bg-black [margin-left:calc(50%-50dvw)] md:min-h-0" data-hero>
    <div class="relative h-[100svh] w-full md:h-auto" data-hero-media>
      <div class="relative w-full" data-hero-bg>
        <picture>
          @if (! empty($mobileImage['ID']))
            <source media="(max-width: 767px)" srcset="{{ wp_get_attachment_image_url($mobileImage['ID'], 'full') }}">
          @endif
          <img
            src="{{ wp_get_attachment_image_url($backgroundImage['ID'], 'full') }}"
            alt="{{ esc_attr($backgroundImage['alt'] ?? $heading) }}"
            class="block h-full w-full object-cover object-top will-change-transform md:h-auto md:object-contain"
            data-hero-image
          >
        </picture>
      </div>

      <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-black/35"></div>

      <div class="absolute left-0 right-0 top-0 z-30 flex min-h-screen items-start justify-start px-6 pb-0 pt-0 md:items-center md:justify-center md:px-10 md:py-0">
        <div class="mx-0 mt-[45svh] flex w-full max-w-none flex-col items-start space-y-5 text-left text-white md:mx-auto md:mt-0 md:max-w-6xl md:items-center md:text-center" data-hero-title-track>
          @if ($eyebrow)
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/75 md:text-sm" data-hero-reveal>
              {{ $eyebrow }}
            </p>
          @endif

          <h1 class="text-[clamp(2.6rem,15vw,5rem)] font-semibold uppercase leading-[0.95] tracking-tight text-white md:text-[clamp(2.6rem,8vw,8rem)]" data-hero-reveal>
            {{ $heading }}
          </h1>

          @if ($description)
            <p class="max-w-2xl text-base leading-relaxed text-white/85 md:text-2xl" data-hero-reveal>
              {!! nl2br(e($description)) !!}
            </p>
          @endif

          @if (! empty($button['url']) && ! empty($button['title']))
            <a
              href="{{ $button['url'] }}"
              target="{{ $button['target'] ?? '_self' }}"
              class="inline-flex items-center rounded-full bg-white px-7 py-3 text-sm font-semibold text-stone-900 transition hover:bg-white/90"
              data-hero-reveal
            >
              {{ $button['title'] }}
            </a>
          @endif
        </div>
      </div>

      @if ($showScrollHint)
        <div class="pointer-events-none absolute right-6 top-1/2 z-30 hidden -translate-y-1/2 text-[10px] font-semibold uppercase tracking-[0.18em] text-white/65 [writing-mode:vertical-rl] md:block" data-hero-reveal>
          Scroll
        </div>
      @endif
    </div>
  </section>

@endif
