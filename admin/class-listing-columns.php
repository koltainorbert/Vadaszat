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
