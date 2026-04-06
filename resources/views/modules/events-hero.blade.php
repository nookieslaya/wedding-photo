@php
    $postId = get_the_ID();
    $featuredImageId = $postId ? get_post_thumbnail_id($postId) : null;

    $overrideImage = $module['hero_image'] ?? null;
    $imageId = !empty($overrideImage['ID']) ? $overrideImage['ID'] : $featuredImageId;

    $title = trim((string) ($module['override_title'] ?? ''));
    if ($title === '' && $postId) {
        $title = get_the_title($postId);
    }

    $dateLabel = trim((string) ($module['override_date'] ?? ''));
    if ($dateLabel === '' && $postId) {
        $dateLabel = get_the_date('Y.m.d', $postId);
    }

    $badgeLabel = trim((string) ($module['override_badge'] ?? ''));
    if ($badgeLabel === '') {
        $badgeLabel = 'Event';
    }

    $categoryLabel = trim((string) ($module['override_category'] ?? ''));
    if ($categoryLabel === '' && $postId) {
        $eventTerms = get_the_terms($postId, 'event_category');
        if (!$eventTerms || is_wp_error($eventTerms)) {
            $eventTerms = get_the_terms($postId, 'category');
        }
        if (!empty($eventTerms) && !is_wp_error($eventTerms)) {
            $categoryLabel = $eventTerms[0]->name;
        }
    }

    $backLink = $module['back_link'] ?? null;
    $backLinkUrl = !empty($backLink['url']) ? $backLink['url'] : (get_post_type_archive_link('event') ?: '/');
    $backLinkLabel = trim((string) ($module['back_link_label'] ?? ''));
    if ($backLinkLabel === '') {
        $backLinkLabel = 'Back to Topics';
    }

    $showTopMeta = array_key_exists('show_top_meta', $module) ? !empty($module['show_top_meta']) : true;
@endphp

@if ($imageId && $title)
    <section class="events-hero-module relative w-[100dvw] bg-black text-white [margin-left:calc(50%-50dvw)] md:py-12">
        <div class="w-full border-x border-white/15">
            <div class="events-hero-head px-6 md:px-12">
                <a href="{{ $backLinkUrl }}"
                    class="inline-flex w-fit items-center gap-4 text-[0.8rem] md:text-2xl !no-underline font-light text-white transition hover:text-white">
                    <span aria-hidden="true">↲</span>
                    <span>{{ $backLinkLabel }}</span>
                </a>

                <div
                    class="mt-12 flex flex-col gap-8 md:mt-20 md:grid md:grid-cols-[minmax(0,1fr)_auto] md:items-end md:gap-12">
                    <h1
                        class="max-w-[40ch] text-[clamp(2rem,3.95vw,3.85rem)] font-light leading-[1.24] tracking-[-0.01em] md:pl-8">
                        {!! nl2br(e($title)) !!}
                    </h1>

                    @if ($showTopMeta && ($dateLabel || $badgeLabel || $categoryLabel))
                        <div
                            class="events-hero-meta flex flex-wrap items-center md:gap-14 justify-between text-[0.78rem] tracking-[0.01em] text-white/80 md:justify-end md:pb-1">
                            @if ($dateLabel)
                                <span class="inline-flex items-center gap-4">
                                    <span
                                        class="events-hero-dot h-[10px] w-[10px] md:h-[14px] md:w-[14px] rounded-full bg-[#ef4f4f]"></span>
                                    <span>{{ $dateLabel }}</span>
                                </span>
                            @endif
                            @if ($badgeLabel)
                                <span
                                    class="events-hero-badge rounded-[2px] bg-white px-1.5 py-[1px] text-[0.64rem] font-semibold uppercase text-black">
                                    {{ $badgeLabel }}
                                </span>
                            @endif
                            @if ($categoryLabel)
                                <span class="text-white/72">{{ $categoryLabel }}</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <div class="border-t border-white/15">
                {!! wp_get_attachment_image($imageId, 'full', false, [
                    'class' => 'h-auto w-full',
                    'alt' => esc_attr($title),
                ]) !!}
            </div>
        </div>
    </section>
@endif
