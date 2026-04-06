@php
    $backLink = $module['back_link'] ?? null;
    $backLinkUrl = !empty($backLink['url']) ? $backLink['url'] : (get_post_type_archive_link('event') ?: '/');
    $backLinkLabel = trim((string) ($module['back_link_label'] ?? ''));
    if ($backLinkLabel === '') {
        $backLinkLabel = 'Back to Topics';
    }

    $content = $module['content'] ?? '';

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

@if ($content)
    <section class="events-sidebar-content-module bg-[#d8d8da] text-black" data-events-sidebar-content>
        <div class="mx-auto w-full max-w-[1900px] px-4 py-10 md:px-10 md:py-16">
            <div class="grid grid-cols-1 gap-10 md:grid-cols-[30%_1fr] md:gap-14">
                <aside class="md:sticky md:top-8 md:self-start">
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

                <div class="events-sidebar-content max-w-none min-w-0 text-black">
                    {!! wp_kses_post($content) !!}
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
        </div>
    </section>
@endif
