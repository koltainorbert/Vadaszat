<?php
/**
 * Meta mezők: Hirdetés + Aukció egyéni adatok
 * Admin metabox + mentés + frontend getter függvények
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Meta_Fields {

    /* ── Listing mezők definíciója ─────────────────────── */
    public static function listing_fields() {
        return [
            'va_price'        => [ 'label' => 'Ár (Ft)', 'type' => 'number', 'min' => 0 ],
            'va_price_type'   => [ 'label' => 'Árazás', 'type' => 'select',
                                   'options' => [ 'fixed' => 'Fix ár', 'negotiable' => 'Alkudható', 'free' => 'Ingyenes', 'on_request' => 'Érdeklődjön' ] ],
            'va_brand'        => [ 'label' => 'Márka / Gyártó', 'type' => 'text' ],
            'va_model'        => [ 'label' => 'Modell / Típus', 'type' => 'text' ],
            'va_caliber'      => [ 'label' => 'Kaliber', 'type' => 'text' ],
            'va_year'         => [ 'label' => 'Gyártási év', 'type' => 'number', 'min' => 1800, 'max' => 2026 ],
            'va_phone'        => [ 'label' => 'Telefonszám', 'type' => 'tel' ],
            'va_email_show'   => [ 'label' => 'Email megjelenítése', 'type' => 'checkbox' ],
            'va_location'     => [ 'label' => 'Helység (város/község)', 'type' => 'text' ],
            'va_expires'      => [ 'label' => 'Lejárat dátuma', 'type' => 'date' ],
            'va_featured'     => [ 'label' => 'Kiemelt hirdetés', 'type' => 'checkbox' ],
            'va_verified'     => [ 'label' => 'Ellenőrzött', 'type' => 'checkbox' ],
            'va_views'        => [ 'label' => 'Megtekintések', 'type' => 'number', 'readonly' => true ],
            'va_license_req'  => [ 'label' => 'Fegyverengedély szükséges', 'type' => 'checkbox' ],
        ];
    }

    /* ── Auction extra mezők ───────────────────────────── */
    public static function auction_fields() {
        return [
            'va_start_price'  => [ 'label' => 'Kikiáltási ár (Ft)', 'type' => 'number', 'min' => 0 ],
            'va_min_bid_step' => [ 'label' => 'Min. licitlépés (Ft)', 'type' => 'number', 'min' => 1 ],
            'va_buyout_price' => [ 'label' => 'Azonnal vásárlás ár (Ft, 0 = nincs)', 'type' => 'number', 'min' => 0 ],
            'va_auction_end'  => [ 'label' => 'Aukció vége (dátum + idő)', 'type' => 'datetime-local' ],
            'va_current_bid'  => [ 'label' => 'Aktuális licit (Ft)', 'type' => 'number', 'readonly' => true ],
            'va_bid_count'    => [ 'label' => 'Licitek száma', 'type' => 'number', 'readonly' => true ],
            'va_auction_winner' => [ 'label' => 'Nyertes user ID', 'type' => 'number', 'readonly' => true ],
            // Listing mezon is öröklik
            'va_brand'        => [ 'label' => 'Márka', 'type' => 'text' ],
            'va_model'        => [ 'label' => 'Modell', 'type' => 'text' ],
            'va_caliber'      => [ 'label' => 'Kaliber', 'type' => 'text' ],
            'va_year'         => [ 'label' => 'Gyártási év', 'type' => 'number' ],
            'va_phone'        => [ 'label' => 'Kapcsolat telefon', 'type' => 'tel' ],
            'va_location'     => [ 'label' => 'Helyszín', 'type' => 'text' ],
            'va_license_req'  => [ 'label' => 'Fegyverengedély szükséges', 'type' => 'checkbox' ],
        ];
    }

    /* ── Init ──────────────────────────────────────────── */
    public static function init() {
        add_action( 'add_meta_boxes', [ __CLASS__, 'add_metaboxes' ] );
        add_action( 'save_post',      [ __CLASS__, 'save_meta' ], 10, 2 );
    }

    public static function add_metaboxes() {
        add_meta_box( 'va_listing_meta', 'Hirdetés adatai', [ __CLASS__, 'render_listing_box' ],
            'va_listing', 'normal', 'high' );
        add_meta_box( 'va_auction_meta', 'Aukció adatai', [ __CLASS__, 'render_auction_box' ],
            'va_auction', 'normal', 'high' );
    }

    /* ── Rendering ─────────────────────────────────────── */
    private static function render_fields( $post, array $fields ) {
        wp_nonce_field( 'va_meta_save', 'va_meta_nonce' );
        echo '<table class="form-table">';
        foreach ( $fields as $key => $f ) {
            $val = get_post_meta( $post->ID, $key, true );
            echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $f['label'] ) . '</label></th><td>';

            if ( $f['type'] === 'select' ) {
                echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '">';
                foreach ( $f['options'] as $optval => $optlabel ) {
                    echo '<option value="' . esc_attr( $optval ) . '"' . selected( $val, $optval, false ) . '>' . esc_html( $optlabel ) . '</option>';
                }
                echo '</select>';
            } elseif ( $f['type'] === 'checkbox' ) {
                echo '<input type="checkbox" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="1"' . checked( $val, '1', false ) . '>';
            } else {
                $attrs = '';
                if ( isset( $f['min'] ) )      $attrs .= ' min="' . esc_attr( $f['min'] ) . '"';
                if ( isset( $f['max'] ) )      $attrs .= ' max="' . esc_attr( $f['max'] ) . '"';
                if ( ! empty( $f['readonly'] ) ) $attrs .= ' readonly';
                echo '<input type="' . esc_attr( $f['type'] ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" class="regular-text"' . $attrs . '>';
            }
            echo '</td></tr>';
        }
        echo '</table>';
    }

    public static function render_listing_box( $post ) {
        self::render_fields( $post, self::listing_fields() );
    }

    public static function render_auction_box( $post ) {
        self::render_fields( $post, self::auction_fields() );
    }

    /* ── Mentés ────────────────────────────────────────── */
    public static function save_meta( $post_id, $post ) {
        if ( ! isset( $_POST['va_meta_nonce'] ) ) return;
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_meta_nonce'] ) ), 'va_meta_save' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $fields = $post->post_type === 'va_auction'
            ? self::auction_fields()
            : self::listing_fields();

        foreach ( $fields as $key => $f ) {
            if ( $f['type'] === 'checkbox' ) {
                update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '0' );
            } elseif ( isset( $_POST[ $key ] ) && empty( $f['readonly'] ) ) {
                $val = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
                update_post_meta( $post_id, $key, $val );
            }
        }
    }
}
