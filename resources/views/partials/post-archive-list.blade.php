@php
    $archiveTitle = is_home() && !is_front_page() ? get_the_title((int) get_option('page_for_posts')) : get_the_archive_title();
    if (!is_string($archiveTitle) || trim($archiveTitle) === '') {
        $archiveTitle = __('Blog', 'sage');
    }
@endphp

<section class="relative bg-black pb-10 pt-24 text-white md:pb-16 md:pt-40" data-posts-archive>
    <div class="pointer-events-none absolute inset-0 opacity-70">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_16%_14%,rgba(255,255,255,0.08),transparent_42%),radial-gradient(circle_at_80%_78%,rgba(239,68,68,0.08),transparent_34%)]"></div>
    </div>

    <div class="relative z-10 mx-auto w-full max-w-[1900px] px-4 md:px-8">
        <header class="mb-6 flex items-end gap-3 md:mb-8">
            <h1 class="text-[clamp(1.65rem,3.1vw,3.15rem)] font-semibold uppercase leading-[0.94] tracking-[0.01em]">
                {{ $archiveTitle }}
            </h1>
        </header>

        @if (have_posts())
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 md:gap-4 xl:grid-cols-3">
                @while (have_posts())
                    @php
                        the_post();
                        $postId = get_the_ID();
                        $imageId = get_post_thumbnail_id($postId);
                        $postDate = get_the_date('Y.m.d', $postId);
                        $postTitle = get_the_title($postId);
                        $postLink = get_permalink($postId);
                        $postExcerpt = trim((string) get_the_excerpt($postId));
                        $categories = get_the_category($postId);
                        $categoryLabel = !empty($categories) ? ($categories[0]->name ?? __('POST', 'sage')) : __('POST', 'sage');
                    @endphp

                    <article class="aspect-square overflow-hidden rounded-[4px] border border-white/14 bg-white/[0.04]" data-post-card>
                        <a href="{{ $postLink }}"
                            class="flex h-full flex-col no-underline hover:no-underline focus:no-underline"
                            style="text-decoration:none !important;">
                            <div class="h-[46%] overflow-hidden bg-white/10">
                                @if ($imageId)
                                    {!! wp_get_attachment_image($imageId, 'large', false, ['class' => 'h-full w-full object-cover']) !!}
                                @else
                                    <div class="h-full w-full bg-white/8"></div>
                                @endif
                            </div>

                            <div class="flex min-h-0 flex-1 flex-col px-3 pb-3 pt-2 md:px-6 md:py-5">
                                <div class="flex flex-wrap items-center gap-2 border-b border-white/15 pb-3 text-[0.66rem] uppercase tracking-[0.12em] text-white/72 md:text-[0.72rem]">
                                    <span>{{ $postDate }}</span>
                                    <span class="rounded bg-white px-1.5 py-[1px] text-[0.55rem] font-semibold tracking-[0.08em] text-black md:text-[0.62rem]">{{ $categoryLabel }}</span>
                                </div>

                                <h2 class="mt-3 line-clamp-3 text-[1rem] font-light leading-[1.28] tracking-[0.01em] normal-case md:text-[1.2rem] xl:text-[1.35rem]"
                                    data-post-card-title>
                                    {{ $postTitle }}
                                </h2>

                                @if ($postExcerpt !== '')
                                    <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-white/72 md:text-[0.92rem]">
                                        {{ $postExcerpt }}
                                    </p>
                                @endif

                                <span class="mt-auto pt-3 text-right text-3xl text-white/68 md:text-[2rem]">↳</span>
                            </div>
                        </a>
                    </article>
                @endwhile
            </div>

            @php
                $pagination = paginate_links([
                    'type' => 'array',
                    'prev_text' => '←',
                    'next_text' => '→',
                    'mid_size' => 1,
                ]);
            @endphp

            @if (!empty($pagination) && is_array($pagination))
                <nav class="mt-8 flex flex-wrap items-center gap-2 md:mt-10" aria-label="{{ esc_attr__('Nawigacja wpisow', 'sage') }}">
                    @foreach ($pagination as $item)
                        <span class="inline-flex min-h-9 min-w-9 items-center justify-center border border-white/24 px-3 text-[0.66rem] font-semibold uppercase tracking-[0.14em] text-white/88 [&_.current]:border-[#ef4444] [&_.current]:text-[#ef4444] [&_a]:text-white/88 [&_a]:no-underline [&_a:hover]:text-white">
                            {!! $item !!}
                        </span>
                    @endforeach
                </nav>
            @endif
        @else
            <p class="mt-8 text-white/70">
                {{ __('Brak wpisow do wyswietlenia.', 'sage') }}
            </p>
        @endif
    </div>
</section>
