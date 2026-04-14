@extends('layouts.app')

@section('content')
  <section class="relative isolate overflow-hidden bg-black text-white">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_20%_15%,rgba(255,255,255,0.10),transparent_42%),radial-gradient(circle_at_80%_85%,rgba(255,0,0,0.10),transparent_38%)]"></div>

    <div class="relative mx-auto flex min-h-[72svh] w-full max-w-[1480px] items-center px-6 pb-20 pt-36 md:px-10 md:pt-44">
      <div class="relative w-full max-w-[980px]">
        <p class="mb-5 text-xs uppercase tracking-[0.35em] text-white/55">Error</p>
        <h1 class="leading-[0.92] font-black uppercase tracking-[0.04em] text-[clamp(3.2rem,11vw,10rem)]">
          404
        </h1>
        <h2 class="mt-4 max-w-[18ch] text-[clamp(1.2rem,3vw,2.1rem)] font-semibold uppercase tracking-[0.08em] text-white/92">
          Ta strona nie istnieje
        </h2>
        <p class="mt-6 max-w-[52ch] text-sm leading-relaxed text-white/72 md:text-base">
          Link mógł być nieaktualny albo adres został wpisany niepoprawnie.
          Przejdź na stronę główną lub sprawdź listę wydarzeń.
        </p>

        <div class="mt-10 flex flex-wrap items-center gap-4 md:gap-5">
          <a href="{{ home_url('/') }}"
             class="inline-flex min-h-12 items-center border border-white bg-white px-6 text-[11px] font-semibold uppercase tracking-[0.28em] text-black no-underline transition hover:bg-white/90"
             style="text-decoration:none !important;">
            Strona główna
          </a>
          <a href="{{ home_url('/wydarzenia') }}"
             class="inline-flex min-h-12 items-center border border-white/45 px-6 text-[11px] font-semibold uppercase tracking-[0.28em] text-white no-underline transition hover:border-white hover:bg-white/8"
             style="text-decoration:none !important;">
            Wydarzenia
          </a>
        </div>

        <span class="pointer-events-none absolute -top-12 right-0 hidden select-none text-[clamp(7rem,22vw,18rem)] font-black leading-none tracking-[0.01em] text-white/[0.07] md:block">
          404
        </span>
      </div>
    </div>
  </section>
@endsection

