<?php
/**
 * AJAX kezelők: hirdetés feladás, watchlist, megtekintés számláló
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Ajax {

    public static function init() {
        // Hirdetés feladás
        add_action( 'wp_ajax_va_submit_listing',  [ __CLASS__, 'submit_listing' ] );
        // Hirdetés szerkesztés (frontend)
        add_action( 'wp_ajax_va_update_listing',  [ __CLASS__, 'update_listing' ] );
        add_action( 'template_redirect', [ __CLASS__, 'handle_listing_payment_callback' ] );

        // Kredit csomag vásárlás
        add_action( 'wp_ajax_va_buy_credits',        [ __CLASS__, 'buy_credits' ] );
        add_action( 'template_redirect',             [ __CLASS__, 'handle_credit_payment_callback' ] );

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
        if ( function_exists( 'va_auctions_enabled' ) && va_auctions_enabled() ) {
            add_action( 'save_post_va_auction', [ __CLASS__, 'flush_filter_cache' ] );
        }

        // Élő keresés
        add_action( 'wp_ajax_va_live_search',        [ __CLASS__, 'live_search' ] );
        add_action( 'wp_ajax_nopriv_va_live_search', [ __CLASS__, 'live_search' ] );
    }

    /* ── Hirdetés szerkesztés (frontend) ──────────────── */
    public static function update_listing() {
        check_ajax_referer( 'va_update_listing', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }

        $post_id = absint( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen hirdetés.' ] );
        }

        $post = get_post( $post_id );
        if ( ! $post || $post->post_type !== 'va_listing' || (int) $post->post_author !== get_current_user_id() ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság ehhez a hirdetéshez.' ] );
        }

        $title       = sanitize_text_field( wp_unslash( $_POST['title']       ?? '' ) );
        $description = sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) );
        $price       = floatval( $_POST['price'] ?? 0 );
        $price_type  = sanitize_key( $_POST['price_type'] ?? 'fixed' );
        $phone       = sanitize_text_field( wp_unslash( $_POST['phone']    ?? '' ) );
        $location    = sanitize_text_field( wp_unslash( $_POST['location'] ?? '' ) );
        $brand       = sanitize_text_field( wp_unslash( $_POST['brand']    ?? '' ) );
        $model       = sanitize_text_field( wp_unslash( $_POST['model']    ?? '' ) );
        $caliber     = sanitize_text_field( wp_unslash( $_POST['caliber']  ?? '' ) );
        $year        = intval( $_POST['year'] ?? 0 );
        $license_req = ! empty( $_POST['license_req'] ) ? '1' : '0';
        $category    = intval( $_POST['category'] ?? 0 );
        $county      = intval( $_POST['county']   ?? 0 );
        $condition   = intval( $_POST['condition'] ?? 0 );

        if ( empty( $title ) ) {
            wp_send_json_error( [ 'message' => 'A cím kötelező.' ] );
        }

        wp_update_post( [
            'ID'           => $post_id,
            'post_title'   => $title,
            'post_content' => $description,
        ] );

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
        ];
        foreach ( $metas as $k => $v ) {
            update_post_meta( $post_id, $k, $v );
        }

        if ( $category ) wp_set_post_terms( $post_id, [ $category ], 'va_category' );
        if ( $county   ) wp_set_post_terms( $post_id, [ $county ],   'va_county'   );
        if ( $condition) wp_set_post_terms( $post_id, [ $condition ], 'va_condition' );

        // Megtartandó meglévő képek
        $keep_raw = sanitize_text_field( wp_unslash( $_POST['keep_images'] ?? '' ) );
        $keep_ids = array_filter( array_map( 'absint', explode( ',', $keep_raw ) ) );

        // Töröljük azokat a galériában lévő képeket amiket nem tartanak meg
        $old_gallery_str = get_post_meta( $post_id, 'va_gallery_ids', true );
        $old_gallery = array_filter( array_map( 'absint', explode( ',', (string) $old_gallery_str ) ) );
        foreach ( $old_gallery as $old_id ) {
            if ( $old_id && ! in_array( $old_id, $keep_ids, true ) ) {
                wp_delete_attachment( $old_id, true );
            }
        }

        // Borítókép / megtartandó galériakép beállítása
        $feat_existing = absint( $_POST['featured_existing_id'] ?? 0 );

        // Új képek feltöltése
        $img_errors = [];
        if ( ! empty( $_FILES['listing_images'] ) && ! empty( $_FILES['listing_images']['name'][0] ) ) {
            $featured_idx = isset( $_POST['featured_image_index'] ) ? intval( $_POST['featured_image_index'] ) : 0;
            $img_errors = self::handle_images( $post_id, $_FILES['listing_images'], max( 0, $featured_idx ) );

            // handle_images beírta az új képeket va_gallery_ids-be; hozzáfűzzük a keep_ids-t elé
            $new_gallery_str = get_post_meta( $post_id, 'va_gallery_ids', true );
            $new_ids = array_filter( array_map( 'absint', explode( ',', (string) $new_gallery_str ) ) );
            $final = array_merge( $keep_ids, $new_ids );
            update_post_meta( $post_id, 'va_gallery_ids', implode( ',', $final ) );

            // Ha meglévő kép a borítókép
            if ( $feat_existing && in_array( $feat_existing, $keep_ids, true ) ) {
                set_post_thumbnail( $post_id, $feat_existing );
            }
        } else {
            // Nincs új kép – csak keep_ids marad
            update_post_meta( $post_id, 'va_gallery_ids', implode( ',', $keep_ids ) );
            if ( $feat_existing && in_array( $feat_existing, $keep_ids, true ) ) {
                set_post_thumbnail( $post_id, $feat_existing );
            } elseif ( ! empty( $keep_ids ) ) {
                set_post_thumbnail( $post_id, $keep_ids[0] );
            }
        }

        // listing_meta szinkronizálás
        if ( function_exists( 'va_sync_listing_meta' ) ) {
            va_sync_listing_meta( $post_id );
        }

        wp_send_json_success( [
            'message'   => 'Hirdetés sikeresen frissítve!',
            'post_id'   => $post_id,
            'permalink' => get_permalink( $post_id ),
        ] );
    }

    /* ── Hirdetés feladás ──────────────────────────────── */
    public static function submit_listing() {
        check_ajax_referer( 'va_submit_listing', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $free_limit = max( 0, absint( get_option( 'va_free_listings_limit', 1 ) ) );
        $paid_price = max( 0, absint( get_option( 'va_listing_price_after_free', 1990 ) ) );
        $payment_url = trim( (string) get_option( 'va_listing_payment_url', '' ) );

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

        // Plan-alapú limit ellenőrzés (VA_User_Roles rendszer)
        $plan_check    = VA_User_Roles::can_post_listing( $user_id );
        $is_free_allowed = true; // plan rendszer engedélyez → nincs kredit levonás

        if ( ! $plan_check['can'] ) {
            wp_send_json_error([
                'message'      => $plan_check['reason'],
                'need_upgrade' => true,
            ]);
        }

        // WP beállítástól függ: auto-publish vagy pending review
        $final_status = get_option( 'va_auto_publish_listings', '0' ) === '1' ? 'publish' : 'pending';
        $status = $final_status;

        $post_id = wp_insert_post([
            'post_title'   => $title,
            'post_content' => $description,
            'post_status'  => $status,
            'post_type'    => 'va_listing',
            'post_author'  => $user_id,
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
        $img_errors = [];
        if ( ! empty( $_FILES['listing_images'] ) ) {
            $featured_idx = isset( $_POST['featured_image_index'] ) ? absint( (string) $_POST['featured_image_index'] ) : 0;
            $img_errors = self::handle_images( $post_id, $_FILES['listing_images'], $featured_idx );
        }

        // Ha nem ingyenes: kredit levonás
        if ( ! $is_free_allowed ) {
            $credits = absint( get_user_meta( $user_id, 'va_listing_credits', true ) );
            update_user_meta( $user_id, 'va_listing_credits', max( 0, $credits - 1 ) );
        }

        $msg = $status === 'publish'
            ? 'Hirdetés sikeresen feladva!'
            : 'Hirdetés mentve – jóváhagyásra vár.';

        wp_send_json_success([
            'message'    => $msg,
            'post_id'    => $post_id,
            'permalink'  => get_permalink( $post_id ),
            'img_errors' => $img_errors,
        ]);
    }

    public static function handle_listing_payment_callback(): void {
        $payment_state = isset( $_GET['va_payment'] ) ? sanitize_key( (string) wp_unslash( $_GET['va_payment'] ) ) : '';
        if ( $payment_state === '' ) {
            return;
        }

        $token = isset( $_GET['token'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['token'] ) ) : '';
        if ( $token === '' || ! is_user_logged_in() ) {
            return;
        }

        $posts = get_posts([
            'post_type'      => 'va_listing',
            'post_status'    => [ 'draft', 'pending', 'publish' ],
            'author'         => get_current_user_id(),
            'posts_per_page' => 1,
            'meta_query'     => [
                [
                    'key'   => 'va_payment_token',
                    'value' => $token,
                ],
            ],
        ]);

        if ( empty( $posts ) ) {
            va_set_flash( 'error', 'A fizetési tranzakció nem található.' );
            self::redirect_submit_page();
        }

        $post = $posts[0];
        $post_id = (int) $post->ID;

        if ( $payment_state === 'cancel' ) {
            va_set_flash( 'warning', 'A fizetés megszakadt. A hirdetés vázlatban maradt.' );
            self::redirect_submit_page();
        }

        if ( $payment_state !== 'success' ) {
            return;
        }

        $already_paid = get_post_meta( $post_id, 'va_payment_status', true ) === 'paid';
        if ( ! $already_paid ) {
            $final_status = get_option( 'va_auto_publish_listings', '0' ) === '1' ? 'publish' : 'pending';
            wp_update_post([
                'ID'          => $post_id,
                'post_status' => $final_status,
            ]);

            update_post_meta( $post_id, 'va_payment_status', 'paid' );
            update_post_meta( $post_id, 'va_payment_paid_at', current_time( 'mysql' ) );

            $invoice_no = self::generate_invoice( $post_id );
            $msg = 'Sikeres fizetés. A hirdetés aktiválva.';
            if ( $invoice_no !== '' ) {
                $msg .= ' Számla: ' . $invoice_no;
            }
            va_set_flash( 'success', $msg );
        } else {
            va_set_flash( 'info', 'A fizetés már feldolgozásra került.' );
        }

        self::redirect_submit_page();
    }

    /* ── Kredit csomag vásárlás ────────────────────────── */
    public static function buy_credits(): void {
        check_ajax_referer( 'va_buy_credits', 'nonce' );
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'Nincs jogosultság.' ] );
        }

        $qty = absint( $_POST['qty'] ?? 0 );
        if ( $qty < 1 ) {
            wp_send_json_error( [ 'message' => 'Érvénytelen mennyiség.' ] );
        }

        $packages = self::get_credit_packages();
        // Legolcsóbb egységár-logika: a legmagasabb darabszámú csomag ami <= $qty
        $unit_price = (int) get_option( 'va_listing_price_after_free', 1990 );
        $total      = $unit_price * $qty;

        // Keresünk matching csomagot
        foreach ( array_reverse( $packages ) as $pkg ) {
            if ( $qty >= $pkg['qty'] ) {
                $unit_price = $pkg['unit_price'];
                $total      = $pkg['total'];
                break;
            }
        }

        $payment_url = trim( (string) get_option( 'va_listing_payment_url', '' ) );

        if ( $payment_url === '' ) {
            wp_send_json_error( [ 'message' => 'Fizetési szolgáltató nincs beállítva. Kérjük, lépjen kapcsolatba az adminisztrátorral.' ] );
        }

        $token  = wp_generate_password( 32, false, false );
        $user_id = get_current_user_id();

        // Token elmentése átmeneti adatban
        set_transient( 'va_credit_token_' . $token, [
            'user_id'    => $user_id,
            'qty'        => $qty,
            'amount'     => $total,
            'created_at' => time(),
        ], 3600 );

        $return_url = home_url( '/' );
        $submit_page = get_page_by_path( 'va-hirdetes-feladas' );
        if ( $submit_page ) {
            $return_url = get_permalink( $submit_page );
        }

        $success_url = add_query_arg([
            'va_credit_payment' => 'success',
            'token'             => rawurlencode( $token ),
        ], $return_url );
        $cancel_url = add_query_arg([
            'va_credit_payment' => 'cancel',
            'token'             => rawurlencode( $token ),
        ], $return_url );

        $checkout_url = add_query_arg([
            'intent'      => 'credit_purchase',
            'qty'         => $qty,
            'amount'      => $total,
            'token'       => $token,
            'success_url' => rawurlencode( $success_url ),
            'cancel_url'  => rawurlencode( $cancel_url ),
        ], $payment_url );

        wp_send_json_success( [
            'checkout_url' => esc_url_raw( $checkout_url ),
            'total'        => $total,
            'qty'          => $qty,
        ] );
    }

    /* ── Kredit fizetés callback ───────────────────────── */
    public static function handle_credit_payment_callback(): void {
        $state = isset( $_GET['va_credit_payment'] ) ? sanitize_key( (string) wp_unslash( $_GET['va_credit_payment'] ) ) : '';
        if ( $state === '' ) return;

        $token = isset( $_GET['token'] ) ? sanitize_text_field( (string) wp_unslash( $_GET['token'] ) ) : '';
        if ( $token === '' || ! is_user_logged_in() ) return;

        $data = get_transient( 'va_credit_token_' . $token );
        if ( ! $data || (int) $data['user_id'] !== get_current_user_id() ) {
            va_set_flash( 'error', 'A fizetési session érvénytelen vagy lejárt.' );
            self::redirect_submit_page();
            return;
        }

        if ( $state === 'cancel' ) {
            delete_transient( 'va_credit_token_' . $token );
            va_set_flash( 'warning', 'A fizetés megszakadt.' );
            self::redirect_submit_page();
            return;
        }

        if ( $state === 'success' ) {
            $qty     = absint( $data['qty'] );
            $user_id = (int) $data['user_id'];
            $current = absint( get_user_meta( $user_id, 'va_listing_credits', true ) );
            update_user_meta( $user_id, 'va_listing_credits', $current + $qty );
            delete_transient( 'va_credit_token_' . $token );
            va_set_flash( 'success', $qty . ' hirdetési kredit jóváírva! Most már feladhatod a hirdetésedet.' );
            self::redirect_submit_page();
        }
    }

    /* ── Kredit csomagok definíciója ───────────────────── */
    public static function get_credit_packages(): array {
        $base = (int) get_option( 'va_listing_price_after_free', 1990 );
        return [
            [ 'qty' => 1,  'label' => '1 hirdetés',   'unit_price' => $base,               'total' => $base,        'badge' => '' ],
            [ 'qty' => 3,  'label' => '3 hirdetés',   'unit_price' => (int)round($base*.9), 'total' => (int)round($base*3*.9),  'badge' => '–10%' ],
            [ 'qty' => 5,  'label' => '5 hirdetés',   'unit_price' => (int)round($base*.8), 'total' => (int)round($base*5*.8),  'badge' => '–20%' ],
            [ 'qty' => 10, 'label' => '10 hirdetés',  'unit_price' => (int)round($base*.7), 'total' => (int)round($base*10*.7), 'badge' => '–30%' ],
        ];
    }

    private static function generate_invoice( int $post_id ): string {
        $prefix_raw = (string) get_option( 'va_invoice_prefix', 'VA' );
        $prefix = strtoupper( preg_replace( '/[^A-Z0-9\-]/', '', remove_accents( $prefix_raw ) ) );
        if ( $prefix === '' ) {
            $prefix = 'VA';
        }

        $next = max( 1, absint( get_option( 'va_invoice_next_number', 1 ) ) );
        update_option( 'va_invoice_next_number', $next + 1 );

        $invoice_no = $prefix . '-' . date( 'Y' ) . '-' . str_pad( (string) $next, 6, '0', STR_PAD_LEFT );
        $amount = (int) get_post_meta( $post_id, 'va_payment_amount', true );
        $post = get_post( $post_id );

        $billing_company = (string) get_option( 'va_billing_company_name', 'Vadaszapro Kft.' );
        $billing_address = (string) get_option( 'va_billing_company_address', 'Magyarorszag' );
        $billing_tax     = (string) get_option( 'va_billing_tax_number', '' );
        $billing_email   = (string) get_option( 'va_billing_email', (string) get_option( 'admin_email', '' ) );
        $billing_phone   = (string) get_option( 'va_billing_phone', '' );
        $footer_note     = (string) get_option( 'va_invoice_footer_note', 'Koszonjuk a vasarlast!' );

        update_post_meta( $post_id, 'va_invoice_no', $invoice_no );
        update_post_meta( $post_id, 'va_invoice_amount', $amount );
        update_post_meta( $post_id, 'va_invoice_generated_at', current_time( 'mysql' ) );
        update_post_meta( $post_id, 'va_invoice_company_name', $billing_company );
        update_post_meta( $post_id, 'va_invoice_company_address', $billing_address );
        update_post_meta( $post_id, 'va_invoice_tax_number', $billing_tax );

        $upload = wp_upload_dir();
        if ( empty( $upload['error'] ) ) {
            $dir = trailingslashit( $upload['basedir'] ) . 'va-invoices';
            if ( ! wp_mkdir_p( $dir ) ) {
                return $invoice_no;
            }

            $filename = sanitize_file_name( strtolower( $invoice_no ) . '.pdf' );
            $path = trailingslashit( $dir ) . $filename;
            $url  = trailingslashit( $upload['baseurl'] ) . 'va-invoices/' . $filename;

            $lines = [
                'Vadaszapro - Szamla',
                'Szamlaszam: ' . $invoice_no,
                'Datum: ' . current_time( 'Y-m-d H:i:s' ),
                'Kiallito: ' . $billing_company,
                'Cim: ' . $billing_address,
                'Adoszam: ' . $billing_tax,
                'Email: ' . $billing_email,
                'Telefon: ' . $billing_phone,
                'Hirdetes ID: ' . $post_id,
                'Hirdetes cim: ' . ( $post ? $post->post_title : '' ),
                'Tetel: Hirdetes feladas dij',
                'Osszeg: ' . number_format( $amount, 0, ',', ' ' ) . ' Ft',
                'Megjegyzes: ' . $footer_note,
            ];

            $pdf = self::build_simple_invoice_pdf( $lines );
            if ( $pdf !== '' ) {
                file_put_contents( $path, $pdf );
            }
            update_post_meta( $post_id, 'va_invoice_url', esc_url_raw( $url ) );
        }

        return $invoice_no;
    }

    private static function build_simple_invoice_pdf( array $lines ): string {
        $safe_lines = [];
        foreach ( $lines as $line ) {
            $line = sanitize_text_field( (string) $line );
            $line = remove_accents( $line );
            $safe_lines[] = self::escape_pdf_text( $line );
        }

        $stream = "BT\n/F1 16 Tf\n50 790 Td\n(" . ( $safe_lines[0] ?? 'Szamla' ) . ") Tj\n";
        $stream .= "/F1 11 Tf\n0 -28 Td\n";

        for ( $i = 1; $i < count( $safe_lines ); $i++ ) {
            $stream .= "(" . $safe_lines[ $i ] . ") Tj\n0 -18 Td\n";
        }
        $stream .= "ET";

        $len = strlen( $stream );

        $objects = [];
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
        $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $objects[] = "5 0 obj\n<< /Length {$len} >>\nstream\n{$stream}\nendstream\nendobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [ 0 ];

        foreach ( $objects as $obj ) {
            $offsets[] = strlen( $pdf );
            $pdf .= $obj;
        }

        $xref_pos = strlen( $pdf );
        $count = count( $offsets );
        $pdf .= "xref\n0 {$count}\n";
        $pdf .= "0000000000 65535 f \n";

        for ( $i = 1; $i < $count; $i++ ) {
            $pdf .= sprintf( "%010d 00000 n \n", $offsets[ $i ] );
        }

        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xref_pos}\n%%EOF";

        return $pdf;
    }

    private static function escape_pdf_text( string $text ): string {
        $text = str_replace( "\\", "\\\\", $text );
        $text = str_replace( "(", "\\(", $text );
        $text = str_replace( ")", "\\)", $text );
        return $text;
    }

    private static function redirect_submit_page(): void {
        $submit_page = get_page_by_path( 'va-hirdetes-feladas' );
        $url = $submit_page ? get_permalink( $submit_page ) : home_url( '/va-hirdetes-feladas/' );
        wp_safe_redirect( $url );
        exit;
    }

    /* ── Képfeltöltés ──────────────────────────────────── */
    private static function handle_images( $post_id, $files, int $featured_idx = 0 ): array {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        // Grant upload_files cap via filter (no DB write, reverted after)
        $cap_filter = static function ( $allcaps ) {
            $allcaps['upload_files'] = true;
            return $allcaps;
        };
        add_filter( 'user_has_cap', $cap_filter );

        // Felhasználónkénti könyvtár: /va-users/{author_id}/listings/{post_id}/
        $listing_author_id = (int) get_post_field( 'post_author', $post_id );
        if ( ! $listing_author_id ) $listing_author_id = get_current_user_id();
        $va_listing_dir_filter = static function( $dirs ) use ( $listing_author_id, $post_id ) {
            $dirs['subdir'] = '/va-users/' . $listing_author_id . '/listings/' . $post_id;
            $dirs['path']   = $dirs['basedir'] . $dirs['subdir'];
            $dirs['url']    = $dirs['baseurl'] . $dirs['subdir'];
            return $dirs;
        };
        add_filter( 'upload_dir', $va_listing_dir_filter );

        $max_images     = intval( get_option( 'va_max_images_per_listing', 10 ) );
        $allowed_types  = [ 'image/jpeg', 'image/png', 'image/webp' ];
        $count          = 0;
        $attachment_ids = [];
        $errors         = [];

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

            if ( (int) $single['error'] !== UPLOAD_ERR_OK ) {
                $errors[] = 'PHP upload error ' . $single['error'] . ' for ' . $single['name'];
                continue;
            }
            if ( ! in_array( $single['type'], $allowed_types, true ) ) {
                $errors[] = 'Invalid type ' . $single['type'];
                continue;
            }

            $_FILES['va_upload'] = $single;
            $attachment_id = media_handle_upload( 'va_upload', $post_id );

            if ( is_wp_error( $attachment_id ) ) {
                $errors[] = $attachment_id->get_error_message();
            } else {
                $attachment_ids[] = $attachment_id;
                $count++;
            }
        }

        remove_filter( 'upload_dir', $va_listing_dir_filter );
        remove_filter( 'user_has_cap', $cap_filter );

        // Főkép beállítása a kiválasztott index alapján (vagy az első ha invalid)
        if ( ! empty( $attachment_ids ) ) {
            $feat = isset( $attachment_ids[ $featured_idx ] ) ? $attachment_ids[ $featured_idx ] : $attachment_ids[0];
            set_post_thumbnail( $post_id, $feat );
            update_post_meta( $post_id, 'va_gallery_ids', implode( ',', $attachment_ids ) );
        }

        return $errors;
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
        $allowed_post_types = [ 'va_listing' ];
        if ( function_exists( 'va_auctions_enabled' ) && va_auctions_enabled() ) {
            $allowed_post_types[] = 'va_auction';
        }

        $post_type = in_array( sanitize_key( $_POST['post_type'] ?? '' ), $allowed_post_types, true )
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

        // ── Boost rendezés konfig ─────────────────────────
        $boost_join         = '';
        $boost_order_prefix = '';
        if ( class_exists( 'VA_User_Roles' ) ) {
            $global_cfg = VA_User_Roles::get_all_plan_configs()['_global'] ?? [];
            if ( ! empty( $global_cfg['boost_enabled'] ) ) {
                $window_days        = (int) ( $global_cfg['boost_badge_window'] ?? 14 );
                $boost_cutoff       = time() - $window_days * DAY_IN_SECONDS;
                $boost_join         = "LEFT JOIN {$wpdb->postmeta} AS va_bst
                                        ON ( va_bst.post_id = p.ID AND va_bst.meta_key = 'va_boost_time' )";
                $boost_order_prefix = "CASE WHEN CAST( va_bst.meta_value AS UNSIGNED ) > {$boost_cutoff} THEN 1 ELSE 0 END DESC, ";
            }
        }

        // ── Rendezés ─────────────────────────────────────
        $order_sql = $boost_order_prefix . match ( $sort ) {
            'price_asc'  => 'lm.featured DESC, lm.price ASC,  p.post_date DESC',
            'price_desc' => 'lm.featured DESC, lm.price DESC, p.post_date DESC',
            'views'      => 'lm.featured DESC, lm.views DESC, p.post_date DESC',
            default      => 'lm.featured DESC, p.post_date DESC',
        };

        // ── Összesített szám (lapozáshoz) ─────────────────
        $count_sql = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$posts} p
             LEFT JOIN {$lm} lm ON lm.post_id = p.ID
             {$boost_join}
             {$where_sql}",
            ...$params
        );
        $total = (int) $wpdb->get_var( $count_sql );

        // ── ID lista – csak az aktuális lap ──────────────
        $id_sql = $wpdb->prepare(
            "SELECT p.ID FROM {$posts} p
             LEFT JOIN {$lm} lm ON lm.post_id = p.ID
             {$boost_join}
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

        // Hirdetés (+ opcionálisan aukció) találatok
        $search_post_types = [ 'va_listing' ];
        if ( function_exists( 'va_auctions_enabled' ) && va_auctions_enabled() ) {
            $search_post_types[] = 'va_auction';
        }

        $query = new WP_Query([
            'post_type'      => $search_post_types,
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
