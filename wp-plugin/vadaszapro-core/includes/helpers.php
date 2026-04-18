<?php
/**
 * Segédfüggvények – az egész plugin használja
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Aukció funkció globális kapcsoló ─────────────────── */
function va_auctions_enabled(): bool {
    return get_option( 'va_enable_auctions', '1' ) === '1';
}

/* ── Megtekintés szám (determinisztikus alap + valós) ─── */
function va_display_views( int $post_id ): int {
    $base = 30 + ( $post_id % 70 );
    return intval( get_post_meta( $post_id, 'va_views', true ) ) + $base;
}

/* ── Template betöltő ─────────────────────────────────── */
function va_template( string $name, array $data = [] ): void {
    $file = VA_PLUGIN_DIR . 'frontend/templates/' . $name . '.php';
    if ( ! file_exists( $file ) ) {
        // Lehetőség a témának saját template-et adni
        $theme_file = get_stylesheet_directory() . '/vadaszapro/' . $name . '.php';
        if ( file_exists( $theme_file ) ) {
            $file = $theme_file;
        } else {
            return;
        }
    }
    if ( $data ) extract( $data, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
    include $file;
}

/* ── Flash üzenetek ──────────────────────────────────── */
function va_set_flash( string $type, string $message ): void {
    if ( ! session_id() ) session_start();
    $_SESSION['va_flash'][] = [ 'type' => $type, 'message' => $message ];
}

function va_get_flash(): array {
    if ( ! session_id() ) session_start();
    $messages = $_SESSION['va_flash'] ?? [];
    unset( $_SESSION['va_flash'] );
    return $messages;
}

function va_display_flash(): void {
    foreach ( va_get_flash() as $f ) {
        $type = in_array( $f['type'], [ 'success', 'error', 'info', 'warning' ], true ) ? $f['type'] : 'info';
        echo '<div class="va-notice va-notice--' . esc_attr( $type ) . '">' . esc_html( $f['message'] ) . '</div>';
    }
}

/* ── Oldal azonosítás (slug alapján) ─────────────────── */
function va_is_page( string $slug ): bool {
    $page = get_page_by_path( $slug );
    return $page && is_page( $page->ID );
}

/* ── Aukció oldalak tiltása kikapcsolt módban ─────────── */
add_action( 'template_redirect', function() {
    if ( va_auctions_enabled() ) {
        return;
    }

    $requested_post_type = sanitize_key( $_GET['post_type'] ?? '' );
    $is_auction_request  = is_singular( 'va_auction' )
        || is_post_type_archive( 'va_auction' )
        || va_is_page( 'va-aukciok' )
        || $requested_post_type === 'va_auction';

    if ( ! $is_auction_request ) {
        return;
    }

    wp_safe_redirect( home_url( '/hirdetes/' ) );
    exit;
}, 1 );

/* ── Ár formázás ──────────────────────────────────────── */
function va_format_price( $price, string $type = 'fixed' ): string {
    if ( $type === 'negotiable' ) return 'Alkudható';
    if ( $type === 'free' )      return 'Ingyenes';
    if ( $type === 'on_request') return 'Érdeklődjön';
    if ( ! $price )              return 'Ár nincs megadva';
    return number_format( (float) $price, 0, ',', ' ' ) . ' Ft';
}

/* ── Hirdetés lejártság ellenőrzés ───────────────────── */
function va_is_listing_expired( int $post_id ): bool {
    $expires = get_post_meta( $post_id, 'va_expires', true );
    if ( ! $expires ) return false;
    return strtotime( $expires ) < time();
}

/* ── Aukció lejárt-e ──────────────────────────────────── */
function va_is_auction_over( int $post_id ): bool {
    $end = get_post_meta( $post_id, 'va_auction_end', true );
    if ( ! $end ) return false;
    return strtotime( $end ) < time();
}

/* ── Aukció visszaszámlálás ───────────────────────────── */
function va_auction_countdown( int $post_id ): string {
    $end = get_post_meta( $post_id, 'va_auction_end', true );
    if ( ! $end ) return '';
    return '<span class="va-countdown" data-end="' . esc_attr( strtotime( $end ) ) . '"></span>';
}

/* ── Watchlist: user figyeli-e ────────────────────────── */
function va_user_watches( int $post_id ): bool {
    if ( ! is_user_logged_in() ) return false;
    global $wpdb;
    return (bool) $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}va_watchlist WHERE user_id = %d AND post_id = %d",
        get_current_user_id(), $post_id
    ));
}

