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
    wp_enqueue_style( 'va-theme', get_stylesheet_uri(), [], '1.0.0' );
});

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

/* ── Kategória SVG ikonok (animált, 2026) ─────────── */
function va_category_icon( int $term_id ): string {
    $icons = [
        'Fegyver' => '<svg class="va-svg-icon" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="4" y="14" width="22" height="7" rx="2" stroke="#e8000d" stroke-width="2" fill="none" stroke-dasharray="200" stroke-dashoffset="0"/>
            <rect x="26" y="15" width="6" height="5" rx="1" stroke="#e8000d" stroke-width="2" fill="none" stroke-dasharray="200" stroke-dashoffset="0"/>
            <line x1="8" y1="14" x2="8" y2="10" stroke="#e8000d" stroke-width="2" stroke-linecap="round"/>
            <circle cx="10" cy="22" r="2" fill="#e8000d" class="va-pop"/>
        </svg>',

        'Sörétes fegyver' => '<svg class="va-svg-icon" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="3" y="15" width="24" height="6" rx="2" stroke="#e8000d" stroke-width="2" fill="none" stroke-dasharray="200" stroke-dashoffset="0"/>
            <rect x="27" y="16" width="6" height="4" rx="1" stroke="#e8000d" stroke-width="2" fill="none" stroke-dasharray="200" stroke-dashoffset="0"/>
            <line x1="7" y1="15" x2="7" y2="11" stroke="#e8000d" stroke-width="2" stroke-linecap="round"/>
            <circle cx="9" cy="23" r="1.5" fill="#e8000d" class="va-pop"/>
        </svg>',

        'Golyós fegyver' => '<svg class="va-svg-icon" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="2" y="15" width="26" height="6" rx="2" stroke="#e8000d" stroke-width="2" fill="none" stroke-dasharray="200" stroke-dashoffset="0"/>
            <rect x="28" y="16" width="5" height="4" rx="1" stroke="#e8000d" stroke-width="2" fill="none" stroke-dasharray="200" stroke-dashoffset="0"/>
            <line x1="6" y1="15" x2="6" y2="10" stroke="#e8000d" stroke-width="2" stroke-linecap="round"/>
            <rect x="10" y="10" width="6" height="5" rx="1" stroke="#e8000d" stroke-width="1.5" fill="none"/>
        </svg>',

        'Kombinált fegyver' => '<svg class="va-svg-icon" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="3" y="13" width="22" height="5" rx="1.5" stroke="#e8000d" stroke-width="2" fill="none" stroke-dasharray="200" stroke-dashoffset="0"/>
            <rect x="3" y="19" width="22" height="5" rx="1.5" stroke="#e8000d" stroke-width="2" fill="none" stroke-dasharray="200" stroke-dashoffset="0"/>
            <rect x="25" y="14" width="8" height="10" rx="1.5" stroke="#e8000d" stroke-width="2" fill="none"/>
        </svg>',

        'Légfegyver' => '<svg class="va-svg-icon" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="4" y="15" width="20" height="5" rx="2" stroke="#e8000d" stroke-width="2" fill="none" stroke-dasharray="200" stroke-dashoffset="0"/>
            <rect x="24" y="16" width="8" height="3" rx="1.5" stroke="#e8000d" stroke-width="2" fill="none"/>
            <path d="M8 15 Q8 10 12 10 Q16 10 16 15" stroke="#e8000d" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <circle cx="6" cy="17.5" r="1.5" fill="#e8000d" class="va-pop"/>
        </svg>',

        'Lőszer' => '<svg class="va-svg-icon" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="13" y="22" width="6" height="8" rx="1" stroke="#e8000d" stroke-width="2" fill="none" stroke-dasharray="200" stroke-dashoffset="0"/>
            <path d="M13 22 Q16 6 19 22" stroke="#e8000d" stroke-width="2" fill="none" stroke-linecap="round" class="va-pop"/>
            <circle cx="7" cy="26" r="3" stroke="#e8000d" stroke-width="2" fill="none"/>
            <circle cx="25" cy="24" r="3" stroke="#e8000d" stroke-width="2" fill="none"/>
            <circle cx="16" cy="28" r="1.5" fill="#e8000d"/>
        </svg>',

        'Optika & Kiegészítők' => '<svg class="va-svg-icon" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="18" r="7" stroke="#e8000d" stroke-width="2" fill="none" stroke-dasharray="200" stroke-dashoffset="0"/>
            <circle cx="24" cy="18" r="7" stroke="#e8000d" stroke-width="2" fill="none" stroke-dasharray="200" stroke-dashoffset="0"/>
            <line x1="19" y1="18" x2="17" y2="18" stroke="#e8000d" stroke-width="2"/>
            <line x1="5" y1="18" x2="3" y2="18" stroke="#e8000d" stroke-width="2" stroke-linecap="round"/>
            <line x1="33" y1="18" x2="31" y2="18" stroke="#e8000d" stroke-width="2" stroke-linecap="round"/>
            <line x1="18" y1="13" x2="18" y2="11" stroke="#e8000d" stroke-width="2" stroke-linecap="round"/>
            <circle cx="12" cy="18" r="2.5" fill="rgba(232,0,13,.2)" class="va-pop"/>
            <circle cx="24" cy="18" r="2.5" fill="rgba(232,0,13,.2)" class="va-pop"/>
        </svg>',

        'Ruházat & Felszerelés' => '<svg class="va-svg-icon" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 6 L6 12 L10 14 L10 30 L26 30 L26 14 L30 12 L24 6" stroke="#e8000d" stroke-width="2" fill="none" stroke-linejoin="round" stroke-dasharray="200" stroke-dashoffset="0"/>
            <path d="M12 6 Q18 10 24 6" stroke="#e8000d" stroke-width="2" fill="none" stroke-linecap="round"/>
            <circle cx="18" cy="8" r="1.5" fill="#e8000d" class="va-pop"/>
        </svg>',

        'Vadászkutya' => '<svg class="va-svg-icon" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="22" cy="20" rx="9" ry="7" stroke="#e8000d" stroke-width="2" fill="none" stroke-dasharray="200" stroke-dashoffset="0"/>
            <circle cx="22" cy="16" r="4" stroke="#e8000d" stroke-width="2" fill="none"/>
            <path d="M18 13 Q16 8 14 10" stroke="#e8000d" stroke-width="2" fill="none" stroke-linecap="round"/>
            <path d="M26 13 Q28 8 27 10" stroke="#e8000d" stroke-width="2" fill="none" stroke-linecap="round"/>
            <path d="M13 22 Q8 22 8 27 Q8 30 11 30" stroke="#e8000d" stroke-width="2" fill="none" stroke-linecap="round"/>
            <circle cx="20" cy="16" r="1" fill="#e8000d" class="va-pop"/>
            <circle cx="24" cy="16" r="1" fill="#e8000d" class="va-pop"/>
        </svg>',

        'Vadászterület & Bérlet' => '<svg class="va-svg-icon" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 4 L18 20" stroke="#e8000d" stroke-width="2" stroke-linecap="round" stroke-dasharray="200" stroke-dashoffset="0"/>
            <path d="M8 14 L18 8 L28 14" stroke="#e8000d" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="va-pop"/>
            <path d="M6 22 L14 16 L18 18 L22 16 L30 22" stroke="#e8000d" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M4 30 L12 22 L18 26 L24 22 L32 30" stroke="#e8000d" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>',

        'Trófeák' => '<svg class="va-svg-icon" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M18 28 L14 20 Q10 18 10 14 Q10 8 14 6 Q16 10 18 12 Q20 10 22 6 Q26 8 26 14 Q26 18 22 20 Z" stroke="#e8000d" stroke-width="2" fill="none" stroke-linejoin="round" stroke-dasharray="200" stroke-dashoffset="0"/>
            <path d="M10 10 Q6 8 4 10 Q4 16 8 16" stroke="#e8000d" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <path d="M26 10 Q30 8 32 10 Q32 16 28 16" stroke="#e8000d" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            <rect x="15" y="28" width="6" height="3" rx="1" stroke="#e8000d" stroke-width="1.5" fill="none"/>
            <rect x="13" y="31" width="10" height="2" rx="1" fill="#e8000d" class="va-pop"/>
        </svg>',

        'Egyéb' => '<svg class="va-svg-icon" width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect x="7" y="14" width="22" height="16" rx="2" stroke="#e8000d" stroke-width="2" fill="none" stroke-dasharray="200" stroke-dashoffset="0"/>
            <path d="M12 14 L12 10 Q12 6 18 6 Q24 6 24 10 L24 14" stroke="#e8000d" stroke-width="2" fill="none" stroke-linecap="round"/>
            <circle cx="18" cy="22" r="2.5" fill="#e8000d" class="va-pop"/>
        </svg>',
    ];

    $term = get_term( $term_id, 'va_category' );
    if ( ! $term ) return $icons['Egyéb'];
    return $icons[ $term->name ] ?? $icons['Egyéb'];
}
