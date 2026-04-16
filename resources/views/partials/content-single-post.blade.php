@php
    $postId = get_the_ID();
    $imageId = get_post_thumbnail_id($postId);
    $postDate = get_the_date('Y.m.d', $postId);
    $categories = get_the_category($postId);
    $categoryLabel = !empty($categories) ? ($categories[0]->name ?? __('POST', 'sage')) : __('POST', 'sage');
    $postTitle = html_entity_decode((string) $title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $postExcerpt = has_excerpt()
        ? html_entity_decode((string) get_the_excerpt($postId), ENT_QUOTES | ENT_HTML5, 'UTF-8')
        : '';

    $postsArchiveUrl = home_url('/posts');
@endphp

<article @php(post_class('post-single relative bg-black text-white'))>
    <section class="relative pb-8 pt-24 md:pb-12 md:pt-40">
        <div class="pointer-events-none absolute inset-0 opacity-70">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_16%_14%,rgba(255,255,255,0.08),transparent_42%),radial-gradient(circle_at_80%_78%,rgba(239,68,68,0.08),transparent_34%)]"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-[1500px] px-4 md:px-8">
            <a href="{{ $postsArchiveUrl }}"
                class="inline-flex items-center gap-2 text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-white/72 no-underline transition hover:text-white"
                style="text-decoration:none !important;">
                <span aria-hidden="true">↳</span>
                <span>{{ __('Wszystkie wpisy', 'sage') }}</span>
            </a>

            <div class="mt-5 flex flex-wrap items-center gap-2 text-[0.66rem] uppercase tracking-[0.12em] text-white/72 md:text-[0.72rem]">
                <span>{{ $postDate }}</span>
                <span class="rounded bg-white px-1.5 py-[1px] text-[0.55rem] font-semibold tracking-[0.08em] text-black md:text-[0.62rem]">{{ $categoryLabel }}</span>
            </div>

            <h1 class="mt-5 max-w-[18ch] text-[clamp(1.85rem,5.6vw,5rem)] font-semibold uppercase leading-[0.93] tracking-[0.01em]">
                {{ $postTitle }}
            </h1>

            @if ($postExcerpt !== '')
                <p class="mt-6 max-w-[66ch] text-sm leading-relaxed text-white/76 md:text-[1rem]">
                    {{ $postExcerpt }}
                </p>
            @endif

            @if ($imageId)
                <div class="mt-7 overflow-hidden rounded-[4px] border border-white/12 bg-white/5 md:mt-9">
                    {!! wp_get_attachment_image($imageId, 'large', false, ['class' => 'h-auto w-full object-cover']) !!}
                </div>
            @endif
        </div>
    </section>

    <section class="pb-16 md:pb-24">
        <div class="mx-auto w-full max-w-[900px] px-4 md:px-8">
            <div class="post-single-content text-white/88 [&_a]:text-white [&_a]:underline [&_a:hover]:text-[#ef4444] [&_blockquote]:border-l [&_blockquote]:border-white/28 [&_blockquote]:pl-4 [&_figcaption]:mt-2 [&_figcaption]:text-sm [&_figcaption]:text-white/62 [&_h2]:mt-12 [&_h2]:text-[clamp(1.35rem,2.3vw,2.25rem)] [&_h2]:font-semibold [&_h2]:uppercase [&_h2]:leading-[1.05] [&_h2]:tracking-[0.01em] [&_h3]:mt-10 [&_h3]:text-[clamp(1.12rem,1.8vw,1.6rem)] [&_h3]:font-semibold [&_h3]:uppercase [&_h3]:tracking-[0.01em] [&_img]:rounded-[2px] [&_img]:border [&_img]:border-white/10 [&_img]:max-w-full [&_li]:leading-relaxed [&_ol]:my-5 [&_ol]:space-y-2 [&_p]:my-5 [&_p]:leading-relaxed [&_pre]:overflow-x-auto [&_pre]:rounded-sm [&_pre]:border [&_pre]:border-white/14 [&_pre]:bg-black/45 [&_pre]:p-4 [&_ul]:my-5 [&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:pl-6">
                @php(the_content())
            </div>

            @if ($pagination())
                <footer class="mt-10 border-t border-white/12 pt-6">
                    <nav class="page-nav text-sm text-white/75" aria-label="{{ esc_attr__('Podstrony wpisu', 'sage') }}">
                        {!! $pagination !!}
                    </nav>
                </footer>
            @endif
        </div>
    </section>
</article>
