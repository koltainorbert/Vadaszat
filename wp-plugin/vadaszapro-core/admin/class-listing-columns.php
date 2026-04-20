<?php
/**
 * Admin listaoszlopok és szűrők a hirdetések/aukciók listájához
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Listing_Columns {

    public static function init() {
        // Hirdetés oszlopok
        add_filter( 'manage_va_listing_posts_columns',       [ __CLASS__, 'listing_columns' ] );
        add_action( 'manage_va_listing_posts_custom_column', [ __CLASS__, 'listing_column_content' ], 10, 2 );
        add_filter( 'manage_edit-va_listing_sortable_columns', [ __CLASS__, 'sortable_columns' ] );

        // Aukció oszlopok
        add_filter( 'manage_va_auction_posts_columns',       [ __CLASS__, 'auction_columns' ] );
        add_action( 'manage_va_auction_posts_custom_column', [ __CLASS__, 'auction_column_content' ], 10, 2 );

        // Gyors szerkesztés és státusz
        add_action( 'restrict_manage_posts', [ __CLASS__, 'add_filters' ] );
        add_filter( 'parse_query',           [ __CLASS__, 'parse_filter_query' ] );

        // Admin duplikálás hirdetésekhez
        add_filter( 'post_row_actions', [ __CLASS__, 'add_duplicate_row_action' ], 10, 2 );
        add_action( 'admin_action_va_duplicate_listing', [ __CLASS__, 'handle_duplicate_action' ] );
        add_action( 'admin_notices', [ __CLASS__, 'duplicate_admin_notice' ] );
    }

    public static function add_duplicate_row_action( array $actions, $post ): array {
        if ( ! $post instanceof WP_Post || $post->post_type !== 'va_listing' ) {
            return $actions;
        }
        if ( ! current_user_can( 'edit_post', $post->ID ) ) {
            return $actions;
        }

        $url = wp_nonce_url(
            admin_url( 'admin.php?action=va_duplicate_listing&post=' . $post->ID ),
            'va_duplicate_listing_' . $post->ID
        );

        $actions['va_duplicate_listing'] = '<a href="' . esc_url( $url ) . '">Duplikálás</a>';
        return $actions;
    }

    public static function handle_duplicate_action(): void {
        $source_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
        if ( $source_id <= 0 ) {
            wp_die( 'Érvénytelen hirdetés azonosító.' );
        }

        if ( ! current_user_can( 'edit_post', $source_id ) ) {
            wp_die( 'Nincs jogosultságod a duplikáláshoz.' );
        }

        check_admin_referer( 'va_duplicate_listing_' . $source_id );

        $source = get_post( $source_id );
        if ( ! $source || $source->post_type !== 'va_listing' ) {
            wp_die( 'A kiválasztott bejegyzés nem duplikálható.' );
        }

        $new_post_id = wp_insert_post( [
            'post_type'      => 'va_listing',
            'post_status'    => 'draft',
            'post_title'     => $source->post_title . ' (Másolat)',
            'post_content'   => $source->post_content,
            'post_excerpt'   => $source->post_excerpt,
            'post_author'    => get_current_user_id(),
            'menu_order'     => (int) $source->menu_order,
            'comment_status' => $source->comment_status,
            'ping_status'    => $source->ping_status,
        ], true );

        if ( is_wp_error( $new_post_id ) ) {
            wp_die( 'A duplikálás sikertelen: ' . esc_html( $new_post_id->get_error_message() ) );
        }

        // Taxonómiák másolása
        $taxonomies = get_object_taxonomies( 'va_listing' );
        foreach ( $taxonomies as $taxonomy ) {
            $term_ids = wp_get_object_terms( $source_id, $taxonomy, [ 'fields' => 'ids' ] );
            if ( ! is_wp_error( $term_ids ) ) {
                wp_set_object_terms( $new_post_id, $term_ids, $taxonomy, false );
            }
        }

        // Meta mezők másolása (szerkesztő-lock kivétel)
        $meta = get_post_meta( $source_id );
        foreach ( $meta as $meta_key => $values ) {
            if ( in_array( $meta_key, [ '_edit_lock', '_edit_last' ], true ) ) {
                continue;
            }
            foreach ( $values as $value ) {
                add_post_meta( $new_post_id, $meta_key, maybe_unserialize( $value ) );
            }
        }

        wp_safe_redirect( admin_url( 'post.php?post=' . $new_post_id . '&action=edit&va_duplicated=1' ) );
        exit;
    }

    public static function duplicate_admin_notice(): void {
        if ( ! is_admin() || empty( $_GET['va_duplicated'] ) ) {
            return;
        }
        echo '<div class="notice notice-success is-dismissible"><p>Hirdetés sikeresen duplikálva. A másolat piszkozatként jött létre.</p></div>';
    }

    public static function listing_columns( $cols ) {
        return [
            'cb'           => $cols['cb'],
            'title'        => 'Cím',
            'va_thumb'     => 'Kép',
            'va_price'     => 'Ár',
            'va_category'  => 'Kategória',
            'va_county'    => 'Megye',
            'va_views'     => 'Megtekintés',
            'va_featured'  => 'Kiemelt',
            'va_verified'  => 'Ell.',
            'va_expires'   => 'Lejárat',
            'author'       => 'Feladó',
            'date'         => 'Dátum',
        ];
    }

    public static function listing_column_content( $col, $post_id ) {
        switch ( $col ) {
            case 'va_thumb':
                echo get_the_post_thumbnail( $post_id, [ 60, 60 ] );
                break;
            case 'va_price':
                $type = get_post_meta( $post_id, 'va_price_type', true );
                echo esc_html( va_format_price( get_post_meta( $post_id, 'va_price', true ), $type ) );
                break;
            case 'va_category':
                $terms = get_the_terms( $post_id, 'va_category' );
                echo $terms ? esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) ) : '–';
                break;
            case 'va_county':
                $terms = get_the_terms( $post_id, 'va_county' );
                echo $terms ? esc_html( $terms[0]->name ) : '–';
                break;
            case 'va_views':
                echo esc_html( get_post_meta( $post_id, 'va_views', true ) ?: 0 );
                break;
            case 'va_featured':
                echo get_post_meta( $post_id, 'va_featured', true ) === '1' ? '⭐' : '–';
                break;
            case 'va_verified':
                echo get_post_meta( $post_id, 'va_verified', true ) === '1' ? '✅' : '–';
                break;
            case 'va_expires':
                $exp = get_post_meta( $post_id, 'va_expires', true );
                if ( $exp ) {
                    $class = strtotime( $exp ) < time() ? 'color:red' : '';
                    echo '<span style="' . esc_attr( $class ) . '">' . esc_html( $exp ) . '</span>';
                } else {
                    echo '–';
                }
                break;
        }
    }

    public static function auction_columns( $cols ) {
        return [
            'cb'               => $cols['cb'],
            'title'            => 'Cím',
            'va_thumb'         => 'Kép',
            'va_start_price'   => 'Kikiáltási ár',
            'va_current_bid'   => 'Aktuális licit',
            'va_bid_count'     => 'Licitek',
            'va_auction_end'   => 'Aukció vége',
            'va_auction_winner'=> 'Nyertes',
            'author'           => 'Feladó',
            'date'             => 'Dátum',
        ];
    }

    public static function auction_column_content( $col, $post_id ) {
        switch ( $col ) {
            case 'va_thumb':
                echo get_the_post_thumbnail( $post_id, [ 60, 60 ] );
                break;
            case 'va_start_price':
                echo esc_html( number_format( (float) get_post_meta( $post_id, 'va_start_price', true ), 0, ',', ' ' ) . ' Ft' );
                break;
            case 'va_current_bid':
                $bid = get_post_meta( $post_id, 'va_current_bid', true );
                echo $bid ? esc_html( number_format( (float) $bid, 0, ',', ' ' ) . ' Ft' ) : '–';
                break;
            case 'va_bid_count':
                echo esc_html( get_post_meta( $post_id, 'va_bid_count', true ) ?: 0 );
                break;
            case 'va_auction_end':
                $end = get_post_meta( $post_id, 'va_auction_end', true );
                if ( $end ) {
                    $over  = strtotime( $end ) < time();
                    $class = $over ? 'color:red' : 'color:green';
                    echo '<span style="' . esc_attr( $class ) . '">' . esc_html( $end ) . '</span>';
                } else {
                    echo '–';
                }
                break;
            case 'va_auction_winner':
                $winner_id = get_post_meta( $post_id, 'va_auction_winner', true );
                if ( $winner_id ) {
                    $user = get_userdata( (int) $winner_id );
                    echo $user ? esc_html( $user->display_name ) : 'ID: ' . esc_html( $winner_id );
                } else {
                    echo '–';
                }
                break;
        }
    }

    public static function sortable_columns( $cols ) {
        $cols['va_price'] = 'va_price';
        $cols['va_views'] = 'va_views';
        return $cols;
    }

    /* ── Szűrő dropdownok a listában ─────────────────── */
    public static function add_filters( $post_type ) {
        if ( ! in_array( $post_type, [ 'va_listing', 'va_auction' ], true ) ) return;

        $categories = get_terms( [ 'taxonomy' => 'va_category', 'hide_empty' => false ] );
        echo '<select name="va_filter_category">';
        echo '<option value="">– Kategória –</option>';
        foreach ( $categories as $t ) {
            $sel = isset( $_GET['va_filter_category'] ) && $_GET['va_filter_category'] == $t->term_id ? ' selected' : '';
            echo '<option value="' . esc_attr( $t->term_id ) . '"' . $sel . '>' . esc_html( $t->name ) . '</option>';
        }
        echo '</select>';

        $counties = get_terms( [ 'taxonomy' => 'va_county', 'hide_empty' => false ] );
        echo '<select name="va_filter_county">';
        echo '<option value="">– Megye –</option>';
        foreach ( $counties as $t ) {
            $sel = isset( $_GET['va_filter_county'] ) && $_GET['va_filter_county'] == $t->term_id ? ' selected' : '';
            echo '<option value="' . esc_attr( $t->term_id ) . '"' . $sel . '>' . esc_html( $t->name ) . '</option>';
        }
        echo '</select>';
    }

    public static function parse_filter_query( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) return;

        $tax_query = [];
        if ( ! empty( $_GET['va_filter_category'] ) ) {
            $tax_query[] = [ 'taxonomy' => 'va_category', 'field' => 'term_id', 'terms' => intval( $_GET['va_filter_category'] ) ];
        }
        if ( ! empty( $_GET['va_filter_county'] ) ) {
            $tax_query[] = [ 'taxonomy' => 'va_county', 'field' => 'term_id', 'terms' => intval( $_GET['va_filter_county'] ) ];
        }
        if ( $tax_query ) {
            $query->set( 'tax_query', array_merge( [ 'relation' => 'AND' ], $tax_query ) );
        }
    }
}
