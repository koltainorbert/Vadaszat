<?php
/**
 * Segédfüggvények – az egész plugin használja
 */

if ( ! defined( 'ABSPATH' ) ) exit;

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
    return get_posts([
        'post_type'      => [ 'va_listing', 'va_auction' ],
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
    return get_posts([
        'post_type'      => [ 'va_listing', 'va_auction' ],
        'post_status'    => 'publish',
        'include'        => $ids,
        'posts_per_page' => $per_page,
        'no_found_rows'  => true,
    ]);
}

/* ── Licitek user által ────────────────────────────────── */
function va_get_user_bids( int $user_id ): array {
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
