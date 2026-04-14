<section class="events-list-module relative bg-[#d8d8da] py-8 text-black md:py-12" data-events-module>
  <div class="mx-auto w-full max-w-[1800px] px-4 md:px-0">
    <header class="mb-4 flex items-end gap-3 md:mb-6">
      <h1 class="text-lg font-semibold uppercase tracking-tight md:text-[1.75rem]">
        {{ __('Wydarzenia', 'sage') }}
      </h1>
    </header>

    @if (have_posts())
      <div class="space-y-1.5 md:space-y-2">
        @while (have_posts())
          <?php
          the_post();
          $eventId = get_the_ID();
          $imageId = get_post_thumbnail_id($eventId);
          $eventDate = get_the_date('Y.m.d', $eventId);
          $eventTitle = get_the_title($eventId);
          $eventLink = get_permalink($eventId);
          $eventTerms = get_the_terms($eventId, 'event_category');
          if (! $eventTerms || is_wp_error($eventTerms)) {
              $eventTerms = get_the_terms($eventId, 'category');
          }
          $eventTypeLabel = ! empty($eventTerms) && ! is_wp_error($eventTerms) ? $eventTerms[0]->name : 'EVENT';
          ?>

          <article class="overflow-hidden rounded-[4px] bg-[#ececee] p-2 md:max-h-[575px] md:p-0" data-events-item>
            <a href="{{ $eventLink }}"
              class="grid gap-3 no-underline hover:no-underline focus:no-underline md:h-full md:grid-cols-[44%_56%] md:items-stretch md:gap-0"
              style="text-decoration:none !important;">
              <div class="overflow-hidden rounded-[2px] bg-black/20 md:rounded-none">
                @if ($imageId)
                  <div class="relative h-full w-full md:min-h-[11.5rem]" data-events-image-shell>
                    {!! wp_get_attachment_image($imageId, 'large', false, [
                        'class' => 'h-full w-full object-cover grayscale',
                    ]) !!}
                    {!! wp_get_attachment_image($imageId, 'large', false, [
                        'class' => 'pointer-events-none absolute inset-0 h-full w-full object-cover',
                        'data-events-image-color' => '1',
                    ]) !!}
                  </div>
                @else
                  <div class="h-[35svh] w-full bg-black/10 md:h-full "></div>
                @endif
              </div>

              <div class="flex flex-col md:gap-20 px-3 pb-2 pt-1 md:px-8 md:p-20">
                <div class="flex items-center justify-between gap-4 border-b border-black/10 pb-3 md:pb-4">
                  <div class="flex items-center gap-2">
                    <span class="text-[0.65rem] tracking-[0.08em] text-black/70 no-underline md:text-[0.72rem]"
                      style="text-decoration:none !important;">
                      {{ $eventDate }}
                    </span>
                    <span class="rounded bg-black px-1.5 py-[1px] text-[0.56rem] font-semibold uppercase tracking-[0.08em] text-white md:text-[0.62rem]">
                      {{ $eventTypeLabel }}
                    </span>
                  </div>
                </div>

                <h3 class="mt-4 text-[1.08rem] font-light leading-[1.35] tracking-medium normal-case no-underline md:mt-3 md:text-[2rem] xl:text-[2.45rem]"
                  style="text-decoration:none !important;" data-events-title>
                  {{ $eventTitle }}
                </h3>

                <span class="mt-auto pt-5 text-right text-4xl text-black/70 md:pt-6">↳</span>
              </div>
            </a>
          </article>
        @endwhile
      </div>

      <div class="mt-8 md:mt-10">
        {!! get_the_posts_navigation() !!}
      </div>
    @else
      <p class="mt-8 text-black/70">
        {{ __('Brak wydarzen do wyswietlenia.', 'sage') }}
      </p>
    @endif
  </div>
</section>
