<?php
/**
 * Settings oldal – minden beállítás egy helyen
 * Lapok: Általános | Reklámzónák | Hirdetések | Aukciók | Felhasználók | Statisztika
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Settings_Page {

    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
    }

    /* ══ Settings regisztráció ════════════════════════════ */
    public static function register_settings() {

        /* Általános */
        $general = [
            'va_site_name'           => 'VadászApró',
            'va_site_description'    => 'Magyarország vadászati apróhirdetési oldala',
            'va_contact_email'       => get_option('admin_email'),
            'va_listings_per_page'   => 20,
            'va_auto_publish_listings' => '0',  // 0=jóváhagyás szükséges, 1=azonnal él
            'va_listing_validity_days' => 60,   // hirdetés lejárata (nap) feladáskor
            'va_max_images_per_listing' => 10,
            'va_require_phone'       => '1',
            'va_maintenance_mode'    => '0',
            'va_maintenance_msg'     => 'Az oldal karbantartás alatt van.',
        ];

        foreach ( $general as $key => $default ) {
            register_setting( 'va_general_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Reklámzónák */
        foreach ( array_keys( VA_Ad_Zones::ZONES ) as $zone ) {
            register_setting( 'va_ad_settings', 'va_ad_zone_' . $zone, [
                'sanitize_callback' => function( $val ) {
                    $allowed = array_merge( wp_kses_allowed_html('post'), [
                        'a'   => [ 'href'=>[], 'target'=>[], 'rel'=>[], 'style'=>[], 'class'=>[] ],
                        'img' => [ 'src'=>[], 'alt'=>[], 'width'=>[], 'height'=>[], 'style'=>[], 'class'=>[] ],
                        'div' => [ 'style'=>[], 'class'=>[] ],
                        'script' => [], // Google Ads iframe-ek miatt
                        'iframe' => [ 'src'=>[], 'width'=>[], 'height'=>[], 'style'=>[], 'frameborder'=>[], 'scrolling'=>[], 'allowtransparency'=>[] ],
                    ]);
                    return wp_kses( wp_unslash( $val ), $allowed );
                },
            ]);
        }

        /* Hirdetések */
        $listing_opts = [
            'va_featured_price'      => 2990,
            'va_featured_days'       => 30,
            'va_free_listings_limit' => 5,
        ];
        foreach ( $listing_opts as $key => $default ) {
            register_setting( 'va_listing_settings', $key, [ 'sanitize_callback' => 'absint' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Aukciók */
        $auction_opts = [
            'va_default_min_bid_step' => 500,
            'va_auction_fee_pct'      => 0,   // % jutalék (jövőre)
        ];
        foreach ( $auction_opts as $key => $default ) {
            register_setting( 'va_auction_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }
    }

    /* ══ Általános beállítások oldal ══════════════════════ */
    public static function render_general() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap va-admin-wrap">
            <h1>⚙️ VadászApró – Általános beállítások</h1>
            <?php settings_errors( 'va_general_settings' ); ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'va_general_settings' ); ?>
                <table class="form-table">
                    <?php self::field_text(  'va_site_name',           'Oldal neve' ); ?>
                    <?php self::field_text(  'va_site_description',     'Oldal alcíme / leírás' ); ?>
                    <?php self::field_email( 'va_contact_email',        'Kapcsolati e-mail' ); ?>
                    <?php self::field_num(   'va_listings_per_page',    'Hirdetés / oldal', 5, 100 ); ?>
                    <?php self::field_num(   'va_listing_validity_days','Hirdetés érvényessége (nap)', 1, 365 ); ?>
                    <?php self::field_num(   'va_max_images_per_listing','Max. képek száma hirdetésenként', 1, 20 ); ?>
                    <?php self::field_toggle('va_auto_publish_listings', 'Hirdetések azonnali megjelenés (jóváhagyás nélkül)' ); ?>
                    <?php self::field_toggle('va_require_phone',         'Telefonszám kötelező' ); ?>
                    <?php self::field_toggle('va_maintenance_mode',      'Karbantartási mód' ); ?>
                    <?php self::field_text(  'va_maintenance_msg',       'Karbantartási üzenet' ); ?>
                </table>
                <?php submit_button( 'Mentés' ); ?>
            </form>
        </div>
        <?php
    }

    /* ══ Reklámzóna oldal ═════════════════════════════════ */
    public static function render_ad_zones() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap va-admin-wrap">
            <h1>📢 VadászApró – Reklámzónák</h1>
            <p class="description">Minden zónába beilleszthetsz bármilyen HTML-t: Google AdSense kódot, saját banner-t, iframe-et stb.</p>
            <?php settings_errors( 'va_ad_settings' ); ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'va_ad_settings' ); ?>
                <?php foreach ( VA_Ad_Zones::get_all() as $zone_key => $zone ): ?>
                <div class="va-ad-zone-block">
                    <h2><?php echo esc_html( $zone['label'] ); ?></h2>
                    <p class="description">Shortcode: <code>[va_ad_zone zone="<?php echo esc_attr( $zone_key ); ?>"]</code></p>
                    <textarea
                        name="va_ad_zone_<?php echo esc_attr( $zone_key ); ?>"
                        rows="6"
                        class="large-text code va-ad-textarea"
                        placeholder="&lt;!-- Google AdSense vagy saját HTML banner kód --&gt;"
                    ><?php echo esc_textarea( $zone['html'] ); ?></textarea>
                </div>
                <hr>
                <?php endforeach; ?>
                <?php submit_button( 'Reklámzónák mentése' ); ?>
            </form>
        </div>
        <?php
    }

    /* ══ Hirdetés beállítások ═════════════════════════════ */
    public static function render_listings() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap va-admin-wrap">
            <h1>📋 VadászApró – Hirdetés beállítások</h1>
            <?php settings_errors( 'va_listing_settings' ); ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'va_listing_settings' ); ?>
                <table class="form-table">
                    <?php self::field_num( 'va_featured_price', 'Kiemelt hirdetés ára (Ft)', 0, 99999 ); ?>
                    <?php self::field_num( 'va_featured_days',  'Kiemelt hirdetés időtartama (nap)', 1, 365 ); ?>
                    <?php self::field_num( 'va_free_listings_limit', 'Ingyenes hirdetések száma felhasználónként (0=korlátlan)', 0, 999 ); ?>
                </table>
                <?php submit_button( 'Mentés' ); ?>
            </form>
        </div>
        <?php
    }

    /* ══ Aukció beállítások ═══════════════════════════════ */
    public static function render_auctions() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap va-admin-wrap">
            <h1>🔨 VadászApró – Aukció beállítások</h1>
            <?php settings_errors( 'va_auction_settings' ); ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'va_auction_settings' ); ?>
                <table class="form-table">
                    <?php self::field_num( 'va_default_min_bid_step', 'Alapértelmezett minimum licitlépés (Ft)', 1, 999999 ); ?>
                    <?php self::field_num( 'va_auction_fee_pct',       'Aukciós jutalék (%)', 0, 100 ); ?>
                </table>
                <?php submit_button( 'Mentés' ); ?>
            </form>
        </div>
        <?php
    }

    /* ══ Felhasználók oldal ═══════════════════════════════ */
    public static function render_users() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $users = get_users([
            'role__in'   => [ 'subscriber', 'contributor', 'editor' ],
            'number'     => 50,
            'orderby'    => 'registered',
            'order'      => 'DESC',
        ]);
        ?>
        <div class="wrap va-admin-wrap">
            <h1>👤 VadászApró – Felhasználók</h1>
            <table class="wp-list-table widefat fixed striped va-users-table">
                <thead>
                    <tr>
                        <th>Felhasználónév</th>
                        <th>Név</th>
                        <th>E-mail</th>
                        <th>Telefon</th>
                        <th>Regisztráció</th>
                        <th>Hirdetések</th>
                        <th>Műveletek</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ( $users as $user ):
                    $phone     = get_user_meta( $user->ID, 'va_phone', true );
                    $listings  = count_user_posts( $user->ID, 'va_listing' );
                    $auctions  = count_user_posts( $user->ID, 'va_auction' );
                ?>
                    <tr>
                        <td><?php echo esc_html( $user->user_login ); ?></td>
                        <td><?php echo esc_html( $user->display_name ); ?></td>
                        <td><?php echo esc_html( $user->user_email ); ?></td>
                        <td><?php echo esc_html( $phone ?: '–' ); ?></td>
                        <td><?php echo esc_html( date_i18n( 'Y.m.d', strtotime( $user->user_registered ) ) ); ?></td>
                        <td><?php echo esc_html( $listings ); ?> hird. / <?php echo esc_html( $auctions ); ?> aukció</td>
                        <td>
                            <a href="<?php echo esc_url( get_edit_user_link( $user->ID ) ); ?>">Szerkesztés</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /* ══ Statisztika oldal ════════════════════════════════ */
    public static function render_stats() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        global $wpdb;
        $total_listings  = wp_count_posts( 'va_listing' );
        $total_auctions  = wp_count_posts( 'va_auction' );
        $total_users     = count_users();
        $total_bids      = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}va_bids" );
        $total_watchlist = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}va_watchlist" );
        $top_viewed      = get_posts([
            'post_type'      => 'va_listing',
            'post_status'    => 'publish',
            'meta_key'       => 'va_views',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'posts_per_page' => 5,
        ]);
        ?>
        <div class="wrap va-admin-wrap">
            <h1>📊 VadászApró – Statisztika</h1>
            <div class="va-stats-grid">
                <div class="va-stat-card">
                    <span class="va-stat-num"><?php echo esc_html( $total_listings->publish ); ?></span>
                    <span class="va-stat-label">Aktív hirdetés</span>
                </div>
                <div class="va-stat-card">
                    <span class="va-stat-num"><?php echo esc_html( $total_listings->pending ); ?></span>
                    <span class="va-stat-label">Jóváhagyásra vár</span>
                </div>
                <div class="va-stat-card">
                    <span class="va-stat-num"><?php echo esc_html( $total_auctions->publish ); ?></span>
                    <span class="va-stat-label">Aktív aukció</span>
                </div>
                <div class="va-stat-card">
                    <span class="va-stat-num"><?php echo esc_html( $total_users['total_users'] ); ?></span>
                    <span class="va-stat-label">Regisztrált felhasználó</span>
                </div>
                <div class="va-stat-card">
                    <span class="va-stat-num"><?php echo esc_html( $total_bids ?: 0 ); ?></span>
                    <span class="va-stat-label">Összes licit</span>
                </div>
                <div class="va-stat-card">
                    <span class="va-stat-num"><?php echo esc_html( $total_watchlist ?: 0 ); ?></span>
                    <span class="va-stat-label">Figyelő (watchlist)</span>
                </div>
            </div>

            <h2>Top 5 legtöbb megtekintés</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead><tr><th>Hirdetés</th><th>Megtekintés</th><th>Dátum</th></tr></thead>
                <tbody>
                <?php foreach ( $top_viewed as $p ):
                    $views = get_post_meta( $p->ID, 'va_views', true );
                ?>
                <tr>
                    <td><a href="<?php echo esc_url( get_permalink( $p->ID ) ); ?>"><?php echo esc_html( $p->post_title ); ?></a></td>
                    <td><?php echo esc_html( $views ?: 0 ); ?></td>
                    <td><?php echo esc_html( get_the_date( 'Y.m.d', $p ) ); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /* ══ Helper mezők ═════════════════════════════════════ */
    private static function field_text( string $key, string $label ): void {
        $val = esc_attr( get_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"text\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text\"></td></tr>";
    }

    private static function field_email( string $key, string $label ): void {
        $val = esc_attr( get_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"email\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text\"></td></tr>";
    }

    private static function field_num( string $key, string $label, int $min = 0, int $max = 9999 ): void {
        $val = esc_attr( get_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"number\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" min=\"{$min}\" max=\"{$max}\" class=\"small-text\"></td></tr>";
    }

    private static function field_toggle( string $key, string $label ): void {
        $val = get_option( $key, '0' );
        echo "<tr><th>{$label}</th><td><label class=\"va-toggle\"><input type=\"checkbox\" name=\"{$key}\" value=\"1\"" . checked( $val, '1', false ) . "><span class=\"va-toggle-slider\"></span></label></td></tr>";
    }
}
