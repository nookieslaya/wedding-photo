@php
    $brandLogo = function_exists('get_field') ? get_field('header_brand_logo', 'option') : null;
    $legacyBrandLogo = function_exists('get_field') ? get_field('navigation_brand_logo', 'option') : null;
    $brandLogo = !empty($brandLogo['ID']) ? $brandLogo : $legacyBrandLogo;
    $brandText = function_exists('get_field') ? get_field('header_brand_text', 'option') : null;
    $legacyBrandText = function_exists('get_field') ? get_field('navigation_brand_text', 'option') : null;
    $brandText = is_string($brandText) && $brandText !== '' ? $brandText : $legacyBrandText;
    $brandText = is_string($brandText) && $brandText !== '' ? $brandText : $siteName;
    $mobilePanelLogo = function_exists('get_field') ? get_field('mobile_menu_header_logo', 'option') : null;
    $mobilePanelText = function_exists('get_field') ? get_field('mobile_menu_header_text', 'option') : null;
    $mobilePanelText = is_string($mobilePanelText) && $mobilePanelText !== '' ? $mobilePanelText : $brandText;

    $menuItems = [];
    if (has_nav_menu('primary_navigation')) {
        $locations = get_nav_menu_locations();
        $menuId = $locations['primary_navigation'] ?? null;

        if ($menuId) {
            $items = wp_get_nav_menu_items($menuId) ?: [];
            $menuItems = array_values(array_filter($items, static fn($item) => (int) $item->menu_item_parent === 0));
        }
    }
@endphp

<header class="pointer-events-none fixed inset-x-0 top-0 z-[120]" data-site-header>
    <div class="pointer-events-auto md:hidden">
        <div class="flex items-center justify-between bg-black/80 px-4 py-4 text-white backdrop-blur-md">
            <a class="inline-flex items-center text-base font-semibold uppercase tracking-[0.1em] text-white no-underline"
                style="text-decoration:none !important;" href="{{ home_url('/') }}" aria-label="Home">
                @if (!empty($brandLogo['ID']))
                    {!! wp_get_attachment_image($brandLogo['ID'], 'medium', false, ['class' => 'h-8 w-auto object-contain']) !!}
                @else
                    {{ $brandText }}
                @endif
            </a>

            <button
                class="inline-flex h-11 w-[5.2rem] items-center justify-center rounded-sm border border-white/35 px-4 text-[0.72rem] font-semibold uppercase tracking-[0.14em] text-white"
                type="button" data-nav-toggle aria-expanded="false" aria-controls="mobile-nav-panel"
                aria-label="Open menu">
                <span class="leading-none">Menu</span>
            </button>
        </div>

        <div id="mobile-nav-panel" class="pointer-events-none fixed inset-0 z-[130] hidden bg-black text-white"
            data-mobile-nav-panel aria-hidden="true">
            <div class="flex h-full flex-col">
                <div class="flex items-center justify-between px-4 py-4">
                    @if (!empty($mobilePanelLogo['ID']))
                        {!! wp_get_attachment_image($mobilePanelLogo['ID'], 'medium', false, ['class' => 'h-8 w-auto object-contain']) !!}
                    @else
                        <span
                            class="inline-flex items-center text-base font-semibold uppercase tracking-[0.1em] text-white">{{ $mobilePanelText }}</span>
                    @endif
                    <button
                        class="relative inline-flex h-11 w-[5.2rem] items-center justify-center rounded-sm border border-white/35 px-4 text-[0.72rem] font-semibold uppercase tracking-[0.14em] text-white"
                        type="button" data-nav-close aria-label="Close menu">
                        <span class="leading-none">Menu</span>
                        <span
                            class="pointer-events-none absolute left-[10%] top-1/2 h-[2px] w-[80%] -translate-y-1/2 rotate-[-24deg] bg-red-500"></span>
                    </button>
                </div>

                <nav class="flex flex-1 items-start justify-center overflow-y-auto px-4 pt-8 pb-6" aria-label="Mobile navigation">
                    <ul class="flex w-full flex-col items-center gap-5">
                        @foreach ($menuItems as $menuItem)
                            <li>
                                <a href="{{ $menuItem->url }}"
                                    class="text-[clamp(1.45rem,5.2vw,2.2rem)] font-semibold uppercase tracking-[0.08em] text-white no-underline"
                                    style="text-decoration:none !important;" data-nav-link>
                                    {{ $menuItem->title }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="pointer-events-auto hidden md:block">
        <div class="fixed inset-x-0 top-0 border-b border-white/15 bg-black/78 px-6 py-6 text-white backdrop-blur-md">
            <div class="mx-auto flex w-full max-w-[1800px] items-center justify-between gap-8">
                <a class="inline-flex min-w-[10rem] items-center justify-start text-left text-base font-semibold uppercase tracking-[0.12em] text-white no-underline"
                    style="text-decoration:none !important;" href="{{ home_url('/') }}" aria-label="Home">
                    @if (!empty($brandLogo['ID']))
                        {!! wp_get_attachment_image($brandLogo['ID'], 'medium', false, ['class' => 'h-6 w-auto object-contain']) !!}
                    @else
                        {{ $brandText }}
                    @endif
                </a>

                @if (!empty($menuItems))
                    <nav aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}"
                        class="ml-auto flex max-w-[1200px] justify-end text-right" data-desktop-nav>
                        <div class="inline-flex flex-col items-stretch">
                            <ul class="inline-flex items-center justify-center gap-8 text-center">
                                @foreach ($menuItems as $menuItem)
                                    <li class="shrink-0 text-center">
                                        <a href="{{ $menuItem->url }}"
                                            class="text-base font-medium uppercase tracking-[0.16em] text-white/90 no-underline transition-colors duration-200 hover:text-white"
                                            style="text-decoration:none !important;" data-nav-link>
                                            {{ $menuItem->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="mt-0.5 hidden h-5 w-full items-end justify-between md:flex" data-nav-bars
                                aria-hidden="true"></div>
                        </div>
                    </nav>
                @endif
            </div>
        </div>
    </div>
</header>
