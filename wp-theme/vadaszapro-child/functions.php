<?php
/**
 * VadászApró Child Theme – functions.php
 *
 * Ide kerüljenek az egyedi módosítások, hook-ok, filter-ek.
 * A szülő téma style.css-ét és functions.php-ját a WP automatikusan betölti.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Szülő + Child stílusok betöltése ─────────────────── */
add_action( 'wp_enqueue_scripts', function () {
    // Szülő téma CSS
    wp_enqueue_style(
        'vadaszapro-parent-style',
        get_template_directory_uri() . '/style.css'
    );
    // Child téma extra CSS (ha van)
    if ( file_exists( get_stylesheet_directory() . '/custom.css' ) ) {
        wp_enqueue_style(
            'vadaszapro-child-style',
            get_stylesheet_directory_uri() . '/custom.css',
            [ 'vadaszapro-parent-style' ],
            wp_get_theme()->get( 'Version' )
        );
    }
}, 20 );

/* ── Egyedi módosítások helye ──────────────────────────── */
// Példa: hook-ok, filter-ek ide jönnek
// add_filter( 'the_title', function( $title ) { return $title; } );
