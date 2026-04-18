<?php
/**
 * Admin főosztály: menük, assets
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Admin {

    public static function init() {
        add_action( 'admin_menu',            [ __CLASS__, 'register_menus' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
    }

    public static function register_menus() {
        $auctions_enabled = function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : true;

        add_menu_page(
            'VadászApró',
            'VadászApró',
            'manage_options',
            'vadaszapro',
            [ VA_Settings_Page::class, 'render_general' ],
            'dashicons-megaphone',
            4
        );

        add_submenu_page( 'vadaszapro', 'Általános beállítások', 'Általános',     'manage_options', 'vadaszapro',             [ VA_Settings_Page::class, 'render_general'  ] );
        add_submenu_page( 'vadaszapro', 'Design beállítások',    'Design',        'manage_options', 'vadaszapro-design',     [ VA_Settings_Page::class, 'render_design'   ] );
        add_submenu_page( 'vadaszapro', 'Fejléc + Lábléc',        'Fejléc + Lábléc','manage_options', 'vadaszapro-header-footer', [ VA_Settings_Page::class, 'render_header_footer' ] );
        add_submenu_page( 'vadaszapro', 'Reklámzónák',           'Reklámzónák',   'manage_options', 'vadaszapro-reklam',      [ VA_Settings_Page::class, 'render_ad_zones'  ] );
        add_submenu_page( 'vadaszapro', 'Hirdetés beállítások',  'Hirdetések',    'manage_options', 'vadaszapro-hirdetes',    [ VA_Settings_Page::class, 'render_listings'  ] );
        if ( $auctions_enabled ) {
            add_submenu_page( 'vadaszapro', 'Aukció beállítások', 'Aukciók',      'manage_options', 'vadaszapro-aukcio',      [ VA_Settings_Page::class, 'render_auctions'  ] );
        }
        add_submenu_page( 'vadaszapro', 'Felhasználók',          'Felhasználók',  'manage_options', 'vadaszapro-users',       [ VA_Settings_Page::class, 'render_users'     ] );
        add_submenu_page( 'vadaszapro', 'Statisztika',           'Statisztika',   'manage_options', 'vadaszapro-stats',       [ VA_Settings_Page::class, 'render_stats'     ] );
    }

    public static function enqueue( $hook ) {
        $post_types = [ 'va_listing' ];
        if ( function_exists( 'va_auctions_enabled' ) && va_auctions_enabled() ) {
            $post_types[] = 'va_auction';
        }

        // Csak a plugin admin oldalain töltjük be
        if ( strpos( $hook, 'vadaszapro' ) === false && ! in_array( get_post_type(), $post_types, true ) ) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style(  'va-admin', VA_PLUGIN_URL . 'admin/admin.css', [], VA_VERSION );
        if ( file_exists( VA_PLUGIN_DIR . 'admin/admin.js' ) ) {
            wp_enqueue_script( 'va-admin', VA_PLUGIN_URL . 'admin/admin.js',  [ 'jquery', 'wp-color-picker' ], VA_VERSION, true );
        }
        wp_enqueue_style( 'wp-color-picker' );
    }
}
