<?php
/**
 * Plugin Name: VadászApró Core
 * Plugin URI:  https://vadaszapro.net
 * Description: Vadászati apróhirdetési rendszer – hirdetés, aukció, felhasználói fiók, reklámzónák.
 * Version:     1.0.1
 * Author:      SDH
 * Text Domain: vadaszapro
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'VA_VERSION',        '1.0.1' );
define( 'VA_REWRITE_VER',   '1.0.3' );   // Növeld meg ha CPT/tax slug változik!
define( 'VA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VA_TEXT_DOMAIN', 'vadaszapro' );

/* ── Autoload includes ────────────────────────────── */
require_once VA_PLUGIN_DIR . 'includes/class-post-types.php';
require_once VA_PLUGIN_DIR . 'includes/class-taxonomy.php';
require_once VA_PLUGIN_DIR . 'includes/class-meta-fields.php';
require_once VA_PLUGIN_DIR . 'includes/class-user-system.php';
require_once VA_PLUGIN_DIR . 'includes/class-user-roles.php';
require_once VA_PLUGIN_DIR . 'includes/class-auctions.php';
require_once VA_PLUGIN_DIR . 'includes/class-ad-zones.php';
require_once VA_PLUGIN_DIR . 'includes/class-ajax.php';
require_once VA_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once VA_PLUGIN_DIR . 'includes/helpers.php';

require_once VA_PLUGIN_DIR . 'admin/class-form-builder.php'; // frontend is használja (VA_Form_Builder::get_fields)

if ( is_admin() ) {
    require_once VA_PLUGIN_DIR . 'admin/class-dashboard.php';
    require_once VA_PLUGIN_DIR . 'admin/class-listing-edit.php';
    require_once VA_PLUGIN_DIR . 'admin/class-admin.php';
    require_once VA_PLUGIN_DIR . 'admin/class-settings-page.php';
    require_once VA_PLUGIN_DIR . 'admin/class-listing-columns.php';
}

/* ── Boot ────────────────────────────────────────── */
add_action( 'plugins_loaded', function () {
    VA_Post_Types::init();
    VA_Taxonomy::init();
    VA_Meta_Fields::init();
    VA_User_System::init();
    VA_User_Roles::init();
    VA_Auctions::init();
    VA_Ad_Zones::init();
    VA_Ajax::init();
    VA_Shortcodes::init();

    if ( is_admin() ) {
        VA_Admin::init();
        VA_Settings_Page::init();
        VA_Listing_Columns::init();
        VA_Listing_Edit::init();
        VA_Form_Builder::init();
    }
});

/* ── Auto rewrite flush – verziókövetéssel ──────────
 * Ha VA_REWRITE_VER változik (pl. új CPT slug),
 * a WP automatikusan újragenerálja a rewrite táblákat.
 * Emberi beavatkozás nem kell.
────────────────────────────────────────────────── */
add_action( 'init', function () {
    if ( get_option( 'va_rewrite_ver' ) !== VA_REWRITE_VER ) {
        flush_rewrite_rules( false );
        update_option( 'va_rewrite_ver', VA_REWRITE_VER );
    }
}, 999 );

/* ── Vadász Naptár – virtuális oldal (WP admin nélkül) ──────────────
 * A /vadasz-naptar/ URL betölti a theme page-vadasz-naptar.php-t
 * automatikusan, adatbázis bejegyzés nélkül.
──────────────────────────────────────────────────────────────────── */
add_action( 'init', function () {
    add_rewrite_rule( '^vadasz-naptar/?$', 'index.php?va_virtual_page=vadasz-naptar', 'top' );
} );
add_filter( 'query_vars', function ( $vars ) {
    $vars[] = 'va_virtual_page';
    return $vars;
} );
add_filter( 'template_include', function ( $template ) {
    if ( get_query_var( 'va_virtual_page' ) !== 'vadasz-naptar' ) {
        return $template;
    }
    $t = locate_template( 'page-vadasz-naptar.php' );
    return $t ?: $template;
} );

/* ── Activation / Deactivation ───────────────────── */
register_activation_hook( __FILE__,   'va_activate'   );
register_deactivation_hook( __FILE__, 'va_deactivate' );

