@php
    $lineOne = $module['title_line_one'] ?? null;
    $lineTwo = $module['title_line_two'] ?? null;
    $description = $module['description'] ?? null;
    $galleryItems = $module['gallery'] ?? [];
@endphp

@if ($lineOne && $lineTwo)
    <section class="story-statement-module relative z-20 min-h-screen bg-black text-white md:min-h-[170svh] md:-mt-[8vh]"
        data-story-statement>
        <canvas class="pointer-events-none absolute inset-0 z-0 h-full w-full opacity-70" data-story-smoke></canvas>

        <div
            class="relative mx-auto flex min-h-screen w-full max-w-6xl flex-col items-center justify-center px-6 py-20 text-center md:sticky md:top-0 md:px-10 md:py-32">
            <h2 class="text-[clamp(2.2rem,9vw,8rem)] font-semibold uppercase leading-[0.92] tracking-tight"
                data-story-line="1">
                {{ $lineOne }}
            </h2>

            <h2 class="mt-2 text-[clamp(2.2rem,9vw,8rem)] font-semibold uppercase leading-[0.92] tracking-tight"
                data-story-line="2">
                {{ $lineTwo }}
            </h2>

            @if ($description)
                <p class="mt-12 max-w-4xl text-sm leading-relaxed text-white/85 md:text-2xl" data-story-description>
                    {!! nl2br(e($description)) !!}
                </p>
            @endif
            @if (!empty($galleryItems))
                <div class="mt-14 w-[100dvw] md:h-[50svh] md:max-h-[585px]" data-story-carousel>
                    <div class="overflow-hidden">
                        <div class="flex gap-0 will-change-transform md:h-[50svh] md:max-h-[585px]" data-story-carousel-track>
                            @foreach ($galleryItems as $item)
                                @php
                                    $image =
                                        is_array($item) && array_key_exists('image', $item) ? $item['image'] : $item;
                                @endphp

                                @if (!empty($image['ID']))
                                    <div class="w-full shrink-0 px-2 md:h-full md:w-full lg:w-1/2 2xl:w-1/3"
                                        data-story-carousel-slide>
                                        <div class="relative overflow-hidden rounded-sm bg-black/30 md:h-full"
                                            data-story-image-shell>
                                            {!! wp_get_attachment_image($image['ID'], 'large', false, [
                                                'class' => 'h-auto w-full object-cover grayscale md:h-full',
                                            ]) !!}
                                            {!! wp_get_attachment_image($image['ID'], 'large', false, [
                                                'class' => 'pointer-events-none absolute inset-0 h-full w-full object-cover md:h-full',
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
