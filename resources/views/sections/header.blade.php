@php
  $brandLogo = function_exists('get_field') ? get_field('navigation_brand_logo', 'option') : null;
  $brandText = function_exists('get_field') ? get_field('navigation_brand_text', 'option') : null;
  $brandText = is_string($brandText) && $brandText !== '' ? $brandText : $siteName;

  $menuItems = [];
  if (has_nav_menu('primary_navigation')) {
      $locations = get_nav_menu_locations();
      $menuId = $locations['primary_navigation'] ?? null;

      if ($menuId) {
          $items = wp_get_nav_menu_items($menuId) ?: [];
          $menuItems = array_values(array_filter($items, static fn ($item) => (int) $item->menu_item_parent === 0));
      }
  }
@endphp

<header class="pointer-events-none fixed inset-x-0 top-0 z-[120]" data-site-header>
  <div class="pointer-events-auto md:hidden">
    <div class="flex items-center justify-between bg-black/80 px-4 py-4 text-white backdrop-blur-md">
      <a class="inline-flex items-center text-base font-semibold uppercase tracking-[0.1em] text-white no-underline" style="text-decoration:none !important;" href="{{ home_url('/') }}" aria-label="Home">
        @if (!empty($brandLogo['ID']))
          {!! wp_get_attachment_image($brandLogo['ID'], 'medium', false, ['class' => 'h-8 w-auto object-contain']) !!}
        @else
          {{ $brandText }}
        @endif
      </a>

      <button
        class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/30 text-white"
        type="button"
        data-nav-toggle
        aria-expanded="false"
        aria-controls="mobile-nav-panel"
        aria-label="Open menu"
      >
        <span class="text-xl leading-none">☰</span>
      </button>
    </div>

    <div id="mobile-nav-panel" class="pointer-events-none fixed inset-0 z-[130] hidden bg-black text-white" data-mobile-nav-panel aria-hidden="true">
      <div class="flex h-full flex-col">
        <div class="flex items-center justify-between px-4 py-3">
          <span class="text-sm uppercase tracking-[0.1em] text-white/70">Menu</span>
          <button
            class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/30 text-white"
            type="button"
            data-nav-close
            aria-label="Close menu"
          >
            <span class="text-xl leading-none">✕</span>
          </button>
        </div>

        <nav class="flex flex-1 items-center justify-center" aria-label="Mobile navigation">
          <ul class="flex flex-col items-center gap-8">
            @foreach ($menuItems as $menuItem)
              <li>
                <a
                  href="{{ $menuItem->url }}"
                  class="text-[clamp(1.9rem,7.4vw,3.1rem)] font-semibold uppercase tracking-[0.08em] text-white no-underline"
                  style="text-decoration:none !important;"
                  data-nav-link
                >
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
      <div class="mx-auto flex w-full max-w-[1900px] items-center justify-between gap-8">
        <a class="inline-flex min-w-[10rem] items-center justify-start text-left text-sm font-semibold uppercase tracking-[0.12em] text-white no-underline" style="text-decoration:none !important;" href="{{ home_url('/') }}" aria-label="Home">
          @if (!empty($brandLogo['ID']))
            {!! wp_get_attachment_image($brandLogo['ID'], 'medium', false, ['class' => 'max-h-12 w-auto object-contain']) !!}
          @else
            {{ $brandText }}
          @endif
        </a>

        @if (!empty($menuItems))
          <nav aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}" class="flex-1 max-w-[1200px] text-center" data-desktop-nav>
            <div class="inline-flex flex-col items-stretch">
              <ul class="inline-flex items-center justify-center gap-8 text-center">
                @foreach ($menuItems as $menuItem)
                  <li class="shrink-0 text-center">
                    <a
                      href="{{ $menuItem->url }}"
                      class="text-base font-medium uppercase tracking-[0.16em] text-white/90 no-underline transition-colors duration-200 hover:text-white"
                      style="text-decoration:none !important;"
                      data-nav-link
                    >
                      {{ $menuItem->title }}
                    </a>
                  </li>
                @endforeach
              </ul>
              <div class="mt-0.5 hidden h-5 w-full items-end justify-between md:flex" data-nav-bars aria-hidden="true"></div>
            </div>
          </nav>
        @endif
      </div>
    </div>
  </div>
</header>
