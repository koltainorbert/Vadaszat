<?php
/**
 * AJAX kezelők: hirdetés feladás, watchlist, megtekintés számláló
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Ajax {

    public static function init() {
        // Hirdetés feladás
        add_action( 'wp_ajax_va_submit_listing',  [ __CLASS__, 'submit_listing' ] );

        // Watchlist
        add_action( 'wp_ajax_va_toggle_watchlist', [ __CLASS__, 'toggle_watchlist' ] );

        // Megtekintés számláló (nem bejelentkezett is)
        add_action( 'wp_ajax_va_increment_views',        [ __CLASS__, 'increment_views' ] );
        add_action( 'wp_ajax_nopriv_va_increment_views', [ __CLASS__, 'increment_views' ] );

        // Szűrő AJAX
        add_action( 'wp_ajax_va_filter_listings',        [ __CLASS__, 'filter_listings' ] );
        add_action( 'wp_ajax_nopriv_va_filter_listings', [ __CLASS__, 'filter_listings' ] );

        // Cache invalidáció – hirdetés mentésekor a szűrő cache törlődik
        add_action( 'save_post_va_listing', [ __CLASS__, 'flush_filter_cache' ] );
        add_action( 'save_post_va_auction', [ __CLASS__, 'flush_filter_cache' ] );

        // Élő keresés
        add_action( 'wp_ajax_va_live_search',        [ __CLASS__, 'live_search' ] );
        add_action( 'wp_ajax_nopriv_va_live_search', [ __CLASS__, 'live_search' ] );
    }

    /* ── Hirdetés feladás ──────────────────────────────── */
    public static function submit_listing() {
        check_ajax_referer( 'va_submit_listing', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }

        $title       = sanitize_text_field( wp_unslash( $_POST['title']       ?? '' ) );
        $description = sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) );
        $price       = floatval( $_POST['price'] ?? 0 );
        $price_type  = sanitize_key( $_POST['price_type'] ?? 'fixed' );
        $phone       = sanitize_text_field( wp_unslash( $_POST['phone']  ?? '' ) );
        $location    = sanitize_text_field( wp_unslash( $_POST['location'] ?? '' ) );
        $brand       = sanitize_text_field( wp_unslash( $_POST['brand']  ?? '' ) );
        $model       = sanitize_text_field( wp_unslash( $_POST['model']  ?? '' ) );
        $caliber     = sanitize_text_field( wp_unslash( $_POST['caliber'] ?? '' ) );
        $year        = intval( $_POST['year'] ?? 0 );
        $license_req = ! empty( $_POST['license_req'] ) ? '1' : '0';
        $category    = intval( $_POST['category'] ?? 0 );
        $county      = intval( $_POST['county']   ?? 0 );
        $condition   = intval( $_POST['condition'] ?? 0 );

        if ( empty( $title ) ) {
            wp_send_json_error( [ 'message' => 'A cím kötelező.' ] );
        }

        // WP beállítástól függ: auto-publish vagy pending review
        $status = get_option( 'va_auto_publish_listings', '0' ) === '1' ? 'publish' : 'pending';

        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => $description,
            'post_status'  => $status,
            'post_type'    => 'va_listing',
            'post_author'  => get_current_user_id(),
        ], true );

        if ( is_wp_error( $post_id ) ) {
            wp_send_json_error( [ 'message' => $post_id->get_error_message() ] );
        }

        // Meta mentés
        $metas = [
            'va_price'       => $price,
            'va_price_type'  => $price_type,
            'va_phone'       => $phone,
            'va_location'    => $location,
            'va_brand'       => $brand,
            'va_model'       => $model,
            'va_caliber'     => $caliber,
            'va_year'        => $year,
            'va_license_req' => $license_req,
            'va_views'       => 0,
        ];
        foreach ( $metas as $k => $v ) {
            update_post_meta( $post_id, $k, $v );
        }

        // Taxonómiák
        if ( $category ) wp_set_post_terms( $post_id, [ $category ], 'va_category' );
        if ( $county   ) wp_set_post_terms( $post_id, [ $county ],   'va_county'   );
        if ( $condition) wp_set_post_terms( $post_id, [ $condition ], 'va_condition' );

        // Képfeltöltés kezelése
        if ( ! empty( $_FILES['listing_images'] ) ) {
            self::handle_images( $post_id, $_FILES['listing_images'] );
        }

        $msg = $status === 'publish'
            ? 'Hirdetés sikeresen feladva!'
            : 'Hirdetés mentve – jóváhagyásra vár.';

        wp_send_json_success([
            'message'    => $msg,
            'post_id'    => $post_id,
            'permalink'  => get_permalink( $post_id ),
        ]);
    }

    /* ── Képfeltöltés ──────────────────────────────────── */
    private static function handle_images( $post_id, $files ) {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $max_images   = intval( get_option( 'va_max_images_per_listing', 10 ) );
        $allowed_types = [ 'image/jpeg', 'image/png', 'image/webp' ];
        $first         = true;
        $count         = 0;

        // Normalizálás (több fájl esetén)
        $file_count = is_array( $files['name'] ) ? count( $files['name'] ) : 1;

        for ( $i = 0; $i < $file_count && $count < $max_images; $i++ ) {
            if ( is_array( $files['name'] ) ) {
                $single = [
                    'name'     => $files['name'][$i],
                    'type'     => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error'    => $files['error'][$i],
                    'size'     => $files['size'][$i],
                ];
            } else {
                $single = $files;
            }

            if ( $single['error'] !== UPLOAD_ERR_OK ) continue;
            if ( ! in_array( $single['type'], $allowed_types, true ) ) continue;

            $_FILES['va_upload'] = $single;
            $attachment_id = media_handle_upload( 'va_upload', $post_id );

            if ( ! is_wp_error( $attachment_id ) ) {
                if ( $first ) {
                    set_post_thumbnail( $post_id, $attachment_id );
                    $first = false;
                }
                $count++;
            }
        }
    }

    /* ── Watchlist (kedvencek) ─────────────────────────── */
    public static function toggle_watchlist() {
        check_ajax_referer( 'va_user_nonce', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Be kell jelentkezni.' ] );
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $post_id = intval( $_POST['post_id'] ?? 0 );

        if ( ! $post_id ) wp_send_json_error();

        $exists = $wpdb->get_var( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}va_watchlist WHERE user_id = %d AND post_id = %d",
            $user_id, $post_id
        ));

        if ( $exists ) {
            $wpdb->delete( $wpdb->prefix . 'va_watchlist', [ 'user_id' => $user_id, 'post_id' => $post_id ], [ '%d', '%d' ] );
            wp_send_json_success( [ 'action' => 'removed', 'message' => 'Eltávolítva a kedvencekből.' ] );
        } else {
            $wpdb->insert( $wpdb->prefix . 'va_watchlist', [ 'user_id' => $user_id, 'post_id' => $post_id ], [ '%d', '%d' ] );
            wp_send_json_success( [ 'action' => 'added', 'message' => 'Hozzáadva a kedvencekhez.' ] );
        }
    }

    /* ── Megtekintés számláló ──────────────────────────── */
    public static function increment_views() {
        $post_id = intval( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) wp_send_json_error();

        $views = intval( get_post_meta( $post_id, 'va_views', true ) ?: 0 );
        update_post_meta( $post_id, 'va_views', $views + 1 );
        wp_send_json_success( [ 'views' => $views + 1 ] );
    }

    /* ── Hirdetések szűrő AJAX ─────────────────────────── */
    /* Skálázható megoldás: wp_va_listing_meta indexelt custom táblát használ
     * meta_query / EAV helyett – 3M hirdetésnél is gyors marad.
     * Transient cache: 5 perc, automatikusan törlődik új hirdetésnél.     */
    public static function filter_listings() {
        global $wpdb;

        $paged     = max( 1, intval( $_POST['paged']     ?? 1 ) );
        $category  = intval( $_POST['category']  ?? 0 );
        $county    = intval( $_POST['county']    ?? 0 );
        $condition = intval( $_POST['condition'] ?? 0 );
        $min_price = floatval( $_POST['min_price'] ?? 0 );
        $max_price = floatval( $_POST['max_price'] ?? 0 );
        $keyword   = sanitize_text_field( wp_unslash( $_POST['keyword'] ?? '' ) );
        $sort      = sanitize_key( $_POST['sort'] ?? 'date' );
        $post_type = in_array( sanitize_key( $_POST['post_type'] ?? '' ), [ 'va_listing', 'va_auction' ], true )
                     ? sanitize_key( $_POST['post_type'] )
                     : 'va_listing';
        $keyword   = sanitize_text_field( wp_unslash( $_POST['keyword'] ?? '' ) );
        $sort      = sanitize_key( $_POST['sort'] ?? 'date' );

        $per_page = intval( get_option( 'va_listings_per_page', 20 ) );
        $offset   = ( $paged - 1 ) * $per_page;

        // ── Transient cache kulcs ─────────────────────────
        $cache_key = 'va_fl_' . md5( serialize( compact(
            'paged','category','county','condition','min_price','max_price','keyword','sort','post_type','per_page'
        ) ) );
        $cached = get_transient( $cache_key );
        if ( $cached !== false ) {
            wp_send_json_success( $cached );
        }

        $lm    = $wpdb->prefix . 'va_listing_meta';
        $posts = $wpdb->posts;

        // ── WHERE feltételek összerakása ──────────────────
        $where  = [ "p.post_type = %s", "p.post_status = 'publish'" ];
        $params = [ $post_type ];

        if ( $category  ) { $where[] = 'lm.category_id  = %d'; $params[] = $category;  }
        if ( $county    ) { $where[] = 'lm.county_id    = %d'; $params[] = $county;    }
        if ( $condition ) { $where[] = 'lm.condition_id = %d'; $params[] = $condition; }
        if ( $min_price > 0 ) { $where[] = 'lm.price >= %f'; $params[] = $min_price; }
        if ( $max_price > 0 ) { $where[] = 'lm.price <= %f'; $params[] = $max_price; }

        if ( $keyword !== '' ) {
            $like     = '%' . $wpdb->esc_like( $keyword ) . '%';
            $where[]  = 'p.post_title LIKE %s';
            $params[] = $like;
        }

        $where_sql = 'WHERE ' . implode( ' AND ', $where );

        // ── Rendezés ─────────────────────────────────────
        $order_sql = match ( $sort ) {
            'price_asc'  => 'lm.featured DESC, lm.price ASC,  p.post_date DESC',
            'price_desc' => 'lm.featured DESC, lm.price DESC, p.post_date DESC',
            'views'      => 'lm.featured DESC, lm.views DESC, p.post_date DESC',
            default      => 'lm.featured DESC, p.post_date DESC',
        };

        // ── Összesített szám (lapozáshoz) ─────────────────
        $count_sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$posts} p
             LEFT JOIN {$lm} lm ON lm.post_id = p.ID
             {$where_sql}",
            ...$params
        );
        $total = (int) $wpdb->get_var( $count_sql );

        // ── ID lista – csak az aktuális lap ──────────────
        $id_sql = $wpdb->prepare(
            "SELECT p.ID FROM {$posts} p
             LEFT JOIN {$lm} lm ON lm.post_id = p.ID
             {$where_sql}
             ORDER BY {$order_sql}
             LIMIT %d OFFSET %d",
            ...array_merge( $params, [ $per_page, $offset ] )
        );
        $ids = $wpdb->get_col( $id_sql );

        // ── WP_Query az ID listára – csak rendereléshez ──
        ob_start();
        if ( ! empty( $ids ) ) {
            $query = new WP_Query([
                'post_type'           => $post_type,
                'post_status'         => 'publish',
                'post__in'            => array_map( 'intval', $ids ),
                'orderby'             => 'post__in',
                'posts_per_page'      => $per_page,
                'no_found_rows'       => true,   // nem kell count – már megvan
                'ignore_sticky_posts' => true,
            ]);
            while ( $query->have_posts() ) {
                $query->the_post();
                va_template( 'listing/card', [ 'post' => get_post() ] );
            }
            wp_reset_postdata();
        } else {
            echo '<p class="va-no-results">Nincs találat a keresési feltételekre.</p>';
        }
        $html = ob_get_clean();

        $result = [
            'html'         => $html,
            'total'        => $total,
            'max_pages'    => $per_page > 0 ? (int) ceil( $total / $per_page ) : 1,
            'current_page' => $paged,
        ];

        // Cache 5 percre – törlődik ha új hirdetés/módosítás (save_post hook)
        set_transient( $cache_key, $result, 5 * MINUTE_IN_SECONDS );

        wp_send_json_success( $result );
    }

    /* ── Filter cache törlése ──────────────────────────── */
    public static function flush_filter_cache(): void {
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_va_fl_%' OR option_name LIKE '_transient_timeout_va_fl_%'" );
    }

    /* ── Élő keresés (header dropdown) ─────────────────── */
    public static function live_search() {
        $q = sanitize_text_field( wp_unslash( $_POST['q'] ?? '' ) );
        if ( strlen( $q ) < 2 ) {
            wp_send_json_success( [] );
        }

        $results = [];

        // Kategória találatok
        $cats = get_terms([
            'taxonomy'   => 'va_category',
            'hide_empty' => false,
            'search'     => $q,
            'number'     => 3,
        ]);
        if ( ! is_wp_error( $cats ) ) {
            $search_page = get_page_by_path( 'va-hirdetes-kereses' );
            $search_url  = $search_page ? get_permalink( $search_page ) : home_url( '/va-hirdetes-kereses/' );
            foreach ( $cats as $cat ) {
                $results[] = [
                    'id'    => $cat->term_id,
                    'title' => $cat->name,
                    'url'   => add_query_arg( 'cat', $cat->term_id, $search_url ),
                    'price' => $cat->count . ' hirdetés',
                    'thumb' => '',
                    'type'  => 'category',
                ];
            }
        }

        // Hirdetés + aukció találatok
        $query = new WP_Query([
            'post_type'      => [ 'va_listing', 'va_auction' ],
            'post_status'    => 'publish',
            'posts_per_page' => 5,
            'no_found_rows'  => true,
            's'              => $q,
        ]);

        foreach ( $query->posts as $post ) {
            $price     = get_post_meta( $post->ID, 'va_price', true );
            $thumb_id  = get_post_thumbnail_id( $post->ID );
            $thumb_url = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';
            $results[] = [
                'id'    => $post->ID,
                'title' => get_the_title( $post ),
                'url'   => get_permalink( $post ),
                'price' => $price ? number_format( (float) $price, 0, ',', ' ' ) . ' Ft' : '',
                'thumb' => $thumb_url,
                'type'  => $post->post_type,
            ];
        }

        wp_reset_postdata();

        // Felhasználó találatok
        $users = get_users([
            'search'         => '*' . $q . '*',
            'search_columns' => [ 'user_login', 'display_name' ],
            'number'         => 3,
            'fields'         => [ 'ID', 'display_name', 'user_login' ],
        ]);
        $search_page_for_user = get_page_by_path( 'va-hirdetes-kereses' );
        $search_url_for_user  = $search_page_for_user ? get_permalink( $search_page_for_user ) : home_url( '/va-hirdetes-kereses/' );
        foreach ( $users as $u ) {
            $avatar  = get_avatar_url( $u->ID, [ 'size' => 80 ] );
            $results[] = [
                'id'    => $u->ID,
                'title' => $u->display_name,
                'url'   => add_query_arg( 'author_id', $u->ID, $search_url_for_user ),
                'price' => '@' . $u->user_login,
                'thumb' => $avatar,
                'type'  => 'user',
            ];
        }

        wp_send_json_success( $results );
    }
}
