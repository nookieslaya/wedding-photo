<?php

/*
|--------------------------------------------------------------------------
| Interview Learning: CPT + Hooki + Security + REST + WP_Query
|--------------------------------------------------------------------------
|
| Ten plik zbiera kod do nauki i ćwiczeń rekrutacyjnych.
| Dzięki temu `functions.php` pozostaje czystszy.
|
*/

/*
|--------------------------------------------------------------------------
| Custom Post Type: Oferta
|--------------------------------------------------------------------------
*/

// Hook `init` to standardowy moment na rejestrację typów postów w WordPressie.
add_action('init', function (): void {
    // Etykiety widoczne w panelu admina.
    $labels = [
        'name' => __('Oferty', 'sage'),
        'singular_name' => __('Oferta', 'sage'),
        'menu_name' => __('Oferty', 'sage'),
        'name_admin_bar' => __('Oferta', 'sage'),
        'add_new' => __('Dodaj nową', 'sage'),
        'add_new_item' => __('Dodaj nową ofertę', 'sage'),
        'new_item' => __('Nowa oferta', 'sage'),
        'edit_item' => __('Edytuj ofertę', 'sage'),
        'view_item' => __('Zobacz ofertę', 'sage'),
        'all_items' => __('Wszystkie oferty', 'sage'),
        'search_items' => __('Szukaj ofert', 'sage'),
        'not_found' => __('Nie znaleziono ofert', 'sage'),
        'not_found_in_trash' => __('Brak ofert w koszu', 'sage'),
    ];

    register_post_type('oferta', [
        'labels' => $labels,
        'public' => true, // Widoczny na froncie i w panelu admin.
        'show_in_rest' => true, // Włącza edytor blokowy + REST API.
        'menu_icon' => 'dashicons-portfolio', // Ikona w menu WordPressa.
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'], // Pola dostępne w edycji wpisu.
        'has_archive' => true, // Tworzy stronę archiwum pod /oferty.
        'rewrite' => ['slug' => 'oferty'], // Ustawia przyjazny URL.
        'publicly_queryable' => true, // Umożliwia otwieranie single na froncie.
        'exclude_from_search' => false, // Pozwala wyszukiwarce WP uwzględniać ten CPT.
    ]);
});

/*
|--------------------------------------------------------------------------
| DEMO: add_action vs add_filter
|--------------------------------------------------------------------------
*/

// DEMO add_action: wykonujemy akcję przy renderowaniu stopki.
add_action('wp_footer', function (): void {
    if (is_admin()) {
        return;
    }

    echo '<div style="position:fixed;bottom:12px;right:12px;z-index:9999;background:#111;color:#fff;padding:8px 12px;border-radius:8px;font-size:12px;">DEMO add_action: ten box został dodany przez hook <code>wp_footer</code>.</div>';
});

// DEMO add_filter: modyfikujemy treść wpisu i zwracamy wynik.
add_filter('the_content', function (string $content): string {
    if (! is_singular('post') || ! in_the_loop() || ! is_main_query()) {
        return $content;
    }

    $demo_note = '<p style="margin-top:16px;padding:10px 12px;background:#f4f4f4;border-left:4px solid #222;"><strong>DEMO add_filter:</strong> ten tekst został doklejony przez filtr <code>the_content</code>.</p>';

    return $content.$demo_note;
});

/*
|--------------------------------------------------------------------------
| Security Demo: formularz + bezpieczny zapis danych
|--------------------------------------------------------------------------
|
| Pokazujemy razem:
| - nonce (ochrona przed CSRF),
| - current_user_can (uprawnienia),
| - sanitize_* (czyszczenie wejścia),
| - esc_* (bezpieczne wyjście do HTML).
|
*/

add_filter('the_content', function (string $content): string {
    if (! is_singular('post') || ! in_the_loop() || ! is_main_query()) {
        return $content;
    }

    $post_id = get_the_ID();
    if (! is_int($post_id) || $post_id <= 0) {
        return $content;
    }

    // Tylko użytkownik z prawem edycji posta zobaczy formularz.
    if (! current_user_can('edit_post', $post_id)) {
        return $content;
    }

    $saved_note = (string) get_post_meta($post_id, '_interview_secure_note', true);
    $saved_flag = isset($_GET['secure_note']) && $_GET['secure_note'] === 'saved';

    ob_start();
    ?>
    <section style="margin-top:20px;padding:14px;border:1px solid #d9d9d9;border-radius:8px;background:#fafafa;">
        <h3 style="margin-top:0;">Security Demo: zapisz notatkę do posta</h3>

        <?php if ($saved_flag): ?>
            <p style="color:#0a7a2f;"><strong>Zapisano poprawnie.</strong></p>
        <?php endif; ?>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('interview_secure_note_save', 'interview_secure_note_nonce'); ?>
            <input type="hidden" name="action" value="interview_secure_note_save">
            <input type="hidden" name="post_id" value="<?php echo esc_attr((string) $post_id); ?>">

            <p>
                <label for="interview_secure_note"><strong>Notatka prywatna:</strong></label>
            </p>
            <textarea id="interview_secure_note" name="interview_secure_note" rows="4" style="width:100%;"><?php echo esc_textarea($saved_note); ?></textarea>

            <p style="margin-bottom:0;">
                <button type="submit">Zapisz notatkę</button>
            </p>
        </form>
    </section>
    <?php

    return $content.(string) ob_get_clean();
}, 20);

