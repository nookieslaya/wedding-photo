@php
    $heading = $module['heading'] ?? null;
    $description = $module['description'] ?? null;
    $photos = $module['photos'] ?? [];
@endphp

@if ($heading && !empty($photos))
    <section class="p-wedding-photo relative bg-black text-white" data-wedding-photo>
        <div class="sticky top-0 z-10 flex min-h-screen items-center overflow-hidden md:overflow-visible"
            data-wedding-sticky>
            <div class="pointer-events-none absolute inset-0 z-0 bg-gradient-to-b from-black via-black/90 to-black">
            </div>
            <canvas class="pointer-events-none absolute inset-0 z-[5] h-full w-full opacity-80 mix-blend-screen"
                data-wedding-smoke></canvas>

            <div class="absolute inset-0 z-10 flex items-start justify-center px-6 pt-8 text-center md:px-10 md:pt-12 md:pb-12"
                data-wedding-title-wrap>
                <div class="relative inline-flex flex-col items-center justify-center px-4 py-3 md:px-8 md:mb-10"
                    data-wedding-title-shell>
                    <h2 class="relative z-10 max-w-6xl text-[clamp(2.4rem,13vw,12rem)]  font-semibold uppercase leading-[0.9] tracking-tight text-white"
                        data-wedding-title>
                        {{ $heading }}
                    </h2>

                    <div class="relative z-10 mt-5 hidden flex-col items-center md:mt-7 md:flex" data-wedding-aperture>
                        <div class="relative h-[clamp(6.75rem,12vw,11.5rem)] w-[clamp(6.75rem,12vw,11.5rem)] overflow-hidden rounded-full border border-white/30 bg-white shadow-[0_0_0_1px_rgba(255,255,255,0.35)_inset]"
                            data-wedding-aperture-disc>
                            <span
                                class="pointer-events-none absolute inset-[10%] rounded-full border border-white/16"></span>
                            <span
                                class="pointer-events-none absolute inset-[2%] rounded-full border border-white/8"></span>

                            <span
                                class="absolute left-1/2 top-1/2 h-[34%] w-[120%] bg-white [clip-path:polygon(0_34%,0_66%,100%_92%,100%_8%)]"
                                data-wedding-aperture-blade></span>
                            <span
                                class="absolute left-1/2 top-1/2 h-[34%] w-[120%] bg-white [clip-path:polygon(0_34%,0_66%,100%_92%,100%_8%)]"
                                data-wedding-aperture-blade></span>
                            <span
                                class="absolute left-1/2 top-1/2 h-[34%] w-[120%] bg-white [clip-path:polygon(0_34%,0_66%,100%_92%,100%_8%)]"
                                data-wedding-aperture-blade></span>
                            <span
                                class="absolute left-1/2 top-1/2 h-[34%] w-[120%] bg-white [clip-path:polygon(0_34%,0_66%,100%_92%,100%_8%)]"
                                data-wedding-aperture-blade></span>
                            <span
                                class="absolute left-1/2 top-1/2 h-[34%] w-[120%] bg-white [clip-path:polygon(0_34%,0_66%,100%_92%,100%_8%)]"
                                data-wedding-aperture-blade></span>
                            <span
                                class="absolute left-1/2 top-1/2 h-[34%] w-[120%] bg-white [clip-path:polygon(0_34%,0_66%,100%_92%,100%_8%)]"
                                data-wedding-aperture-blade></span>

                            <span
                                class="absolute left-1/2 top-1/2 h-[30%] w-[30%] bg-black [clip-path:polygon(50%_6%,88%_28%,88%_72%,50%_94%,12%_72%,12%_28%)]"
                                data-wedding-aperture-hole></span>

                        </div>

                        <p class="mt-2 text-[0.85rem] font-medium tracking-[0.06em] text-white/82 md:mt-3 md:text-[1rem]"
                            data-wedding-aperture-label>
                            f/1.4
                        </p>
                    </div>
                </div>
            </div>

            <div class="relative z-20 mx-auto w-full max-w-[1920px] px-6 pt-[24vh] md:px-12 md:pt-[36vh]"
                data-wedding-gallery>
                @if ($description)
                    <p class="mb-8 max-w-3xl text-sm leading-relaxed text-white/75 md:mb-12 md:text-lg">
                        {!! nl2br(e($description)) !!}
                    </p>
                @endif

                <div class="grid grid-cols-1 gap-5 md:grid-cols-1 md:gap-y-12">
                    @foreach ($photos as $index => $photo)
                        @php
                            $image = $photo['image'] ?? null;
                            $caption = $photo['caption'] ?? null;
                            $imageWidth = $image['width'] ?? null;
                            $imageHeight = $image['height'] ?? null;
                            $isLandscape = $imageWidth && $imageHeight ? $imageWidth >= $imageHeight : true;
                            $sizeClass = $isLandscape ? 'md:w-[50%]' : 'md:w-[34%]';
                            $desktopAlign = $index % 2 === 0 ? 'md:justify-self-start' : 'md:justify-self-end';
                            $desktopWidth = $sizeClass . ' ' . $desktopAlign;
                            $imageMaxHeight = $isLandscape ? 'md:max-h-[52svh]' : 'md:max-h-[68svh]';
                            $desktopSpacingVariants = ['md:mt-0', 'md:mt-2', 'md:mt-4', 'md:mt-1'];
                            $desktopSpacingClass = $desktopSpacingVariants[$index % count($desktopSpacingVariants)];
                        @endphp

                        @if (!empty($image['ID']))
                            <figure class="group w-full md:w-auto {{ $desktopWidth }} {{ $desktopSpacingClass }}" data-wedding-item>
                                <div class="relative overflow-hidden rounded-sm" data-wedding-image-shell>
                                    {!! wp_get_attachment_image($image['ID'], 'large', false, [
                                        'class' => 'h-auto w-full object-contain grayscale ' . $imageMaxHeight,
                                    ]) !!}

                                    {!! wp_get_attachment_image($image['ID'], 'large', false, [
                                        'class' => 'pointer-events-none absolute inset-0 h-full w-full object-contain ' . $imageMaxHeight,
                                        'data-wedding-image-color' => '1',
                                    ]) !!}
                                </div>

                                @if ($caption)
                                    <figcaption class="pt-3 text-[11px] uppercase tracking-[0.16em] text-white/55">
                                        {{ $caption }}
                                    </figcaption>
                                @endif
                            </figure>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
