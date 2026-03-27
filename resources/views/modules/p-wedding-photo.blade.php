@php
    $heading = $module['heading'] ?? null;
    $description = $module['description'] ?? null;
    $photos = $module['photos'] ?? [];
@endphp

@if ($heading && !empty($photos))
    <section class="p-wedding-photo relative min-h-[240svh] bg-black text-white md:min-h-[300svh]" data-wedding-photo>
        <div class="sticky top-0 z-10 flex min-h-screen items-center overflow-hidden">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-black via-black/90 to-black"></div>

            <div class="absolute inset-0 z-10 flex top-1/4 justify-center px-6 text-center md:px-10"
                data-wedding-title-wrap>
                <h2 class="max-w-6xl text-[clamp(2.4rem,13vw,12rem)] font-semibold uppercase leading-[0.9] tracking-tight text-white"
                    data-wedding-title>
                    {{ $heading }}
                </h2>
            </div>

            <div class="relative z-20 mx-auto w-full max-w-6xl px-6 opacity-0 md:px-10" data-wedding-gallery>
                @if ($description)
                    <p class="mb-8 max-w-3xl text-sm leading-relaxed text-white/75 md:mb-12 md:text-lg">
                        {!! nl2br(e($description)) !!}
                    </p>
                @endif

                <div class="grid grid-cols-1 gap-5 md:grid-cols-12 md:gap-6">
                    @foreach ($photos as $index => $photo)
                        @php
                            $image = $photo['image'] ?? null;
                            $caption = $photo['caption'] ?? null;
                            $desktopCols = match ($index % 6) {
                                0 => 'md:col-span-7',
                                1 => 'md:col-span-5',
                                2 => 'md:col-span-4',
                                3 => 'md:col-span-8',
                                4 => 'md:col-span-6',
                                default => 'md:col-span-6',
                            };
                        @endphp

                        @if (!empty($image['ID']))
                            <figure class="group {{ $desktopCols }}" data-wedding-item>
                                <div class="overflow-hidden rounded-sm bg-neutral-900">
                                    {!! wp_get_attachment_image($image['ID'], 'large', false, [
                                        'class' =>
                                            'h-[42svh] w-full object-cover grayscale transition duration-500 ease-out group-hover:grayscale-0 md:h-[48svh]',
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
