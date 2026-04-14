@php
    $sectionTitle = trim((string) ($module['section_title'] ?? ''));
    $sectionTitle = $sectionTitle !== '' ? $sectionTitle : 'PRODUCTS';

    $sectionSubtitle = trim((string) ($module['section_subtitle'] ?? ''));
    $sectionSubtitle = $sectionSubtitle !== '' ? $sectionSubtitle : '(PRODUKTY)';

    $heading = trim((string) ($module['heading'] ?? ''));
    $heading = $heading !== '' ? $heading : 'Produkty i dodatki do Twojej historii';

    $description = trim((string) ($module['description'] ?? ''));
    $mainImage = $module['main_image'] ?? null;

    $cards = $module['product_cards'] ?? [];
    $cards = is_array($cards) ? $cards : [];
    $hasCards = !empty($cards);
@endphp

    <section class="products-module relative bg-[#d8d8da] text-black {{ $hasCards ? '' : 'min-h-[100svh]' }}"
        data-products-module>
        <div
            class="mx-auto w-full max-w-[1900px] px-4 {{ $hasCards ? 'py-16 md:px-10 md:py-24' : 'flex min-h-[100svh] items-center py-16 md:px-10 md:py-24' }}">
            <div
                class="grid grid-cols-1 gap-10 {{ $hasCards ? 'md:grid-cols-[32%_1fr] md:gap-14' : 'md:grid-cols-1 md:gap-0' }}">
                <div class="md:sticky md:top-28 md:self-start">
                    <p class="text-[0.75rem] uppercase tracking-[0.28em] text-black/58">{{ $sectionTitle }}</p>
                    <p class="mt-2 text-[0.72rem] uppercase tracking-[0.16em] text-black/42">{{ $sectionSubtitle }}</p>

                    <h2 class="mt-7 text-[clamp(1.85rem,4.2vw,4rem)] font-semibold uppercase leading-[0.94] tracking-[0.01em]">
                        {{ $heading }}
                    </h2>

                    @if ($description !== '')
                        <p class="mt-6 max-w-[38ch] text-sm leading-relaxed text-black/68 md:text-base">
                            {!! nl2br(e($description)) !!}
                        </p>
                    @endif

                    @if (!empty($mainImage['ID']))
                        <div class="mt-8 overflow-hidden rounded-[4px] border border-black/10 bg-white/35">
                            {!! wp_get_attachment_image($mainImage['ID'], 'large', false, ['class' => 'h-auto w-full object-cover']) !!}
                        </div>
                    @endif
                </div>

                @if ($hasCards)
                    <div class="grid grid-cols-1 gap-4 md:gap-5">
                        @foreach ($cards as $card)
                        @php
                            $label = trim((string) ($card['label'] ?? 'Produkt'));
                            $title = trim((string) ($card['title'] ?? ''));
                            $price = trim((string) ($card['price'] ?? ''));
                            $cardDescription = trim((string) ($card['description'] ?? ''));
                            $cardImage = $card['image'] ?? null;
                            $button = $card['button'] ?? null;
                            $features = $card['features'] ?? [];
                            $features = is_array($features) ? $features : [];
                        @endphp

                        @if ($title !== '')
                            <article class="products-card relative overflow-hidden rounded-[4px] border border-black/12 bg-[#ececee] p-5 md:p-7"
                                data-products-card>
                                <div class="flex flex-wrap items-start justify-between gap-4">
                                    <div>
                                        <p class="text-[0.66rem] uppercase tracking-[0.2em] text-black/44">
                                            {{ $label }}
                                        </p>
                                        <h3 class="mt-2 text-[clamp(1.15rem,2.2vw,2.1rem)] font-semibold uppercase leading-[1.05] tracking-[0.01em]"
                                            data-products-card-title>
                                            {{ $title }}
                                        </h3>
                                    </div>

                                    @if ($price !== '')
                                        <p class="inline-flex rounded-sm border border-black/16 bg-black/5 px-3 py-1 text-[0.72rem] font-medium uppercase tracking-[0.12em] text-black/86">
                                            {{ $price }}
                                        </p>
                                    @endif
                                </div>

                                <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-[1fr_auto] md:items-start">
                                    <div>
                                        @if ($cardDescription !== '')
                                            <p class="max-w-[72ch] text-sm leading-relaxed text-black/70 md:text-[0.98rem]">
                                                {!! nl2br(e($cardDescription)) !!}
                                            </p>
                                        @endif

                                        @php
                                            $featureRows = array_values(
                                                array_filter($features, static fn($item) => trim((string) ($item['text'] ?? '')) !== ''),
                                            );
                                        @endphp
                                        @if (!empty($featureRows))
                                            <ul class="mt-5 grid grid-cols-1 gap-y-2 text-sm text-black/72 md:grid-cols-2 md:gap-x-6">
                                                @foreach ($featureRows as $feature)
                                                    <li class="flex items-start gap-2.5">
                                                        <span class="mt-[0.45rem] h-[5px] w-[5px] shrink-0 rounded-full bg-[#ef4444]"></span>
                                                        <span>{{ $feature['text'] }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>

                                    @if (!empty($cardImage['ID']))
                                        <div class="overflow-hidden rounded-[3px] border border-black/12 bg-white/40 md:w-[210px]">
                                            {!! wp_get_attachment_image($cardImage['ID'], 'medium', false, ['class' => 'h-full w-full object-cover']) !!}
                                        </div>
                                    @endif
                                </div>

                                @if (!empty($button['url']) && !empty($button['title']))
                                    <div class="mt-6">
                                        <a href="{{ $button['url'] }}" target="{{ $button['target'] ?? '_self' }}"
                                            class="inline-flex items-center gap-2 text-[0.7rem] font-semibold uppercase tracking-[0.22em] text-black no-underline transition hover:text-[#ef4444]"
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
                @endif
            </div>
        </div>
    </section>
