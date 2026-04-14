@php
    $backLink = $module['back_link'] ?? null;
    $backLinkUrl = !empty($backLink['url']) ? $backLink['url'] : (get_post_type_archive_link('event') ?: '/');
    $backLinkLabel = trim((string) ($module['back_link_label'] ?? ''));
    if ($backLinkLabel === '') {
        $backLinkLabel = 'Back to Topics';
    }

    $legacyContent = $module['content'] ?? '';
    $contentSections = $module['content_sections'] ?? [];
    $contentSections = is_array($contentSections) ? $contentSections : [];

    if (empty($contentSections) && !empty($legacyContent)) {
        $contentSections = [
            [
                'acf_fc_layout' => 'content_block',
                'content' => $legacyContent,
            ],
        ];
    }

    $shareTitle = trim((string) (get_field('share_us_on_label', 'option') ?? ''));
    if ($shareTitle === '') {
        $shareTitle = 'Share us on';
    }

    $shareLinkLabel = trim((string) (get_field('share_link_label', 'option') ?? ''));
    if ($shareLinkLabel === '') {
        $shareLinkLabel = 'Share';
    }

    $socialLinks = get_field('social_links', 'option');
    $socialLinks = is_array($socialLinks) ? $socialLinks : [];
@endphp

@if (!empty($contentSections))
    <section class="events-sidebar-content-module bg-[#d8d8da] text-black" data-events-sidebar-content>
        <div class="mx-auto w-full max-w-[1900px] px-4 py-10 md:px-10 md:py-16">
            <div class="grid grid-cols-1 gap-10 md:grid-cols-[30%_1fr] md:gap-14">
                <aside class="md:sticky md:top-28 md:self-start">
                    <div class="hidden border-y border-black/25 py-6 md:block">
                        <a href="{{ $backLinkUrl }}"
                            class="inline-flex items-center !no-underline gap-3 text-3xl tracking-[0.03em]"
                            data-events-back-link>
                            <span aria-hidden="true">↲</span>
                            <span data-events-back-link-label>{{ $backLinkLabel }}</span>
                        </a>
                    </div>

                    <div class="mt-12 hidden md:block">
                        <h3 class="text-4xl leading-none tracking-[0.03em]">{{ $shareTitle }}</h3>

                        @if (!empty($socialLinks))
                            <div class="mt-6 flex flex-wrap items-center gap-6">
                                @foreach ($socialLinks as $item)
                                    @php
                                        $socialTitle = $item['title'] ?? $shareLinkLabel;
                                        $socialIcon = $item['icon'] ?? null;
                                        $socialUrl = $item['url']['url'] ?? null;
                                        $socialTarget = $item['url']['target'] ?? '_self';
                                    @endphp
                                    @if ($socialUrl)
                                        <a href="{{ $socialUrl }}" target="{{ $socialTarget }}"
                                            class="inline-flex h-10 w-10 items-center justify-center text-black transition hover:opacity-70"
                                            aria-label="{{ esc_attr($socialTitle) }}">
                                            @if (!empty($socialIcon['ID']))
                                                {!! wp_get_attachment_image($socialIcon['ID'], 'thumbnail', false, [
                                                    'class' => 'h-8 w-8 object-contain',
                                                    'alt' => esc_attr($socialIcon['alt'] ?? $socialTitle),
                                                ]) !!}
                                            @else
                                                <span class="text-sm">{{ $shareLinkLabel }}</span>
                                            @endif
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                    </div>
                </aside>

                <div class="max-w-none min-w-0 text-black">
                    @foreach ($contentSections as $sectionBlock)
                        @php
                            $blockLayout = $sectionBlock['acf_fc_layout'] ?? '';
                        @endphp

                        @if ($blockLayout === 'content_block')
                            @php
                                $blockContent = $sectionBlock['content'] ?? '';
                            @endphp
                            @if (!empty($blockContent))
                                <div class="events-sidebar-content mt-12 first:mt-0 md:mt-16">
                                    {!! wp_kses_post($blockContent) !!}
                                </div>
                            @endif
                        @elseif ($blockLayout === 'gallery_block')
                            @php
                                $galleryTitle = trim((string) ($sectionBlock['title'] ?? ''));
                                $galleryItems = $sectionBlock['images'] ?? [];
                                $galleryItems = is_array($galleryItems) ? $galleryItems : [];
                                $layoutStyle = $sectionBlock['layout_style'] ?? 'masonry';
                                $layoutStyle = in_array($layoutStyle, ['equal', 'masonry', 'mixed'], true) ? $layoutStyle : 'masonry';
                            @endphp
                            @if (!empty($galleryItems))
                                <section class="mt-12 first:mt-0 md:mt-16">
                                    @if ($galleryTitle !== '')
                                        <h3 class="mb-4 text-[1.35rem] font-semibold leading-tight tracking-[-0.01em] md:mb-5 md:text-[1.7rem]">
                                            {{ $galleryTitle }}
                                        </h3>
                                    @endif
                                    @php
                                        if ($layoutStyle === 'equal') {
                                            $galleryWrapperClass = 'grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4';
                                        } elseif ($layoutStyle === 'mixed') {
                                            $galleryWrapperClass = 'grid grid-cols-2 gap-3 md:grid-cols-3 md:gap-4';
                                        } else {
                                            $galleryWrapperClass = 'columns-2 gap-3 md:columns-3 md:gap-4';
                                        }
                                    @endphp
                                    <div class="{{ $galleryWrapperClass }}">
                                        @foreach ($galleryItems as $galleryItem)
                                            @php
                                                $image = $galleryItem['image'] ?? null;
                                                $caption = trim((string) ($galleryItem['caption'] ?? ''));
                                                $fullSrc = $image['url'] ?? null;
                                                $alt = trim((string) ($image['alt'] ?? $caption));
                                                $width = (int) ($image['width'] ?? 0);
                                                $height = (int) ($image['height'] ?? 0);
                                                $isPortrait = $width > 0 && $height > 0 ? $height > $width : false;

                                                if ($layoutStyle === 'equal') {
                                                    $itemClass = 'group text-left';
                                                    $imageClass = 'h-40 w-full object-cover transition duration-300 group-hover:scale-[1.03] md:h-52';
                                                } elseif ($layoutStyle === 'mixed') {
                                                    $itemClass = 'group text-left';
                                                    $imageClass = $isPortrait
                                                        ? 'h-56 w-full object-cover transition duration-300 group-hover:scale-[1.03] md:h-72'
                                                        : 'h-40 w-full object-cover transition duration-300 group-hover:scale-[1.03] md:h-52';
                                                } else {
                                                    $itemClass = 'group mb-3 inline-block w-full break-inside-avoid text-left md:mb-4';
                                                    $imageClass = 'h-auto w-full object-cover transition duration-300 group-hover:scale-[1.03]';
                                                }
                                            @endphp
                                            @if (!empty($image['ID']) && $fullSrc)
                                                <button type="button"
                                                    class="{{ $itemClass }}"
                                                    data-events-lightbox-item
                                                    data-events-lightbox-src="{{ esc_url($fullSrc) }}"
                                                    data-events-lightbox-alt="{{ esc_attr($alt) }}"
                                                    data-events-lightbox-caption="{{ esc_attr($caption) }}">
                                                    <span class="block overflow-hidden rounded-sm bg-black/10">
                                                        {!! wp_get_attachment_image($image['ID'], 'large', false, [
                                                            'class' => $imageClass,
                                                            'alt' => esc_attr($alt),
                                                        ]) !!}
                                                    </span>
                                                    @if ($caption !== '')
                                                        <span class="mt-2 block text-[0.68rem] uppercase tracking-[0.08em] text-black/62 md:text-[0.72rem]">
                                                            {{ $caption }}
                                                        </span>
                                                    @endif
                                                </button>
                                            @endif
                                        @endforeach
                                    </div>
                                </section>
                            @endif
                        @endif
                    @endforeach
                </div>

                <div class="border-t border-black/25 pt-10 md:hidden">
                    <h3 class="text-4xl leading-none tracking-[0.03em]">{{ $shareTitle }}</h3>

                    @if (!empty($socialLinks))
                        <div class="mt-6 flex flex-wrap items-center gap-6">
                            @foreach ($socialLinks as $item)
                                @php
                                    $socialTitle = $item['title'] ?? $shareLinkLabel;
                                    $socialIcon = $item['icon'] ?? null;
                                    $socialUrl = $item['url']['url'] ?? null;
                                    $socialTarget = $item['url']['target'] ?? '_self';
                                @endphp
                                @if ($socialUrl)
                                    <a href="{{ $socialUrl }}" target="{{ $socialTarget }}"
                                        class="inline-flex h-10 w-10 items-center justify-center text-black transition hover:opacity-70"
                                        aria-label="{{ esc_attr($socialTitle) }}">
                                        @if (!empty($socialIcon['ID']))
                                            {!! wp_get_attachment_image($socialIcon['ID'], 'thumbnail', false, [
                                                'class' => 'h-8 w-8 object-contain',
                                                'alt' => esc_attr($socialIcon['alt'] ?? $socialTitle),
                                            ]) !!}
                                        @else
                                            <span class="text-sm">{{ $shareLinkLabel }}</span>
                                        @endif
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="border-t border-black/25 pt-6 md:hidden">
                    <a href="{{ $backLinkUrl }}"
                        class="inline-flex items-center !no-underline gap-3 text-3xl tracking-[0.03em]"
                        data-events-back-link>
                        <span aria-hidden="true">↲</span>
                        <span data-events-back-link-label>{{ $backLinkLabel }}</span>
                    </a>
                </div>
            </div>

            <div class="fixed inset-0 z-[240] hidden items-center justify-center bg-black/90 p-4 md:p-8"
                data-events-lightbox aria-hidden="true">
                <button type="button"
                    class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-sm border border-white/40 text-white md:right-8 md:top-8"
                    data-events-lightbox-close
                    aria-label="Close lightbox">
                    <span class="text-lg leading-none">✕</span>
                </button>
                <button type="button"
                    class="absolute left-3 top-1/2 inline-flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-sm border border-white/40 text-white md:left-8"
                    data-events-lightbox-prev
                    aria-label="Previous image">
                    <span class="text-lg leading-none">←</span>
                </button>
                <button type="button"
                    class="absolute right-3 top-1/2 inline-flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-sm border border-white/40 text-white md:right-8"
                    data-events-lightbox-next
                    aria-label="Next image">
                    <span class="text-lg leading-none">→</span>
                </button>

                <figure class="mx-auto flex max-h-full w-full max-w-6xl flex-col items-center justify-center">
                    <img class="max-h-[80svh] w-auto max-w-full object-contain" data-events-lightbox-image alt="">
                    <figcaption class="mt-4 min-h-[1.25rem] text-center text-[0.72rem] uppercase tracking-[0.08em] text-white/80"
                        data-events-lightbox-caption></figcaption>
                </figure>
            </div>
        </div>
    </section>
@endif
