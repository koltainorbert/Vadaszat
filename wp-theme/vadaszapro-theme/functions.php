<?php
/**
 * VadászApró Theme – functions.php
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Theme setup ──────────────────────────────────── */
add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'gallery', 'caption' ] );
    add_image_size( 'va-card',   400, 300, true );
    add_image_size( 'va-detail', 900, 600, false );

    register_nav_menus([
        'primary'   => 'Főmenü',
        'footer'    => 'Footer menü',
    ]);
});

/* ── Widgetek ─────────────────────────────────────── */
add_action( 'widgets_init', function () {
    register_sidebar([ 'id' => 'va-sidebar-left',  'name' => 'Bal oldalsáv',   'before_widget' => '', 'after_widget' => '' ]);
    register_sidebar([ 'id' => 'va-sidebar-right', 'name' => 'Jobb oldalsáv',  'before_widget' => '', 'after_widget' => '' ]);
    register_sidebar([ 'id' => 'va-footer-1',      'name' => 'Footer widget 1','before_widget' => '', 'after_widget' => '' ]);
    register_sidebar([ 'id' => 'va-footer-2',      'name' => 'Footer widget 2','before_widget' => '', 'after_widget' => '' ]);
});

/* ── Enqueue ──────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style( 'va-theme', get_stylesheet_uri(), [], '2.9.7' );
});

/* ── Alapoldalak automatikus létrehozása (egyszer fut) ── */
add_action( 'wp_loaded', function () {
    if ( get_option( 'va_pages_created_v3' ) ) return;
    $pages = [
        'kategoria'           => 'Kategóriák',
        'va-hirdetes-kereses' => 'Hirdetés keresés',
        'va-hirdetes-feladas' => 'Hirdetés feladás',
        'va-bejelentkezes'    => 'Bejelentkezés',
        'va-regisztracio'     => 'Regisztráció',
        'va-fiok'             => 'Fiókom',
        'va-aukciok'          => 'Aukciók',
    ];
    foreach ( $pages as $slug => $title ) {
        if ( ! get_page_by_path( $slug ) ) {
            wp_insert_post( [
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ] );
        }
    }
    update_option( 'va_pages_created_v3', '1' );
} );

/* ── Custom login/register page átirányítás ──────── */
add_action( 'template_redirect', function () {
    if ( is_page() && get_option( 'va_maintenance_mode', '0' ) === '1' && ! current_user_can( 'administrator' ) ) {
        wp_die( esc_html( get_option( 'va_maintenance_msg', 'Karbantartás alatt.' ) ), 'Karbantartás', 503 );
    }
});

/* ── Breadcrumb ──────────────────────────────────── */
function va_breadcrumb(): void {
    echo '<nav class="va-breadcrumb" aria-label="Breadcrumb"><ul>';
    echo '<li><a href="' . esc_url( home_url() ) . '">Főoldal</a></li>';
    if ( is_singular( 'va_listing' ) ) {
        echo '<li><a href="' . esc_url( get_post_type_archive_link( 'va_listing' ) ) . '">Hirdetések</a></li>';
        echo '<li>' . esc_html( get_the_title() ) . '</li>';
    } elseif ( is_singular( 'va_auction' ) ) {
        echo '<li><a href="' . esc_url( get_post_type_archive_link( 'va_auction' ) ) . '">Aukciók</a></li>';
        echo '<li>' . esc_html( get_the_title() ) . '</li>';
    } elseif ( is_page() ) {
        echo '<li>' . esc_html( get_the_title() ) . '</li>';
    }
    echo '</ul></nav>';
}

/* ── Kategória emoji ikonok ───────────────────────── */
function va_category_icon( int $term_id ): string {
    // Slug-alapú emoji ikonok (referencia: koltainorbert/tt1 vadasz-apro)
    $slug_icons = [
        'golyos-puska'       => '🎯',
        'soretes-puska'      => '🔫',
        'vegyescsovu'        => '🎯',
        'maroklofegyver'     => '🔫',
        'egyeb-fegyver'      => '⚙️',
        'loszer'             => '🔴',
        'kesek'              => '🔪',
        'tavcsovek'          => '🔭',
        'ejjellato'          => '👁️',
        'hokamera'           => '🌡️',
        'vadkamera'          => '📷',
        'vadaszlampa'        => '💡',
        'vadasz-ruhazat'     => '🧥',
        'cipo-bakancs'       => '👢',
        'egyeb-ruhazat'      => '👕',
        'vadasz-felsz'       => '🎒',
        'sportlovo-felsz'    => '🏆',
        'trofea'             => '🦌',
        'vadasz-kutya'       => '🐕',
        'vadasz-lehetoseg'   => '🌲',
        'vadkarelharias'     => '⚡',
        'szallas'            => '🏡',
        'ingatlan'           => '🏘️',
        'jarmu'              => '🚙',
        'takarmany'          => '🌾',
        'konyv'              => '📚',
        'disztargy'          => '🏺',
        'kurtok-sipok'       => '📯',
        'szolgaltatas'       => '🔧',
        'allas'              => '💼',
        'csere'              => '🔄',
        'hagyatek'           => '📦',
        'egyeb'              => '📌',
    ];
    // Névalapú fallback
    $name_icons = [
        'Fegyver'                => '🔫',
        'Golyós fegyver'         => '🎯',
        'Sörétes fegyver'        => '🔫',
        'Kombinált fegyver'      => '🎯',
        'Légfegyver'             => '🔫',
        'Lőszer'                 => '🔴',
        'Optika & Kiegészítők'   => '🔭',
        'Ruházat & Felszerelés'  => '🧥',
        'Vadászkutya'            => '🐕',
        'Vadászterület & Bérlet' => '🌲',
        'Trófeák'                => '🦌',
        'Egyéb'                  => '📌',
    ];

    $term = get_term( $term_id, 'va_category' );
    if ( is_wp_error( $term ) || ! $term ) return '📌';
    return $slug_icons[ $term->slug ] ?? $name_icons[ $term->name ] ?? '📌';
}
