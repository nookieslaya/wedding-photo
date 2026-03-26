@php
  $lineOne = $module['title_line_one'] ?? null;
  $lineTwo = $module['title_line_two'] ?? null;
  $description = $module['description'] ?? null;
  $bottomGif = $module['bottom_gif'] ?? null;
@endphp

@if ($lineOne && $lineTwo)
  <section class="story-statement-module relative z-20 min-h-[170svh] bg-black text-white" data-story-statement>
    <div class="sticky top-0 mx-auto flex min-h-screen w-full max-w-6xl flex-col items-center justify-center px-6 py-20 text-center md:px-10 md:py-32">
      <h2 class="text-[clamp(2.2rem,9vw,8rem)] font-semibold uppercase leading-[0.92] tracking-tight" data-story-line="1">
        {{ $lineOne }}
      </h2>

      <h2 class="mt-2 text-[clamp(2.2rem,9vw,8rem)] font-semibold uppercase leading-[0.92] tracking-tight" data-story-line="2">
        {{ $lineTwo }}
      </h2>

      @if ($description)
        <p class="mt-12 max-w-4xl text-sm leading-relaxed text-white/85 md:text-2xl" data-story-description>
          {!! nl2br(e($description)) !!}
        </p>
      @endif

      @if (! empty($bottomGif['ID']))
        <div class="mt-14 w-full max-w-3xl overflow-hidden rounded-sm" data-story-gif>
          {!! wp_get_attachment_image($bottomGif['ID'], 'large', false, ['class' => 'h-auto w-full object-cover']) !!}
        </div>
      @endif
    </div>
  </section>
@endif
