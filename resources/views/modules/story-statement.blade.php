@php
    $lineOne = $module['title_line_one'] ?? null;
    $lineTwo = $module['title_line_two'] ?? null;
    $description = $module['description'] ?? null;
    $galleryItems = $module['gallery'] ?? [];
    $lineOne = is_string($lineOne) ? trim($lineOne) : '';
    $lineTwo = is_string($lineTwo) ? trim($lineTwo) : '';
    $description = is_string($description) ? trim($description) : '';
    $hasGallery = is_array($galleryItems) && !empty($galleryItems);
    $shouldRenderSection = $lineOne !== '' || $lineTwo !== '' || $description !== '' || $hasGallery;
    $titleFontSizeMobileRaw = $module['title_font_size_mobile'] ?? null;
    $titleFontSizeDesktopRaw = $module['title_font_size_desktop'] ?? null;

    $parseTitleFontSize = static function ($raw, float $min, float $max): ?float {
        if ($raw === null) {
            return null;
        }

        if (is_string($raw)) {
            $raw = trim($raw);
            if ($raw === '') {
                return null;
            }

            $raw = str_replace(',', '.', $raw);
            if (preg_match('/-?\d+(?:\.\d+)?/', $raw, $matches)) {
                $raw = $matches[0];
            }
        }

        if (!is_numeric($raw)) {
            return null;
        }

        return max($min, min($max, (float) $raw));
    };

    $titleFontSizeMobile = $parseTitleFontSize($titleFontSizeMobileRaw, 1, 12);
    $titleFontSizeDesktop = $parseTitleFontSize($titleFontSizeDesktopRaw, 1, 20);

    $mobileTitleSize = $titleFontSizeMobile !== null
        ? rtrim(rtrim(number_format($titleFontSizeMobile, 2, '.', ''), '0'), '.') . 'rem'
        : 'clamp(2.2rem,9vw,8rem)';
    $desktopTitleSize = $titleFontSizeDesktop !== null
        ? rtrim(rtrim(number_format($titleFontSizeDesktop, 2, '.', ''), '0'), '.') . 'rem'
        : 'clamp(2.2rem,9vw,8rem)';
@endphp

@if ($shouldRenderSection)
    <section class="story-statement-module relative z-20 min-h-screen bg-black text-white md:min-h-[170svh] md:-mt-[8vh]"
        data-story-statement
        style="--story-title-size-mobile: {{ $mobileTitleSize }}; --story-title-size-desktop: {{ $desktopTitleSize }};">
        <canvas class="pointer-events-none absolute inset-0 z-0 h-full w-full opacity-70" data-story-smoke></canvas>

        <div
            class="relative mx-auto flex min-h-screen w-full max-w-6xl flex-col items-center justify-center px-6 py-20 text-center md:sticky md:top-0 md:px-10 md:py-32">
            @if ($lineOne !== '')
                <h2 class="font-semibold uppercase leading-[0.92] tracking-tight"
                    data-story-line="1">
                    {{ $lineOne }}
                </h2>
            @endif

            @if ($lineTwo !== '')
                <h2 class="mt-2 font-semibold uppercase leading-[0.92] tracking-tight"
                    data-story-line="2">
                    {{ $lineTwo }}
                </h2>
            @endif

            @if ($description !== '')
                <p class="mt-12 max-w-4xl text-sm leading-relaxed text-white/85 md:text-2xl" data-story-description>
                    {!! nl2br(e($description)) !!}
                </p>
            @endif
            @if ($hasGallery)
                <div class="mt-14 w-[100dvw] min-[768px]:h-[50svh] min-[768px]:max-h-[585px]" data-story-carousel>
                    <div class="overflow-hidden">
                        <div class="flex gap-0 will-change-transform min-[768px]:h-[50svh] min-[768px]:max-h-[585px]" data-story-carousel-track>
                            @foreach ($galleryItems as $item)
                                @php
                                    $image =
                                        is_array($item) && array_key_exists('image', $item) ? $item['image'] : $item;
                                @endphp

                                @if (!empty($image['ID']))
                                    <div class="w-full shrink-0 px-2 min-[768px]:h-full min-[768px]:w-full min-[1024px]:w-1/2 min-[1536px]:w-1/3"
                                        data-story-carousel-slide>
                                        <div class="relative overflow-hidden rounded-sm bg-black/30 min-[768px]:h-full"
                                            data-story-image-shell>
                                            {!! wp_get_attachment_image($image['ID'], 'large', false, [
                                                'class' => 'h-auto w-full object-cover grayscale min-[768px]:h-full',
                                            ]) !!}
                                            {!! wp_get_attachment_image($image['ID'], 'large', false, [
                                                'class' => 'pointer-events-none absolute inset-0 h-full w-full object-cover min-[768px]:h-full',
                                                'data-story-image-color' => '1',
                                            ]) !!}
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