/* ── Felhasználó hirdetései ───────────────────────────── */
function va_get_user_listings( int $user_id, string $status = 'any', int $per_page = 50, int $page = 1 ): array {
    $post_types = [ 'va_listing' ];
    if ( va_auctions_enabled() ) {
        $post_types[] = 'va_auction';
    }

    return get_posts([
        'post_type'      => $post_types,
        'post_status'    => $status === 'any' ? [ 'publish', 'pending', 'draft' ] : $status,
        'author'         => $user_id,
        'posts_per_page' => $per_page,
        'offset'         => ( $page - 1 ) * $per_page,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ]);
}

/* ── Felhasználó watchlist-je ─────────────────────────── */
function va_get_user_watchlist( int $user_id, int $per_page = 20, int $page = 1 ): array {
    global $wpdb;
    $ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT post_id FROM {$wpdb->prefix}va_watchlist WHERE user_id = %d ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $user_id, $per_page, ( $page - 1 ) * $per_page
    ));
    if ( ! $ids ) return [];

    $post_types = [ 'va_listing' ];
    if ( va_auctions_enabled() ) {
        $post_types[] = 'va_auction';
    }

    return get_posts([
        'post_type'      => $post_types,
        'post_status'    => 'publish',
        'include'        => $ids,
        'posts_per_page' => $per_page,
        'no_found_rows'  => true,
    ]);
}

/* ── Licitek user által ────────────────────────────────── */
function va_get_user_bids( int $user_id ): array {
    if ( ! va_auctions_enabled() ) {
        return [];
    }

    global $wpdb;
    return $wpdb->get_results( $wpdb->prepare(
        "SELECT b.*, p.post_title
         FROM {$wpdb->prefix}va_bids b
         JOIN {$wpdb->posts} p ON p.ID = b.auction_id
         WHERE b.user_id = %d
         ORDER BY b.created_at DESC",
        $user_id
    ));
}

/* ── Listing meta gyorstábla szinkron ─────────────────────
 * Hívd meg minden mentéskor: va_sync_listing_meta($post_id)
 * A wp_va_listing_meta tábla gyors ár/szűrés alapja.
─────────────────────────────────────────────────────── */
function va_sync_listing_meta( int $post_id ): void {
    global $wpdb;

    $cats    = get_the_terms( $post_id, 'va_category' );
    $county  = get_the_terms( $post_id, 'va_county' );
    $cond    = get_the_terms( $post_id, 'va_condition' );
    $expires = get_post_meta( $post_id, 'va_expires', true );

    $data = [
        'post_id'      => $post_id,
        'price'        => (float) ( get_post_meta( $post_id, 'va_price',    true ) ?: 0 ),
        'price_type'   => get_post_meta( $post_id, 'va_price_type', true ) ?: 'fixed',
        'category_id'  => $cats   && ! is_wp_error( $cats )   ? (int) $cats[0]->term_id   : null,
        'county_id'    => $county && ! is_wp_error( $county ) ? (int) $county[0]->term_id : null,
        'condition_id' => $cond   && ! is_wp_error( $cond   ) ? (int) $cond[0]->term_id   : null,
        'location'     => (string) get_post_meta( $post_id, 'va_location', true ),
        'expires'      => $expires ? date( 'Y-m-d', strtotime( $expires ) ) : null,
        'featured'     => get_post_meta( $post_id, 'va_featured', true ) === '1' ? 1 : 0,
        'views'        => (int) get_post_meta( $post_id, 'va_views', true ),
    ];

    $formats = [ '%d', '%f', '%s', '%d', '%d', '%d', '%s', '%s', '%d', '%d' ];

    $exists = $wpdb->get_var( $wpdb->prepare(
        "SELECT post_id FROM {$wpdb->prefix}va_listing_meta WHERE post_id = %d", $post_id
    ));

    if ( $exists ) {
        $wpdb->update( $wpdb->prefix . 'va_listing_meta', $data, [ 'post_id' => $post_id ], $formats, [ '%d' ] );
    } else {
        $wpdb->insert( $wpdb->prefix . 'va_listing_meta', $data, $formats );
    }
}

/* ── Auto-szinkron mentéskor ─────────────────────────── */
add_action( 'save_post_va_listing', function( $post_id ) {
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) return;
    va_sync_listing_meta( $post_id );
}, 20 );
