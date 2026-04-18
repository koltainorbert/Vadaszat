<?php
/**
 * VadászApró Theme – functions.php
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ══════════════════════════════════════════════════════
 * ⚡ SEBESSÉG – WordPress bloat eltávolítás
 * ══════════════════════════════════════════════════════ */
add_action( 'init', function () {
    // Emoji – felesleges JS+CSS minden oldalon
    remove_action( 'wp_head',             'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles',     'print_emoji_styles' );
    remove_action( 'admin_print_styles',  'print_emoji_styles' );
    remove_filter( 'the_content_feed',    'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss',    'wp_staticize_emoji' );
    remove_filter( 'wp_mail',             'wp_staticize_emoji_for_email' );

    // Felesleges head meta
    remove_action( 'wp_head', 'wp_generator' );             // WP verzió elrejtése (biztonság is)
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'wp_shortlink_wp_head' );
    remove_action( 'wp_head', 'feed_links',          2 );
    remove_action( 'wp_head', 'feed_links_extra',    3 );
    remove_action( 'wp_head', 'rest_output_link_wp_head' );
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
    remove_action( 'template_redirect', 'rest_output_link_header', 11 );
} );

// jQuery migrate – nincs rá szükség
add_action( 'wp_default_scripts', function ( $scripts ) {
    if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
        $scripts->registered['jquery']->deps = array_diff(
            $scripts->registered['jquery']->deps,
            [ 'jquery-migrate' ]
        );
    }
} );

// DNS prefetch + preconnect
add_action( 'wp_head', function () {
    echo '<link rel="preconnect" href="' . esc_url( home_url() ) . '">' . "\n";
    echo '<link rel="dns-prefetch" href="//s.gravatar.com">' . "\n";
}, 1 );