add_action('admin_post_interview_secure_note_save', function (): void {
    $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
    if ($post_id <= 0) {
        wp_die('Nieprawidłowy post_id.');
    }

    if (! current_user_can('edit_post', $post_id)) {
        wp_die('Brak uprawnień.');
    }

    if (
        ! isset($_POST['interview_secure_note_nonce']) ||
        ! wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['interview_secure_note_nonce'])),
            'interview_secure_note_save'
        )
    ) {
        wp_die('Nieprawidłowy token bezpieczeństwa.');
    }

    $note = isset($_POST['interview_secure_note'])
        ? sanitize_textarea_field(wp_unslash($_POST['interview_secure_note']))
        : '';

    update_post_meta($post_id, '_interview_secure_note', $note);

    $redirect_url = add_query_arg('secure_note', 'saved', get_permalink($post_id));
    wp_safe_redirect($redirect_url);
    exit;
});

/*
|--------------------------------------------------------------------------
| REST API Demo: prosty endpoint do zapisu notatki
|--------------------------------------------------------------------------
|
| Endpoint:
| POST /wp-json/interview/v1/note/{post_id}
|
| Uwaga: permission_callback zostawiony tymczasowo otwarty do łatwego
| testowania lokalnie. Po ćwiczeniach wróć do wersji z current_user_can.
|
*/

add_action('rest_api_init', function (): void {
    register_rest_route('interview/v1', '/note/(?P<post_id>\d+)', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => function (WP_REST_Request $request): WP_REST_Response {
            $post_id = (int) $request->get_param('post_id');
            $note = sanitize_text_field((string) $request->get_param('note'));

            update_post_meta($post_id, '_interview_secure_note', $note);

            return rest_ensure_response([
                'success' => true,
                'post_id' => $post_id,
                'saved_note' => $note,
            ]);
        },
        'permission_callback' => '__return_true',
        // Bezpieczna wersja (do włączenia po testach):
        // 'permission_callback' => function (WP_REST_Request $request): bool {
        //     $post_id = (int) $request->get_param('post_id');
        //     return $post_id > 0 && current_user_can('edit_post', $post_id);
        // },
        'args' => [
            'note' => [
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ],
    ]);
});

/*
|--------------------------------------------------------------------------
| WP_Query Demo: pobieranie i wyświetlanie CPT "oferta"
|--------------------------------------------------------------------------
|
| Test:
| - dodaj shortcode [interview_oferty] w treści strony lub wpisu.
|
*/

add_shortcode('interview_oferty', function (): string {
    $query = new WP_Query([
        'post_type' => 'oferta',
        'posts_per_page' => 5,
        'orderby' => 'date',
        'order' => 'DESC',
    ]);

    if (! $query->have_posts()) {
        return '<p>Brak ofert do wyświetlenia.</p>';
    }

    ob_start();
    ?>
    <section style="margin:20px 0;padding:14px;border:1px solid #e5e5e5;border-radius:8px;">
        <h3 style="margin-top:0;">Demo WP_Query: ostatnie oferty</h3>
        <ul style="margin:0;padding-left:20px;">
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <li>
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <?php echo esc_html(get_the_title()); ?>
                    </a>
                    <small style="opacity:.7;">
                        (<?php echo esc_html(get_the_date()); ?>)
                    </small>
                </li>
            <?php endwhile; ?>
        </ul>
    </section>
    <?php

    // Ważne: sprzątamy po custom loopie.
    wp_reset_postdata();

    return (string) ob_get_clean();
});

/*
|--------------------------------------------------------------------------
| Blocks Demo: ACF block + Native block (server-side render)
|--------------------------------------------------------------------------
|
| Minimalne przykłady do nauki:
| 1) ACF block: rejestracja na `acf/init`
| 2) Native block: rejestracja na `init` przez `register_block_type`
|
*/

