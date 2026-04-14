<?php
/**
 * Plugin Name: Interview - Simple Post Field
 * Description: Prosty plugin rekrutacyjny: dodaje pole do posta i zapisuje jego wartość.
 * Version: 1.0.0
 * Author: Interview Practice
 */

if (! defined('ABSPATH')) {
    exit; // Blokuje bezpośrednie wejście do pliku poza WordPressem.
}

/**
 * 1) Dodajemy meta box do ekranu edycji zwykłego posta (`post`).
 * Hook: `add_meta_boxes` uruchamia się podczas budowania formularza edycji wpisu.
 */
add_action('add_meta_boxes', function (): void {
    add_meta_box(
        'interview_extra_note',             // Unikalne ID meta boxa.
        'Dodatkowa notatka (plugin demo)',  // Tytuł widoczny w panelu.
        'interview_render_extra_note_box',  // Funkcja renderująca HTML pola.
        'post',                             // Typ wpisu: tylko post.
        'normal',                           // Pozycja.
        'default'                           // Priorytet.
    );
});

/**
 * 2) Render HTML pola w edycji posta.
 * Używamy nonce, żeby zabezpieczyć zapis przed nieautoryzowanym żądaniem.
 */
function interview_render_extra_note_box(WP_Post $post): void
{
    // Tworzymy nonce i zapisujemy je jako hidden input.
    wp_nonce_field('interview_save_extra_note', 'interview_extra_note_nonce');

    // Pobieramy istniejącą wartość z bazy (meta key: _interview_extra_note).
    $value = get_post_meta($post->ID, '_interview_extra_note', true);
    ?>
    <p>
        <label for="interview_extra_note">
            To pole zapisuje prostą notatkę przypisaną do posta:
        </label>
    </p>
    <textarea
        id="interview_extra_note"
        name="interview_extra_note"
        rows="4"
        style="width:100%;"
    ><?php echo esc_textarea((string) $value); ?></textarea>
    <?php
}

/**
 * 3) Zapis pola podczas zapisu posta.
 * Hook: `save_post` odpala się przy zapisie/aktualizacji wpisu.
 */
add_action('save_post', function (int $post_id): void {
    // 3a) Pomijamy autosave, żeby nie nadpisywać danych w tle.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // 3b) Sprawdzamy, czy nonce istnieje i jest poprawny.
    if (
        ! isset($_POST['interview_extra_note_nonce']) ||
        ! wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['interview_extra_note_nonce'])),
            'interview_save_extra_note'
        )
    ) {
        return;
    }

    // 3c) Sprawdzamy uprawnienia użytkownika do edycji posta.
    if (! current_user_can('edit_post', $post_id)) {
        return;
    }

    // 3d) Sprawdzamy, czy nasze pole przyszło w żądaniu.
    if (! isset($_POST['interview_extra_note'])) {
        return;
    }

    // 3e) Sanitizacja danych z formularza (bezpieczny tekst).
    $note = sanitize_textarea_field(wp_unslash($_POST['interview_extra_note']));

    // 3f) Zapis do post meta.
    // update_post_meta:
    // - utworzy klucz, jeśli nie istnieje,
    // - zaktualizuje wartość, jeśli już istnieje.
    update_post_meta($post_id, '_interview_extra_note', $note);
});
