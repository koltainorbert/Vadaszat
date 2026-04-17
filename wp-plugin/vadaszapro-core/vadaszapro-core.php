<?php
/**
 * Plugin Name: VadászApró Core
 * Plugin URI:  https://vadaszapro.net
 * Description: Vadászati apróhirdetési rendszer – hirdetés, aukció, felhasználói fiók, reklámzónák.
 * Version:     1.0.0
 * Author:      SDH
 * Text Domain: vadaszapro
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'VA_VERSION',  '1.0.0' );
define( 'VA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VA_TEXT_DOMAIN', 'vadaszapro' );

/* ── Autoload includes ────────────────────────────── */
require_once VA_PLUGIN_DIR . 'includes/class-post-types.php';
require_once VA_PLUGIN_DIR . 'includes/class-taxonomy.php';
require_once VA_PLUGIN_DIR . 'includes/class-meta-fields.php';
require_once VA_PLUGIN_DIR . 'includes/class-user-system.php';
require_once VA_PLUGIN_DIR . 'includes/class-auctions.php';
require_once VA_PLUGIN_DIR . 'includes/class-ad-zones.php';
require_once VA_PLUGIN_DIR . 'includes/class-ajax.php';
require_once VA_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once VA_PLUGIN_DIR . 'includes/helpers.php';

if ( is_admin() ) {
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
    VA_Auctions::init();
    VA_Ad_Zones::init();
    VA_Ajax::init();
    VA_Shortcodes::init();

    if ( is_admin() ) {
        VA_Admin::init();
        VA_Settings_Page::init();
        VA_Listing_Columns::init();
    }
});

/* ── Activation / Deactivation ───────────────────── */
register_activation_hook( __FILE__,   'va_activate'   );
register_deactivation_hook( __FILE__, 'va_deactivate' );

function va_activate() {
    VA_Post_Types::init();
    VA_Taxonomy::init();
    flush_rewrite_rules();

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

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
    dbDelta( $sql2 );

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
        'va-aukciok'           => [ 'title' => 'Aukciók',              'content' => '[va_auction_list]' ],
        'va-hirdetes-kereses'  => [ 'title' => 'Hirdetések keresése',  'content' => '[va_listing_search]' ],
    ];

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
