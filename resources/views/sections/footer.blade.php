@php
    $brandLogo = function_exists('get_field') ? get_field('footer_brand_logo', 'option') : null;
    $legacyBrandLogo = function_exists('get_field') ? get_field('navigation_brand_logo', 'option') : null;
    $brandLogo = !empty($brandLogo['ID']) ? $brandLogo : $legacyBrandLogo;
    $brandText = function_exists('get_field') ? get_field('footer_brand_text', 'option') : null;
    $legacyBrandText = function_exists('get_field') ? get_field('navigation_brand_text', 'option') : null;
    $brandText = is_string($brandText) && $brandText !== '' ? $brandText : $legacyBrandText;
    $brandText = is_string($brandText) && $brandText !== '' ? $brandText : get_bloginfo('name');

    $footerMetaText = function_exists('get_field') ? get_field('footer_meta_text', 'option') : null;
    $footerMetaText =
        is_string($footerMetaText) && $footerMetaText !== '' ? $footerMetaText : get_bloginfo('name') . ' · {year}';
    $footerMetaText = str_replace('{year}', (string) date('Y'), $footerMetaText);

    $footerBg = function_exists('get_field') ? get_field('footer_background_image', 'option') : null;
    $footerOverlayOpacityRaw = function_exists('get_field') ? get_field('footer_overlay_opacity', 'option') : null;
    $footerOverlayOpacity = is_numeric($footerOverlayOpacityRaw) ? (int) $footerOverlayOpacityRaw : 78;
    $footerOverlayOpacity = max(0, min(100, $footerOverlayOpacity));
    $footerOverlayAlpha = $footerOverlayOpacity / 100;
    $footerLargeText = function_exists('get_field') ? get_field('footer_large_text', 'option') : null;
    $footerLargeText =
        is_string($footerLargeText) && $footerLargeText !== '' ? $footerLargeText : strtoupper($brandText);

    $footerContactLink = function_exists('get_field') ? get_field('footer_contact_link', 'option') : null;
    $footerContactDescription = trim(
        (string) (function_exists('get_field') ? get_field('footer_contact_description', 'option') : ''),
    );

    $footerPhone = trim((string) (function_exists('get_field') ? get_field('footer_phone', 'option') : ''));
    $footerPhoneHours = trim((string) (function_exists('get_field') ? get_field('footer_phone_hours', 'option') : ''));

    $footerLinkedinLink = function_exists('get_field') ? get_field('footer_linkedin_link', 'option') : null;
    $footerLinkedinLabel = trim(
        (string) (function_exists('get_field') ? get_field('footer_linkedin_label', 'option') : ''),
    );
    $footerLinkedinLabel = $footerLinkedinLabel !== '' ? $footerLinkedinLabel : 'Media';
    $footerLinkedinImage = function_exists('get_field') ? get_field('footer_linkedin_image', 'option') : null;

    $socialLinks = function_exists('get_field') ? get_field('social_links', 'option') : [];
    $socialLinks = is_array($socialLinks) ? $socialLinks : [];

    $menuItems = [];
    if (has_nav_menu('primary_navigation')) {
        $locations = get_nav_menu_locations();
        $menuId = $locations['primary_navigation'] ?? null;
        if ($menuId) {
            $items = wp_get_nav_menu_items($menuId) ?: [];
            $menuItems = array_values(array_filter($items, static fn($item) => (int) $item->menu_item_parent === 0));
        }
    }

    $showContactCard = !empty($footerContactLink['url']) || $footerContactDescription !== '';
    $showPhoneCard = $footerPhone !== '' || $footerPhoneHours !== '';
    $showLinkedinCard = !empty($footerLinkedinLink['url']) || !empty($footerLinkedinImage['ID']);
    $footerShowCardsRaw = function_exists('get_field') ? get_field('footer_show_cards', 'option') : 1;
    $footerShowCards = $footerShowCardsRaw === null
        ? true
        : !in_array($footerShowCardsRaw, [0, '0', false, 'false'], true);
    $renderFooterCardsColumn = $footerShowCards && ($showContactCard || $showPhoneCard || $showLinkedinCard);
@endphp

