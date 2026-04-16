@php
    $sidebarTitle = trim((string) ($module['sidebar_title'] ?? ''));
    if ($sidebarTitle === '') {
        $sidebarTitle = 'CONTACT';
    }

    $sidebarSubtitle = trim((string) ($module['sidebar_subtitle'] ?? ''));
    if ($sidebarSubtitle === '') {
        $sidebarSubtitle = 'Skontaktujmy się';
    }

    $sidebarSteps = trim((string) ($module['sidebar_steps'] ?? ''));
    if ($sidebarSteps === '') {
        $sidebarSteps = '[Wyślij]  -  [Odpowiedź]  -  [Start]';
    }

    $sidebarAddressLines = $module['sidebar_address_lines'] ?? [];
    $sidebarAddressLines = is_array($sidebarAddressLines) ? $sidebarAddressLines : [];

    $sidebarNote = trim((string) ($module['sidebar_note'] ?? ''));
    if ($sidebarNote === '') {
        $sidebarNote = 'Wypełnij formularz, a odpowiem najszybciej jak to możliwe.';
    }

    $formShortcode = trim((string) ($module['form_shortcode'] ?? ''));
    $renderShortcode = $formShortcode !== '';
@endphp

<section class="contact-contact-form-module relative bg-[#d8d8da] text-black">
    <div class="mx-auto w-full max-w-[1900px] px-4 pb-10 pt-24 md:px-10 md:pb-16 md:pt-32">
        <div class="grid grid-cols-1 gap-10 md:grid-cols-[32%_1fr] md:gap-14">
            <aside class="md:sticky md:top-28 md:self-start">
                <div class="max-w-[34rem] space-y-7 md:space-y-8">
                    <div>
                        <h2 class="text-[clamp(1.65rem,3.1vw,3.15rem)] font-semibold uppercase leading-[0.94] tracking-[0.01em]">
                            {{ $sidebarTitle }}
                        </h2>
                        <p class="mt-3 text-lg font-medium text-black/85 md:text-xl">{{ $sidebarSubtitle }}</p>
                    </div>

                    <p class="text-[0.78rem] uppercase tracking-[0.1em] text-black/45">{{ $sidebarSteps }}</p>

                    @if (!empty($sidebarAddressLines))
                        <div class="space-y-1 text-sm leading-relaxed text-black/72 md:text-base">
                            @foreach ($sidebarAddressLines as $lineItem)
                                @php($line = trim((string) ($lineItem['line'] ?? '')))
                                @if ($line !== '')
                                    <p>{{ $line }}</p>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <p class="max-w-lg text-sm leading-relaxed text-black/62 md:text-base">{{ $sidebarNote }}</p>
                </div>
            </aside>

            <div class="rounded-[4px] bg-[#dfe0e2] px-5 py-6 md:px-10 md:py-10">
                @if ($renderShortcode)
                    <div class="contact-contact-form-shortcode max-w-none min-w-0">
                        {!! do_shortcode($formShortcode) !!}
                    </div>
                @else
                    <form class="contact-contact-form-fake" action="#" method="post" novalidate>
                        <div class="ccf-field">
                            <label for="ccf-full-name">Imię i nazwisko <span>*</span></label>
                            <input id="ccf-full-name" name="full_name" type="text" placeholder="Wpisz imię i nazwisko" required>
                        </div>

                        <div class="ccf-field">
                            <label for="ccf-email">Adres e-mail <span>*</span></label>
                            <input id="ccf-email" name="email" type="email" placeholder="Wpisz adres e-mail" required>
                        </div>

                        <div class="ccf-field">
                            <label for="ccf-phone">Numer telefonu</label>
                            <input id="ccf-phone" name="phone" type="tel" placeholder="Wpisz numer telefonu">
                        </div>

                        <div class="ccf-field">
                            <label for="ccf-event-type">Jakie wydarzenie Cię interesuje? <span>*</span></label>
                            <select id="ccf-event-type" name="event_type" required>
                                <option value="" selected disabled>Wybierz typ wydarzenia</option>
                                <option value="slub">Ślub</option>
                                <option value="przyjecie">Przyjęcie</option>
                                <option value="sesja-rodzinna">Sesja rodzinna</option>
                                <option value="sesja-narzeczenska">Sesja narzeczeńska</option>
                                <option value="chrzest">Chrzciny</option>
                                <option value="inne">Inne</option>
                            </select>
                        </div>

                        <div class="ccf-field ccf-field-textarea">
                            <label for="ccf-message">Wiadomość</label>
                            <textarea id="ccf-message" name="message" rows="5" placeholder="Opowiedz mi o swoim wydarzeniu"></textarea>
                        </div>

                        <div class="ccf-checkbox-wrap">
                            <label class="ccf-checkbox">
                                <input type="checkbox" name="style_confirm" required>
                                <span>Zapoznałam/em się z Twoim stylem pracy i wiem, że to to, czego szukam <span>*</span></span>
                            </label>
                        </div>

                        <button type="submit" class="ccf-submit">Wyślij zapytanie</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</section>
