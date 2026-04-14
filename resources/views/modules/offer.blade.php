@php
    $sectionTitle = trim((string) ($module['section_title'] ?? ''));
    $sectionTitle = $sectionTitle !== '' ? $sectionTitle : 'OFFER';

    $sectionSubtitle = trim((string) ($module['section_subtitle'] ?? ''));
    $sectionSubtitle = $sectionSubtitle !== '' ? $sectionSubtitle : '(OFERTA)';

    $heading = trim((string) ($module['heading'] ?? ''));
    $heading = $heading !== '' ? $heading : 'Oferta dopasowana do Twojego wydarzenia';

    $description = trim((string) ($module['description'] ?? ''));

    $cards = $module['offer_cards'] ?? [];
    $cards = is_array($cards) ? $cards : [];
@endphp

@if (!empty($cards))
    <section class="offer-module relative bg-black text-white" data-offer-module>
        <div class="pointer-events-none absolute inset-0 opacity-70">
            <div
                class="absolute inset-0 bg-[radial-gradient(circle_at_22%_18%,rgba(255,255,255,0.08),transparent_40%),radial-gradient(circle_at_82%_82%,rgba(239,68,68,0.1),transparent_34%)]">
            </div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-[1900px] px-4 py-16 md:px-10 md:py-24">
            <div class="grid grid-cols-1 gap-10 md:grid-cols-[30%_1fr] md:gap-14">
                <div class="md:sticky md:top-28 md:self-start" data-offer-left>
                    <p class="text-[0.75rem] uppercase tracking-[0.28em] text-white/55" data-offer-left-anim>{{ $sectionTitle }}</p>
                    <p class="mt-2 text-[0.72rem] uppercase tracking-[0.16em] text-white/38">{{ $sectionSubtitle }}</p>

                    <h2
                        class="mt-7 text-[clamp(1.85rem,4.2vw,4rem)] font-semibold uppercase leading-[0.94] tracking-[0.01em] text-white"
                        data-offer-left-anim>
                        {{ $heading }}
                    </h2>

                    @if ($description !== '')
                        <p class="mt-6 max-w-[36ch] text-sm leading-relaxed text-white/72 md:text-base">
                            {!! nl2br(e($description)) !!}
                        </p>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-4 md:gap-5">
                    @foreach ($cards as $index => $card)
                        @php
                            $label = trim((string) ($card['label'] ?? 'Pakiet'));
                            $title = trim((string) ($card['title'] ?? ''));
                            $price = trim((string) ($card['price'] ?? ''));
                            $cardDescription = trim((string) ($card['description'] ?? ''));
                            $button = $card['button'] ?? null;
                            $features = $card['features'] ?? [];
                            $features = is_array($features) ? $features : [];
                        @endphp

                        @if ($title !== '')
                            <article class="offer-card group relative overflow-hidden rounded-sm border border-white/14 bg-white/[0.03] p-5 md:p-7"
                                data-offer-card>
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div>
                                        <p class="text-[0.66rem] uppercase tracking-[0.2em] text-white/45">
                                            {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }} · {{ $label }}
                                        </p>
                                        <h3
                                            class="mt-2 text-[clamp(1.15rem,2.2vw,2.1rem)] font-semibold uppercase leading-[1.05] tracking-[0.01em]"
                                            data-offer-card-title>
                                            {{ $title }}
                                        </h3>
                                    </div>

                                    @if ($price !== '')
                                        <p
                                            class="inline-flex rounded-sm border border-white/20 bg-black/40 px-3 py-1 text-[0.72rem] font-medium uppercase tracking-[0.12em] text-white/88">
                                            {{ $price }}
                                        </p>
                                    @endif
                                </div>

                                @if ($cardDescription !== '')
                                    <p class="mt-5 max-w-[74ch] text-sm leading-relaxed text-white/73 md:text-[0.98rem]">
                                        {!! nl2br(e($cardDescription)) !!}
                                    </p>
                                @endif

                                @php
                                    $featureRows = array_values(
                                        array_filter($features, static fn($item) => trim((string) ($item['text'] ?? '')) !== ''),
                                    );
                                @endphp
                                @if (!empty($featureRows))
                                    <ul class="mt-5 grid grid-cols-1 gap-y-2 text-sm text-white/78 md:grid-cols-2 md:gap-x-6">
                                        @foreach ($featureRows as $feature)
                                            <li class="flex items-start gap-2.5">
                                                <span class="mt-[0.45rem] h-[5px] w-[5px] shrink-0 rounded-full bg-[#ef4444]"></span>
                                                <span>{{ $feature['text'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if (!empty($button['url']) && !empty($button['title']))
                                    <div class="mt-6">
                                        <a href="{{ $button['url'] }}" target="{{ $button['target'] ?? '_self' }}"
                                            class="inline-flex items-center gap-2 text-[0.7rem] font-semibold uppercase tracking-[0.22em] text-white no-underline transition hover:text-[#ef4444]"
                                            style="text-decoration:none !important;">
                                            <span>{{ $button['title'] }}</span>
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M7 7h10v10M17 7L7 17" stroke="currentColor" stroke-width="1.8"
                                                    stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </a>
                                    </div>
                                @endif
                            </article>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