// 1) ACF BLOCK
// Rejestrujemy tylko jeśli ACF PRO jest aktywne i ma funkcję acf_register_block_type.
add_action('acf/init', function (): void {
    if (! function_exists('acf_register_block_type')) {
        return;
    }

    acf_register_block_type([
        'name' => 'interview-acf-note',
        'title' => __('Interview: ACF Note', 'sage'),
        'description' => __('Prosty blok ACF z jednym polem tekstowym.', 'sage'),
        'category' => 'widgets',
        'icon' => 'edit',
        'keywords' => ['interview', 'acf', 'note'],
        'mode' => 'preview',
        'supports' => [
            'align' => false,
        ],
        'render_callback' => function (): void {
            // Odczyt pola ACF z kontekstu bloku.
            $text = function_exists('get_field') ? (string) get_field('interview_acf_note_text') : '';
            if ($text === '') {
                $text = 'ACF block: wpisz tekst w polu "Treść notatki".';
            }

            echo '<div style="padding:12px;border:1px dashed #999;border-radius:8px;">';
            echo '<strong>ACF Block:</strong> '.esc_html($text);
            echo '</div>';
        },
    ]);

    // Minimalna definicja pola dla bloku ACF (bez klikania w panelu ACF).
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group([
            'key' => 'group_interview_acf_block',
            'title' => 'Interview ACF Block Fields',
            'fields' => [
                [
                    'key' => 'field_interview_acf_note_text',
                    'label' => 'Treść notatki',
                    'name' => 'interview_acf_note_text',
                    'type' => 'text',
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'block',
                        'operator' => '==',
                        'value' => 'acf/interview-acf-note',
                    ],
                ],
            ],
        ]);
    }
});

// 2) NATIVE BLOCK (Gutenberg, bez JS - renderowany po stronie PHP).
add_action('init', function (): void {
    register_block_type('interview/native-note', [
        'api_version' => 2,
        'title' => __('Interview: Native Note', 'sage'),
        'description' => __('Prosty natywny blok renderowany w PHP.', 'sage'),
        'category' => 'widgets',
        'icon' => 'info',
        'keywords' => ['interview', 'native', 'note'],
        'attributes' => [
            'text' => [
                'type' => 'string',
                'default' => 'Native block: to jest domyślna treść.',
            ],
        ],
        'supports' => [
            'html' => false,
        ],
        'render_callback' => function (array $attributes): string {
            $text = isset($attributes['text']) ? (string) $attributes['text'] : '';
            if ($text === '') {
                $text = 'Native block: brak tekstu.';
            }

            return '<div style="padding:12px;border:1px solid #cfcfcf;border-radius:8px;background:#f8f8f8;">'
                .'<strong>Native Block:</strong> '.esc_html($text)
                .'</div>';
        },
    ]);
});

// Rejestracja edytorowa (JS) dla native blocka, żeby był widoczny w inserterze Gutenberg.
add_action('enqueue_block_editor_assets', function (): void {
    $handle = 'interview-native-note-editor';

    // Rejestrujemy "pusty" handle i dokładamy do niego inline JS.
    wp_register_script(
        $handle,
        false,
        ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n'],
        '1.0.0',
        true
    );

    wp_enqueue_script($handle);

    wp_add_inline_script(
        $handle,
        <<<'JS'
(function (blocks, element, blockEditor, i18n) {
  var el = element.createElement;
  var __ = i18n.__;
  var PlainText = blockEditor.PlainText;

  blocks.registerBlockType('interview/native-note', {
    title: __('Interview: Native Note', 'sage'),
    description: __('Prosty natywny blok renderowany w PHP.', 'sage'),
    icon: 'info',
    category: 'widgets',
    attributes: {
      text: {
        type: 'string',
        default: 'Native block: to jest domyślna treść.',
      },
    },
    edit: function (props) {
      return el(
        'div',
        { style: { padding: '12px', border: '1px solid #cfcfcf', borderRadius: '8px', background: '#f8f8f8' } },
        el('strong', null, 'Native Block:'),
        el(PlainText, {
          value: props.attributes.text,
          onChange: function (value) {
            props.setAttributes({ text: value });
          },
          placeholder: 'Wpisz treść...',
        })
      );
    },
    save: function () {
      return null; // Dynamic block: frontend renderuje PHP (render_callback).
    },
  });
})(window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.i18n);
JS
    );
});


// esc_* w WordPressie = escaping, czyli bezpieczne przygotowanie danych do wyświetlenia.

// Po co:

// chroni przed XSS,
// pilnuje, żeby tekst/URL/atrybut HTML nie “zepsuł” markupu.
// Najczęstsze:

// esc_html($text) → zwykły tekst w HTML
// esc_attr($text) → wartość atrybutu, np. value="", id=""
// esc_url($url) → linki w href/src
// esc_textarea($text) → zawartość <textarea>
// Zasada:

// sanitize_* przy zapisie danych,
// esc_* przy wyświetlaniu danych.

// WP_Query to klasa WordPressa do pobierania postów z bazy według warunków.
