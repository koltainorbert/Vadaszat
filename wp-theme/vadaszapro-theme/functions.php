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
    wp_enqueue_style( 'va-frontend', get_template_directory_uri() . '/assets/css/frontend.css', [], '1.0.0' );
    wp_enqueue_script( 'va-theme', get_template_directory_uri() . '/assets/js/theme.js', [ 'jquery' ], '1.0.0', true );
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

/* ── Kategória ikonok ─────────────────────────────── */
function va_category_icon( int $term_id ): string {
    $icons = [
        'Fegyver'               => '🔫',
        'Sörétes fegyver'       => '🔫',
        'Golyós fegyver'        => '🎯',
        'Kombinált fegyver'     => '🔫',
        'Légfegyver'            => '💨',
        'Lőszer'                => '🔴',
        'Optika & Kiegészítők'  => '🔭',
        'Ruházat & Felszerelés' => '🧥',
        'Vadászkutya'           => '🐕',
        'Vadászterület & Bérlet'=> '🌲',
        'Trófeák'               => '🦌',
        'Egyéb'                 => '📦',
    ];
    $term = get_term( $term_id, 'va_category' );
    return $term ? ( $icons[ $term->name ] ?? '🏷' ) : '🏷';
}
