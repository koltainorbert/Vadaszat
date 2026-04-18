<?php
/**
 * Settings oldal – minden beállítás egy helyen
 * Lapok: Általános | Reklámzónák | Hirdetések | Aukciók | Felhasználók | Statisztika
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Settings_Page {

    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_post_va_export_settings', [ __CLASS__, 'handle_export_settings' ] );
        add_action( 'admin_post_va_import_settings', [ __CLASS__, 'handle_import_settings' ] );
        add_action( 'admin_post_va_reset_settings',  [ __CLASS__, 'handle_reset_settings' ] );
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

            // Fejléc layout/kinézet (külön menühöz)
            'va_hf_header_height'                  => 66,
            'va_hf_header_max_width'               => 1480,
            'va_hf_header_padding_x'               => 32,
            'va_hf_header_padding_top'             => 6,
            'va_hf_header_padding_bottom'          => 10,
            'va_hf_header_gap'                     => 0,
            'va_hf_header_bg_opacity'              => '0.82',
            'va_hf_header_bg_opacity_scrolled'     => '0.88',
            'va_hf_header_blur'                    => 16,
            'va_hf_header_blur_scrolled'           => 20,
            'va_hf_header_shadow_alpha'            => '0.70',
            'va_hf_header_color_base'              => '#050505',
            'va_hf_header_color_alt'               => '#140909',
            'va_hf_header_border_color'            => '#ff2a2a',
            'va_hf_header_shadow_color'            => 'rgba(0,0,0,.72)',
            'va_hf_header_glow_color'              => 'rgba(255,0,0,.24)',

            // Fejléc kereső
            'va_hf_header_search_max_width'        => 460,
            'va_hf_header_search_height'           => 42,
            'va_hf_header_search_radius'           => 30,
            'va_hf_header_search_border_alpha'     => '0.14',
            'va_hf_header_search_bg_alpha'         => '0.02',
            'va_hf_header_search_hover_border_alpha' => '0.38',
            'va_hf_header_search_focus_border_alpha' => '0.52',
            'va_hf_header_search_icon_size'        => 16,
            'va_hf_header_search_icon_bg_alpha'    => '0.14',
            'va_hf_header_search_icon_bg_hover_alpha' => '0.22',
            'va_hf_header_search_glow_color'       => 'rgba(255,0,0,.18)',
            'va_hf_header_search_placeholder'      => 'keresés…',

            // Fejléc gombok
            'va_hf_header_btn_radius'              => 999,
            'va_hf_header_btn_pad_y'               => 8,
            'va_hf_header_btn_pad_x'               => 20,
            'va_hf_header_btn_glow_alpha'          => '0.40',
            'va_hf_header_btn_glow_color'          => 'rgba(255,0,0,.52)',
            'va_hf_header_user_border_alpha'       => '0.12',
            'va_hf_header_user_bg_alpha'           => '0.06',
            'va_hf_header_mobile_show_search'      => '0',
            'va_hf_header_mobile_show_submit'      => '0',
            'va_hf_header_submit_text'             => '+ Hirdetés feladása',
            'va_hf_header_register_text'           => 'Regisztráció',
            'va_hf_header_login_text'              => 'Bejelentkezés',

            // Lábléc layout/kinézet
            'va_hf_footer_top_padding'             => 48,
            'va_hf_footer_bottom_padding'          => 24,
            'va_hf_footer_grid_gap'                => 32,
            'va_hf_footer_col_min_width'           => 160,
            'va_hf_footer_title_gap'               => 12,
            'va_hf_footer_link_pad_y'              => 4,
            'va_hf_footer_bottom_top_padding'      => 20,
            'va_hf_footer_border_alpha'            => '0.07',
            'va_hf_footer_bottom_border_alpha'     => '0.07',
            'va_hf_footer_max_width'               => 1400,
            'va_hf_footer_color_base'              => '#0a0a0a',
            'va_hf_footer_color_alt'               => '#150707',
            'va_hf_footer_border_color'            => '#ff2a2a',
            'va_hf_footer_shadow_color'            => 'rgba(0,0,0,.36)',
            'va_hf_footer_glow_color'              => 'rgba(255,0,0,.14)',
            'va_hf_footer_link_hover_color'        => '#ffffff',

            // Lábléc szövegek
            'va_hf_footer_brand_title'             => 'VadászApró',
            'va_hf_footer_col_categories_title'    => 'Kategóriák',
            'va_hf_footer_col_account_title'       => 'Fiók',
            'va_hf_footer_col_legal_title'         => 'Jogi információk',
            'va_hf_footer_link_aszf'               => 'ÁSZF',
            'va_hf_footer_link_privacy'            => 'Adatvédelmi nyilatkozat',
            'va_hf_footer_link_contact'            => 'Kapcsolat',
            'va_hf_footer_link_help'               => 'Súgó',
            'va_hf_footer_copy_text'               => 'VadászApró – Minden jog fenntartva.',
            'va_hf_footer_privacy_text'            => 'Adatvédelem',
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

    /* ══ Fejléc + Lábléc oldal (maximális paraméterezés) ════════ */
    public static function render_header_footer() {
        if ( ! current_user_can( 'manage_options' ) ) return;

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
            <h1>🧩 VadászApró – Fejléc + Lábléc (100% kontroll)</h1>
            <p class="description">Külön menüből finomhangolhatod a fejléc és a lábléc layoutját, szövegeit, méreteit, mobil viselkedését és vizuális részleteit.</p>
            <?php settings_errors( 'va_design_settings' ); ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'va_design_settings' ); ?>

                <h2>Fejléc: alap layout és üveg-hatás</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_hf_header_height',              'Fejléc magasság (px)', 50, 120 ); ?>
                    <?php self::field_num( 'va_hf_header_max_width',           'Fejléc belső max szélesség (px)', 960, 2200 ); ?>
                    <?php self::field_num( 'va_hf_header_padding_x',           'Fejléc belső vízszintes padding (px)', 0, 80 ); ?>
                    <?php self::field_num( 'va_hf_header_padding_top',         'Fejléc belső felső padding (px)', 0, 30 ); ?>
                    <?php self::field_num( 'va_hf_header_padding_bottom',      'Fejléc belső alsó padding (px)', 0, 30 ); ?>
                    <?php self::field_num( 'va_hf_header_gap',                 'Fejléc elemek közti gap (px)', 0, 40 ); ?>
                    <?php self::field_decimal( 'va_hf_header_bg_opacity',      'Fejléc háttér opacitás (0-1)', 0, 1, 0.01 ); ?>
                    <?php self::field_decimal( 'va_hf_header_bg_opacity_scrolled', 'Fejléc háttér opacitás scroll után (0-1)', 0, 1, 0.01 ); ?>
                    <?php self::field_num( 'va_hf_header_blur',                'Fejléc blur (px)', 0, 40 ); ?>
                    <?php self::field_num( 'va_hf_header_blur_scrolled',       'Fejléc blur scroll után (px)', 0, 44 ); ?>
                    <?php self::field_decimal( 'va_hf_header_shadow_alpha',    'Fejléc árnyék opacitás (0-1)', 0, 1, 0.01 ); ?>
                </table>

                <h2>Fejléc: modern színpaletta és árnyékok</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_hf_header_color_base',    'Fejléc alapszín (gradient 1)' ); ?>
                    <?php self::field_color( 'va_hf_header_color_alt',     'Fejléc másodlagos szín (gradient 2)' ); ?>
                    <?php self::field_color( 'va_hf_header_border_color',  'Fejléc alsó border szín' ); ?>
                    <?php self::field_text(  'va_hf_header_shadow_color',  'Fejléc fő árnyék szín (hex/rgba)' ); ?>
                    <?php self::field_text(  'va_hf_header_glow_color',    'Fejléc neon glow szín (hex/rgba)' ); ?>
                    <?php self::field_text(  'va_hf_header_search_glow_color', 'Kereső glow szín (hex/rgba)' ); ?>
                    <?php self::field_text(  'va_hf_header_btn_glow_color',    'CTA gomb glow szín (hex/rgba)' ); ?>
                </table>

                <h2>Fejléc: kereső részletes vezérlés</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_hf_header_search_max_width',        'Kereső max szélesség (px)', 220, 760 ); ?>
                    <?php self::field_num( 'va_hf_header_search_height',           'Kereső magasság (px)', 30, 64 ); ?>
                    <?php self::field_num( 'va_hf_header_search_radius',           'Kereső lekerekítés (px)', 8, 999 ); ?>
                    <?php self::field_decimal( 'va_hf_header_search_border_alpha', 'Kereső keret opacitás (0-1)', 0, 1, 0.01 ); ?>
                    <?php self::field_decimal( 'va_hf_header_search_bg_alpha',     'Kereső háttér opacitás (0-1)', 0, 1, 0.01 ); ?>
                    <?php self::field_decimal( 'va_hf_header_search_hover_border_alpha', 'Kereső keret opacitás hover (0-1)', 0, 1, 0.01 ); ?>
                    <?php self::field_decimal( 'va_hf_header_search_focus_border_alpha', 'Kereső keret opacitás fókusz (0-1)', 0, 1, 0.01 ); ?>
                    <?php self::field_num( 'va_hf_header_search_icon_size',        'Nagyító ikon méret (px)', 10, 28 ); ?>
                    <?php self::field_decimal( 'va_hf_header_search_icon_bg_alpha', 'Nagyító gomb háttér opacitás (0-1)', 0, 1, 0.01 ); ?>
                    <?php self::field_decimal( 'va_hf_header_search_icon_bg_hover_alpha', 'Nagyító gomb háttér opacitás hover (0-1)', 0, 1, 0.01 ); ?>
                    <?php self::field_text( 'va_hf_header_search_placeholder',      'Kereső placeholder szöveg' ); ?>
                </table>

                <h2>Fejléc: gombok, feliratok és mobil viselkedés</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_hf_header_btn_radius',          'Header gombok lekerekítés (px)', 8, 999 ); ?>
                    <?php self::field_num( 'va_hf_header_btn_pad_y',           'Header gombok függőleges padding (px)', 4, 20 ); ?>
                    <?php self::field_num( 'va_hf_header_btn_pad_x',           'Header gombok vízszintes padding (px)', 8, 40 ); ?>
                    <?php self::field_decimal( 'va_hf_header_btn_glow_alpha',  'Piros gomb glow opacitás (0-1)', 0, 1, 0.01 ); ?>
                    <?php self::field_decimal( 'va_hf_header_user_border_alpha', 'Felhasználó gomb keret opacitás (0-1)', 0, 1, 0.01 ); ?>
                    <?php self::field_decimal( 'va_hf_header_user_bg_alpha',   'Felhasználó gomb háttér opacitás (0-1)', 0, 1, 0.01 ); ?>
                    <?php self::field_toggle( 'va_hf_header_mobile_show_search', 'Kereső megjelenjen mobilon is' ); ?>
                    <?php self::field_toggle( 'va_hf_header_mobile_show_submit', 'Piros CTA gomb megjelenjen mobilon is' ); ?>
                    <?php self::field_text( 'va_hf_header_submit_text',        'CTA gomb szöveg (bejelentkezve)' ); ?>
                    <?php self::field_text( 'va_hf_header_register_text',      'CTA gomb szöveg (vendég)' ); ?>
                    <?php self::field_text( 'va_hf_header_login_text',         'Belépés gomb szöveg (vendég)' ); ?>
                    <?php self::field_select( 'va_weight_header_brand', 'Brand név súly/típus', $weights ); ?>
                    <?php self::field_select( 'va_weight_header_nav',   'Navigáció súly/típus', $weights ); ?>
                    <?php self::field_num( 'va_size_header_brand',      'Brand név méret (px)', 10, 44 ); ?>
                    <?php self::field_num( 'va_size_header_nav',        'Navigáció méret (px)', 10, 34 ); ?>
                    <?php self::field_num( 'va_size_header_search',     'Kereső szövegméret (px)', 10, 30 ); ?>
                    <?php self::field_num( 'va_size_header_btn',        'Fejléc gomb szövegméret (px)', 10, 30 ); ?>
                </table>

                <h2>Lábléc: layout, spacing, tipográfia</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_hf_footer_top_padding',        'Lábléc felső padding (px)', 12, 120 ); ?>
                    <?php self::field_num( 'va_hf_footer_bottom_padding',     'Lábléc alsó padding (px)', 8, 80 ); ?>
                    <?php self::field_num( 'va_hf_footer_grid_gap',           'Lábléc oszlop gap (px)', 8, 80 ); ?>
                    <?php self::field_num( 'va_hf_footer_col_min_width',      'Lábléc oszlop minimum szélesség (px)', 120, 420 ); ?>
                    <?php self::field_num( 'va_hf_footer_title_gap',          'Lábléc oszlopcím alsó margó (px)', 4, 36 ); ?>
                    <?php self::field_num( 'va_hf_footer_link_pad_y',         'Lábléc link függőleges térköz (px)', 0, 20 ); ?>
                    <?php self::field_num( 'va_hf_footer_bottom_top_padding', 'Lábléc alsó sor felső padding (px)', 6, 40 ); ?>
                    <?php self::field_decimal( 'va_hf_footer_border_alpha',   'Lábléc felső keret opacitás (0-1)', 0, 1, 0.01 ); ?>
                    <?php self::field_decimal( 'va_hf_footer_bottom_border_alpha', 'Lábléc alsó sor keret opacitás (0-1)', 0, 1, 0.01 ); ?>
                    <?php self::field_num( 'va_hf_footer_max_width',          'Lábléc tartalom max szélesség (px)', 800, 2200 ); ?>
                    <?php self::field_select( 'va_weight_footer_title', 'Lábléc címsor súly/típus', $weights ); ?>
                    <?php self::field_select( 'va_weight_footer_link',  'Lábléc link súly/típus', $weights ); ?>
                    <?php self::field_num( 'va_size_footer_title',      'Lábléc oszlopcím méret (px)', 10, 34 ); ?>
                    <?php self::field_num( 'va_size_footer_link',       'Lábléc link méret (px)', 10, 30 ); ?>
                    <?php self::field_num( 'va_size_footer_bottom',     'Lábléc alsó sor méret (px)', 10, 28 ); ?>
                </table>

                <h2>Lábléc: modern színpaletta és árnyékok</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_hf_footer_color_base',       'Lábléc alapszín (gradient 1)' ); ?>
                    <?php self::field_color( 'va_hf_footer_color_alt',        'Lábléc másodlagos szín (gradient 2)' ); ?>
                    <?php self::field_color( 'va_hf_footer_border_color',     'Lábléc border szín' ); ?>
                    <?php self::field_text(  'va_hf_footer_shadow_color',     'Lábléc árnyék szín (hex/rgba)' ); ?>
                    <?php self::field_text(  'va_hf_footer_glow_color',       'Lábléc glow szín (hex/rgba)' ); ?>
                    <?php self::field_color( 'va_hf_footer_link_hover_color', 'Lábléc link hover szín' ); ?>
                </table>

                <h2>Lábléc: összes felirat és link címke</h2>
                <table class="form-table">
                    <?php self::field_text( 'va_hf_footer_brand_title',          'Brand oszlop cím' ); ?>
                    <?php self::field_text( 'va_hf_footer_col_categories_title', 'Kategóriák oszlop cím' ); ?>
                    <?php self::field_text( 'va_hf_footer_col_account_title',    'Fiók oszlop cím' ); ?>
                    <?php self::field_text( 'va_hf_footer_col_legal_title',      'Jogi oszlop cím' ); ?>
                    <?php self::field_text( 'va_hf_footer_link_aszf',            'ÁSZF link felirat' ); ?>
                    <?php self::field_text( 'va_hf_footer_link_privacy',         'Adatvédelem link felirat (jogi oszlop)' ); ?>
                    <?php self::field_text( 'va_hf_footer_link_contact',         'Kapcsolat link felirat' ); ?>
                    <?php self::field_text( 'va_hf_footer_link_help',            'Súgó link felirat' ); ?>
                    <?php self::field_text( 'va_hf_footer_copy_text',            'Copyright szöveg (év után)' ); ?>
                    <?php self::field_text( 'va_hf_footer_privacy_text',         'Alsó sor adatvédelem link felirat' ); ?>
                </table>

                <?php submit_button( 'Fejléc + Lábléc mentése' ); ?>
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

    /* ══ Export / Import / Reset ═════════════════════════ */
    public static function render_tools() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $msg  = isset( $_GET['va_tools_msg'] ) ? sanitize_key( (string) $_GET['va_tools_msg'] ) : '';
        $note = '';
        $cls  = 'notice notice-info';

        if ( $msg === 'import_ok' ) {
            $cnt  = absint( $_GET['count'] ?? 0 );
            $tax  = absint( $_GET['tax'] ?? 0 );
            $pages= absint( $_GET['pages'] ?? 0 );
            $note = 'Import kész. Frissített opciók: ' . $cnt . ', taxonómiák: ' . $tax . ', oldalak: ' . $pages;
            $cls  = 'notice notice-success';
        } elseif ( $msg === 'reset_ok' ) {
            $cnt  = absint( $_GET['count'] ?? 0 );
            $note = 'Alaphelyzet visszaállítva. Újra felvett alap opciók: ' . $cnt;
            $cls  = 'notice notice-success';
        } elseif ( $msg === 'import_invalid' ) {
            $note = 'Hibás import fájl. Kérlek ellenőrizd a JSON tartalmát.';
            $cls  = 'notice notice-error';
        } elseif ( $msg === 'import_empty' ) {
            $note = 'Nem érkezett import fájl.';
            $cls  = 'notice notice-error';
        }
        ?>
        <div class="wrap va-admin-wrap">
            <h1>📦 VadászApró – Export / Import / Reset</h1>
            <p class="description">A teljes <code>va_*</code> beállításkészlet exportálható és importálható (általános, design, fejléc/lábléc, reklámzónák, hirdetés/aukció opciók).</p>

            <?php if ( $note !== '' ): ?>
                <div class="<?php echo esc_attr( $cls ); ?>"><p><?php echo esc_html( $note ); ?></p></div>
            <?php endif; ?>

            <div class="card" style="max-width:960px;padding:18px 22px;margin-top:16px;">
                <h2>1) Export összes beállítás</h2>
                <p>JSON fájl letöltése, amit másik oldalon vissza tudsz importálni. Opcionálisan teljes migrációs adatokkal (taxonómia + fix oldalak).</p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="va_export_settings">
                    <?php wp_nonce_field( 'va_export_settings' ); ?>
                    <p>
                        <label><input type="checkbox" name="va_export_taxonomies" value="1" checked> Taxonómiák exportálása is (kategória, megye, állapot)</label><br>
                        <label><input type="checkbox" name="va_export_pages" value="1" checked> Fix oldalak exportálása is (slug + tartalom)</label>
                    </p>
                    <?php submit_button( 'Összes beállítás exportálása', 'primary', 'submit', false ); ?>
                </form>
            </div>

            <div class="card" style="max-width:960px;padding:18px 22px;margin-top:16px;">
                <h2>2) Import beállítás fájlból</h2>
                <p>Csak ezen plugin exportjából származó JSON fájlt tölts fel. Beállíthatod, hogy a taxonómiákat és oldalakat is importálja.</p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="va_import_settings">
                    <?php wp_nonce_field( 'va_import_settings' ); ?>
                    <input type="file" name="va_import_file" accept="application/json,.json" required>
                    <p style="margin-top:10px;">
                        <label><input type="checkbox" name="va_import_taxonomies" value="1" checked> Taxonómiák importálása is</label><br>
                        <label><input type="checkbox" name="va_import_pages" value="1" checked> Fix oldalak importálása is</label>
                    </p>
                    <p style="margin-top:12px;">
                        <?php submit_button( 'Import indítása', 'secondary', 'submit', false ); ?>
                    </p>
                </form>
            </div>

            <div class="card" style="max-width:960px;padding:18px 22px;margin-top:16px;border-left:4px solid #d63638;">
                <h2>3) Visszaállítás alaphelyzetbe</h2>
                <p>Ez törli a jelenlegi <code>va_*</code> beállításokat, majd visszaállítja az alapértékeket.</p>
                <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('Biztosan alaphelyzetbe állítod az összes beállítást?');">
                    <input type="hidden" name="action" value="va_reset_settings">
                    <?php wp_nonce_field( 'va_reset_settings' ); ?>
                    <input type="hidden" name="va_reset_confirm" value="1">
                    <?php submit_button( 'Összes beállítás alaphelyzetbe', 'delete', 'submit', false ); ?>
                </form>
            </div>
        </div>
        <?php
    }

    public static function handle_export_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nincs jogosultság.' );
        }
        check_admin_referer( 'va_export_settings' );

        $payload = [
            'meta' => [
                'schema'      => 'vadaszapro-settings-v1',
                'exported_at' => current_time( 'mysql' ),
                'site_url'    => home_url( '/' ),
                'va_version'  => defined( 'VA_VERSION' ) ? VA_VERSION : 'unknown',
            ],
            'options' => self::get_all_va_options(),
        ];

        if ( (string) ( $_POST['va_export_taxonomies'] ?? '' ) === '1' ) {
            $payload['taxonomies'] = self::get_export_taxonomies();
        }
        if ( (string) ( $_POST['va_export_pages'] ?? '' ) === '1' ) {
            $payload['pages'] = self::get_export_pages();
        }

        $filename = 'vadaszapro-settings-' . gmdate( 'Ymd-His' ) . '.json';
        nocache_headers();
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=' . $filename );
        echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
        exit;
    }

    public static function handle_import_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nincs jogosultság.' );
        }
        check_admin_referer( 'va_import_settings' );

        $redirect = admin_url( 'admin.php?page=vadaszapro-tools' );

        if ( empty( $_FILES['va_import_file']['tmp_name'] ) ) {
            wp_safe_redirect( add_query_arg( 'va_tools_msg', 'import_empty', $redirect ) );
            exit;
        }

        $tmp_name = (string) $_FILES['va_import_file']['tmp_name'];
        $content  = file_get_contents( $tmp_name );
        if ( $content === false || trim( $content ) === '' ) {
            wp_safe_redirect( add_query_arg( 'va_tools_msg', 'import_invalid', $redirect ) );
            exit;
        }

        $data = json_decode( $content, true );
        if ( ! is_array( $data ) || ! isset( $data['options'] ) || ! is_array( $data['options'] ) ) {
            wp_safe_redirect( add_query_arg( 'va_tools_msg', 'import_invalid', $redirect ) );
            exit;
        }

        $count = 0;
        $tax_count = 0;
        $page_count = 0;
        foreach ( $data['options'] as $key => $value ) {
            if ( ! is_string( $key ) || strpos( $key, 'va_' ) !== 0 ) {
                continue;
            }
            update_option( $key, $value );
            $count++;
        }

        if ( (string) ( $_POST['va_import_taxonomies'] ?? '' ) === '1' && isset( $data['taxonomies'] ) && is_array( $data['taxonomies'] ) ) {
            $tax_count = self::import_taxonomies( $data['taxonomies'] );
        }

        if ( (string) ( $_POST['va_import_pages'] ?? '' ) === '1' && isset( $data['pages'] ) && is_array( $data['pages'] ) ) {
            $page_count = self::import_pages( $data['pages'] );
        }

        wp_safe_redirect( add_query_arg( [ 'va_tools_msg' => 'import_ok', 'count' => $count, 'tax' => $tax_count, 'pages' => $page_count ], $redirect ) );
        exit;
    }

    public static function handle_reset_settings() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nincs jogosultság.' );
        }
        check_admin_referer( 'va_reset_settings' );

        if ( (string) ( $_POST['va_reset_confirm'] ?? '' ) !== '1' ) {
            wp_die( 'Hiányzó megerősítés.' );
        }

        $keep_keys = [
            'va_pages_created_v4',
        ];

        $all = self::get_all_va_options();
        foreach ( array_keys( $all ) as $key ) {
            if ( in_array( $key, $keep_keys, true ) ) {
                continue;
            }
            delete_option( $key );
        }

        // Alap opciók visszaépítése.
        self::register_settings();

        $all_after = self::get_all_va_options();
        wp_safe_redirect( add_query_arg( [ 'va_tools_msg' => 'reset_ok', 'count' => count( $all_after ) ], admin_url( 'admin.php?page=vadaszapro-tools' ) ) );
        exit;
    }

    private static function get_all_va_options(): array {
        global $wpdb;

        $like = $wpdb->esc_like( 'va_' ) . '%';
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
                $like
            ),
            ARRAY_A
        );

        $options = [];
        foreach ( (array) $rows as $row ) {
            $name = (string) ( $row['option_name'] ?? '' );
            if ( $name === '' ) {
                continue;
            }
            $options[ $name ] = maybe_unserialize( $row['option_value'] ?? '' );
        }

        ksort( $options );
        return $options;
    }

    private static function get_export_taxonomies(): array {
        $out = [];
        $taxonomies = [ 'va_category', 'va_county', 'va_condition' ];
        foreach ( $taxonomies as $taxonomy ) {
            $terms = get_terms([
                'taxonomy'   => $taxonomy,
                'hide_empty' => false,
            ]);
            if ( is_wp_error( $terms ) ) {
                continue;
            }

            $out[ $taxonomy ] = [];
            foreach ( $terms as $term ) {
                $parent_slug = '';
                if ( $term->parent ) {
                    $parent = get_term( (int) $term->parent, $taxonomy );
                    if ( $parent && ! is_wp_error( $parent ) ) {
                        $parent_slug = (string) $parent->slug;
                    }
                }

                $out[ $taxonomy ][] = [
                    'slug'        => (string) $term->slug,
                    'name'        => (string) $term->name,
                    'description' => (string) $term->description,
                    'parent_slug' => $parent_slug,
                ];
            }
        }

        return $out;
    }

    private static function get_export_pages(): array {
        $slugs = [
            'kategoria',
            'kapcsolat',
            'va-hirdetes-kereses',
            'va-hirdetes-feladas',
            'va-bejelentkezes',
            'va-regisztracio',
            'va-fiok',
            'va-aukciok',
            'aszf',
            'adatvedelmi-nyilatkozat',
            'sugo',
        ];

        $pages = [];
        foreach ( $slugs as $slug ) {
            $page = get_page_by_path( $slug );
            if ( ! $page ) {
                continue;
            }
            $pages[] = [
                'slug'    => (string) $page->post_name,
                'title'   => (string) $page->post_title,
                'status'  => (string) $page->post_status,
                'content' => (string) $page->post_content,
                'excerpt' => (string) $page->post_excerpt,
            ];
        }

        return $pages;
    }

    private static function import_taxonomies( array $tax_data ): int {
        $processed = 0;

        foreach ( $tax_data as $taxonomy => $terms ) {
            if ( ! taxonomy_exists( (string) $taxonomy ) || ! is_array( $terms ) ) {
                continue;
            }

            // 1. kör: létrehozás/frissítés parent nélkül.
            foreach ( $terms as $item ) {
                if ( ! is_array( $item ) ) {
                    continue;
                }
                $slug = sanitize_title( (string) ( $item['slug'] ?? '' ) );
                $name = sanitize_text_field( (string) ( $item['name'] ?? '' ) );
                $desc = sanitize_textarea_field( (string) ( $item['description'] ?? '' ) );
                if ( $slug === '' || $name === '' ) {
                    continue;
                }

                $existing = get_term_by( 'slug', $slug, (string) $taxonomy );
                if ( $existing && ! is_wp_error( $existing ) ) {
                    wp_update_term( (int) $existing->term_id, (string) $taxonomy, [
                        'name'        => $name,
                        'description' => $desc,
                        'slug'        => $slug,
                    ] );
                } else {
                    wp_insert_term( $name, (string) $taxonomy, [
                        'description' => $desc,
                        'slug'        => $slug,
                    ] );
                }
                $processed++;
            }

            // 2. kör: parent kapcsolatok.
            foreach ( $terms as $item ) {
                if ( ! is_array( $item ) ) {
                    continue;
                }
                $slug = sanitize_title( (string) ( $item['slug'] ?? '' ) );
                $parent_slug = sanitize_title( (string) ( $item['parent_slug'] ?? '' ) );
                if ( $slug === '' || $parent_slug === '' ) {
                    continue;
                }

                $term = get_term_by( 'slug', $slug, (string) $taxonomy );
                $parent = get_term_by( 'slug', $parent_slug, (string) $taxonomy );
                if ( ! $term || is_wp_error( $term ) || ! $parent || is_wp_error( $parent ) ) {
                    continue;
                }

                wp_update_term( (int) $term->term_id, (string) $taxonomy, [ 'parent' => (int) $parent->term_id ] );
            }
        }

        return $processed;
    }

    private static function import_pages( array $pages ): int {
        $processed = 0;
        foreach ( $pages as $row ) {
            if ( ! is_array( $row ) ) {
                continue;
            }

            $slug    = sanitize_title( (string) ( $row['slug'] ?? '' ) );
            $title   = sanitize_text_field( (string) ( $row['title'] ?? '' ) );
            $status  = sanitize_key( (string) ( $row['status'] ?? 'publish' ) );
            $content = wp_kses_post( (string) ( $row['content'] ?? '' ) );
            $excerpt = sanitize_textarea_field( (string) ( $row['excerpt'] ?? '' ) );

            if ( $slug === '' || $title === '' ) {
                continue;
            }
            if ( ! in_array( $status, [ 'publish', 'draft', 'private', 'pending' ], true ) ) {
                $status = 'publish';
            }

            $existing = get_page_by_path( $slug );
            $postarr = [
                'post_type'    => 'page',
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_status'  => $status,
                'post_content' => $content,
                'post_excerpt' => $excerpt,
            ];

            if ( $existing ) {
                $postarr['ID'] = (int) $existing->ID;
                wp_update_post( $postarr );
            } else {
                wp_insert_post( $postarr );
            }
            $processed++;
        }

        return $processed;
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
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"number\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" min=\"{$min}\" max=\"{$max}\" step=\"{$step}\" class=\"small-text\"></td></tr>";
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
