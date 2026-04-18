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
            'va_enable_auctions'     => '1',
            'va_header_logo_height'  => 36,
            'va_hero_logo_height'    => 72,
            'va_hero_logo_position'  => 'left',
            'va_home_hero_align'     => 'left',
            'va_kategoria_hero_align'=> 'center',
            'va_tax_hero_align'      => 'center',
            'va_contact_hero_align'  => 'center',
            'va_listings_per_page'   => 20,
            'va_auto_publish_listings' => '0',  // 0=jóváhagyás szükséges, 1=azonnal él
            'va_listing_validity_days' => 60,   // hirdetés lejárata (nap) feladáskor
            'va_max_images_per_listing' => 10,
            'va_require_phone'       => '1',
            'va_maintenance_mode'    => '0',
            'va_maintenance_msg'     => 'Az oldal karbantartás alatt van.',

            // Főoldal hero szövegek
            'va_home_hero_badge_text'        => 'Magyarország első vadászati hirdetőoldala',
            'va_home_hero_title_top'         => 'VadászBazár',
            'va_home_hero_title_bottom'      => 'és Apróhirdetés',
            'va_home_hero_sub_text'          => 'Magyarország első vadászati hirdetőoldala',
            'va_home_hero_primary_cta_text'  => '+ Hirdetés feladása',
            'va_home_hero_secondary_cta_text'=> 'Hirdetések böngészése →',

            // Kategória oldal hero szövegek
            'va_kategoria_hero_badge_text'   => 'Vadász Apróhirdetések',
            'va_kategoria_hero_title_top'    => 'Válassz',
            'va_kategoria_hero_title_bottom' => 'Kategóriát',
            'va_kategoria_hero_sub_text'     => 'Golyós puskáktól a trófea-alapzatokig – minden vadász felszerelésnél egy helyen',
            'va_kategoria_hero_stat1_label'  => 'Főkategória',
            'va_kategoria_hero_stat2_label'  => 'Aktív hirdetés',

            // Alkategória hero szövegek
            'va_tax_hero_badge_text'         => 'Kategória ajánló',
            'va_tax_hero_fallback_lead'      => 'A kiválasztott kategóriában böngészel, görgess tovább a friss ajánlatokért.',
            'va_tax_hero_count_suffix'       => 'hirdetés',

            // Kapcsolat oldal hero szövegek
            'va_contact_hero_badge_text'     => 'Kapcsolat',
            'va_contact_hero_title_text'     => 'Írj nekünk e-mailt',
            'va_contact_hero_lead_text'      => 'A kapcsolatfelvétel kizárólag e-mailben történik. Az itt elküldött üzenetek WordPress oldalon keresztül, a WP Mail SMTP bővítményen át jutnak el hozzánk.',
        ];

        foreach ( $general as $key => $default ) {
            register_setting( 'va_general_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Design (betűk + színek) */
        $design = [
            // Betűtípusok
            'va_font_global'        => 'system',
            'va_font_headings'      => 'montserrat',
            'va_font_header'        => 'montserrat',
            'va_font_content'       => 'source-sans-3',
            'va_font_footer'        => 'source-sans-3',

            // Globális színek
            'va_color_global_bg'     => '#060606',
            'va_color_global_text'   => '#ffffff',
            'va_color_global_muted'  => 'rgba(255,255,255,.65)',
            'va_color_global_accent' => '#ff0000',

            // Header
            'va_color_header_bg'     => 'rgba(6,4,4,.82)',
            'va_color_header_text'   => '#ffffff',
            'va_color_header_accent' => '#ff0000',

            // Tartalom
            'va_color_content_bg'       => '#060606',
            'va_color_content_text'     => '#ffffff',
            'va_color_content_headings' => '#ffffff',
            'va_color_content_links'    => '#ff4444',

            // Footer
            'va_color_footer_bg'       => '#0a0a0a',
            'va_color_footer_text'     => 'rgba(255,255,255,.72)',
            'va_color_footer_headings' => '#ffffff',
            'va_color_footer_links'    => '#ff4444',

            // Hero méretek (összes oldal)
            'va_size_home_hero_badge'      => 11,
            'va_size_home_hero_title'      => 64,
            'va_size_home_hero_sub'        => 19,
            'va_size_home_hero_btn'        => 15,
            'va_size_kat_hero_badge'       => 10,
            'va_size_kat_hero_title'       => 56,
            'va_size_kat_hero_sub'         => 15,
            'va_size_kat_hero_stat_num'    => 20,
            'va_size_kat_hero_stat_label'  => 10,
            'va_size_tax_hero_badge'       => 11,
            'va_size_tax_hero_title'       => 48,
            'va_size_tax_hero_lead'        => 16,
            'va_size_tax_hero_count'       => 14,
            'va_size_contact_hero_badge'   => 11,
            'va_size_contact_hero_title'   => 62,
            'va_size_contact_hero_lead'    => 16,

            // Hero sorközök
            'va_lh_home_hero_title'        => '1.05',
            'va_lh_home_hero_sub'          => '1.60',
            'va_lh_kat_hero_title'         => '1.06',
            'va_lh_kat_hero_sub'           => '1.70',
            'va_lh_tax_hero_title'         => '1.05',
            'va_lh_tax_hero_lead'          => '1.75',
            'va_lh_contact_hero_title'     => '1.02',
            'va_lh_contact_hero_lead'      => '1.80',

            // Fejléc elemek (méret + típus/súly)
            'va_size_header_brand'         => 18,
            'va_size_header_nav'           => 14,
            'va_size_header_search'        => 14,
            'va_size_header_btn'           => 13,
            'va_weight_header_brand'       => '800',
            'va_weight_header_nav'         => '600',

            // Lábléc elemek (méret + típus/súly)
            'va_size_footer_title'         => 13,
            'va_size_footer_link'          => 13,
            'va_size_footer_bottom'        => 12,
            'va_weight_footer_title'       => '700',
            'va_weight_footer_link'        => '500',

            // Mobil skála (%)
            'va_mobile_factor_hero'        => 100,
            'va_mobile_factor_header'      => 100,
            'va_mobile_factor_footer'      => 100,
        ];
        foreach ( $design as $key => $default ) {
            register_setting( 'va_design_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        // Videó URL-ek (hero/oldalblokkok) – adminból szerkeszthető
        $video_urls = [
            'va_home_hero_video_url'      => content_url( 'uploads/2026/04/521380_Gun_Woman_1920x1080.mp4' ),
            'va_contact_hero_video_url'   => content_url( 'uploads/2026/04/0_Offroad_4x4_1920x1080.mp4' ),
            'va_category_video_url'       => content_url( 'uploads/2026/04/1434963_Hunter_Autumn_1920x1080.mp4' ),
            'va_tax_category_video_url'   => content_url( 'uploads/2026/04/1434963_Hunter_Autumn_1920x1080.mp4' ),
        ];
        foreach ( $video_urls as $key => $default ) {
            register_setting( 'va_general_settings', $key, [ 'sanitize_callback' => 'esc_url_raw' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        // Márka ikon URL (header ikon + automata favicon)
        register_setting( 'va_general_settings', 'va_brand_icon_url', [ 'sanitize_callback' => 'esc_url_raw' ] );
        if ( get_option( 'va_brand_icon_url' ) === false ) {
            update_option( 'va_brand_icon_url', '' );
        }

        // Logók (fejléc + hero)
        register_setting( 'va_general_settings', 'va_header_logo_url', [ 'sanitize_callback' => 'esc_url_raw' ] );
        if ( get_option( 'va_header_logo_url' ) === false ) {
            update_option( 'va_header_logo_url', '' );
        }
        register_setting( 'va_general_settings', 'va_hero_logo_url', [ 'sanitize_callback' => 'esc_url_raw' ] );
        if ( get_option( 'va_hero_logo_url' ) === false ) {
            update_option( 'va_hero_logo_url', '' );
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
                    <?php self::field_media( 'va_brand_icon_url',       'Ikon (automata favicon, ajánlott: négyzetes PNG)' ); ?>
                    <?php self::field_media( 'va_header_logo_url',      'Fejléc logó' ); ?>
                    <?php self::field_num(   'va_header_logo_height',   'Fejléc logó magasság (px)', 20, 120 ); ?>
                    <?php self::field_media( 'va_hero_logo_url',        'Hero logó (főoldal)' ); ?>
                    <?php self::field_num(   'va_hero_logo_height',     'Hero logó magasság (px)', 30, 260 ); ?>
                    <?php self::field_select('va_hero_logo_position',   'Hero logó pozíció', [ 'left' => 'Bal', 'center' => 'Közép', 'right' => 'Jobb' ] ); ?>
                    <?php self::field_select('va_home_hero_align',      'Főoldal hero elemek igazítása', [ 'left' => 'Balra zárt', 'center' => 'Középre', 'right' => 'Jobbra zárt' ] ); ?>
                    <?php self::field_url(   'va_home_hero_video_url',  'Főoldal hero videó URL' ); ?>
                    <?php self::field_url(   'va_contact_hero_video_url', 'Kapcsolat oldal videó URL' ); ?>
                    <?php self::field_url(   'va_category_video_url', 'Kategória főoldal videó URL' ); ?>
                    <?php self::field_url(   'va_tax_category_video_url', 'Alkategória oldal videó URL' ); ?>
                    <?php self::field_select('va_kategoria_hero_align', 'Kategória hero elemek igazítása', [ 'left' => 'Balra zárt', 'center' => 'Középre', 'right' => 'Jobbra zárt' ] ); ?>
                    <?php self::field_select('va_tax_hero_align',       'Alkategória hero elemek igazítása', [ 'left' => 'Balra zárt', 'center' => 'Középre', 'right' => 'Jobbra zárt' ] ); ?>
                    <?php self::field_select('va_contact_hero_align',   'Kapcsolat hero elemek igazítása', [ 'left' => 'Balra zárt', 'center' => 'Középre', 'right' => 'Jobbra zárt' ] ); ?>
                    <?php self::field_toggle('va_enable_auctions',       'Aukció funkció engedélyezése' ); ?>
                    <?php self::field_num(   'va_listings_per_page',    'Hirdetés / oldal', 5, 100 ); ?>
                    <?php self::field_num(   'va_listing_validity_days','Hirdetés érvényessége (nap)', 1, 365 ); ?>
                    <?php self::field_num(   'va_max_images_per_listing','Max. képek száma hirdetésenként', 1, 20 ); ?>
                    <?php self::field_toggle('va_auto_publish_listings', 'Hirdetések azonnali megjelenés (jóváhagyás nélkül)' ); ?>
                    <?php self::field_toggle('va_require_phone',         'Telefonszám kötelező' ); ?>
                    <?php self::field_toggle('va_maintenance_mode',      'Karbantartási mód' ); ?>
                    <?php self::field_text(  'va_maintenance_msg',       'Karbantartási üzenet' ); ?>

                    <?php self::field_text(  'va_home_hero_badge_text',         'Főoldal hero badge szöveg' ); ?>
                    <?php self::field_text(  'va_home_hero_title_top',          'Főoldal hero cím 1. sor' ); ?>
                    <?php self::field_text(  'va_home_hero_title_bottom',       'Főoldal hero cím 2. sor' ); ?>
                    <?php self::field_text(  'va_home_hero_sub_text',           'Főoldal hero alcím' ); ?>
                    <?php self::field_text(  'va_home_hero_primary_cta_text',   'Főoldal hero első gomb szöveg' ); ?>
                    <?php self::field_text(  'va_home_hero_secondary_cta_text', 'Főoldal hero második gomb szöveg' ); ?>

                    <?php self::field_text(  'va_kategoria_hero_badge_text',    'Kategória hero badge szöveg' ); ?>
                    <?php self::field_text(  'va_kategoria_hero_title_top',     'Kategória hero cím 1. sor' ); ?>
                    <?php self::field_text(  'va_kategoria_hero_title_bottom',  'Kategória hero cím 2. sor' ); ?>
                    <?php self::field_text(  'va_kategoria_hero_sub_text',      'Kategória hero alcím' ); ?>
                    <?php self::field_text(  'va_kategoria_hero_stat1_label',   'Kategória hero stat 1 felirat' ); ?>
                    <?php self::field_text(  'va_kategoria_hero_stat2_label',   'Kategória hero stat 2 felirat' ); ?>

                    <?php self::field_text(  'va_tax_hero_badge_text',          'Alkategória hero badge szöveg' ); ?>
                    <?php self::field_text(  'va_tax_hero_fallback_lead',       'Alkategória hero alapértelmezett leírás' ); ?>
                    <?php self::field_text(  'va_tax_hero_count_suffix',        'Alkategória hero találatszám utótag' ); ?>

                    <?php self::field_text(  'va_contact_hero_badge_text',      'Kapcsolat hero badge szöveg' ); ?>
                    <?php self::field_text(  'va_contact_hero_title_text',      'Kapcsolat hero cím' ); ?>
                    <?php self::field_text(  'va_contact_hero_lead_text',       'Kapcsolat hero alcím' ); ?>
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

    /* ══ Design oldal (globális + header/content/footer) ════════ */
    public static function render_design() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $fonts = [
            'system'         => 'System UI (gyors, natív)',
            'inter'          => 'Inter',
            'roboto'         => 'Roboto',
            'montserrat'     => 'Montserrat',
            'oswald'         => 'Oswald',
            'merriweather'   => 'Merriweather',
            'playfair'       => 'Playfair Display',
            'lora'           => 'Lora',
            'nunito'         => 'Nunito',
            'source-sans-3'  => 'Source Sans 3',
            'pt-sans'        => 'PT Sans',
            'raleway'        => 'Raleway',
            'bebas-neue'     => 'Bebas Neue',
            'rubik'          => 'Rubik',
            'dm-sans'        => 'DM Sans',
            'work-sans'      => 'Work Sans',
            'manrope'        => 'Manrope',
            'fira-sans'      => 'Fira Sans',
            'ibm-plex-sans'  => 'IBM Plex Sans',
            'noto-sans'      => 'Noto Sans',
        ];
        $weights = [
            '300' => '300 – Light',
            '400' => '400 – Normal',
            '500' => '500 – Medium',
            '600' => '600 – SemiBold',
            '700' => '700 – Bold',
            '800' => '800 – ExtraBold',
            '900' => '900 – Black',
        ];
        ?>
        <div class="wrap va-admin-wrap">
            <h1>🎨 VadászApró – Design (globális + fejtől lábig)</h1>
            <p class="description">Külön oldalon kezelhető a teljes tipográfia és színvilág: globális, fejléc, tartalom és lábléc szinten.</p>
            <?php settings_errors( 'va_design_settings' ); ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'va_design_settings' ); ?>

                <h2>Betűtípusok</h2>
                <table class="form-table">
                    <?php self::field_select( 'va_font_global',   'Globális alap betűtípus', $fonts ); ?>
                    <?php self::field_select( 'va_font_headings', 'Címsorok (H1-H6) betűtípus', $fonts ); ?>
                    <?php self::field_select( 'va_font_header',   'Fejléc/Navigáció betűtípus', $fonts ); ?>
                    <?php self::field_select( 'va_font_content',  'Tartalmi szöveg betűtípus', $fonts ); ?>
                    <?php self::field_select( 'va_font_footer',   'Lábléc betűtípus', $fonts ); ?>
                </table>

                <h2>Globális színek</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_color_global_bg',     'Globális háttér' ); ?>
                    <?php self::field_color( 'va_color_global_text',   'Globális fő szöveg' ); ?>
                    <?php self::field_text(  'va_color_global_muted',  'Globális halvány szöveg (pl. rgba...)' ); ?>
                    <?php self::field_color( 'va_color_global_accent', 'Globális accent szín' ); ?>
                </table>

                <h2>Fejléc színek</h2>
                <table class="form-table">
                    <?php self::field_text(  'va_color_header_bg',     'Fejléc háttér (hex vagy rgba)' ); ?>
                    <?php self::field_color( 'va_color_header_text',   'Fejléc szöveg' ); ?>
                    <?php self::field_color( 'va_color_header_accent', 'Fejléc accent' ); ?>
                </table>

                <h2>Tartalom színek</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_color_content_bg',       'Tartalom háttér' ); ?>
                    <?php self::field_color( 'va_color_content_text',     'Tartalom szöveg' ); ?>
                    <?php self::field_color( 'va_color_content_headings', 'Tartalom címsorok' ); ?>
                    <?php self::field_color( 'va_color_content_links',    'Tartalom linkek' ); ?>
                </table>

                <h2>Lábléc színek</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_color_footer_bg',       'Lábléc háttér' ); ?>
                    <?php self::field_text(  'va_color_footer_text',     'Lábléc szöveg (hex vagy rgba)' ); ?>
                    <?php self::field_color( 'va_color_footer_headings', 'Lábléc címsorok' ); ?>
                    <?php self::field_color( 'va_color_footer_links',    'Lábléc linkek' ); ?>
                </table>

                <h2>Hero szöveg méretek (összes oldal)</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_size_home_hero_badge',    'Főoldal hero badge méret (px)', 8, 32 ); ?>
                    <?php self::field_num( 'va_size_home_hero_title',    'Főoldal hero cím méret (px)', 24, 120 ); ?>
                    <?php self::field_num( 'va_size_home_hero_sub',      'Főoldal hero alcím méret (px)', 10, 42 ); ?>
                    <?php self::field_num( 'va_size_home_hero_btn',      'Főoldal hero gomb szövegméret (px)', 10, 28 ); ?>

                    <?php self::field_num( 'va_size_kat_hero_badge',     'Kategória hero badge méret (px)', 8, 32 ); ?>
                    <?php self::field_num( 'va_size_kat_hero_title',     'Kategória hero cím méret (px)', 20, 110 ); ?>
                    <?php self::field_num( 'va_size_kat_hero_sub',       'Kategória hero alcím méret (px)', 10, 40 ); ?>
                    <?php self::field_num( 'va_size_kat_hero_stat_num',  'Kategória hero stat szám méret (px)', 10, 44 ); ?>
                    <?php self::field_num( 'va_size_kat_hero_stat_label','Kategória hero stat felirat méret (px)', 8, 24 ); ?>

                    <?php self::field_num( 'va_size_tax_hero_badge',     'Alkategória hero badge méret (px)', 8, 32 ); ?>
                    <?php self::field_num( 'va_size_tax_hero_title',     'Alkategória hero cím méret (px)', 18, 100 ); ?>
                    <?php self::field_num( 'va_size_tax_hero_lead',      'Alkategória hero leírás méret (px)', 10, 40 ); ?>
                    <?php self::field_num( 'va_size_tax_hero_count',     'Alkategória hero találatszám méret (px)', 10, 34 ); ?>

                    <?php self::field_num( 'va_size_contact_hero_badge', 'Kapcsolat hero badge méret (px)', 8, 32 ); ?>
                    <?php self::field_num( 'va_size_contact_hero_title', 'Kapcsolat hero cím méret (px)', 20, 120 ); ?>
                    <?php self::field_num( 'va_size_contact_hero_lead',  'Kapcsolat hero alcím méret (px)', 10, 40 ); ?>
                </table>

                <h2>Hero sorköz magasságok</h2>
                <table class="form-table">
                    <?php self::field_decimal( 'va_lh_home_hero_title',    'Főoldal hero cím sorköz', 0.8, 2.4, 0.01 ); ?>
                    <?php self::field_decimal( 'va_lh_home_hero_sub',      'Főoldal hero alcím sorköz', 0.8, 2.8, 0.01 ); ?>
                    <?php self::field_decimal( 'va_lh_kat_hero_title',     'Kategória hero cím sorköz', 0.8, 2.4, 0.01 ); ?>
                    <?php self::field_decimal( 'va_lh_kat_hero_sub',       'Kategória hero alcím sorköz', 0.8, 2.8, 0.01 ); ?>
                    <?php self::field_decimal( 'va_lh_tax_hero_title',     'Alkategória hero cím sorköz', 0.8, 2.4, 0.01 ); ?>
                    <?php self::field_decimal( 'va_lh_tax_hero_lead',      'Alkategória hero leírás sorköz', 0.8, 2.8, 0.01 ); ?>
                    <?php self::field_decimal( 'va_lh_contact_hero_title', 'Kapcsolat hero cím sorköz', 0.8, 2.4, 0.01 ); ?>
                    <?php self::field_decimal( 'va_lh_contact_hero_lead',  'Kapcsolat hero alcím sorköz', 0.8, 2.8, 0.01 ); ?>
                </table>

                <h2>Fejléc elemek méretei és típusai</h2>
                <table class="form-table">
                    <?php self::field_num(    'va_size_header_brand',   'Brand név méret (px)', 10, 44 ); ?>
                    <?php self::field_num(    'va_size_header_nav',     'Navigáció méret (px)', 10, 34 ); ?>
                    <?php self::field_num(    'va_size_header_search',  'Keresőmező szövegméret (px)', 10, 30 ); ?>
                    <?php self::field_num(    'va_size_header_btn',     'Fejléc gomb szövegméret (px)', 10, 30 ); ?>
                    <?php self::field_select( 'va_weight_header_brand', 'Brand név súly/típus', $weights ); ?>
                    <?php self::field_select( 'va_weight_header_nav',   'Navigáció súly/típus', $weights ); ?>
                </table>

                <h2>Lábléc elemek méretei és típusai</h2>
                <table class="form-table">
                    <?php self::field_num(    'va_size_footer_title',   'Lábléc oszlopcím méret (px)', 10, 34 ); ?>
                    <?php self::field_num(    'va_size_footer_link',    'Lábléc link méret (px)', 10, 30 ); ?>
                    <?php self::field_num(    'va_size_footer_bottom',  'Lábléc alsó sor méret (px)', 10, 28 ); ?>
                    <?php self::field_select( 'va_weight_footer_title', 'Lábléc címsor súly/típus', $weights ); ?>
                    <?php self::field_select( 'va_weight_footer_link',  'Lábléc link súly/típus', $weights ); ?>
                </table>

                <h2>Mobil skála finomhangolás</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_mobile_factor_hero',   'Hero mobil szorzó (%)', 70, 120 ); ?>
                    <?php self::field_num( 'va_mobile_factor_header', 'Fejléc mobil szorzó (%)', 70, 120 ); ?>
                    <?php self::field_num( 'va_mobile_factor_footer', 'Lábléc mobil szorzó (%)', 70, 120 ); ?>
                </table>

                <?php submit_button( 'Design mentése' ); ?>
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

        $auctions_enabled = function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : true;

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
                    $auctions  = $auctions_enabled ? count_user_posts( $user->ID, 'va_auction' ) : 0;
                ?>
                    <tr>
                        <td><?php echo esc_html( $user->user_login ); ?></td>
                        <td><?php echo esc_html( $user->display_name ); ?></td>
                        <td><?php echo esc_html( $user->user_email ); ?></td>
                        <td><?php echo esc_html( $phone ?: '–' ); ?></td>
                        <td><?php echo esc_html( date_i18n( 'Y.m.d', strtotime( $user->user_registered ) ) ); ?></td>
                        <td>
                            <?php if ( $auctions_enabled ): ?>
                                <?php echo esc_html( $listings ); ?> hird. / <?php echo esc_html( $auctions ); ?> aukció
                            <?php else: ?>
                                <?php echo esc_html( $listings ); ?> hirdetés
                            <?php endif; ?>
                        </td>
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

        $auctions_enabled = function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : true;

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
                <?php if ( $auctions_enabled ): ?>
                <div class="va-stat-card">
                    <span class="va-stat-num"><?php echo esc_html( $total_auctions->publish ); ?></span>
                    <span class="va-stat-label">Aktív aukció</span>
                </div>
                <?php endif; ?>
                <div class="va-stat-card">
                    <span class="va-stat-num"><?php echo esc_html( $total_users['total_users'] ); ?></span>
                    <span class="va-stat-label">Regisztrált felhasználó</span>
                </div>
                <?php if ( $auctions_enabled ): ?>
                <div class="va-stat-card">
                    <span class="va-stat-num"><?php echo esc_html( $total_bids ?: 0 ); ?></span>
                    <span class="va-stat-label">Összes licit</span>
                </div>
                <?php endif; ?>
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

    private static function field_url( string $key, string $label ): void {
        $val = esc_attr( get_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"url\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text code";
        echo " placeholder=\"https://.../video.mp4\"></td></tr>";
    }

    private static function field_media( string $key, string $label ): void {
        $val = esc_attr( get_option( $key, '' ) );
        $preview = $val !== '' ? '<img src="' . $val . '" alt="" class="va-media-preview">' : '';
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td>";
        echo "<div class=\"va-media-field\">";
        echo "<input type=\"url\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text code va-media-input\" placeholder=\"https://.../logo.png\">";
        echo "<button type=\"button\" class=\"button va-media-pick\" data-target=\"{$key}\">Tallózás</button>";
        echo "<button type=\"button\" class=\"button va-media-clear\" data-target=\"{$key}\">Törlés</button>";
        echo "</div>";
        echo "<div class=\"va-media-preview-wrap\" id=\"{$key}_preview\">{$preview}</div>";
        echo "</td></tr>";
    }

    private static function field_num( string $key, string $label, int $min = 0, int $max = 9999 ): void {
        $val = esc_attr( get_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"number\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" min=\"{$min}\" max=\"{$max}\" class=\"small-text\"></td></tr>";
    }

    private static function field_decimal( string $key, string $label, float $min = 0.1, float $max = 5, float $step = 0.01 ): void {
        $val = esc_attr( get_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"number\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" min=\"{$min}\" max=\"{$max}\" step=\"{$step}\" class=\"small-text"></td></tr>";
    }

    private static function field_select( string $key, string $label, array $options ): void {
        $current = (string) get_option( $key, '' );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><select id=\"{$key}\" name=\"{$key}\">";
        foreach ( $options as $value => $text ) {
            echo '<option value="' . esc_attr( $value ) . '" ' . selected( $current, (string) $value, false ) . '>' . esc_html( (string) $text ) . '</option>';
        }
        echo '</select></td></tr>';
    }

    private static function field_color( string $key, string $label ): void {
        $val = esc_attr( get_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"text\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text va-color-input\" data-default-color=\"{$val}\"></td></tr>";
    }

    private static function field_toggle( string $key, string $label ): void {
        $val = get_option( $key, '0' );
        echo "<tr><th>{$label}</th><td><input type=\"hidden\" name=\"{$key}\" value=\"0\"><label class=\"va-toggle\"><input type=\"checkbox\" name=\"{$key}\" value=\"1\"" . checked( $val, '1', false ) . "><span class=\"va-toggle-slider\"></span></label></td></tr>";
    }
}
