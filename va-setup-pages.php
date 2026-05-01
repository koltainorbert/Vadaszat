<?php
/**
 * VA Setup Pages – egyszer futtatandó!
 * Hozzáférés: http://apro.local/va-setup-pages.php
 * TÖRÖLD LE FUTTATÁS UTÁN!
 */

define( 'ABSPATH', dirname(__FILE__) . '/' );
require_once dirname(__FILE__) . '/wp-load.php';

if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Nincs jogosultságod. Előbb jelentkezz be adminként!' );
}

$pages = [
    [
        'slug'      => 'va-regisztracio',
        'title'     => 'Regisztráció',
        'shortcode' => '[va_register_form]',
    ],
    [
        'slug'      => 'va-bejelentkezes',
        'title'     => 'Bejelentkezés',
        'shortcode' => '[va_login_form]',
    ],
    [
        'slug'      => 'va-hirdetes-feladas',
        'title'     => 'Hirdetés feladása',
        'shortcode' => '[va_submit_listing]',
    ],
    [
        'slug'      => 'va-fiok',
        'title'     => 'Fiókom',
        'shortcode' => '[va_user_dashboard]',
    ],
    [
        'slug'      => 'va-hirdetes-kereses',
        'title'     => 'Hirdetések',
        'shortcode' => '[va_listing_search]',
    ],
    [
        'slug'      => 'va-aukciok',
        'title'     => 'Aukciók',
        'shortcode' => '[va_auction_list]',
    ],
];

echo '<style>body{font-family:sans-serif;max-width:700px;margin:40px auto;background:#111;color:#eee;} .ok{color:#0f0;} .skip{color:#aaa;} h2{color:#f00;}</style>';
echo '<h2>VA Oldalak beállítása</h2><ul>';

foreach ( $pages as $p ) {
    $existing = get_page_by_path( $p['slug'] );

    if ( $existing ) {
        // Frissíti a tartalmat ha üres
        if ( empty( trim( $existing->post_content ) ) ) {
            wp_update_post( [
                'ID'           => $existing->ID,
                'post_content' => $p['shortcode'],
            ] );
            echo '<li class="ok">✓ Frissítve: <b>' . $p['title'] . '</b> (ID: ' . $existing->ID . ') → ' . $p['shortcode'] . '</li>';
        } else {
            echo '<li class="skip">– Már megvan (nem üres): <b>' . $p['title'] . '</b> → ' . esc_html( $existing->post_content ) . '</li>';
        }
    } else {
        $id = wp_insert_post( [
            'post_title'   => $p['title'],
            'post_name'    => $p['slug'],
            'post_content' => $p['shortcode'],
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ] );
        echo '<li class="ok">✓ Létrehozva: <b>' . $p['title'] . '</b> (ID: ' . $id . ') → ' . $p['shortcode'] . '</li>';
    }
}

echo '</ul>';

// Rewrite flush
flush_rewrite_rules();
echo '<p class="ok"><b>Permalink flush kész.</b></p>';
echo '<p style="color:#f00;font-weight:bold;">TÖRÖLD LE EZT A FÁJLT: /va-setup-pages.php</p>';
