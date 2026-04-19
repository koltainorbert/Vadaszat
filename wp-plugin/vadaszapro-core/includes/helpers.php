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

/* ── Social Media SVG ikonok (hivatalos brand logók) ─── */
function va_social_svg( string $platform, int $size = 20 ): string {
    $s = esc_attr( (string) $size );
    $icons = [
        'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073C24 5.404 18.627 0 12 0S0 5.404 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047v-2.66c0-3.025 1.791-4.697 4.533-4.697 1.313 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.931-1.956 1.886v2.265h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>',

        'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>',

        'youtube' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',

        'tiktok' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>',

        'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',

        'pinterest' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 0C5.373 0 0 5.372 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 01.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>',

        'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',

        'whatsapp' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>',

        'telegram' => '<svg xmlns="http://www.w3.org/2000/svg" width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
    ];

    return $icons[ $platform ] ?? '';
}

/* ── Social Media sáv renderelése ─────────────────────── */
function va_social_bar( string $style = 'icons', int $size = 20 ): string {
    $platforms = [
        'facebook'  => 'Facebook',
        'instagram' => 'Instagram',
        'youtube'   => 'YouTube',
        'tiktok'    => 'TikTok',
        'twitter'   => 'X',
        'pinterest' => 'Pinterest',
        'linkedin'  => 'LinkedIn',
        'whatsapp'  => 'WhatsApp',
        'telegram'  => 'Telegram',
    ];

    $brand_colors = [
        'facebook'  => '#1877F2',
        'instagram' => '#E1306C',
        'youtube'   => '#FF0000',
        'tiktok'    => '#010101',
        'twitter'   => '#000000',
        'pinterest' => '#BD081C',
        'linkedin'  => '#0A66C2',
        'whatsapp'  => '#25D366',
        'telegram'  => '#26A5E4',
    ];

    $html = '<div class="va-social-bar va-social-bar--' . esc_attr( $style ) . '">';

    foreach ( $platforms as $key => $label ) {
        $url = trim( (string) get_option( 'va_social_' . $key, '' ) );
        if ( $url === '' ) continue;

        $svg   = va_social_svg( $key, $size );
        $color = $brand_colors[ $key ] ?? '#fff';
        $rel   = 'noopener noreferrer';

        if ( $style === 'pills' ) {
            $html .= '<a href="' . esc_url( $url ) . '" class="va-social-pill" target="_blank" rel="' . esc_attr( $rel ) . '" aria-label="' . esc_attr( $label ) . '" style="--sc:' . esc_attr( $color ) . '">'
                   . $svg . '<span>' . esc_html( $label ) . '</span></a>';
        } else {
            $html .= '<a href="' . esc_url( $url ) . '" class="va-social-icon" target="_blank" rel="' . esc_attr( $rel ) . '" aria-label="' . esc_attr( $label ) . '" style="--sc:' . esc_attr( $color ) . '">'
                   . $svg . '</a>';
        }
    }

    $html .= '</div>';
    return $html;
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