function va_activate() {
    VA_Post_Types::init();
    VA_Taxonomy::init();
    flush_rewrite_rules();
    update_option( 'va_rewrite_ver', VA_REWRITE_VER );

    // Régi hourly cron törlése, új 5 perces ütemezés
    $old = wp_next_scheduled( 'va_close_expired_auctions' );
    if ( $old ) wp_unschedule_event( $old, 'va_close_expired_auctions' );
    if ( function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : get_option( 'va_enable_auctions', '1' ) === '1' ) {
        wp_schedule_event( time(), 'va_every_5min', 'va_close_expired_auctions' );
    }

    global $wpdb;
    $charset = $wpdb->get_charset_collate();

    // Licitek táblája
    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}va_bids (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        auction_id  BIGINT UNSIGNED NOT NULL,
        user_id     BIGINT UNSIGNED NOT NULL,
        amount      DECIMAL(12,2)    NOT NULL,
        created_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY auction_id (auction_id),
        KEY user_id (user_id)
    ) $charset;";

    // Hirdetésfigyelő (értesítések) tábla
    $sql2 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}va_watchlist (
        id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id     BIGINT UNSIGNED NOT NULL,
        post_id     BIGINT UNSIGNED NOT NULL,
        created_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_post (user_id, post_id)
    ) $charset;";

    // Hirdetés meta gyorstábla – kereshetőség és szűrés index nélküli meta_query helyett
    // 2-3M hirdetésnél ez a tábla teszi lehetővé a gyors ár/lokáció/kategória szűrést.
    $sql3 = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}va_listing_meta (
        post_id     BIGINT UNSIGNED NOT NULL,
        price       DECIMAL(14,2)   DEFAULT NULL,
        price_type  VARCHAR(20)     DEFAULT 'fixed',
        county_id   BIGINT UNSIGNED DEFAULT NULL,
        category_id BIGINT UNSIGNED DEFAULT NULL,
        condition_id BIGINT UNSIGNED DEFAULT NULL,
        location    VARCHAR(100)    DEFAULT NULL,
        expires     DATE            DEFAULT NULL,
        featured    TINYINT(1)      NOT NULL DEFAULT 0,
        views       BIGINT UNSIGNED NOT NULL DEFAULT 0,
        PRIMARY KEY (post_id),
        KEY price (price),
        KEY county_id (county_id),
        KEY category_id (category_id),
        KEY featured (featured),
        KEY expires (expires),
        KEY cat_price (category_id, price)
    ) $charset;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
    dbDelta( $sql2 );
    dbDelta( $sql3 );

    // Alapértelmezett oldalak létrehozása ha nem léteznek
    va_create_default_pages();
}

function va_deactivate() {
    flush_rewrite_rules();
}

function va_create_default_pages() {
    $pages = [
        'va-hirdetes-feladas'  => [ 'title' => 'Hirdetés feladása',   'content' => '[va_submit_listing]' ],
        'va-bejelentkezes'     => [ 'title' => 'Bejelentkezés',        'content' => '[va_login_form]' ],
        'va-regisztracio'      => [ 'title' => 'Regisztráció',         'content' => '[va_register_form]' ],
        'va-fiok'              => [ 'title' => 'Fiókom',               'content' => '[va_user_dashboard]' ],
        'va-hirdetes-kereses'  => [ 'title' => 'Hirdetések keresése',  'content' => '[va_listing_search]' ],
        'va-kredit-vasarlas'   => [ 'title' => 'Vásárlás',             'content' => '[va_buy_credits]' ],
    ];

    if ( function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : get_option( 'va_enable_auctions', '1' ) === '1' ) {
        $pages['va-aukciok'] = [ 'title' => 'Aukciók', 'content' => '[va_auction_list]' ];
    }

    foreach ( $pages as $slug => $data ) {
        if ( ! get_page_by_path( $slug ) ) {
            wp_insert_post([
                'post_title'   => $data['title'],
                'post_name'    => $slug,
                'post_content' => $data['content'],
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ]);
        }
    }
}