/* ── Theme setup ──────────────────────────────────── */
add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'gallery', 'caption' ] );
    add_image_size( 'va-card',   600, 450, true );  // Hard crop 4:3 – kártyakép szabvány
    add_image_size( 'va-detail', 1200, 800, false ); // Arányőrző – részletoldal

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
    wp_enqueue_style( 'va-theme', get_stylesheet_uri(), [], '3.0.2' );
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
    $svg = [
        // Fegyverek
        'golyos-puska'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 14h13l2-4h2l1 2v2h-1M3 14v2h2v-2M7 16a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0"/><path d="M16 10V8h2"/></svg>',
        'soretes-puska'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2 13h14l3-5h2v5h-2M2 13v3h3v-3M6 16a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0"/></svg>',
        'vegyescsovu'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2 12h14l3-4h2v4h-2M2 12v3h3v-3M6 15a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0"/><line x1="16" y1="10" x2="16" y2="14"/></svg>',
        'maroklofegyver'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 4h8l2 4H6zM6 8v8h3v-3h5v3h2V8"/><path d="M9 13v3M14 4V2"/></svg>',
        'egyeb-fegyver'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="3"/><path d="M12 2v4M12 18v4M2 12h4M18 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M5.6 18.4l2.8-2.8M15.6 8.4l2.8-2.8"/></svg>',
        'loszer'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="9" y="8" width="6" height="12" rx="1"/><path d="M10 8V5a2 2 0 0 1 4 0v3"/><line x1="12" y1="12" x2="12" y2="16"/></svg>',
        'kesek'           => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 20 L18 4 L20 6 L10 20 Z"/><path d="M6 20 L9 17"/></svg>',
        // Optika
        'tavcsovek'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="7"/><path d="M16 16 L22 22" stroke-linecap="round"/></svg>',
        'ejjellato'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="7" cy="12" r="4"/><circle cx="17" cy="12" r="4"/><line x1="11" y1="12" x2="13" y2="12"/><path d="M3 12H1M23 12h-2M7 6V4M17 6V4"/></svg>',
        'hokamera'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="6" width="16" height="12" rx="2"/><path d="M18 9l4-2v10l-4-2"/><circle cx="10" cy="12" r="3"/></svg>',
        'vadkamera'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="7" width="20" height="14" rx="2"/><circle cx="12" cy="14" r="4"/><path d="M16 3l-4 4-4-4"/></svg>',
        'vadaszlampa'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 2h6l2 8H7z"/><rect x="7" y="10" width="10" height="3" rx="1"/><line x1="12" y1="13" x2="12" y2="22"/><line x1="8" y1="22" x2="16" y2="22"/></svg>',
        // Ruházat
        'vadasz-ruhazat'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M20 4L16 6 12 4 8 6 4 4v14h16z"/><path d="M8 6v14M16 6v14"/></svg>',
        'cipo-bakancs'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2 18h14l4-6H8L5 7H2z"/><line x1="2" y1="18" x2="20" y2="18"/></svg>',
        'egyeb-ruhazat'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 3C10 3 8 5 8 7H4l2 4h12l2-4h-4c0-2-2-4-4-4z"/><rect x="6" y="11" width="12" height="10" rx="1"/></svg>',
        // Felszerelés
        'vadasz-felsz'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 20V10l8-8 8 8v10H4z"/><rect x="9" y="14" width="6" height="6"/></svg>',
        'sportlovo-felsz' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="4"/><path d="M8 14l-2 8h12l-2-8"/></svg>',
        // Kiegészítők
        'trofea'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 4h14v8a7 7 0 0 1-14 0V4z"/><path d="M5 6H2v4a3 3 0 0 0 3 3M19 6h3v4a3 3 0 0 1-3 3"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="8" y1="22" x2="16" y2="22"/></svg>',
        'vadasz-kutya'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 12c0-4 2-7 6-7h4c3 0 5 2 6 5l2 4-4 1-1 4H10l-1-4-3-1z"/><circle cx="15" cy="9" r="1" fill="currentColor"/></svg>',
        'vadasz-lehetoseg'=> '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
        'vadkarelharias'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke-linejoin="round"/></svg>',
        'szallas'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 22V8l9-6 9 6v14H3z"/><rect x="9" y="14" width="6" height="8"/></svg>',
        'ingatlan'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="9" width="18" height="13" rx="1"/><path d="M1 9l11-7 11 7"/><line x1="9" y1="22" x2="9" y2="13"/><line x1="15" y1="22" x2="15" y2="13"/></svg>',
        'jarmu'           => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 17H3V9l3-5h10l3 5v8h-2"/><circle cx="7.5" cy="17" r="2"/><circle cx="16.5" cy="17" r="2"/><line x1="9.5" y1="17" x2="14.5" y2="17"/></svg>',
        'takarmany'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2C8 2 5 6 5 10c0 5 3 8 7 10 4-2 7-5 7-10 0-4-3-8-7-8z"/><line x1="12" y1="6" x2="12" y2="14"/><line x1="9" y1="9" x2="15" y2="9"/></svg>',
        'konyv'           => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 19V5a2 2 0 0 1 2-2h14"/><path d="M20 19H6a2 2 0 0 0 0 4h14V3"/></svg>',
        'disztargy'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg>',
        'kurtok-sipok'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>',
        'szolgaltatas'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 1 1-14.14 0"/></svg>',
        'allas'           => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>',
        'csere'           => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7 16V4m0 0L3 8m4-4l4 4"/><path d="M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>',
        'hagyatek'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>',
        'egyeb'           => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="1"/><circle cx="6" cy="12" r="1"/><circle cx="18" cy="12" r="1"/></svg>',
    ];

    $term = get_term( $term_id, 'va_category' );
    if ( is_wp_error( $term ) || ! $term ) {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="1"/><circle cx="6" cy="12" r="1"/><circle cx="18" cy="12" r="1"/></svg>';
    }
    return $svg[ $term->slug ] ?? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="1"/><circle cx="6" cy="12" r="1"/><circle cx="18" cy="12" r="1"/></svg>';
}