<footer class="content-info relative overflow-hidden bg-black text-white" data-site-footer>
    @if (!empty($footerBg['ID']))
        <div class="pointer-events-none absolute inset-0 z-0">
            {!! wp_get_attachment_image($footerBg['ID'], 'full', false, ['class' => 'h-full w-full object-cover']) !!}
            <div class="absolute inset-0" style="background: rgba(0, 0, 0, {{ $footerOverlayAlpha }});"></div>
        </div>
    @else
        <div class="pointer-events-none absolute inset-0 z-0 bg-black"></div>
    @endif

    <div class="relative z-10 mx-auto w-full max-w-[1900px] px-4 py-10 md:px-8 md:py-14">
        <div
            class="grid grid-cols-1 gap-10 {{ $renderFooterCardsColumn ? 'lg:grid-cols-[30%_1fr] lg:gap-14' : 'lg:gap-10' }}">
            @if ($renderFooterCardsColumn)
                <div class="space-y-3">
                    @if ($showContactCard)
                        <a href="{{ $footerContactLink['url'] ?? '#' }}"
                            @if (!empty($footerContactLink['target'])) target="{{ $footerContactLink['target'] }}" @endif
                            class="block rounded-sm bg-white p-6 text-black no-underline"
                            style="text-decoration:none !important;">
                            <p class="text-[2rem] font-semibold uppercase tracking-[0.02em]">
                                {{ $footerContactLink['title'] ?? 'Contact' }}</p>
                            @if ($footerContactDescription !== '')
                                <p class="mt-10 max-w-[26rem] text-sm leading-relaxed text-black/78 md:text-base">
                                    {{ $footerContactDescription }}</p>
                            @endif
                            <p class="mt-5 flex justify-end text-black/70">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M6 6h12v12M18 6L6 18" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </p>
                        </a>
                    @endif

                    @if ($showPhoneCard)
                        <div class="rounded-sm bg-white/10 p-5">
                            @if ($footerPhone !== '')
                                <p class="text-[1.7rem] font-semibold tracking-[0.03em]">{{ $footerPhone }}</p>
                            @endif
                            @if ($footerPhoneHours !== '')
                                <p class="mt-2 text-sm leading-relaxed text-white/78">{{ $footerPhoneHours }}</p>
                            @endif
                        </div>
                    @endif

                    @if ($showLinkedinCard)
                        @if (!empty($footerLinkedinLink['url']))
                            <a href="{{ $footerLinkedinLink['url'] }}"
                                @if (!empty($footerLinkedinLink['target'])) target="{{ $footerLinkedinLink['target'] }}" @endif
                                class="group relative block min-h-[11rem] overflow-hidden rounded-sm border border-white/35 bg-black/30 no-underline"
                                style="text-decoration:none !important;" aria-label="{{ $footerLinkedinLabel }}">
                        @else
                            <div class="group relative block min-h-[11rem] overflow-hidden rounded-sm border border-white/35 bg-black/30"
                                aria-label="{{ $footerLinkedinLabel }}">
                        @endif
                        @if (!empty($footerLinkedinImage['ID']))
                            {!! wp_get_attachment_image($footerLinkedinImage['ID'], 'large', false, [
                                'class' => 'h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]',
                            ]) !!}
                        @else
                            <div class="flex h-full w-full items-end p-4">
                                <span
                                    class="text-lg font-semibold uppercase tracking-[0.08em]">{{ $footerLinkedinLabel }}</span>
                            </div>
                        @endif
                        <span class="absolute right-3 top-3 text-white/72" aria-hidden="true">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                <path d="M7 7h10v10M17 7L7 17" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        @if (!empty($footerLinkedinLink['url']))
                            </a>
                        @else
                            </div>
                        @endif
                    @endif
                </div>
            @endif

            <div class="flex h-full min-h-[100%] flex-col justify-center">
                <div class="text-center">
                    @if (!empty($menuItems))
                        <nav class="text-center" aria-label="Footer navigation" data-footer-nav>
                            <div class="inline-flex flex-col items-stretch">
                                <ul class="flex flex-wrap items-center justify-center gap-x-8 gap-y-3">
                                    @foreach ($menuItems as $menuItem)
                                        <li>
                                            <a href="{{ $menuItem->url }}"
                                                class="text-sm font-medium uppercase tracking-[0.16em] text-white/92 no-underline transition-colors duration-200 hover:text-white"
                                                style="text-decoration:none !important;" data-footer-link
                                                data-footer-anim-link>
                                                {{ $menuItem->title }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-1 hidden h-5 w-full items-end justify-between md:flex" data-footer-bars
                                    aria-hidden="true"></div>
                            </div>
                        </nav>
                    @endif

                    @if (!empty($socialLinks))
                        <div
                            class="mx-auto mt-14 grid max-w-[980px] grid-cols-1 gap-6 md:mt-16 md:grid-cols-2 xl:grid-cols-2">
                            @foreach ($socialLinks as $item)
                                @php
                                    $socialTitle = trim((string) ($item['title'] ?? ''));
                                    $socialUrl = $item['url']['url'] ?? null;
                                    $socialTarget = $item['url']['target'] ?? '_self';
                                @endphp
                                @if ($socialTitle !== '' && $socialUrl)
                                    <a href="{{ $socialUrl }}" target="{{ $socialTarget }}"
                                        class="border-b border-white/22 pb-3 text-sm text-white/85 no-underline transition hover:text-white"
                                        style="text-decoration:none !important;" data-footer-anim-link>
                                        <span class="text-white/52">{{ $socialTitle }}:</span>
                                        <span class="ml-3">{{ parse_url($socialUrl, PHP_URL_HOST) ?: $socialUrl }}</span>
                                        <span class="ml-2 inline-flex text-white/45" aria-hidden="true">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none">
                                                <path d="M7 7h10v10M17 7L7 17" stroke="currentColor"
                                                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-14 border-t border-white/15 pt-8 text-center md:mt-20 md:pt-10">
            <p
                class="text-[clamp(2.7rem,9vw,11rem)] font-semibold uppercase leading-none tracking-[-0.02em] text-white/92">
                {{ $footerLargeText }}
            </p>
            <p class="mt-4 text-[0.7rem] uppercase tracking-[0.12em] text-white/55">{{ $footerMetaText }}</p>
        </div>
    </div>
</footer>
