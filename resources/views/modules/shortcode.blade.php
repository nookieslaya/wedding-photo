@php
    $sectionTitle = trim((string) ($module['section_title'] ?? ''));
    $sectionSubtitle = trim((string) ($module['section_subtitle'] ?? ''));
    $shortcode = trim((string) ($module['shortcode'] ?? ''));
    $fullWidth = !empty($module['full_width']);
@endphp

@if ($shortcode !== '')
    <section class="shortcode-module relative text-white">
        <div
            class="{{ $fullWidth ? 'w-[100dvw] [margin-left:calc(50%-50dvw)]' : 'mx-auto w-full max-w-[1900px] px-4 md:px-10' }} py-10 md:py-14">
            @if ($sectionTitle !== '' || $sectionSubtitle !== '')
                <div class="{{ $fullWidth ? 'mx-auto w-full max-w-[1900px] px-4 md:px-10' : '' }} mb-6 md:mb-8">
                    @if ($sectionTitle !== '')
                        <p class="text-[0.74rem] uppercase tracking-[0.22em] text-white/62">{{ $sectionTitle }}</p>
                    @endif
                    @if ($sectionSubtitle !== '')
                        <p class="mt-2 text-[0.74rem] uppercase tracking-[0.16em] text-white/45">{{ $sectionSubtitle }}</p>
                    @endif
                </div>
            @endif

            {!! do_shortcode($shortcode) !!}
        </div>
    </section>
@endif

