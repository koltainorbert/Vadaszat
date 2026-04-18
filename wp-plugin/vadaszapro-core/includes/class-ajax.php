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
    public static function filter_listings() {
        $paged     = intval( $_POST['paged']     ?? 1 );
        $category  = intval( $_POST['category']  ?? 0 );
        $county    = intval( $_POST['county']    ?? 0 );
        $condition = intval( $_POST['condition'] ?? 0 );
        $min_price = floatval( $_POST['min_price'] ?? 0 );
        $max_price = floatval( $_POST['max_price'] ?? 0 );
        $keyword   = sanitize_text_field( wp_unslash( $_POST['keyword'] ?? '' ) );
        $sort      = sanitize_key( $_POST['sort'] ?? 'date' );
        $post_type = sanitize_key( $_POST['post_type'] ?? 'va_listing' );

        $args = [
            'post_type'      => in_array( $post_type, [ 'va_listing', 'va_auction' ], true ) ? $post_type : 'va_listing',
            'post_status'    => 'publish',
            'posts_per_page' => intval( get_option( 'va_listings_per_page', 20 ) ),
            'paged'          => $paged,
            's'              => $keyword,
        ];

        // Taxonómia szűrők
        $tax_query = [];
        if ( $category ) $tax_query[] = [ 'taxonomy' => 'va_category',  'field' => 'term_id', 'terms' => $category ];
        if ( $county   ) $tax_query[] = [ 'taxonomy' => 'va_county',    'field' => 'term_id', 'terms' => $county   ];
        if ( $condition) $tax_query[] = [ 'taxonomy' => 'va_condition', 'field' => 'term_id', 'terms' => $condition ];
        if ( $tax_query ) {
            $args['tax_query'] = array_merge( [ 'relation' => 'AND' ], $tax_query );
        }

        // Ár szűrő
        $meta_query = [];
        if ( $min_price > 0 ) {
            $meta_query[] = [ 'key' => 'va_price', 'value' => $min_price, 'compare' => '>=', 'type' => 'NUMERIC' ];
        }
        if ( $max_price > 0 ) {
            $meta_query[] = [ 'key' => 'va_price', 'value' => $max_price, 'compare' => '<=', 'type' => 'NUMERIC' ];
        }
        if ( $meta_query ) {
            $args['meta_query'] = array_merge( [ 'relation' => 'AND' ], $meta_query );
        }

        // Rendezés
        switch ( $sort ) {
            case 'price_asc':
                $args['meta_key'] = 'va_price'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'ASC'; break;
            case 'price_desc':
                $args['meta_key'] = 'va_price'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; break;
            case 'views':
                $args['meta_key'] = 'va_views'; $args['orderby'] = 'meta_value_num'; $args['order'] = 'DESC'; break;
            default:
                $args['orderby'] = 'date'; $args['order'] = 'DESC';
        }

        // Kiemelt hirdetések először
        $args['meta_query'][] = [
            'relation' => 'OR',
            [ 'key' => 'va_featured', 'value' => '1', 'compare' => '=' ],
            [ 'key' => 'va_featured', 'compare' => 'NOT EXISTS' ],
        ];

        $query = new WP_Query( $args );
        ob_start();
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                va_template( 'listing/card', [ 'post' => get_post() ] );
            }
        } else {
            echo '<p class="va-no-results">Nincs találat a keresési feltételekre.</p>';
        }
        wp_reset_postdata();
        $html = ob_get_clean();

        wp_send_json_success([
            'html'        => $html,
            'total'       => $query->found_posts,
            'max_pages'   => $query->max_num_pages,
            'current_page'=> $paged,
        ]);
    }

    /* ── Élő keresés (header dropdown) ─────────────────── */
    public static function live_search() {
        $q = sanitize_text_field( wp_unslash( $_POST['q'] ?? '' ) );
        if ( strlen( $q ) < 2 ) {
            wp_send_json_success( [] );
        }

        $query = new WP_Query([
            'post_type'      => [ 'va_listing', 'va_auction' ],
            'post_status'    => 'publish',
            'posts_per_page' => 6,
            'no_found_rows'  => true,
            's'              => $q,
        ]);

        $results = [];
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
        wp_send_json_success( $results );
    }
}
