<?php
/**
 * Settings oldal – minden beállítás egy helyen
 * Lapok: Általános | Reklámzónák | Hirdetések | Aukciók | Felhasználók | Statisztika
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Settings_Page {

    private static $defaults = [];

    public static function init() {
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
        add_action( 'admin_post_va_export_settings', [ __CLASS__, 'handle_export_settings' ] );
        add_action( 'admin_post_va_import_settings', [ __CLASS__, 'handle_import_settings' ] );
        add_action( 'admin_post_va_reset_settings',  [ __CLASS__, 'handle_reset_settings' ] );
        add_action( 'admin_post_va_apply_hf_preset', [ __CLASS__, 'handle_apply_hf_preset' ] );
        add_action( 'admin_post_va_apply_ap_preset',  [ __CLASS__, 'handle_apply_ap_preset'  ] );
        add_action( 'admin_post_va_apply_single_preset', [ __CLASS__, 'handle_apply_single_preset' ] );
    }

    /* ══ Settings regisztráció ════════════════════════════ */
    public static function register_settings() {

        /* Általános */
        $general = [
            'va_site_name'           => 'VadászApró',
            'va_site_description'    => 'Magyarország vadászati apróhirdetési oldala',
            'va_contact_email'       => get_option('admin_email'),
            'va_enable_auctions'     => '1',
            'va_enable_login'        => '1',
            'va_enable_register'     => '1',
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
            'va_show_hunting_season_widget' => '1',
            'va_show_moon_widget'          => '1',
            'va_show_weather_widget'       => '1',
            'va_enable_hunting_calendar_page' => '1',
            'va_show_home_hunting_calendar' => '1',

            // Hirdetes kartya meta layout (lista/fooldal)
            'va_card_meta_rows'            => '1',
            'va_card_meta_col_gap'         => '12',
            'va_card_meta_row_gap'         => '2',
            'va_card_meta_stack_gap'       => '4',
            'va_card_meta_show_category'   => '0',
            'va_card_meta_show_county'     => '0',
            'va_card_meta_show_location'   => '1',
            'va_card_meta_show_views'      => '0',
            'va_card_meta_show_author'     => '0',
            'va_card_meta_show_date'       => '1',
            'va_card_meta_row_category'    => '1',
            'va_card_meta_row_county'      => '1',
            'va_card_meta_row_location'    => '1',
            'va_card_meta_row_views'       => '1',
            'va_card_meta_row_author'      => '1',
            'va_card_meta_row_date'        => '1',

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
            self::$defaults[ $key ] = $default;
            register_setting( 'va_general_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        // Migráció: ha a korábbi alap 2 soros hely+dátum konfiguráció él, állítsuk 1 sorra.
        $is_old_default_card_layout =
            (string) get_option( 'va_card_meta_rows', '2' ) === '2'
            && (string) get_option( 'va_card_meta_show_category', '0' ) === '0'
            && (string) get_option( 'va_card_meta_show_county', '0' ) === '0'
            && (string) get_option( 'va_card_meta_show_location', '1' ) === '1'
            && (string) get_option( 'va_card_meta_show_views', '0' ) === '0'
            && (string) get_option( 'va_card_meta_show_author', '0' ) === '0'
            && (string) get_option( 'va_card_meta_show_date', '1' ) === '1'
            && (string) get_option( 'va_card_meta_row_location', '1' ) === '1'
            && (string) get_option( 'va_card_meta_row_date', '2' ) === '2';

        if ( $is_old_default_card_layout ) {
            update_option( 'va_card_meta_rows', '1' );
            update_option( 'va_card_meta_row_date', '1' );
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
            'va_hf_footer_logo_url'                => '',
            'va_hf_footer_logo_height'             => 48,

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

        $header_footer_keys = [
            'va_color_header_bg',
            'va_color_header_text',
            'va_color_header_accent',
            'va_color_footer_bg',
            'va_color_footer_text',
            'va_color_footer_headings',
            'va_color_footer_links',
            'va_size_header_brand',
            'va_size_header_nav',
            'va_size_header_search',
            'va_size_header_btn',
            'va_weight_header_brand',
            'va_weight_header_nav',
            'va_size_footer_title',
            'va_size_footer_link',
            'va_size_footer_bottom',
            'va_weight_footer_title',
            'va_weight_footer_link',
            'va_mobile_factor_header',
            'va_mobile_factor_footer',
            'va_hf_header_height',
            'va_hf_header_max_width',
            'va_hf_header_padding_x',
            'va_hf_header_padding_top',
            'va_hf_header_padding_bottom',
            'va_hf_header_gap',
            'va_hf_header_bg_opacity',
            'va_hf_header_bg_opacity_scrolled',
            'va_hf_header_blur',
            'va_hf_header_blur_scrolled',
            'va_hf_header_shadow_alpha',
            'va_hf_header_color_base',
            'va_hf_header_color_alt',
            'va_hf_header_border_color',
            'va_hf_header_shadow_color',
            'va_hf_header_glow_color',
            'va_hf_header_search_max_width',
            'va_hf_header_search_height',
            'va_hf_header_search_radius',
            'va_hf_header_search_border_alpha',
            'va_hf_header_search_bg_alpha',
            'va_hf_header_search_hover_border_alpha',
            'va_hf_header_search_focus_border_alpha',
            'va_hf_header_search_icon_size',
            'va_hf_header_search_icon_bg_alpha',
            'va_hf_header_search_icon_bg_hover_alpha',
            'va_hf_header_search_glow_color',
            'va_hf_header_search_placeholder',
            'va_hf_header_btn_radius',
            'va_hf_header_btn_pad_y',
            'va_hf_header_btn_pad_x',
            'va_hf_header_btn_glow_alpha',
            'va_hf_header_btn_glow_color',
            'va_hf_header_user_border_alpha',
            'va_hf_header_user_bg_alpha',
            'va_hf_header_mobile_show_search',
            'va_hf_header_mobile_show_submit',
            'va_hf_header_submit_text',
            'va_hf_header_register_text',
            'va_hf_header_login_text',
            'va_hf_footer_top_padding',
            'va_hf_footer_bottom_padding',
            'va_hf_footer_grid_gap',
            'va_hf_footer_col_min_width',
            'va_hf_footer_title_gap',
            'va_hf_footer_link_pad_y',
            'va_hf_footer_bottom_top_padding',
            'va_hf_footer_border_alpha',
            'va_hf_footer_bottom_border_alpha',
            'va_hf_footer_max_width',
            'va_hf_footer_color_base',
            'va_hf_footer_color_alt',
            'va_hf_footer_border_color',
            'va_hf_footer_shadow_color',
            'va_hf_footer_glow_color',
            'va_hf_footer_link_hover_color',
            'va_hf_footer_logo_url',
            'va_hf_footer_logo_height',
            'va_hf_footer_brand_title',
            'va_hf_footer_col_categories_title',
            'va_hf_footer_col_account_title',
            'va_hf_footer_col_legal_title',
            'va_hf_footer_link_aszf',
            'va_hf_footer_link_privacy',
            'va_hf_footer_link_contact',
            'va_hf_footer_link_help',
            'va_hf_footer_copy_text',
            'va_hf_footer_privacy_text',
        ];

        $header_footer = [];
        foreach ( $header_footer_keys as $hf_key ) {
            if ( array_key_exists( $hf_key, $design ) ) {
                $header_footer[ $hf_key ] = $design[ $hf_key ];
                unset( $design[ $hf_key ] );
            }
        }

        foreach ( $design as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_design_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        foreach ( $header_footer as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_header_footer_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Layout builder (Divi/Porto jellegu) */
        $layout = [
            'va_layout_preset'                => 'porto',

            // Global container
            'va_layout_page_max_width'        => '1400',
            'va_layout_container_pad_x'       => '20',
            'va_layout_container_pad_x_mobile'=> '12',

            // Main content and sidebars
            'va_layout_main_pad_y'            => '20',
            'va_layout_main_pad_x'            => '24',
            'va_layout_home_main_pad_x'       => '28',
            'va_layout_content_gap'           => '0',
            'va_layout_right_sidebar_width'   => '340',
            'va_layout_right_sidebar_sticky_top' => '48',
            'va_layout_show_right_sidebar'    => '1',

            // Listing grids
            'va_layout_grid_cols_desktop'     => '4',
            'va_layout_grid_cols_tablet'      => '2',
            'va_layout_grid_cols_mobile'      => '1',
            'va_layout_grid_gap'              => '14',
            'va_layout_bp_desktop_tablet'     => '1200',
            'va_layout_bp_tablet_mobile'      => '560',
            'va_layout_bp_sidebar_hide'       => '1100',

            // Card appearance
            'va_layout_card_radius'           => '6',
            'va_layout_card_border_alpha'     => '0.08',
            'va_layout_card_padding_y'        => '14',
            'va_layout_card_padding_x'        => '14',
            'va_layout_card_title_size'       => '15',
            'va_layout_card_price_size'       => '17',
            'va_layout_card_meta_size'        => '12',
            'va_layout_card_img_ratio'        => '4/3',

            // Effects
            'va_layout_card_hover_lift'       => '2',
            'va_layout_card_shadow_strength'  => '35',
            'va_layout_card_shadow_red'       => '16',
            'va_layout_widget_radius'         => '10',
            'va_layout_widget_padding'        => '16',
        ];

        foreach ( $layout as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_layout_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
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
            self::$defaults[ $key ] = $default;
            register_setting( 'va_general_settings', $key, [ 'sanitize_callback' => 'esc_url_raw' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        // Márka ikon URL (header ikon + automata favicon)
        register_setting( 'va_general_settings', 'va_brand_icon_url', [ 'sanitize_callback' => 'esc_url_raw' ] );
        self::$defaults['va_brand_icon_url'] = '';
        if ( get_option( 'va_brand_icon_url' ) === false ) {
            update_option( 'va_brand_icon_url', '' );
        }

        // Logók (fejléc + hero)
        register_setting( 'va_general_settings', 'va_header_logo_url', [ 'sanitize_callback' => 'esc_url_raw' ] );
        self::$defaults['va_header_logo_url'] = '';
        if ( get_option( 'va_header_logo_url' ) === false ) {
            update_option( 'va_header_logo_url', '' );
        }
        register_setting( 'va_general_settings', 'va_hero_logo_url', [ 'sanitize_callback' => 'esc_url_raw' ] );
        self::$defaults['va_hero_logo_url'] = '';
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
            'va_listing_price_after_free' => 1990,
        ];
        foreach ( $listing_opts as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_listing_settings', $key, [ 'sanitize_callback' => 'absint' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        self::$defaults['va_listing_payment_url'] = '';
        register_setting( 'va_listing_settings', 'va_listing_payment_url', [ 'sanitize_callback' => 'esc_url_raw' ] );
        if ( get_option( 'va_listing_payment_url' ) === false ) {
            update_option( 'va_listing_payment_url', '' );
        }

        $listing_payment = [
            'va_payment_provider'        => 'none',
            'va_payment_mode'            => 'test',
            'va_payment_public_key'      => '',
            'va_payment_secret_key'      => '',
            'va_payment_webhook_secret'  => '',
            'va_payment_success_url'     => '',
            'va_payment_cancel_url'      => '',
        ];
        foreach ( $listing_payment as $key => $default ) {
            self::$defaults[ $key ] = $default;
            $sanitize = in_array( $key, [ 'va_payment_success_url', 'va_payment_cancel_url' ], true )
                ? 'esc_url_raw'
                : 'sanitize_text_field';
            register_setting( 'va_listing_settings', $key, [ 'sanitize_callback' => $sanitize ] );
            if ( get_option( $key ) === false ) {
                update_option( $key, $default );
            }
        }

        $billing = [
            'va_billing_company_name'    => 'Vadaszapro Kft.',
            'va_billing_company_address' => 'Magyarorszag',
            'va_billing_tax_number'      => '',
            'va_billing_email'           => (string) get_option( 'admin_email', '' ),
            'va_billing_phone'           => '',
            'va_invoice_prefix'          => 'VA',
            'va_invoice_next_number'     => 1,
            'va_invoice_footer_note'     => 'Koszonjuk a vasarlast!',
        ];
        foreach ( $billing as $key => $default ) {
            self::$defaults[ $key ] = $default;
            if ( $key === 'va_billing_email' ) {
                register_setting( 'va_listing_settings', $key, [ 'sanitize_callback' => 'sanitize_email' ] );
            } elseif ( $key === 'va_invoice_next_number' ) {
                register_setting( 'va_listing_settings', $key, [ 'sanitize_callback' => 'absint' ] );
            } else {
                register_setting( 'va_listing_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            }

            if ( get_option( $key ) === false ) {
                update_option( $key, $default );
            }
        }

        /* Aukciók */
        $auction_opts = [
            'va_default_min_bid_step' => 500,
            'va_auction_fee_pct'      => 0,
        ];
        foreach ( $auction_opts as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_auction_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Social Media linkek */
        $social_keys = [
            'va_social_facebook'   => '',
            'va_social_instagram'  => '',
            'va_social_youtube'    => '',
            'va_social_tiktok'     => '',
            'va_social_twitter'    => '',
            'va_social_pinterest'  => '',
            'va_social_linkedin'   => '',
            'va_social_whatsapp'   => '',
            'va_social_telegram'   => '',
            'va_social_header_show' => '1',
            'va_social_footer_show' => '1',
            'va_social_header_style' => 'icons',   // icons | pills
            'va_social_footer_style' => 'icons',   // icons | pills | full
            'va_social_icon_size'    => '20',
        ];
        foreach ( $social_keys as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_social_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Admin Panel megjelenés (va_ap_*) */
        $adminpanel = [
            'va_ap_panel_name'    => 'VadászApró',
            'va_ap_panel_icon'    => '🎯',
            'va_ap_logo_url'      => '',
            'va_ap_logo_height'   => 32,
            'va_ap_color_bg'      => '#070709',
            'va_ap_color_bg2'     => '#0d0d11',
            'va_ap_color_bg3'     => '#111118',
            'va_ap_color_bg4'     => '#161620',
            'va_ap_color_text'    => '#e8e8f0',
            'va_ap_color_muted'   => 'rgba(255,255,255,.45)',
            'va_ap_color_accent'  => '#ff2020',
            'va_ap_color_accent2' => '#ff5050',
            'va_ap_color_border'  => 'rgba(255,255,255,.07)',
            'va_ap_color_border2' => 'rgba(255,255,255,.12)',
            'va_ap_sidebar_width' => 230,
            'va_ap_topbar_height' => 60,
            'va_ap_radius'        => 12,
            'va_ap_radius_sm'     => 8,
            'va_ap_font'          => 'montserrat',
        ];
        foreach ( $adminpanel as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_ap_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) update_option( $key, $default );
        }

        /* Termékoldal Designer (va_single_*) */
        $single_designer = [
            'va_single_preset'             => 'cinematic',
            'va_single_layout_mode'        => 'split',
            'va_single_content_max'        => 1320,
            'va_single_sidebar_width'      => 390,
            'va_single_layout_gap'         => 24,
            'va_single_gallery_ratio'      => '4/3',
            'va_single_gallery_fit'        => 'cover',
            'va_single_thumb_size'         => 86,
            'va_single_card_radius'        => 14,
            'va_single_card_padding'       => 22,
            'va_single_title_size'         => 40,
            'va_single_price_size'         => 42,
            'va_single_meta_size'          => 13,
            'va_single_btn_height'         => 48,
            'va_single_share_size'         => 40,
            'va_single_mobile_title_scale' => 78,
            'va_single_viewer_bg'          => 'rgba(4,4,4,.96)',
            'va_single_accent'             => '#ff2a2a',
            'va_single_glass'              => 'rgba(255,255,255,.07)',
            'va_single_border'             => 'rgba(255,255,255,.12)',
        ];
        foreach ( $single_designer as $key => $default ) {
            self::$defaults[ $key ] = $default;
            register_setting( 'va_single_settings', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
            if ( get_option( $key ) === false ) {
                update_option( $key, $default );
            }
        }
    }

    private static function get_display_option( string $key, $fallback = '' ) {
        $val = get_option( $key, null );
        if ( $val === null || $val === false || $val === '' ) {
            if ( array_key_exists( $key, self::$defaults ) ) {
                return self::$defaults[ $key ];
            }
            return $fallback;
        }
        return $val;
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
                    <?php self::field_toggle('va_enable_login',          'Bejelentkezés engedélyezése' ); ?>
                    <?php self::field_toggle('va_enable_register',       'Regisztráció engedélyezése' ); ?>
                    <?php self::field_num(   'va_listings_per_page',    'Hirdetés / oldal', 5, 100 ); ?>
                    <?php self::field_num(   'va_listing_validity_days','Hirdetés érvényessége (nap)', 1, 365 ); ?>
                    <?php self::field_num(   'va_max_images_per_listing','Max. képek száma hirdetésenként', 1, 20 ); ?>
                    <?php self::field_toggle('va_auto_publish_listings', 'Hirdetések azonnali megjelenés (jóváhagyás nélkül)' ); ?>
                    <?php self::field_toggle('va_require_phone',         'Telefonszám kötelező' ); ?>
                    <?php self::field_toggle('va_maintenance_mode',      'Karbantartási mód' ); ?>
                    <?php self::field_text(  'va_maintenance_msg',       'Karbantartási üzenet' ); ?>
                    <?php self::field_toggle('va_show_hunting_season_widget', 'Vadászati idény widget megjelenjen' ); ?>
                    <?php self::field_toggle('va_show_moon_widget',          'Hold widget megjelenjen' ); ?>
                    <?php self::field_toggle('va_show_weather_widget',       'Időjárás widget megjelenjen (geolokáció + 7 nap)' ); ?>
                    <?php self::field_toggle('va_enable_hunting_calendar_page', 'Vadászati naptár oldal engedélyezve' ); ?>
                    <?php self::field_toggle('va_show_home_hunting_calendar', 'Főoldali vadászati naptár panel megjelenjen' ); ?>

                    <tr><th colspan="2" style="padding-top:18px;"><h2 style="margin:0;">Hirdetés kártya meta layout</h2><p class="description" style="margin:6px 0 0;">Főoldali/listás kártyák alsó meta sorainak teljes vezérlése.</p></th></tr>
                    <?php self::field_select('va_card_meta_rows', 'Meta sorok száma', [ '1' => '1 sor', '2' => '2 sor', '3' => '3 sor' ] ); ?>
                    <?php self::field_num(   'va_card_meta_col_gap', 'Meta oszlopköz (px)', 0, 40 ); ?>
                    <?php self::field_num(   'va_card_meta_row_gap', 'Meta sorköz egy soron belül (px)', 0, 20 ); ?>
                    <?php self::field_num(   'va_card_meta_stack_gap', 'Meta sorblokkok közti távolság (px)', 0, 20 ); ?>

                    <?php self::field_toggle('va_card_meta_show_category', 'Mutassa: kategória' ); ?>
                    <?php self::field_select('va_card_meta_row_category', 'Kategória sora', [ '1' => '1. sor', '2' => '2. sor', '3' => '3. sor' ] ); ?>
                    <?php self::field_toggle('va_card_meta_show_county', 'Mutassa: megye' ); ?>
                    <?php self::field_select('va_card_meta_row_county', 'Megye sora', [ '1' => '1. sor', '2' => '2. sor', '3' => '3. sor' ] ); ?>
                    <?php self::field_toggle('va_card_meta_show_location', 'Mutassa: település/hely' ); ?>
                    <?php self::field_select('va_card_meta_row_location', 'Település/hely sora', [ '1' => '1. sor', '2' => '2. sor', '3' => '3. sor' ] ); ?>
                    <?php self::field_toggle('va_card_meta_show_views', 'Mutassa: megtekintésszám' ); ?>
                    <?php self::field_select('va_card_meta_row_views', 'Megtekintésszám sora', [ '1' => '1. sor', '2' => '2. sor', '3' => '3. sor' ] ); ?>
                    <?php self::field_toggle('va_card_meta_show_author', 'Mutassa: feladó neve' ); ?>
                    <?php self::field_select('va_card_meta_row_author', 'Feladó sora', [ '1' => '1. sor', '2' => '2. sor', '3' => '3. sor' ] ); ?>
                    <?php self::field_toggle('va_card_meta_show_date', 'Mutassa: dátum' ); ?>
                    <?php self::field_select('va_card_meta_row_date', 'Dátum sora', [ '1' => '1. sor', '2' => '2. sor', '3' => '3. sor' ] ); ?>

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

                <h2>Tartalom színek</h2>
                <table class="form-table">
                    <?php self::field_color( 'va_color_content_bg',       'Tartalom háttér' ); ?>
                    <?php self::field_color( 'va_color_content_text',     'Tartalom szöveg' ); ?>
                    <?php self::field_color( 'va_color_content_headings', 'Tartalom címsorok' ); ?>
                    <?php self::field_color( 'va_color_content_links',    'Tartalom linkek' ); ?>
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

                <h2>Mobil skála finomhangolás</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_mobile_factor_hero',   'Hero mobil szorzó (%)', 70, 120 ); ?>
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

        $preset_msg = isset( $_GET['va_hf_preset'] ) ? sanitize_key( (string) $_GET['va_hf_preset'] ) : '';
        $presets = self::get_header_footer_presets();
        ?>
        <div class="wrap va-admin-wrap">
            <h1>🧩 VadászApró – Fejléc + Lábléc (100% kontroll)</h1>
            <p class="description">Külön menüből finomhangolhatod a fejléc és a lábléc layoutját, szövegeit, méreteit, mobil viselkedését és vizuális részleteit.</p>
            <?php if ( $preset_msg === 'ok' ): ?>
                <div class="notice notice-success"><p>Preset alkalmazva. Mentés nélkül is azonnal él.</p></div>
            <?php elseif ( $preset_msg === 'invalid' ): ?>
                <div class="notice notice-error"><p>Ismeretlen preset kulcs.</p></div>
            <?php endif; ?>
            <?php settings_errors( 'va_header_footer_settings' ); ?>

            <h2>🎛️ Egy kattintásos modern presetek</h2>
            <p class="description">10 előre összehangolt fejléc + lábléc paletta, árnyék és glow beállítással.</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;max-width:1200px;margin:10px 0 18px;">
                <?php foreach ( $presets as $preset_key => $preset ): ?>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="border:1px solid #dcdcde;border-radius:8px;padding:12px;background:#fff;">
                        <input type="hidden" name="action" value="va_apply_hf_preset">
                        <input type="hidden" name="preset_key" value="<?php echo esc_attr( $preset_key ); ?>">
                        <?php wp_nonce_field( 'va_apply_hf_preset' ); ?>
                        <strong><?php echo esc_html( $preset['label'] ); ?></strong>
                        <div style="margin:8px 0 10px;color:#50575e;"><?php echo esc_html( $preset['desc'] ); ?></div>
                        <button type="submit" class="button button-secondary">Preset alkalmazása</button>
                    </form>
                <?php endforeach; ?>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields( 'va_header_footer_settings' ); ?>

                <h2>Fejléc/Lábléc: alapszínek (tipográfia + linkek)</h2>
                <table class="form-table">
                    <?php self::field_text(  'va_color_header_bg',     'Fejléc háttér (hex vagy rgba)' ); ?>
                    <?php self::field_color( 'va_color_header_text',   'Fejléc szöveg szín' ); ?>
                    <?php self::field_color( 'va_color_header_accent', 'Fejléc accent szín' ); ?>
                    <?php self::field_color( 'va_color_footer_bg',       'Lábléc háttér alapszín' ); ?>
                    <?php self::field_text(  'va_color_footer_text',     'Lábléc szöveg szín (hex vagy rgba)' ); ?>
                    <?php self::field_color( 'va_color_footer_headings', 'Lábléc címsor szín' ); ?>
                    <?php self::field_color( 'va_color_footer_links',    'Lábléc link alapszín' ); ?>
                </table>

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
                    <?php self::field_media( 'va_hf_footer_logo_url',         'Lábléc logó (opcionális)' ); ?>
                    <?php self::field_num(   'va_hf_footer_logo_height',      'Lábléc logó magasság (px)', 20, 180 ); ?>
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

    /* ══ Layout Builder oldal ═════════════════════════════ */
    public static function render_layout_builder() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        ?>
        <div class="wrap va-admin-wrap">
            <h1>🧩 VadászApró – Layout Állító (Divi / Porto mintára)</h1>
            <p class="description">Nagy léptékű layout finomhangolás: konténer, rácsok, oldalsáv, kártyák, árnyékok, tördelés, mobil viselkedés.</p>
            <?php settings_errors( 'va_layout_settings' ); ?>

            <div class="va-layout-guide">
                <div class="va-layout-guide__card">
                    <h3>Konténer + oldalpárna</h3>
                    <div class="va-diagram va-diagram--container">
                        <div class="va-d-outer">
                            <div class="va-d-gutter"></div>
                            <div class="va-d-inner"></div>
                            <div class="va-d-gutter"></div>
                        </div>
                    </div>
                    <p>Az <strong>Oldal max szélesség</strong> és a <strong>Konténer oldalpárna</strong> itt rajzolódik ki.</p>
                </div>

                <div class="va-layout-guide__card">
                    <h3>Töréspontok (responsive)</h3>
                    <div class="va-diagram va-diagram--breakpoints">
                        <div class="va-d-bp va-d-bp--desktop">Desktop</div>
                        <div class="va-d-arrow">→</div>
                        <div class="va-d-bp va-d-bp--tablet">Tablet</div>
                        <div class="va-d-arrow">→</div>
                        <div class="va-d-bp va-d-bp--mobile">Mobil</div>
                    </div>
                    <p>A <strong>desktop → tablet</strong> és <strong>tablet → mobil</strong> váltás pontos px értékekkel állítható.</p>
                </div>

                <div class="va-layout-guide__card">
                    <h3>Kártya anatómia</h3>
                    <div class="va-diagram va-diagram--card">
                        <div class="va-d-card-img"></div>
                        <div class="va-d-card-body">
                            <div class="va-d-line va-d-line--title"></div>
                            <div class="va-d-line va-d-line--price"></div>
                            <div class="va-d-line va-d-line--meta"></div>
                        </div>
                    </div>
                    <p>Itt állítod a <strong>radius</strong>, <strong>padding</strong>, <strong>árnyék</strong>, <strong>tipográfia</strong> értékeit.</p>
                </div>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields( 'va_layout_settings' ); ?>

                <h2>Preset</h2>
                <table class="form-table">
                    <?php self::field_select( 'va_layout_preset', 'Layout preset', [
                        'porto' => 'Porto (kompakt, commerce fókusz)',
                        'divi'  => 'Divi (légiesebb, moduláris)',
                        'custom'=> 'Custom (kézi finomhangolás)',
                    ] ); ?>
                </table>

                <h2>Konténer és Tördelés</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_layout_page_max_width', 'Oldal max szélesség (px)', 960, 2200 ); ?>
                    <?php self::field_num( 'va_layout_container_pad_x', 'Konténer oldalpárna desktop (px)', 0, 80 ); ?>
                    <?php self::field_num( 'va_layout_container_pad_x_mobile', 'Konténer oldalpárna mobil (px)', 0, 40 ); ?>
                    <?php self::field_num( 'va_layout_main_pad_y', 'Főtartalom függőleges padding (px)', 0, 80 ); ?>
                    <?php self::field_num( 'va_layout_main_pad_x', 'Főtartalom vízszintes padding (px)', 0, 80 ); ?>
                    <?php self::field_num( 'va_layout_home_main_pad_x', 'Főoldal extra vízszintes padding (px)', 0, 100 ); ?>
                    <?php self::field_num( 'va_layout_content_gap', 'Tartalmi oszlopok közti gap (px)', 0, 64 ); ?>
                </table>

                <h2>Oldalsáv</h2>
                <table class="form-table">
                    <?php self::field_toggle( 'va_layout_show_right_sidebar', 'Jobb oldalsáv megjelenjen desktopon' ); ?>
                    <?php self::field_num( 'va_layout_right_sidebar_width', 'Jobb oldalsáv szélesség (px)', 220, 520 ); ?>
                    <?php self::field_num( 'va_layout_right_sidebar_sticky_top', 'Jobb oldalsáv sticky offset (px)', 0, 180 ); ?>
                </table>

                <h2>Hirdetés Rács</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_layout_grid_cols_desktop', 'Desktop oszlopok száma', 1, 6 ); ?>
                    <?php self::field_num( 'va_layout_grid_cols_tablet', 'Tablet oszlopok száma', 1, 4 ); ?>
                    <?php self::field_num( 'va_layout_grid_cols_mobile', 'Mobil oszlopok száma', 1, 2 ); ?>
                    <?php self::field_num( 'va_layout_grid_gap', 'Kártyák közti gap (px)', 4, 40 ); ?>
                    <?php self::field_num( 'va_layout_bp_desktop_tablet', 'Töréspont: desktop → tablet (px)', 680, 2000 ); ?>
                    <?php self::field_num( 'va_layout_bp_tablet_mobile', 'Töréspont: tablet → mobil (px)', 320, 1200 ); ?>
                    <?php self::field_num( 'va_layout_bp_sidebar_hide', 'Jobb oldalsáv rejtése (px alatt)', 480, 1800 ); ?>
                </table>

                <h2>Mobil Preview (felső admin sáv)</h2>
                <p class="description">Frontend nézetben, bejelentkezett adminként a felső fekete sávban megjelenik egy <strong>VA Breakpoint Preview</strong> menü. Itt 1 kattintással válthatsz preset szélességekre, és <strong>egyedi px</strong> értéket is megadhatsz (Bricks-szerű workflow).</p>

                <h2>Kártya Design</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_layout_card_radius', 'Kártya lekerekítés (px)', 0, 28 ); ?>
                    <?php self::field_decimal( 'va_layout_card_border_alpha', 'Kártya border erősség (0..1)', 0, 1, 0.01 ); ?>
                    <?php self::field_num( 'va_layout_card_padding_y', 'Kártya belső padding Y (px)', 6, 40 ); ?>
                    <?php self::field_num( 'va_layout_card_padding_x', 'Kártya belső padding X (px)', 6, 40 ); ?>
                    <?php self::field_num( 'va_layout_card_title_size', 'Kártya cím méret (px)', 12, 28 ); ?>
                    <?php self::field_num( 'va_layout_card_price_size', 'Kártya ár méret (px)', 12, 36 ); ?>
                    <?php self::field_num( 'va_layout_card_meta_size', 'Kártya meta méret (px)', 10, 20 ); ?>
                    <?php self::field_select( 'va_layout_card_img_ratio', 'Kártya képarány', [
                        '4/3' => '4:3 (klasszikus)',
                        '16/10' => '16:10 (szélesebb)',
                        '1/1' => '1:1 (négyzetes)',
                        '3/2' => '3:2',
                    ] ); ?>
                </table>

                <h2>Interakció és Widgetek</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_layout_card_hover_lift', 'Kártya hover emelés (px)', 0, 16 ); ?>
                    <?php self::field_num( 'va_layout_card_shadow_strength', 'Kártya árnyék erősség (%)', 0, 100 ); ?>
                    <?php self::field_num( 'va_layout_card_shadow_red', 'Kártya vörös glow erősség (%)', 0, 100 ); ?>
                    <?php self::field_num( 'va_layout_widget_radius', 'Widget lekerekítés (px)', 0, 28 ); ?>
                    <?php self::field_num( 'va_layout_widget_padding', 'Widget belső padding (px)', 6, 40 ); ?>
                </table>

                <?php submit_button( 'Layout mentése' ); ?>
            </form>
        </div>
        <?php
    }

    /* ══ Admin Panel személyre szabás ══════════════════════ */
    public static function render_adminpanel(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;
        wp_enqueue_media();

        $fonts = [
            'system'        => 'System UI (natív, leggyorsabb)',
            'inter'         => 'Inter',
            'roboto'        => 'Roboto',
            'montserrat'    => 'Montserrat (alapértelmezett)',
            'nunito'        => 'Nunito',
            'poppins'       => 'Poppins',
            'raleway'       => 'Raleway',
            'dm-sans'       => 'DM Sans',
            'manrope'       => 'Manrope',
            'work-sans'     => 'Work Sans',
            'rubik'         => 'Rubik',
            'source-sans-3' => 'Source Sans 3',
            'fira-sans'     => 'Fira Sans',
            'oswald'        => 'Oswald',
        ];

        $presets = self::get_adminpanel_presets();
        $preset_msg = isset( $_GET['va_ap_preset'] ) ? sanitize_key( (string) $_GET['va_ap_preset'] ) : '';
        $g = static fn( string $k, string $d = '' ) => (string) ( get_option( $k, $d ) ?: $d );
        ?>
        <div class="wrap va-admin-wrap va-aps-wrap">
            <h1>🖥️ Admin Panel – Teljes személyre szabás</h1>
            <p class="description" style="margin-bottom:20px;">Állítsd be az admin felület teljes megjelenését: szín, betűtípus, méretek, logó és branding. Minden azonnal él mentés után.</p>

            <?php if ( $preset_msg === 'ok' ): ?>
            <div class="notice notice-success is-dismissible"><p>✅ Preset alkalmazva és elmentve!</p></div>
            <?php elseif ( $preset_msg === 'invalid' ): ?>
            <div class="notice notice-error is-dismissible"><p>❌ Ismeretlen preset kulcs.</p></div>
            <?php endif; ?>
            <?php settings_errors( 'va_ap_settings' ); ?>

            <!-- ══ Presetek ══════════════════════════════════════ -->
            <div class="va-aps-presets-box">
                <div class="va-aps-presets-hdr">
                    <h2>🎨 Egy kattintásos presetek</h2>
                    <p>10 előre összehangolt Admin Panel téma – választ után minden szín és beállítás azonnal frissül.</p>
                </div>
                <div class="va-aps-presets-grid">
                    <?php foreach ( $presets as $pk => $pr ): ?>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="va-aps-pf">
                        <input type="hidden" name="action" value="va_apply_ap_preset">
                        <input type="hidden" name="preset_key" value="<?php echo esc_attr( $pk ); ?>">
                        <?php wp_nonce_field( 'va_apply_ap_preset' ); ?>
                        <button type="submit" class="va-aps-preset">
                            <span class="va-aps-swatches">
                                <span style="background:<?php echo esc_attr( $pr['bg2'] ); ?>"></span>
                                <span style="background:<?php echo esc_attr( $pr['bg'] ); ?>"></span>
                                <span style="background:<?php echo esc_attr( $pr['accent'] ); ?>"></span>
                                <span style="background:<?php echo esc_attr( $pr['accent2'] ); ?>"></span>
                            </span>
                            <strong><?php echo esc_html( $pr['label'] ); ?></strong>
                            <small><?php echo esc_html( $pr['desc'] ); ?></small>
                        </button>
                    </form>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ══ Fő layout: form + live preview ═══════════════ -->
            <div class="va-aps-main">

                <!-- Form -->
                <div class="va-aps-form-col">
                    <form method="post" action="options.php" id="va-aps-form">
                        <?php settings_fields( 'va_ap_settings' ); ?>

                        <!-- Márka -->
                        <div class="va-aps-section">
                            <div class="va-aps-section-hdr">
                                <div class="va-aps-section-icon">🏷️</div>
                                <div>
                                    <strong>Márka és logó</strong>
                                    <p>Panel neve, ikonja, sidebar logó</p>
                                </div>
                            </div>
                            <table class="form-table">
                                <?php self::field_text(  'va_ap_panel_name',  'Panel neve (sidebar fejléc)' ); ?>
                                <?php self::field_text(  'va_ap_panel_icon',  'Panel ikon (emoji, pl. 🎯)' ); ?>
                                <?php self::field_media( 'va_ap_logo_url',    'Sidebar logó kép (opcionális – ha van, az ikon helyére kerül)' ); ?>
                                <?php self::field_num(   'va_ap_logo_height', 'Logó magasság (px)', 16, 80 ); ?>
                            </table>
                        </div>

                        <!-- Háttér színek -->
                        <div class="va-aps-section">
                            <div class="va-aps-section-hdr">
                                <div class="va-aps-section-icon">🌑</div>
                                <div>
                                    <strong>Háttér rétegek</strong>
                                    <p>Főháttér, sidebar, kártyák, hover állapot</p>
                                </div>
                            </div>
                            <table class="form-table">
                                <?php self::field_color( 'va_ap_color_bg',  'Főháttér (--va-bg)' ); ?>
                                <?php self::field_color( 'va_ap_color_bg2', 'Sidebar / topbar háttér (--va-bg2)' ); ?>
                                <?php self::field_color( 'va_ap_color_bg3', 'Kártya / panel háttér (--va-bg3)' ); ?>
                                <?php self::field_color( 'va_ap_color_bg4', 'Hover / aktív háttér (--va-bg4)' ); ?>
                            </table>
                        </div>

                        <!-- Szöveg és szegélyek -->
                        <div class="va-aps-section">
                            <div class="va-aps-section-hdr">
                                <div class="va-aps-section-icon">✍️</div>
                                <div>
                                    <strong>Szöveg és szegélyek</strong>
                                    <p>Alap szöveg, halvány szöveg, keretek</p>
                                </div>
                            </div>
                            <table class="form-table">
                                <?php self::field_color( 'va_ap_color_text', 'Fő szövegszín (--va-text)' ); ?>
                                <?php self::field_text(  'va_ap_color_muted', 'Halvány szöveg – rgba megengedett (--va-muted)' ); ?>
                                <?php self::field_text(  'va_ap_color_border', 'Keret alap – rgba megengedett (--va-border)' ); ?>
                                <?php self::field_text(  'va_ap_color_border2', 'Keret erős – rgba megengedett (--va-border2)' ); ?>
                            </table>
                        </div>

                        <!-- Accent szín -->
                        <div class="va-aps-section">
                            <div class="va-aps-section-hdr">
                                <div class="va-aps-section-icon">⚡</div>
                                <div>
                                    <strong>Accent (kiemelő) szín</strong>
                                    <p>Az admin panel fő hangsúlyszíne – aktív elemek, gombok, indikátorok</p>
                                </div>
                            </div>
                            <table class="form-table">
                                <?php self::field_color( 'va_ap_color_accent',  'Accent szín (--va-accent)' ); ?>
                                <?php self::field_color( 'va_ap_color_accent2', 'Accent hover / világosabb (--va-accent2)' ); ?>
                            </table>
                        </div>

                        <!-- Layout méretek -->
                        <div class="va-aps-section">
                            <div class="va-aps-section-hdr">
                                <div class="va-aps-section-icon">📐</div>
                                <div>
                                    <strong>Layout méretek</strong>
                                    <p>Sidebar szélesség, topbar magasság, lekerekítések</p>
                                </div>
                            </div>
                            <table class="form-table">
                                <?php self::field_num( 'va_ap_sidebar_width', 'Sidebar szélesség (px)', 180, 340 ); ?>
                                <?php self::field_num( 'va_ap_topbar_height', 'Topbar magasság (px)', 44, 80 ); ?>
                                <?php self::field_num( 'va_ap_radius',     'Kártya / panel lekerekítés (px)', 0, 24 ); ?>
                                <?php self::field_num( 'va_ap_radius_sm',  'Kisebb elem lekerekítés (px)', 0, 16 ); ?>
                            </table>
                        </div>

                        <!-- Betűtípus -->
                        <div class="va-aps-section">
                            <div class="va-aps-section-hdr">
                                <div class="va-aps-section-icon">🔤</div>
                                <div>
                                    <strong>Admin betűtípus</strong>
                                    <p>Az admin panel globális betűcsaládja</p>
                                </div>
                            </div>
                            <table class="form-table">
                                <?php self::field_select( 'va_ap_font', 'Admin betűtípus', $fonts ); ?>
                            </table>
                        </div>

                        <div style="margin-top:8px;">
                            <?php submit_button( '💾 Admin Panel beállítások mentése', 'primary', 'submit', false ); ?>
                        </div>
                    </form>
                </div><!-- .va-aps-form-col -->

                <!-- Live Preview -->
                <div class="va-aps-preview-col">
                    <div class="va-aps-preview-sticky">
                        <div class="va-aps-preview-label">👁 Élő előnézet</div>

                        <!-- Topbar -->
                        <div class="va-aps-prev-topbar" id="va-aps-prev-topbar"
                             style="--pv-bg2:<?php echo esc_attr($g('va_ap_color_bg2','#0d0d11')); ?>;--pv-text:<?php echo esc_attr($g('va_ap_color_text','#e8e8f0')); ?>;--pv-accent:<?php echo esc_attr($g('va_ap_color_accent','#ff2020')); ?>;--pv-border:<?php echo $g('va_ap_color_border','rgba(255,255,255,.07)'); ?>;--pv-muted:<?php echo $g('va_ap_color_muted','rgba(255,255,255,.45)'); ?>;">
                            <span class="va-aps-topbar-title">Irányítópult <small>VadászApró</small></span>
                            <span class="va-aps-topbar-btn">+ Új hirdetés</span>
                        </div>

                        <!-- Sidebar -->
                        <div class="va-aps-prev-sidebar" id="va-aps-prev-sidebar"
                             style="--pv-bg:<?php echo esc_attr($g('va_ap_color_bg','#070709')); ?>;--pv-bg2:<?php echo esc_attr($g('va_ap_color_bg2','#0d0d11')); ?>;--pv-bg3:<?php echo esc_attr($g('va_ap_color_bg3','#111118')); ?>;--pv-bg4:<?php echo esc_attr($g('va_ap_color_bg4','#161620')); ?>;--pv-text:<?php echo esc_attr($g('va_ap_color_text','#e8e8f0')); ?>;--pv-muted:<?php echo $g('va_ap_color_muted','rgba(255,255,255,.45)'); ?>;--pv-accent:<?php echo esc_attr($g('va_ap_color_accent','#ff2020')); ?>;--pv-accent2:<?php echo esc_attr($g('va_ap_color_accent2','#ff5050')); ?>;--pv-border:<?php echo $g('va_ap_color_border','rgba(255,255,255,.07)'); ?>;">
                            <div class="va-aps-prev-logo">
                                <div class="va-aps-prev-icon" id="va-aps-prev-icon"><?php echo esc_html( $g('va_ap_panel_icon','🎯') ); ?></div>
                                <div>
                                    <div class="va-aps-prev-name" id="va-aps-prev-name"><?php echo esc_html( $g('va_ap_panel_name','VadászApró') ); ?></div>
                                    <div class="va-aps-prev-sub">Admin Panel</div>
                                </div>
                            </div>
                            <nav class="va-aps-prev-nav">
                                <a class="va-aps-prev-item va-aps-prev-item--active"><span>📊</span>Irányítópult</a>
                                <a class="va-aps-prev-item"><span>📋</span>Hirdetések <span class="va-aps-prev-badge">3</span></a>
                                <a class="va-aps-prev-item"><span>👥</span>Felhasználók</a>
                                <div class="va-aps-prev-sep">Beállítások</div>
                                <a class="va-aps-prev-item"><span>⚙️</span>Általános</a>
                                <a class="va-aps-prev-item"><span>🎨</span>Design</a>
                                <a class="va-aps-prev-item va-aps-prev-item--current"><span>🖥️</span>Admin Panel</a>
                                <div class="va-aps-prev-sep">Tartalom</div>
                                <a class="va-aps-prev-item"><span>📢</span>Reklámzónák</a>
                                <a class="va-aps-prev-item"><span>📈</span>Statisztika</a>
                            </nav>
                        </div>

                        <!-- Content area snippet -->
                        <div class="va-aps-prev-content" id="va-aps-prev-content"
                             style="--pv-bg:<?php echo esc_attr($g('va_ap_color_bg','#070709')); ?>;--pv-bg3:<?php echo esc_attr($g('va_ap_color_bg3','#111118')); ?>;--pv-text:<?php echo esc_attr($g('va_ap_color_text','#e8e8f0')); ?>;--pv-muted:<?php echo $g('va_ap_color_muted','rgba(255,255,255,.45)'); ?>;--pv-accent:<?php echo esc_attr($g('va_ap_color_accent','#ff2020')); ?>;--pv-border:<?php echo $g('va_ap_color_border','rgba(255,255,255,.07)'); ?>;--pv-radius:<?php echo (int)$g('va_ap_radius','12'); ?>px;">
                            <div class="va-aps-prev-kpi-row">
                                <div class="va-aps-prev-kpi"><span>📋</span><strong>1 247</strong><small>Aktív hird.</small></div>
                                <div class="va-aps-prev-kpi"><span>👥</span><strong>384</strong><small>Felhasználó</small></div>
                                <div class="va-aps-prev-kpi"><span>👁</span><strong>42 k</strong><small>Megtekintés</small></div>
                            </div>
                            <div class="va-aps-prev-table-row">
                                <div class="va-aps-prev-tr"><div class="va-aps-prev-td va-aps-prev-td--img"></div><div class="va-aps-prev-td va-aps-prev-td--title"></div><div class="va-aps-prev-td va-aps-prev-td--price"></div></div>
                                <div class="va-aps-prev-tr"><div class="va-aps-prev-td va-aps-prev-td--img"></div><div class="va-aps-prev-td va-aps-prev-td--title"></div><div class="va-aps-prev-td va-aps-prev-td--price"></div></div>
                                <div class="va-aps-prev-tr"><div class="va-aps-prev-td va-aps-prev-td--img"></div><div class="va-aps-prev-td va-aps-prev-td--title"></div><div class="va-aps-prev-td va-aps-prev-td--price"></div></div>
                            </div>
                        </div>

                        <div class="va-aps-preview-note">Az előnézet automatikusan frissül gépelés közben.</div>
                    </div>
                </div><!-- .va-aps-preview-col -->

            </div><!-- .va-aps-main -->
        </div><!-- .va-aps-wrap -->

        <style>
        /* ── Admin Panel Settings Page ── */
        .va-aps-wrap { max-width:1400px; }

        /* Presets */
        .va-aps-presets-box { background:var(--va-bg2); border:1px solid var(--va-border); border-radius:var(--va-radius); padding:20px 24px; margin-bottom:24px; }
        .va-aps-presets-hdr h2 { margin:0 0 2px; font-size:15px; color:var(--va-text); }
        .va-aps-presets-hdr p  { margin:0 0 16px; font-size:12px; color:var(--va-muted); }
        .va-aps-presets-grid { display:flex; flex-wrap:wrap; gap:8px; }
        .va-aps-pf { display:contents; }
        .va-aps-preset {
            display:flex; flex-direction:column; gap:5px; align-items:flex-start;
            background:var(--va-bg3); border:1px solid var(--va-border2);
            border-radius:10px; padding:10px 14px; cursor:pointer; color:var(--va-text);
            text-align:left; min-width:150px; transition:.15s;
        }
        .va-aps-preset:hover { border-color:var(--va-accent); transform:translateY(-1px); box-shadow:0 4px 18px rgba(0,0,0,.4); }
        .va-aps-preset strong { font-size:12px; font-weight:700; }
        .va-aps-preset small  { font-size:11px; color:var(--va-muted); }
        .va-aps-swatches { display:flex; gap:4px; margin-bottom:2px; }
        .va-aps-swatches span { width:14px; height:14px; border-radius:50%; border:1px solid rgba(255,255,255,.12); }

        /* Main layout */
        .va-aps-main { display:grid; grid-template-columns:1fr 260px; gap:24px; align-items:start; }

        /* Sections */
        .va-aps-section { background:var(--va-bg2); border:1px solid var(--va-border); border-radius:var(--va-radius); overflow:hidden; margin-bottom:16px; }
        .va-aps-section-hdr { display:flex; align-items:center; gap:14px; padding:14px 20px; border-bottom:1px solid var(--va-border); }
        .va-aps-section-icon { font-size:20px; flex-shrink:0; }
        .va-aps-section-hdr strong { font-size:13px; font-weight:700; color:var(--va-text); display:block; margin-bottom:2px; }
        .va-aps-section-hdr p { margin:0; font-size:11px; color:var(--va-muted); }
        .va-aps-section .form-table { margin:0; padding:8px 20px 14px; background:transparent; }
        .va-aps-section .form-table th { font-size:12px; color:var(--va-muted); font-weight:600; text-transform:uppercase; letter-spacing:.5px; padding:10px 18px 10px 0; width:220px; }
        .va-aps-section .form-table td { padding:10px 0; }
        .va-aps-section .form-table input[type=text],
        .va-aps-section .form-table input[type=number],
        .va-aps-section .form-table select { background:var(--va-bg3) !important; border:1px solid var(--va-border2) !important; color:var(--va-text) !important; border-radius:var(--va-radius-sm) !important; padding:7px 10px !important; font-size:13px !important; }
        .va-aps-section .form-table input[type=color] { width:44px; height:34px; border-radius:var(--va-radius-sm); border:1px solid var(--va-border2); background:transparent; cursor:pointer; padding:2px; }

        /* Preview column */
        .va-aps-preview-col { position:sticky; top:72px; }
        .va-aps-preview-sticky { display:flex; flex-direction:column; gap:0; }
        .va-aps-preview-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--va-muted); margin-bottom:6px; }
        .va-aps-preview-note { font-size:10px; color:var(--va-muted); margin-top:8px; text-align:center; }

        /* Topbar preview */
        .va-aps-prev-topbar {
            background:var(--pv-bg2); border:1px solid var(--pv-border,rgba(255,255,255,.07));
            border-radius:8px 8px 0 0; padding:8px 12px;
            display:flex; justify-content:space-between; align-items:center;
            font-size:11px; color:var(--pv-text,#e8e8f0); gap:8px;
            font-family:system-ui,sans-serif;
        }
        .va-aps-topbar-title { font-weight:600; }
        .va-aps-topbar-title small { font-size:9px; color:var(--pv-muted,rgba(255,255,255,.45)); margin-left:4px; }
        .va-aps-topbar-btn { background:var(--pv-accent,#ff2020); color:#fff; border-radius:5px; padding:3px 9px; font-size:10px; font-weight:700; }

        /* Sidebar preview */
        .va-aps-prev-sidebar {
            background:var(--pv-bg2); border-left:1px solid var(--pv-border,rgba(255,255,255,.07));
            border-right:1px solid var(--pv-border,rgba(255,255,255,.07));
            font-family:system-ui,sans-serif; font-size:11px; color:var(--pv-text,#e8e8f0);
            display:flex; flex-direction:column;
        }
        .va-aps-prev-logo { display:flex; align-items:center; gap:9px; padding:12px 12px 10px; border-bottom:1px solid var(--pv-border,rgba(255,255,255,.07)); }
        .va-aps-prev-icon { width:26px; height:26px; background:var(--pv-accent,#ff2020); border-radius:7px; display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; }
        .va-aps-prev-name { font-size:11px; font-weight:700; color:var(--pv-text,#e8e8f0); }
        .va-aps-prev-sub  { font-size:9px; color:var(--pv-muted,rgba(255,255,255,.42)); }
        .va-aps-prev-nav  { padding:6px 7px; display:flex; flex-direction:column; gap:1px; }
        .va-aps-prev-item { display:flex; align-items:center; gap:7px; padding:5px 7px; border-radius:6px; color:var(--pv-text,#e8e8f0); font-size:10px; text-decoration:none; cursor:default; }
        .va-aps-prev-item--active { background:var(--pv-accent,#ff2020); color:#fff !important; }
        .va-aps-prev-item--current { background:var(--pv-bg4,#161620); }
        .va-aps-prev-sep  { font-size:8px; font-weight:700; text-transform:uppercase; letter-spacing:.7px; color:var(--pv-muted,rgba(255,255,255,.32)); padding:6px 7px 2px; }
        .va-aps-prev-badge { margin-left:auto; background:var(--pv-accent,#ff2020); color:#fff; border-radius:999px; padding:0 5px; font-size:9px; font-weight:700; }

        /* Content preview */
        .va-aps-prev-content {
            background:var(--pv-bg); padding:10px;
            border:1px solid var(--pv-border,rgba(255,255,255,.07)); border-top:none;
            border-radius:0 0 8px 8px; font-family:system-ui,sans-serif;
        }
        .va-aps-prev-kpi-row { display:grid; grid-template-columns:repeat(3,1fr); gap:6px; margin-bottom:8px; }
        .va-aps-prev-kpi { background:var(--pv-bg3); border:1px solid var(--pv-border,rgba(255,255,255,.07)); border-radius:var(--pv-radius,8px); padding:6px 8px; display:flex; flex-direction:column; align-items:center; gap:1px; font-size:9px; color:var(--pv-muted,rgba(255,255,255,.45)); }
        .va-aps-prev-kpi span { font-size:13px; }
        .va-aps-prev-kpi strong { font-size:13px; font-weight:700; color:var(--pv-accent,#ff2020); }
        .va-aps-prev-table-row { background:var(--pv-bg3); border:1px solid var(--pv-border,rgba(255,255,255,.07)); border-radius:var(--pv-radius,8px); overflow:hidden; }
        .va-aps-prev-tr { display:flex; align-items:center; gap:6px; padding:5px 8px; border-bottom:1px solid var(--pv-border,rgba(255,255,255,.07)); }
        .va-aps-prev-tr:last-child { border-bottom:none; }
        .va-aps-prev-td--img { width:24px; height:24px; background:var(--pv-bg,#070709); border-radius:4px; flex-shrink:0; }
        .va-aps-prev-td--title { flex:1; height:8px; background:var(--pv-border2,rgba(255,255,255,.12)); border-radius:3px; }
        .va-aps-prev-td--price { width:36px; height:8px; background:var(--pv-accent,#ff2020); opacity:.6; border-radius:3px; }

        @media (max-width:1100px) {
            .va-aps-main { grid-template-columns:1fr; }
            .va-aps-preview-col { position:static; }
        }
        @media (max-width:680px) {
            .va-aps-presets-grid { gap:6px; }
            .va-aps-preset { min-width:130px; }
        }
        </style>

        <script>
        (function () {
            const form    = document.getElementById('va-aps-form');
            const sidebar = document.getElementById('va-aps-prev-sidebar');
            const topbar  = document.getElementById('va-aps-prev-topbar');
            const content = document.getElementById('va-aps-prev-content');
            const iconEl  = document.getElementById('va-aps-prev-icon');
            const nameEl  = document.getElementById('va-aps-prev-name');
            if (!form || !sidebar) return;

            const colorMap = {
                'va_ap_color_bg':      '--pv-bg',
                'va_ap_color_bg2':     '--pv-bg2',
                'va_ap_color_bg3':     '--pv-bg3',
                'va_ap_color_bg4':     '--pv-bg4',
                'va_ap_color_text':    '--pv-text',
                'va_ap_color_muted':   '--pv-muted',
                'va_ap_color_accent':  '--pv-accent',
                'va_ap_color_accent2': '--pv-accent2',
                'va_ap_color_border':  '--pv-border',
                'va_ap_color_border2': '--pv-border2',
            };
            const allPrevEls = [sidebar, topbar, content].filter(Boolean);

            function sync() {
                for (const [key, prop] of Object.entries(colorMap)) {
                    const el = form.querySelector('[name="' + key + '"]');
                    if (!el) continue;
                    const val = el.value;
                    allPrevEls.forEach(n => n && n.style.setProperty(prop, val));
                }
                const r = form.querySelector('[name="va_ap_radius"]');
                if (r && content) content.style.setProperty('--pv-radius', r.value + 'px');

                const icon = form.querySelector('[name="va_ap_panel_icon"]');
                const name = form.querySelector('[name="va_ap_panel_name"]');
                if (iconEl && icon && icon.value) iconEl.textContent = icon.value;
                if (nameEl && name && name.value) nameEl.textContent = name.value;
            }

            form.addEventListener('input',  sync);
            form.addEventListener('change', sync);
        })();
        </script>
        <?php
    }

    /* ══ Hirdetés beállítások ═════════════════════════════ */
    public static function render_listings() {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $submit_page = get_page_by_path( 'va-hirdetes-feladas' );
        $submit_url = $submit_page ? get_permalink( $submit_page ) : home_url( '/va-hirdetes-feladas/' );
        $callback_example = add_query_arg(
            [
                'va_payment' => 'success',
                'token'      => '{TOKEN}',
            ],
            $submit_url
        );

        $cancel_example = add_query_arg(
            [
                'va_payment' => 'cancel',
                'token'      => '{TOKEN}',
            ],
            $submit_url
        );
        ?>
        <div class="wrap va-admin-wrap">
            <h1>📋 VadászApró – Hirdetés beállítások</h1>
            <?php settings_errors( 'va_listing_settings' ); ?>
            <form method="post" action="options.php">
                <?php settings_fields( 'va_listing_settings' ); ?>
                <h2>Alap díjazás</h2>
                <table class="form-table">
                    <?php self::field_num( 'va_featured_price', 'Kiemelt hirdetés ára (Ft)', 0, 99999 ); ?>
                    <?php self::field_num( 'va_featured_days',  'Kiemelt hirdetés időtartama (nap)', 1, 365 ); ?>
                    <?php self::field_num( 'va_free_listings_limit', 'Ingyenes hirdetések száma felhasználónként (0=korlátlan)', 0, 999 ); ?>
                    <?php self::field_num( 'va_listing_price_after_free', 'További hirdetés ára (Ft)', 0, 999999 ); ?>
                    <?php self::field_url( 'va_listing_payment_url', 'Bankkártyás fizetési URL (Stripe/Barion/OTP Simple checkout link)' ); ?>
                </table>

                <h2>Fizetési beállítások (szolgáltatóhoz)</h2>
                <table class="form-table">
                    <?php self::field_select( 'va_payment_provider', 'Fizetési szolgáltató', [
                        'none'      => 'Nincs még szolgáltató',
                        'barion'    => 'Barion',
                        'stripe'    => 'Stripe',
                        'simplepay' => 'OTP SimplePay',
                        'custom'    => 'Egyedi szolgáltató',
                    ] ); ?>
                    <?php self::field_select( 'va_payment_mode', 'Fizetési mód', [ 'test' => 'Teszt', 'live' => 'Éles' ] ); ?>
                    <?php self::field_text( 'va_payment_public_key', 'Publikus kulcs / Merchant ID' ); ?>
                    <?php self::field_text( 'va_payment_secret_key', 'Titkos kulcs (API Secret)' ); ?>
                    <?php self::field_text( 'va_payment_webhook_secret', 'Webhook aláírás kulcs' ); ?>
                    <?php self::field_url( 'va_payment_success_url', 'Sikeres fizetés URL (opcionális, üresen automatikus)' ); ?>
                    <?php self::field_url( 'va_payment_cancel_url', 'Megszakított fizetés URL (opcionális, üresen automatikus)' ); ?>
                    <tr>
                        <th>Automatikus callback minta</th>
                        <td><input type="text" readonly class="regular-text code" value="<?php echo esc_attr( $callback_example ); ?>"></td>
                    </tr>
                    <tr>
                        <th>Automatikus cancel minta</th>
                        <td><input type="text" readonly class="regular-text code" value="<?php echo esc_attr( $cancel_example ); ?>"></td>
                    </tr>
                </table>

                <h2>Számlázási beállítások</h2>
                <table class="form-table">
                    <?php self::field_text( 'va_billing_company_name', 'Számlakiállító neve' ); ?>
                    <?php self::field_text( 'va_billing_company_address', 'Számlakiállító címe' ); ?>
                    <?php self::field_text( 'va_billing_tax_number', 'Adószám' ); ?>
                    <?php self::field_email( 'va_billing_email', 'Számlázási e-mail' ); ?>
                    <?php self::field_text( 'va_billing_phone', 'Számlázási telefonszám' ); ?>
                    <?php self::field_text( 'va_invoice_prefix', 'Számlaszám előtag (pl. VA)' ); ?>
                    <?php self::field_num( 'va_invoice_next_number', 'Következő számlasorszám', 1, 99999999 ); ?>
                    <?php self::field_text( 'va_invoice_footer_note', 'Számla lábléc megjegyzés' ); ?>
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

        if ( isset( $_POST['va_user_role_nonce'], $_POST['va_user_id'], $_POST['va_user_role'] )
            && wp_verify_nonce( sanitize_text_field( wp_unslash( (string) $_POST['va_user_role_nonce'] ) ), 'va_update_user_role' ) ) {

            $user_id = absint( wp_unslash( (string) $_POST['va_user_id'] ) );
            $new_role = sanitize_key( wp_unslash( (string) $_POST['va_user_role'] ) );

            $roles = wp_roles()->roles;
            $allowed_roles = array_diff( array_keys( $roles ), [ 'administrator' ] );

            if ( $user_id > 0 && in_array( $new_role, $allowed_roles, true ) ) {
                $target_user = get_user_by( 'id', $user_id );
                if ( $target_user instanceof WP_User ) {
                    $target_user->set_role( $new_role );
                    add_settings_error( 'va_users', 'va_users_role_ok', 'Szerepkör sikeresen frissítve.', 'updated' );
                }
            } else {
                add_settings_error( 'va_users', 'va_users_role_err', 'Érvénytelen szerepkör vagy felhasználó.', 'error' );
            }
        }

        $auctions_enabled = function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : true;
        $roles = wp_roles()->roles;
        $allowed_roles = array_diff( array_keys( $roles ), [ 'administrator' ] );

        $users = get_users([
            'role__not_in' => [ 'administrator' ],
            'number'     => 50,
            'orderby'    => 'registered',
            'order'      => 'DESC',
        ]);
        ?>
        <div class="wrap va-admin-wrap">
            <h1>👤 VadászApró – Felhasználók</h1>
            <?php settings_errors( 'va_users' ); ?>
            <table class="wp-list-table widefat striped va-users-table">
                <thead>
                    <tr>
                        <th>Felhasználónév</th>
                        <th>Név</th>
                        <th>E-mail</th>
                        <th>Szerepkör</th>
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
                        <td>
                            <form method="post" style="margin:0;">
                                <?php wp_nonce_field( 'va_update_user_role', 'va_user_role_nonce' ); ?>
                                <input type="hidden" name="va_user_id" value="<?php echo esc_attr( (string) $user->ID ); ?>">
                                <div style="overflow:hidden;width:100%;box-sizing:border-box;">
                                <select name="va_user_role" onchange="this.form.submit()" style="cursor:pointer;width:100%;box-sizing:border-box;">
                                    <?php
                                    $current_role = ! empty( $user->roles ) ? (string) $user->roles[0] : 'subscriber';
                                    foreach ( $allowed_roles as $role_key ):
                                        $role_name = isset( $roles[ $role_key ]['name'] ) ? (string) $roles[ $role_key ]['name'] : $role_key;
                                    ?>
                                        <option value="<?php echo esc_attr( $role_key ); ?>" <?php selected( $current_role, $role_key ); ?>>
                                            <?php echo esc_html( $role_name ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                </div>
                            </form>
                        </td>
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
        global $wpdb;
        $auctions_enabled = function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : true;

        /* ── Hirdetés KPI ─────────────────────────────────────── */
        $lc        = wp_count_posts( 'va_listing' );
        $published = (int)( $lc->publish  ?? 0 );
        $pending   = (int)( $lc->pending  ?? 0 );
        $draft     = (int)( $lc->draft    ?? 0 );

        $ac              = $auctions_enabled ? wp_count_posts( 'va_auction' ) : null;
        $auctions_active = (int)( $ac->publish ?? 0 );

        $uc          = count_users();
        $total_users = (int)( $uc['total_users'] ?? 0 );

        $total_bids      = (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}va_bids" );
        $total_watchlist = (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}va_watchlist" );

        $total_views = (int)$wpdb->get_var( $wpdb->prepare(
            "SELECT SUM( CAST( meta_value AS UNSIGNED ) ) FROM {$wpdb->postmeta} WHERE meta_key = %s",
            'va_views'
        ) );

        $today = current_time( 'Y-m-d' );
        $today_listings = (int)$wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type = %s AND post_status IN ('publish','pending') AND DATE(post_date) = %s",
            'va_listing', $today
        ) );

        $this_month_listings = (int)$wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type = %s AND post_status IN ('publish','pending')
             AND YEAR(post_date) = %d AND MONTH(post_date) = %d",
            'va_listing', (int)date('Y'), (int)date('m')
        ) );

        $this_week_users = count( get_users([
            'number'     => 999,
            'date_query' => [[ 'after' => '7 days ago', 'column' => 'user_registered' ]],
        ]) );

        /* ── Ár statisztikák (wp_va_listing_meta ha létezik) ─── */
        $lmeta_table = $wpdb->prefix . 'va_listing_meta';
        $listing_meta_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $lmeta_table ) ) === $lmeta_table;
        $avg_price = $min_price = $max_price = 0;
        $price_ranges = [];
        if ( $listing_meta_exists ) {
            $ps = $wpdb->get_row( "SELECT AVG(price) as a, MIN(price) as mi, MAX(price) as ma FROM {$lmeta_table} WHERE price > 0" );
            $avg_price = (int)round( (float)( $ps->a  ?? 0 ) );
            $min_price = (int)( $ps->mi ?? 0 );
            $max_price = (int)( $ps->ma ?? 0 );
            $price_ranges = [
                '0–10 e Ft'     => (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$lmeta_table} WHERE price > 0 AND price <= 10000" ),
                '10–50 e Ft'    => (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$lmeta_table} WHERE price > 10000 AND price <= 50000" ),
                '50–200 e Ft'   => (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$lmeta_table} WHERE price > 50000 AND price <= 200000" ),
                '200–500 e Ft'  => (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$lmeta_table} WHERE price > 200000 AND price <= 500000" ),
                '500 e+ Ft'     => (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$lmeta_table} WHERE price > 500000" ),
            ];
        }

        /* ── 30 napos aktivitás ───────────────────────────────── */
        $days30 = [];
        for ( $i = 29; $i >= 0; $i-- ) $days30[ date( 'Y-m-d', strtotime( "-{$i} days" ) ) ] = 0;
        $rows30 = $wpdb->get_results( $wpdb->prepare(
            "SELECT DATE(post_date) as d, COUNT(*) as cnt FROM {$wpdb->posts}
             WHERE post_type = %s AND post_status IN ('publish','pending')
             AND post_date >= %s GROUP BY DATE(post_date)",
            'va_listing', date( 'Y-m-d', strtotime( '-29 days' ) )
        ), ARRAY_A );
        foreach ( $rows30 as $r ) { if ( isset( $days30[$r['d']] ) ) $days30[$r['d']] = (int)$r['cnt']; }

        /* ── 30 napos regisztráció ────────────────────────────── */
        $reg30 = [];
        for ( $i = 29; $i >= 0; $i-- ) $reg30[ date( 'Y-m-d', strtotime( "-{$i} days" ) ) ] = 0;
        $reg_rows = $wpdb->get_results(
            "SELECT DATE(user_registered) as d, COUNT(*) as cnt FROM {$wpdb->users}
             WHERE user_registered >= '" . esc_sql( date( 'Y-m-d', strtotime( '-29 days' ) ) ) . "'
             GROUP BY DATE(user_registered)",
            ARRAY_A
        );
        foreach ( $reg_rows as $r ) { if ( isset( $reg30[$r['d']] ) ) $reg30[$r['d']] = (int)$r['cnt']; }

        /* ── Top kategóriák ──────────────────────────────────── */
        $top_cats = get_terms(['taxonomy'=>'va_category','orderby'=>'count','order'=>'DESC','number'=>12,'hide_empty'=>false]);
        if ( is_wp_error($top_cats) ) $top_cats = [];

        /* ── Top megyék ──────────────────────────────────────── */
        $top_counties = get_terms(['taxonomy'=>'va_county','orderby'=>'count','order'=>'DESC','number'=>20,'hide_empty'=>false]);
        if ( is_wp_error($top_counties) ) $top_counties = [];

        /* ── Top megtekintett hirdetések ─────────────────────── */
        $top_viewed = get_posts([
            'post_type'      => 'va_listing',
            'post_status'    => 'publish',
            'meta_key'       => 'va_views',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'posts_per_page' => 10,
            'no_found_rows'  => true,
        ]);

        /* ── Top watchlistezett hirdetések ───────────────────── */
        $top_wl_rows = $wpdb->get_results(
            "SELECT post_id, COUNT(*) as cnt FROM {$wpdb->prefix}va_watchlist
             GROUP BY post_id ORDER BY cnt DESC LIMIT 10",
            ARRAY_A
        );
        foreach ( $top_wl_rows as &$wl ) {
            $wl['title'] = get_the_title( $wl['post_id'] );
            $wl['url']   = get_permalink( $wl['post_id'] );
        }
        unset($wl);

        /* ── Top hirdetők ────────────────────────────────────── */
        $top_posters = $wpdb->get_results( $wpdb->prepare(
            "SELECT post_author, COUNT(*) as cnt FROM {$wpdb->posts}
             WHERE post_type = %s AND post_status = 'publish'
             GROUP BY post_author ORDER BY cnt DESC LIMIT 10",
            'va_listing'
        ), ARRAY_A );

        /* ── Legutóbbi regisztrációk ──────────────────────────── */
        $recent_regs = get_users(['number'=>10,'orderby'=>'registered','order'=>'DESC']);

        /* ── Legutóbbi hirdetések ─────────────────────────────── */
        $recent_listings = get_posts([
            'post_type'      => 'va_listing',
            'post_status'    => ['publish','pending'],
            'posts_per_page' => 10,
            'no_found_rows'  => true,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        /* ── Aukció extra ────────────────────────────────────── */
        $top_auctions = [];
        $total_bids_value = 0;
        if ( $auctions_enabled ) {
            $total_bids_value = (float)$wpdb->get_var( "SELECT SUM(amount) FROM {$wpdb->prefix}va_bids" );
            $top_auctions = $wpdb->get_results(
                "SELECT post_id, COUNT(*) as bid_cnt, MAX(amount) as max_bid
                 FROM {$wpdb->prefix}va_bids GROUP BY post_id ORDER BY bid_cnt DESC LIMIT 8",
                ARRAY_A
            );
        }

        /* ── JSON adatok Chart.js-hez ────────────────────────── */
        $ch_day_lbl  = wp_json_encode( array_map( fn($d) => date('m.d', strtotime($d)), array_keys($days30) ) );
        $ch_day_data = wp_json_encode( array_values($days30) );
        $ch_reg_data = wp_json_encode( array_values($reg30) );
        $ch_cat_lbl  = wp_json_encode( array_map( fn($t) => $t->name,  $top_cats ) );
        $ch_cat_data = wp_json_encode( array_map( fn($t) => (int)$t->count, $top_cats ) );
        $ch_cty_lbl  = wp_json_encode( array_map( fn($t) => $t->name,  $top_counties ) );
        $ch_cty_data = wp_json_encode( array_map( fn($t) => (int)$t->count, $top_counties ) );
        $ch_pr_lbl   = wp_json_encode( array_keys($price_ranges) );
        $ch_pr_data  = wp_json_encode( array_values($price_ranges) );
        $ch_st_lbl   = wp_json_encode( ['Aktív', 'Függő', 'Vázlat'] );
        $ch_st_data  = wp_json_encode( [$published, $pending, $draft] );
        ?>
        <div class="va-stats-page wrap va-admin-wrap">
            <h1>📈 Statisztika</h1>

            <!-- ══ KPI kártyák ═══════════════════════════════════ -->
            <div class="va-st-kpi-grid">
                <?php
                $kpis = [
                    ['icon'=>'📋','num'=>$published,         'label'=>'Aktív hirdetés',     'sub'=>"+{$today_listings} ma · +{$this_month_listings} idén",                         'color'=>'red'],
                    ['icon'=>'⏳','num'=>$pending,            'label'=>'Jóváhagyásra vár',  'sub'=>$draft . ' vázlat',                                                              'color'=>'orange'],
                    ['icon'=>'👥','num'=>$total_users,        'label'=>'Regisztrált user',   'sub'=>"+{$this_week_users} az elmúlt 7 napban",                                       'color'=>'blue'],
                    ['icon'=>'👁',  'num'=>number_format($total_views,0,',','&nbsp;'), 'label'=>'Összes megtekintés','sub'=>'Összes hirdetésen',                                 'color'=>'purple'],
                    ['icon'=>'❤️', 'num'=>$total_watchlist,  'label'=>'Figyelőlista',       'sub'=>'mentett hirdetés összesen',                                                    'color'=>'pink'],
                ];
                if ($auctions_enabled) {
                    $kpis[] = ['icon'=>'🔨','num'=>$auctions_active,'label'=>'Aktív aukció','sub'=>$total_bids . ' licit · ' . number_format($total_bids_value,0,',','&nbsp;') . ' Ft forgalom','color'=>'green'];
                }
                foreach ($kpis as $k): ?>
                <div class="va-st-kpi va-st-kpi--<?php echo esc_attr($k['color']); ?>">
                    <div class="va-st-kpi__icon"><?php echo $k['icon']; ?></div>
                    <div class="va-st-kpi__num"><?php echo $k['num']; ?></div>
                    <div class="va-st-kpi__label"><?php echo esc_html($k['label']); ?></div>
                    <div class="va-st-kpi__sub"><?php echo wp_kses_post($k['sub']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ══ Ár blokk ══════════════════════════════════════ -->
            <?php if ($avg_price > 0): ?>
            <div class="va-st-price-strip">
                <div class="va-st-price-item">
                    <span class="va-st-price-lbl">Átlagár</span>
                    <span class="va-st-price-val"><?php echo number_format($avg_price,0,',','&nbsp;'); ?> Ft</span>
                </div>
                <div class="va-st-price-sep">|</div>
                <div class="va-st-price-item">
                    <span class="va-st-price-lbl">Legalacsonyabb</span>
                    <span class="va-st-price-val"><?php echo number_format($min_price,0,',','&nbsp;'); ?> Ft</span>
                </div>
                <div class="va-st-price-sep">|</div>
                <div class="va-st-price-item">
                    <span class="va-st-price-lbl">Legmagasabb</span>
                    <span class="va-st-price-val va-st-price-val--hi"><?php echo number_format($max_price,0,',','&nbsp;'); ?> Ft</span>
                </div>
            </div>
            <?php endif; ?>

            <!-- ══ Grafikonok sor 1 ═══════════════════════════════ -->
            <div class="va-st-charts-row">
                <div class="va-st-chart-card va-st-chart-card--wide">
                    <div class="va-st-chart-hdr">
                        <div><h3>Hirdetési aktivitás</h3><p>Utolsó 30 nap – napi új feladások</p></div>
                        <span class="va-st-badge"><?php echo array_sum(array_values($days30)); ?> hirdetés / 30 nap</span>
                    </div>
                    <div class="va-st-chart-body" style="height:200px;">
                        <canvas id="va-st-bar"></canvas>
                    </div>
                </div>
                <div class="va-st-chart-card">
                    <div class="va-st-chart-hdr">
                        <div><h3>Új regisztrációk</h3><p>Utolsó 30 nap</p></div>
                        <span class="va-st-badge"><?php echo array_sum(array_values($reg30)); ?> / 30 nap</span>
                    </div>
                    <div class="va-st-chart-body" style="height:200px;">
                        <canvas id="va-st-reg"></canvas>
                    </div>
                </div>
            </div>

            <!-- ══ Grafikonok sor 2 ═══════════════════════════════ -->
            <div class="va-st-charts-row">
                <?php if (!empty($top_cats)): ?>
                <div class="va-st-chart-card va-st-chart-card--wide">
                    <div class="va-st-chart-hdr">
                        <div><h3>Top kategóriák</h3><p>Hirdetésszám alapján</p></div>
                    </div>
                    <div class="va-st-chart-body" style="height:220px;">
                        <canvas id="va-st-cat"></canvas>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($top_counties)): ?>
                <div class="va-st-chart-card">
                    <div class="va-st-chart-hdr">
                        <div><h3>Top megyék</h3><p>Hirdetésszám alapján</p></div>
                    </div>
                    <div class="va-st-chart-body" style="height:220px;">
                        <canvas id="va-st-county"></canvas>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ══ Grafikonok sor 3 – státusz + ár ═══════════════ -->
            <div class="va-st-charts-row va-st-charts-row--sm">
                <div class="va-st-chart-card">
                    <div class="va-st-chart-hdr"><div><h3>Hirdetés státuszok</h3><p>Jelenlegi eloszlás</p></div></div>
                    <div class="va-st-chart-body va-st-chart-body--donut">
                        <canvas id="va-st-status" width="140" height="140"></canvas>
                        <div class="va-st-donut-legend">
                            <?php foreach (['Aktív'=>['#ff4444',$published],'Függő'=>['#ff8c42',$pending],'Vázlat'=>['#4da6ff',$draft]] as $lbl=>[$col,$val]): ?>
                            <div class="va-st-leg-row">
                                <span class="va-st-leg-dot" style="background:<?php echo $col; ?>"></span>
                                <span class="va-st-leg-name"><?php echo esc_html($lbl); ?></span>
                                <span class="va-st-leg-val"><?php echo $val; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php if (!empty($price_ranges)): ?>
                <div class="va-st-chart-card">
                    <div class="va-st-chart-hdr"><div><h3>Ár sávok</h3><p>Hirdetések ár szerint csoportosítva</p></div></div>
                    <div class="va-st-chart-body va-st-chart-body--donut">
                        <canvas id="va-st-price" width="140" height="140"></canvas>
                        <div class="va-st-donut-legend" id="va-st-price-legend"></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- ══ Táblázatok ─────────────────────────────────── -->
            <div class="va-st-tables-row">

                <!-- Top megtekintett -->
                <div class="va-st-panel">
                    <div class="va-st-panel__hdr"><h3>👁 Top 10 megtekintett hirdetés</h3></div>
                    <table class="va-st-table">
                        <thead><tr><th>#</th><th>Hirdetés</th><th>Kat.</th><th>Megtekintés</th><th>Feladva</th></tr></thead>
                        <tbody>
                        <?php foreach ($top_viewed as $idx => $p):
                            $views = (int)get_post_meta($p->ID, 'va_views', true);
                            $cats  = get_the_terms($p->ID, 'va_category');
                            $cat   = (!is_wp_error($cats) && $cats) ? $cats[0]->name : '–';
                        ?>
                        <tr>
                            <td class="va-st-rank"><?php echo $idx+1; ?></td>
                            <td><a href="<?php echo esc_url(get_permalink($p->ID)); ?>" target="_blank"><?php echo esc_html(wp_trim_words($p->post_title,6,'…')); ?></a></td>
                            <td><span class="va-st-tag"><?php echo esc_html($cat); ?></span></td>
                            <td><strong class="va-st-accent"><?php echo number_format($views,0,',','&nbsp;'); ?></strong></td>
                            <td class="va-st-muted"><?php echo esc_html(date_i18n('Y.m.d', strtotime($p->post_date))); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Top hirdetők -->
                <div class="va-st-panel">
                    <div class="va-st-panel__hdr"><h3>🏆 Top 10 hirdető</h3></div>
                    <table class="va-st-table">
                        <thead><tr><th>#</th><th>Felhasználó</th><th>E-mail</th><th>Hirdetés</th></tr></thead>
                        <tbody>
                        <?php foreach ($top_posters as $idx => $row):
                            $u = get_userdata($row['post_author']);
                            if (!$u) continue;
                        ?>
                        <tr>
                            <td class="va-st-rank"><?php echo $idx+1; ?></td>
                            <td><strong><?php echo esc_html($u->display_name); ?></strong></td>
                            <td class="va-st-muted"><?php echo esc_html($u->user_email); ?></td>
                            <td><strong class="va-st-accent"><?php echo (int)$row['cnt']; ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="va-st-tables-row">

                <!-- Top watchlist -->
                <?php if (!empty($top_wl_rows)): ?>
                <div class="va-st-panel">
                    <div class="va-st-panel__hdr"><h3>❤️ Top 10 mentett hirdetés (watchlist)</h3></div>
                    <table class="va-st-table">
                        <thead><tr><th>#</th><th>Hirdetés</th><th>Mentések</th></tr></thead>
                        <tbody>
                        <?php foreach ($top_wl_rows as $idx => $wl): ?>
                        <tr>
                            <td class="va-st-rank"><?php echo $idx+1; ?></td>
                            <td><a href="<?php echo esc_url($wl['url']); ?>" target="_blank"><?php echo esc_html(wp_trim_words($wl['title'],8,'…')); ?></a></td>
                            <td><strong class="va-st-accent"><?php echo (int)$wl['cnt']; ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Legutóbbi regisztrációk -->
                <div class="va-st-panel">
                    <div class="va-st-panel__hdr"><h3>🆕 Legutóbbi 10 regisztráció</h3></div>
                    <table class="va-st-table">
                        <thead><tr><th>Név</th><th>E-mail</th><th>Regisztrált</th></tr></thead>
                        <tbody>
                        <?php foreach ($recent_regs as $u): ?>
                        <tr>
                            <td><strong><?php echo esc_html($u->display_name); ?></strong></td>
                            <td class="va-st-muted"><?php echo esc_html($u->user_email); ?></td>
                            <td class="va-st-muted"><?php echo esc_html(date_i18n('Y.m.d H:i', strtotime($u->user_registered))); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Legutóbbi hirdetések -->
            <div class="va-st-panel va-st-panel--full">
                <div class="va-st-panel__hdr"><h3>🕐 Legutóbbi 10 hirdetés</h3></div>
                <table class="va-st-table">
                    <thead><tr><th>Cím</th><th>Hirdető</th><th>Kategória</th><th>Ár</th><th>Státusz</th><th>Dátum</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($recent_listings as $p):
                        $author = get_userdata($p->post_author);
                        $cats   = get_the_terms($p->ID, 'va_category');
                        $cat    = (!is_wp_error($cats) && $cats) ? $cats[0]->name : '–';
                        $price  = (float)get_post_meta($p->ID, 'va_price', true);
                        $status_map = ['publish'=>['Aktív','#4ade80'],'pending'=>['Függő','#fb923c'],'draft'=>['Vázlat','#94a3b8']];
                        [$st_label, $st_color] = $status_map[$p->post_status] ?? ['–','#666'];
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html(wp_trim_words($p->post_title,7,'…')); ?></strong></td>
                        <td class="va-st-muted"><?php echo $author ? esc_html($author->display_name) : '–'; ?></td>
                        <td><span class="va-st-tag"><?php echo esc_html($cat); ?></span></td>
                        <td><?php echo $price > 0 ? '<span class="va-st-price">' . number_format($price,0,',','&nbsp;') . ' Ft</span>' : '<span class="va-st-muted">—</span>'; ?></td>
                        <td><span class="va-st-status-dot" style="color:<?php echo $st_color; ?>"><?php echo esc_html($st_label); ?></span></td>
                        <td class="va-st-muted"><?php echo esc_html(date_i18n('m.d H:i', strtotime($p->post_date))); ?></td>
                        <td><a href="<?php echo esc_url(admin_url("post.php?action=edit&post={$p->ID}")); ?>" class="va-st-edit-btn">Szerk.</a></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($auctions_enabled && !empty($top_auctions)): ?>
            <!-- Aukció statisztikák -->
            <div class="va-st-panel va-st-panel--full">
                <div class="va-st-panel__hdr"><h3>🔨 Legtöbb licitet kapott aukciók</h3></div>
                <table class="va-st-table">
                    <thead><tr><th>Aukció</th><th>Licitek száma</th><th>Legmagasabb licit</th></tr></thead>
                    <tbody>
                    <?php foreach ($top_auctions as $a):
                        $title = get_the_title($a['post_id']);
                        $url   = get_permalink($a['post_id']);
                    ?>
                    <tr>
                        <td><a href="<?php echo esc_url($url); ?>" target="_blank"><?php echo esc_html(wp_trim_words($title,8,'…')); ?></a></td>
                        <td><strong class="va-st-accent"><?php echo (int)$a['bid_cnt']; ?></strong></td>
                        <td class="va-st-price"><?php echo number_format((float)$a['max_bid'],0,',','&nbsp;'); ?> Ft</td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

        </div><!-- .va-stats-page -->

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            Chart.defaults.color = 'rgba(255,255,255,.5)';
            Chart.defaults.font.family = '"Montserrat", system-ui, sans-serif';
            Chart.defaults.font.size = 11;

            const palette = ['#ff2222','#ff6b35','#ffd166','#06d6a0','#118ab2','#9b5de5','#f72585','#4cc9f0','#80b918','#e07a5f','#3d405b','#81b29a'];

            const tooltipDark = {
                backgroundColor: 'rgba(12,12,18,.95)',
                borderColor: 'rgba(255,0,0,.35)',
                borderWidth: 1,
                padding: 10,
                titleColor: '#fff',
                bodyColor: 'rgba(255,255,255,.7)',
            };

            function gradientRed(ctx, chart) {
                const g = ctx.createLinearGradient(0, 0, 0, chart.chartArea?.bottom || 200);
                g.addColorStop(0, 'rgba(255,0,0,.7)');
                g.addColorStop(1, 'rgba(255,0,0,.1)');
                return g;
            }

            /* Bar – hirdetési aktivitás */
            const ctxBar = document.getElementById('va-st-bar');
            if (ctxBar) new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: <?php echo $ch_day_lbl; ?>,
                    datasets: [{
                        label: 'Új hirdetés',
                        data: <?php echo $ch_day_data; ?>,
                        backgroundColor: 'rgba(255,0,0,.55)',
                        borderColor: 'rgba(255,60,60,.9)',
                        borderWidth: 1.5,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { ...tooltipDark, callbacks: { label: c => ` ${c.parsed.y} db` } } },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { maxTicksLimit: 10 } },
                        y: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { precision: 0, stepSize: 1 }, beginAtZero: true }
                    }
                }
            });

            /* Line – regisztrációk */
            const ctxReg = document.getElementById('va-st-reg');
            if (ctxReg) new Chart(ctxReg, {
                type: 'line',
                data: {
                    labels: <?php echo $ch_day_lbl; ?>,
                    datasets: [{
                        label: 'Regisztráció',
                        data: <?php echo $ch_reg_data; ?>,
                        borderColor: '#4da6ff',
                        backgroundColor: 'rgba(77,166,255,.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#4da6ff',
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { ...tooltipDark, callbacks: { label: c => ` ${c.parsed.y} fő` } } },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { maxTicksLimit: 10 } },
                        y: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { precision: 0, stepSize: 1 }, beginAtZero: true }
                    }
                }
            });

            /* Horizontal Bar – kategóriák */
            const ctxCat = document.getElementById('va-st-cat');
            if (ctxCat && <?php echo $ch_cat_lbl; ?>.length) new Chart(ctxCat, {
                type: 'bar',
                data: {
                    labels: <?php echo $ch_cat_lbl; ?>,
                    datasets: [{
                        label: 'Hirdetés',
                        data: <?php echo $ch_cat_data; ?>,
                        backgroundColor: palette,
                        borderRadius: 5,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { ...tooltipDark } },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { precision: 0 }, beginAtZero: true },
                        y: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,.6)' } }
                    }
                }
            });

            /* Horizontal Bar – megyék */
            const ctxCty = document.getElementById('va-st-county');
            if (ctxCty && <?php echo $ch_cty_lbl; ?>.length) new Chart(ctxCty, {
                type: 'bar',
                data: {
                    labels: <?php echo $ch_cty_lbl; ?>,
                    datasets: [{
                        label: 'Hirdetés',
                        data: <?php echo $ch_cty_data; ?>,
                        backgroundColor: 'rgba(77,166,255,.65)',
                        borderColor: 'rgba(77,166,255,.9)',
                        borderWidth: 1,
                        borderRadius: 5,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { ...tooltipDark } },
                    scales: {
                        x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { precision: 0 }, beginAtZero: true },
                        y: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,.6)' } }
                    }
                }
            });

            /* Doughnut – státusz */
            const ctxSt = document.getElementById('va-st-status');
            if (ctxSt) new Chart(ctxSt, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $ch_st_lbl; ?>,
                    datasets: [{ data: <?php echo $ch_st_data; ?>, backgroundColor: ['#ff4444','#ff8c42','#4da6ff'], borderColor: '#0d0d11', borderWidth: 3, hoverOffset: 5 }]
                },
                options: { cutout: '68%', plugins: { legend: { display: false }, tooltip: { ...tooltipDark } } }
            });

            /* Doughnut – ár sávok */
            const ctxPr = document.getElementById('va-st-price');
            if (ctxPr) {
                const prLabels = <?php echo $ch_pr_lbl; ?>;
                const prData   = <?php echo $ch_pr_data; ?>;
                const prTotal  = prData.reduce((a,b)=>a+b,0);
                new Chart(ctxPr, {
                    type: 'doughnut',
                    data: {
                        labels: prLabels,
                        datasets: [{ data: prData, backgroundColor: ['#4ade80','#06d6a0','#4da6ff','#9b5de5','#ff4444'], borderColor: '#0d0d11', borderWidth: 3, hoverOffset: 5 }]
                    },
                    options: { cutout: '68%', plugins: { legend: { display: false }, tooltip: { ...tooltipDark, callbacks: { label: c => ` ${c.label}: ${c.parsed} db (${prTotal>0?Math.round(c.parsed/prTotal*100):0}%)` } } } }
                });
                const leg = document.getElementById('va-st-price-legend');
                if (leg) leg.innerHTML = prLabels.map((l,i)=>`<div class="va-st-leg-row"><span class="va-st-leg-dot" style="background:${['#4ade80','#06d6a0','#4da6ff','#9b5de5','#ff4444'][i]}"></span><span class="va-st-leg-name">${l}</span><span class="va-st-leg-val">${prData[i]}</span></div>`).join('');
            }
        });
        </script>
        <?php
    }

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

    public static function handle_apply_hf_preset() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nincs jogosultság.' );
        }
        check_admin_referer( 'va_apply_hf_preset' );

        $preset_key = sanitize_key( (string) ( $_POST['preset_key'] ?? '' ) );
        $presets = self::get_header_footer_presets();

        if ( ! isset( $presets[ $preset_key ] ) ) {
            wp_safe_redirect( add_query_arg( 'va_hf_preset', 'invalid', admin_url( 'admin.php?page=vadaszapro-header-footer' ) ) );
            exit;
        }

        foreach ( $presets[ $preset_key ]['options'] as $key => $value ) {
            if ( strpos( (string) $key, 'va_' ) !== 0 ) {
                continue;
            }
            update_option( (string) $key, $value );
        }

        wp_safe_redirect( add_query_arg( 'va_hf_preset', 'ok', admin_url( 'admin.php?page=vadaszapro-header-footer' ) ) );
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

    /* ── Admin Panel preset alkalmazás ───────────────────── */
    public static function handle_apply_ap_preset(): void {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Nincs jogosultság.' );
        check_admin_referer( 'va_apply_ap_preset' );

        $preset_key = sanitize_key( (string) ( $_POST['preset_key'] ?? '' ) );
        $presets = self::get_adminpanel_presets();

        if ( ! isset( $presets[ $preset_key ] ) ) {
            wp_safe_redirect( add_query_arg( 'va_ap_preset', 'invalid', admin_url( 'admin.php?page=vadaszapro-adminpanel' ) ) );
            exit;
        }

        foreach ( $presets[ $preset_key ]['options'] as $key => $value ) {
            update_option( (string) $key, $value );
        }

        wp_safe_redirect( add_query_arg( 'va_ap_preset', 'ok', admin_url( 'admin.php?page=vadaszapro-adminpanel' ) ) );
        exit;
    }

    public static function handle_apply_single_preset(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Nincs jogosultság.' );
        }
        check_admin_referer( 'va_apply_single_preset' );

        $preset_key = sanitize_key( (string) ( $_POST['preset_key'] ?? '' ) );
        $presets    = self::get_single_presets();

        if ( ! isset( $presets[ $preset_key ] ) ) {
            wp_safe_redirect( add_query_arg( 'va_single_preset', 'invalid', admin_url( 'admin.php?page=vadaszapro-single-designer' ) ) );
            exit;
        }

        foreach ( $presets[ $preset_key ]['options'] as $key => $value ) {
            if ( strpos( (string) $key, 'va_single_' ) !== 0 ) {
                continue;
            }
            update_option( (string) $key, $value );
        }

        update_option( 'va_single_preset', $preset_key );
        wp_safe_redirect( add_query_arg( 'va_single_preset', 'ok', admin_url( 'admin.php?page=vadaszapro-single-designer' ) ) );
        exit;
    }

    private static function get_single_presets(): array {
        return [
            'cinematic' => [
                'label' => 'Cinematic Hero',
                'desc'  => 'Nagy cím, hangsúlyos galéria, erős kontraszt.',
                'options' => [
                    'va_single_layout_mode'        => 'split',
                    'va_single_content_max'        => 1380,
                    'va_single_sidebar_width'      => 410,
                    'va_single_layout_gap'         => 28,
                    'va_single_gallery_ratio'      => '16/10',
                    'va_single_gallery_fit'        => 'cover',
                    'va_single_thumb_size'         => 88,
                    'va_single_card_radius'        => 15,
                    'va_single_card_padding'       => 24,
                    'va_single_title_size'         => 46,
                    'va_single_price_size'         => 46,
                    'va_single_meta_size'          => 13,
                    'va_single_btn_height'         => 50,
                    'va_single_share_size'         => 40,
                    'va_single_mobile_title_scale' => 76,
                    'va_single_viewer_bg'          => 'rgba(4,4,4,.97)',
                    'va_single_accent'             => '#ff2a2a',
                    'va_single_glass'              => 'rgba(255,255,255,.07)',
                    'va_single_border'             => 'rgba(255,255,255,.12)',
                ],
            ],
            'compact_trade' => [
                'label' => 'Compact Trade',
                'desc'  => 'Sűrűbb, mobilon gyorsan áttekinthető.',
                'options' => [
                    'va_single_layout_mode'        => 'split',
                    'va_single_content_max'        => 1260,
                    'va_single_sidebar_width'      => 360,
                    'va_single_layout_gap'         => 18,
                    'va_single_gallery_ratio'      => '4/3',
                    'va_single_gallery_fit'        => 'cover',
                    'va_single_thumb_size'         => 76,
                    'va_single_card_radius'        => 12,
                    'va_single_card_padding'       => 18,
                    'va_single_title_size'         => 34,
                    'va_single_price_size'         => 38,
                    'va_single_meta_size'          => 12,
                    'va_single_btn_height'         => 44,
                    'va_single_share_size'         => 36,
                    'va_single_mobile_title_scale' => 82,
                    'va_single_viewer_bg'          => 'rgba(6,6,6,.96)',
                    'va_single_accent'             => '#ff3b30',
                    'va_single_glass'              => 'rgba(255,255,255,.06)',
                    'va_single_border'             => 'rgba(255,255,255,.10)',
                ],
            ],
            'editorial_stack' => [
                'label' => 'Editorial Stack',
                'desc'  => 'Tartalom-first, egyhasábos fókusz.',
                'options' => [
                    'va_single_layout_mode'        => 'stacked',
                    'va_single_content_max'        => 1120,
                    'va_single_sidebar_width'      => 380,
                    'va_single_layout_gap'         => 20,
                    'va_single_gallery_ratio'      => '16/9',
                    'va_single_gallery_fit'        => 'contain',
                    'va_single_thumb_size'         => 80,
                    'va_single_card_radius'        => 13,
                    'va_single_card_padding'       => 22,
                    'va_single_title_size'         => 42,
                    'va_single_price_size'         => 40,
                    'va_single_meta_size'          => 13,
                    'va_single_btn_height'         => 46,
                    'va_single_share_size'         => 38,
                    'va_single_mobile_title_scale' => 86,
                    'va_single_viewer_bg'          => 'rgba(3,3,3,.95)',
                    'va_single_accent'             => '#ff1f1f',
                    'va_single_glass'              => 'rgba(255,255,255,.08)',
                    'va_single_border'             => 'rgba(255,255,255,.13)',
                ],
            ],
        ];
    }

    private static function get_adminpanel_presets(): array {
        return [
            'dark_crimson' => [
                'label' => 'Dark Crimson', 'desc' => 'Alapértelmezett – sötét + piros',
                'bg' => '#070709', 'bg2' => '#0d0d11', 'accent' => '#ff2020', 'accent2' => '#ff5050',
                'options' => [
                    'va_ap_color_bg' => '#070709', 'va_ap_color_bg2' => '#0d0d11', 'va_ap_color_bg3' => '#111118', 'va_ap_color_bg4' => '#161620',
                    'va_ap_color_text' => '#e8e8f0', 'va_ap_color_muted' => 'rgba(255,255,255,.45)',
                    'va_ap_color_accent' => '#ff2020', 'va_ap_color_accent2' => '#ff5050',
                    'va_ap_color_border' => 'rgba(255,255,255,.07)', 'va_ap_color_border2' => 'rgba(255,255,255,.12)',
                ],
            ],
            'midnight_navy' => [
                'label' => 'Midnight Navy', 'desc' => 'Sötétkék + neon kék accent',
                'bg' => '#07090f', 'bg2' => '#0b0f1a', 'accent' => '#3b8af7', 'accent2' => '#6aadff',
                'options' => [
                    'va_ap_color_bg' => '#07090f', 'va_ap_color_bg2' => '#0b0f1a', 'va_ap_color_bg3' => '#101520', 'va_ap_color_bg4' => '#151c2b',
                    'va_ap_color_text' => '#dce8ff', 'va_ap_color_muted' => 'rgba(220,232,255,.45)',
                    'va_ap_color_accent' => '#3b8af7', 'va_ap_color_accent2' => '#6aadff',
                    'va_ap_color_border' => 'rgba(59,138,247,.08)', 'va_ap_color_border2' => 'rgba(59,138,247,.15)',
                ],
            ],
            'forest_command' => [
                'label' => 'Forest Command', 'desc' => 'Mély zöld, vadász feeling',
                'bg' => '#060f0b', 'bg2' => '#0b1910', 'accent' => '#25d468', 'accent2' => '#5de891',
                'options' => [
                    'va_ap_color_bg' => '#060f0b', 'va_ap_color_bg2' => '#0b1910', 'va_ap_color_bg3' => '#0f2018', 'va_ap_color_bg4' => '#142a20',
                    'va_ap_color_text' => '#d4f5e3', 'va_ap_color_muted' => 'rgba(212,245,227,.45)',
                    'va_ap_color_accent' => '#25d468', 'va_ap_color_accent2' => '#5de891',
                    'va_ap_color_border' => 'rgba(37,212,104,.07)', 'va_ap_color_border2' => 'rgba(37,212,104,.14)',
                ],
            ],
            'obsidian_gold' => [
                'label' => 'Obsidian Gold', 'desc' => 'Fekete + arany, prémium',
                'bg' => '#080807', 'bg2' => '#10100d', 'accent' => '#e8b840', 'accent2' => '#f5cf6a',
                'options' => [
                    'va_ap_color_bg' => '#080807', 'va_ap_color_bg2' => '#10100d', 'va_ap_color_bg3' => '#161612', 'va_ap_color_bg4' => '#1e1d17',
                    'va_ap_color_text' => '#f5f0e0', 'va_ap_color_muted' => 'rgba(245,240,224,.45)',
                    'va_ap_color_accent' => '#e8b840', 'va_ap_color_accent2' => '#f5cf6a',
                    'va_ap_color_border' => 'rgba(232,184,64,.07)', 'va_ap_color_border2' => 'rgba(232,184,64,.14)',
                ],
            ],
            'graphite_purple' => [
                'label' => 'Graphite Purple', 'desc' => 'Grafit + lila, tech-SaaS vibe',
                'bg' => '#090910', 'bg2' => '#0f0f19', 'accent' => '#8b5cf6', 'accent2' => '#a78bfa',
                'options' => [
                    'va_ap_color_bg' => '#090910', 'va_ap_color_bg2' => '#0f0f19', 'va_ap_color_bg3' => '#141420', 'va_ap_color_bg4' => '#1a1a28',
                    'va_ap_color_text' => '#e4e4f0', 'va_ap_color_muted' => 'rgba(228,228,240,.42)',
                    'va_ap_color_accent' => '#8b5cf6', 'va_ap_color_accent2' => '#a78bfa',
                    'va_ap_color_border' => 'rgba(139,92,246,.08)', 'va_ap_color_border2' => 'rgba(139,92,246,.16)',
                ],
            ],
            'carbon_steel' => [
                'label' => 'Carbon Steel', 'desc' => 'Tiszta szürke-acél, minimál',
                'bg' => '#0a0a0a', 'bg2' => '#111111', 'accent' => '#64748b', 'accent2' => '#94a3b8',
                'options' => [
                    'va_ap_color_bg' => '#0a0a0a', 'va_ap_color_bg2' => '#111111', 'va_ap_color_bg3' => '#171717', 'va_ap_color_bg4' => '#1e1e1e',
                    'va_ap_color_text' => '#e0e0e0', 'va_ap_color_muted' => 'rgba(224,224,224,.45)',
                    'va_ap_color_accent' => '#64748b', 'va_ap_color_accent2' => '#94a3b8',
                    'va_ap_color_border' => 'rgba(255,255,255,.06)', 'va_ap_color_border2' => 'rgba(255,255,255,.11)',
                ],
            ],
            'copper_dark' => [
                'label' => 'Copper Dark', 'desc' => 'Sötétbarna + réz, prémium outdoor',
                'bg' => '#0a0806', 'bg2' => '#140f0a', 'accent' => '#c97d3e', 'accent2' => '#e0a265',
                'options' => [
                    'va_ap_color_bg' => '#0a0806', 'va_ap_color_bg2' => '#140f0a', 'va_ap_color_bg3' => '#1c160e', 'va_ap_color_bg4' => '#241c13',
                    'va_ap_color_text' => '#f2e8d8', 'va_ap_color_muted' => 'rgba(242,232,216,.44)',
                    'va_ap_color_accent' => '#c97d3e', 'va_ap_color_accent2' => '#e0a265',
                    'va_ap_color_border' => 'rgba(201,125,62,.08)', 'va_ap_color_border2' => 'rgba(201,125,62,.14)',
                ],
            ],
            'steel_ember' => [
                'label' => 'Steel Ember', 'desc' => 'Acélos szürke + izzó narancs',
                'bg' => '#0d0e10', 'bg2' => '#111317', 'accent' => '#ff7a22', 'accent2' => '#ffaa66',
                'options' => [
                    'va_ap_color_bg' => '#0d0e10', 'va_ap_color_bg2' => '#111317', 'va_ap_color_bg3' => '#16181e', 'va_ap_color_bg4' => '#1c1e25',
                    'va_ap_color_text' => '#f0f2f5', 'va_ap_color_muted' => 'rgba(240,242,245,.45)',
                    'va_ap_color_accent' => '#ff7a22', 'va_ap_color_accent2' => '#ffaa66',
                    'va_ap_color_border' => 'rgba(255,122,34,.08)', 'va_ap_color_border2' => 'rgba(255,122,34,.15)',
                ],
            ],
            'arctic_white' => [
                'label' => 'Arctic White', 'desc' => 'Fehér/világos, magas kontraszt',
                'bg' => '#f4f6f9', 'bg2' => '#ffffff', 'accent' => '#ef4444', 'accent2' => '#f87171',
                'options' => [
                    'va_ap_color_bg' => '#f4f6f9', 'va_ap_color_bg2' => '#ffffff', 'va_ap_color_bg3' => '#eef0f5', 'va_ap_color_bg4' => '#e4e7ed',
                    'va_ap_color_text' => '#1a1a2e', 'va_ap_color_muted' => 'rgba(26,26,46,.5)',
                    'va_ap_color_accent' => '#ef4444', 'va_ap_color_accent2' => '#f87171',
                    'va_ap_color_border' => 'rgba(0,0,0,.08)', 'va_ap_color_border2' => 'rgba(0,0,0,.14)',
                ],
            ],
            'royal_plum' => [
                'label' => 'Royal Plum', 'desc' => 'Sötét lila-bordó, exkluzív',
                'bg' => '#12091a', 'bg2' => '#1a0f24', 'accent' => '#c75cff', 'accent2' => '#da98ff',
                'options' => [
                    'va_ap_color_bg' => '#12091a', 'va_ap_color_bg2' => '#1a0f24', 'va_ap_color_bg3' => '#22132e', 'va_ap_color_bg4' => '#2b1a38',
                    'va_ap_color_text' => '#f8f0ff', 'va_ap_color_muted' => 'rgba(248,240,255,.44)',
                    'va_ap_color_accent' => '#c75cff', 'va_ap_color_accent2' => '#da98ff',
                    'va_ap_color_border' => 'rgba(199,92,255,.08)', 'va_ap_color_border2' => 'rgba(199,92,255,.16)',
                ],
            ],
        ];
    }

    private static function get_header_footer_presets(): array {
        return [
            'carbon_red' => [
                'label' => 'Carbon Red',
                'desc'  => 'Fekete karbon alap, tiszta piros glow.',
                'options' => [
                    'va_hf_header_color_base' => '#050505',
                    'va_hf_header_color_alt' => '#1a0a0a',
                    'va_hf_header_border_color' => '#ff2a2a',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.74)',
                    'va_hf_header_glow_color' => 'rgba(255,0,0,.24)',
                    'va_hf_header_search_glow_color' => 'rgba(255,0,0,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(255,0,0,.52)',
                    'va_hf_footer_color_base' => '#090909',
                    'va_hf_footer_color_alt' => '#180808',
                    'va_hf_footer_border_color' => '#ff2a2a',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.38)',
                    'va_hf_footer_glow_color' => 'rgba(255,0,0,.14)',
                    'va_hf_footer_link_hover_color' => '#ffffff',
                    'va_color_header_text' => '#ffffff',
                    'va_color_footer_text' => 'rgba(255,255,255,.76)',
                    'va_color_footer_links' => '#ff4d4d',
                ],
            ],
            'steel_ember' => [
                'label' => 'Steel Ember',
                'desc'  => 'Acélos szürke + izzó narancs hangsúly.',
                'options' => [
                    'va_hf_header_color_base' => '#111317',
                    'va_hf_header_color_alt' => '#25170f',
                    'va_hf_header_border_color' => '#ff7a22',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.70)',
                    'va_hf_header_glow_color' => 'rgba(255,122,34,.20)',
                    'va_hf_header_search_glow_color' => 'rgba(255,122,34,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(255,122,34,.42)',
                    'va_hf_footer_color_base' => '#0d0f13',
                    'va_hf_footer_color_alt' => '#20140f',
                    'va_hf_footer_border_color' => '#ff7a22',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.36)',
                    'va_hf_footer_glow_color' => 'rgba(255,122,34,.12)',
                    'va_hf_footer_link_hover_color' => '#ffd0b0',
                    'va_color_header_text' => '#f5f7fa',
                    'va_color_footer_text' => 'rgba(245,247,250,.76)',
                    'va_color_footer_links' => '#ff9a57',
                ],
            ],
            'night_copper' => [
                'label' => 'Night Copper',
                'desc'  => 'Sotet barna-femes retegzes, premium hangulat.',
                'options' => [
                    'va_hf_header_color_base' => '#0d0a08',
                    'va_hf_header_color_alt' => '#2a1b12',
                    'va_hf_header_border_color' => '#c67a3d',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.72)',
                    'va_hf_header_glow_color' => 'rgba(198,122,61,.20)',
                    'va_hf_header_search_glow_color' => 'rgba(198,122,61,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(198,122,61,.40)',
                    'va_hf_footer_color_base' => '#0e0906',
                    'va_hf_footer_color_alt' => '#20140d',
                    'va_hf_footer_border_color' => '#c67a3d',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.37)',
                    'va_hf_footer_glow_color' => 'rgba(198,122,61,.12)',
                    'va_hf_footer_link_hover_color' => '#ffe2cc',
                    'va_color_header_text' => '#fff5eb',
                    'va_color_footer_text' => 'rgba(255,245,235,.74)',
                    'va_color_footer_links' => '#e4a06d',
                ],
            ],
            'midnight_ice' => [
                'label' => 'Midnight Ice',
                'desc'  => 'Hideg kek-szurke modern, minimal premium.',
                'options' => [
                    'va_hf_header_color_base' => '#080d16',
                    'va_hf_header_color_alt' => '#122339',
                    'va_hf_header_border_color' => '#57b0ff',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.72)',
                    'va_hf_header_glow_color' => 'rgba(87,176,255,.20)',
                    'va_hf_header_search_glow_color' => 'rgba(87,176,255,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(87,176,255,.40)',
                    'va_hf_footer_color_base' => '#070b12',
                    'va_hf_footer_color_alt' => '#102034',
                    'va_hf_footer_border_color' => '#57b0ff',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.38)',
                    'va_hf_footer_glow_color' => 'rgba(87,176,255,.12)',
                    'va_hf_footer_link_hover_color' => '#d8efff',
                    'va_color_header_text' => '#eef7ff',
                    'va_color_footer_text' => 'rgba(238,247,255,.74)',
                    'va_color_footer_links' => '#89c8ff',
                ],
            ],
            'forest_glass' => [
                'label' => 'Forest Glass',
                'desc'  => 'Mely zold uveges panel erzes.',
                'options' => [
                    'va_hf_header_color_base' => '#06100b',
                    'va_hf_header_color_alt' => '#0f2b1f',
                    'va_hf_header_border_color' => '#36d487',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.70)',
                    'va_hf_header_glow_color' => 'rgba(54,212,135,.18)',
                    'va_hf_header_search_glow_color' => 'rgba(54,212,135,.16)',
                    'va_hf_header_btn_glow_color' => 'rgba(54,212,135,.36)',
                    'va_hf_footer_color_base' => '#060f0a',
                    'va_hf_footer_color_alt' => '#10261c',
                    'va_hf_footer_border_color' => '#36d487',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.36)',
                    'va_hf_footer_glow_color' => 'rgba(54,212,135,.11)',
                    'va_hf_footer_link_hover_color' => '#cbffe5',
                    'va_color_header_text' => '#effff7',
                    'va_color_footer_text' => 'rgba(239,255,247,.74)',
                    'va_color_footer_links' => '#7decb7',
                ],
            ],
            'obsidian_gold' => [
                'label' => 'Obsidian Gold',
                'desc'  => 'Fekete + arany, exkluziv karakter.',
                'options' => [
                    'va_hf_header_color_base' => '#090909',
                    'va_hf_header_color_alt' => '#1d1705',
                    'va_hf_header_border_color' => '#e5b843',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.74)',
                    'va_hf_header_glow_color' => 'rgba(229,184,67,.20)',
                    'va_hf_header_search_glow_color' => 'rgba(229,184,67,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(229,184,67,.40)',
                    'va_hf_footer_color_base' => '#080808',
                    'va_hf_footer_color_alt' => '#1b1505',
                    'va_hf_footer_border_color' => '#e5b843',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.38)',
                    'va_hf_footer_glow_color' => 'rgba(229,184,67,.12)',
                    'va_hf_footer_link_hover_color' => '#fff2cb',
                    'va_color_header_text' => '#fff9e8',
                    'va_color_footer_text' => 'rgba(255,249,232,.74)',
                    'va_color_footer_links' => '#f0cf76',
                ],
            ],
            'graphite_rose' => [
                'label' => 'Graphite Rose',
                'desc'  => 'Grafit alap finom rozsas accenttel.',
                'options' => [
                    'va_hf_header_color_base' => '#0f1013',
                    'va_hf_header_color_alt' => '#261521',
                    'va_hf_header_border_color' => '#ff6f91',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.72)',
                    'va_hf_header_glow_color' => 'rgba(255,111,145,.20)',
                    'va_hf_header_search_glow_color' => 'rgba(255,111,145,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(255,111,145,.38)',
                    'va_hf_footer_color_base' => '#0d0e10',
                    'va_hf_footer_color_alt' => '#21131b',
                    'va_hf_footer_border_color' => '#ff6f91',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.36)',
                    'va_hf_footer_glow_color' => 'rgba(255,111,145,.11)',
                    'va_hf_footer_link_hover_color' => '#ffdbe5',
                    'va_color_header_text' => '#fff0f5',
                    'va_color_footer_text' => 'rgba(255,240,245,.74)',
                    'va_color_footer_links' => '#ff97b0',
                ],
            ],
            'arctic_mint' => [
                'label' => 'Arctic Mint',
                'desc'  => 'Hideg tiszta menta vilagitas, modern tech vibe.',
                'options' => [
                    'va_hf_header_color_base' => '#071015',
                    'va_hf_header_color_alt' => '#103036',
                    'va_hf_header_border_color' => '#4ce8d2',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.70)',
                    'va_hf_header_glow_color' => 'rgba(76,232,210,.20)',
                    'va_hf_header_search_glow_color' => 'rgba(76,232,210,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(76,232,210,.38)',
                    'va_hf_footer_color_base' => '#060f12',
                    'va_hf_footer_color_alt' => '#0f282d',
                    'va_hf_footer_border_color' => '#4ce8d2',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.36)',
                    'va_hf_footer_glow_color' => 'rgba(76,232,210,.11)',
                    'va_hf_footer_link_hover_color' => '#d8fff8',
                    'va_color_header_text' => '#eefffb',
                    'va_color_footer_text' => 'rgba(238,255,251,.74)',
                    'va_color_footer_links' => '#8bf4e7',
                ],
            ],
            'royal_plum' => [
                'label' => 'Royal Plum',
                'desc'  => 'Kiralyi sotet lila-bordo, eros kontraszttal.',
                'options' => [
                    'va_hf_header_color_base' => '#12091a',
                    'va_hf_header_color_alt' => '#2b1020',
                    'va_hf_header_border_color' => '#c75cff',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.73)',
                    'va_hf_header_glow_color' => 'rgba(199,92,255,.22)',
                    'va_hf_header_search_glow_color' => 'rgba(199,92,255,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(199,92,255,.40)',
                    'va_hf_footer_color_base' => '#100717',
                    'va_hf_footer_color_alt' => '#230d1a',
                    'va_hf_footer_border_color' => '#c75cff',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.37)',
                    'va_hf_footer_glow_color' => 'rgba(199,92,255,.12)',
                    'va_hf_footer_link_hover_color' => '#f1dcff',
                    'va_color_header_text' => '#f8f0ff',
                    'va_color_footer_text' => 'rgba(248,240,255,.74)',
                    'va_color_footer_links' => '#da98ff',
                ],
            ],
            'desert_sand' => [
                'label' => 'Desert Sand',
                'desc'  => 'Meleg homok + rozsda modern outdoor erzet.',
                'options' => [
                    'va_hf_header_color_base' => '#15110c',
                    'va_hf_header_color_alt' => '#3a2312',
                    'va_hf_header_border_color' => '#ff9d4d',
                    'va_hf_header_shadow_color' => 'rgba(0,0,0,.72)',
                    'va_hf_header_glow_color' => 'rgba(255,157,77,.20)',
                    'va_hf_header_search_glow_color' => 'rgba(255,157,77,.18)',
                    'va_hf_header_btn_glow_color' => 'rgba(255,157,77,.40)',
                    'va_hf_footer_color_base' => '#120e0a',
                    'va_hf_footer_color_alt' => '#2f1d10',
                    'va_hf_footer_border_color' => '#ff9d4d',
                    'va_hf_footer_shadow_color' => 'rgba(0,0,0,.36)',
                    'va_hf_footer_glow_color' => 'rgba(255,157,77,.12)',
                    'va_hf_footer_link_hover_color' => '#ffe7d2',
                    'va_color_header_text' => '#fff5eb',
                    'va_color_footer_text' => 'rgba(255,245,235,.74)',
                    'va_color_footer_links' => '#ffbf8b',
                ],
            ],
        ];
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
        $val = esc_attr( (string) self::get_display_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"text\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text\"></td></tr>";
    }

    private static function field_email( string $key, string $label ): void {
        $val = esc_attr( (string) self::get_display_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"email\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text\"></td></tr>";
    }

    private static function field_url( string $key, string $label ): void {
        $val = esc_attr( (string) self::get_display_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"url\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text code";
        echo " placeholder=\"https://.../video.mp4\"></td></tr>";
    }

    private static function field_media( string $key, string $label ): void {
        $val = esc_attr( (string) self::get_display_option( $key, '' ) );
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
        $val = esc_attr( (string) self::get_display_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"number\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" min=\"{$min}\" max=\"{$max}\" class=\"small-text\"></td></tr>";
    }

    private static function field_decimal( string $key, string $label, float $min = 0.1, float $max = 5, float $step = 0.01 ): void {
        $val = esc_attr( (string) self::get_display_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"number\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" min=\"{$min}\" max=\"{$max}\" step=\"{$step}\" class=\"small-text\"></td></tr>";
    }

    private static function field_select( string $key, string $label, array $options ): void {
        $current = (string) self::get_display_option( $key, '' );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><select id=\"{$key}\" name=\"{$key}\">";
        foreach ( $options as $value => $text ) {
            echo '<option value="' . esc_attr( $value ) . '" ' . selected( $current, (string) $value, false ) . '>' . esc_html( (string) $text ) . '</option>';
        }
        echo '</select></td></tr>';
    }

    private static function field_color( string $key, string $label ): void {
        $val = esc_attr( (string) self::get_display_option( $key, '' ) );
        echo "<tr><th><label for=\"{$key}\">{$label}</label></th><td><input type=\"text\" id=\"{$key}\" name=\"{$key}\" value=\"{$val}\" class=\"regular-text va-color-input\" data-default-color=\"{$val}\"></td></tr>";
    }

    private static function field_toggle( string $key, string $label ): void {
        $val = (string) self::get_display_option( $key, '0' );
        echo "<tr><th>{$label}</th><td><input type=\"hidden\" name=\"{$key}\" value=\"0\"><label class=\"va-toggle\"><input type=\"checkbox\" name=\"{$key}\" value=\"1\"" . checked( $val, '1', false ) . "><span class=\"va-toggle-slider\"></span></label></td></tr>";
    }

    /* ══ Social Media beállítások ═════════════════════════ */
    public static function render_social(): void {
        if ( ! current_user_can( 'manage_options' ) ) return;

        $platforms = [
            'facebook'  => [ 'label' => 'Facebook',   'placeholder' => 'https://facebook.com/vadaszapro' ],
            'instagram' => [ 'label' => 'Instagram',  'placeholder' => 'https://instagram.com/vadaszapro' ],
            'youtube'   => [ 'label' => 'YouTube',    'placeholder' => 'https://youtube.com/@vadaszapro' ],
            'tiktok'    => [ 'label' => 'TikTok',     'placeholder' => 'https://tiktok.com/@vadaszapro' ],
            'twitter'   => [ 'label' => 'X (Twitter)','placeholder' => 'https://x.com/vadaszapro' ],
            'pinterest' => [ 'label' => 'Pinterest',  'placeholder' => 'https://pinterest.com/vadaszapro' ],
            'linkedin'  => [ 'label' => 'LinkedIn',   'placeholder' => 'https://linkedin.com/company/vadaszapro' ],
            'whatsapp'  => [ 'label' => 'WhatsApp',   'placeholder' => 'https://wa.me/36301234567' ],
            'telegram'  => [ 'label' => 'Telegram',   'placeholder' => 'https://t.me/vadaszapro' ],
        ];
        ?>
        <div class="wrap va-admin-wrap">
            <h1>🌐 VadászApró – Social Media</h1>
            <p class="description">Add meg a közösségi média profilok URL-jeit. Az ikonok automatikusan megjelennek a fejlécben és a láblécben.</p>

            <form method="post" action="options.php">
                <?php settings_fields( 'va_social_settings' ); ?>

                <div class="va-le-card" style="max-width:700px;margin-top:20px;">
                    <div class="va-le-card-hdr">🔗 Platform linkek</div>
                    <table class="form-table">
                        <?php foreach ( $platforms as $key => $cfg ): ?>
                        <tr>
                            <th style="display:flex;align-items:center;gap:10px;">
                                <?php echo va_social_svg( $key, 20 ); ?>
                                <?php echo esc_html( $cfg['label'] ); ?>
                            </th>
                            <td>
                                <input type="url" name="va_social_<?php echo esc_attr( $key ); ?>"
                                       value="<?php echo esc_attr( (string) get_option( 'va_social_' . $key, '' ) ); ?>"
                                       placeholder="<?php echo esc_attr( $cfg['placeholder'] ); ?>"
                                       class="va-le-input regular-text">
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>

                <div class="va-le-card" style="max-width:700px;margin-top:20px;">
                    <div class="va-le-card-hdr">⚙️ Megjelenítés</div>
                    <table class="form-table">
                        <?php self::field_toggle( 'va_social_header_show', 'Fejlécben megjelenjen' ); ?>
                        <?php self::field_toggle( 'va_social_footer_show', 'Láblécben megjelenjen' ); ?>
                        <tr>
                            <th>Fejléc stílus</th>
                            <td><?php self::field_select( 'va_social_header_style', '', [
                                'icons' => 'Csak ikonok (kompakt)',
                                'pills' => 'Pill (ikon + platformnév)',
                            ] ); ?></td>
                        </tr>
                        <tr>
                            <th>Lábléc stílus</th>
                            <td><?php self::field_select( 'va_social_footer_style', '', [
                                'icons' => 'Csak ikonok',
                                'pills' => 'Pill (ikon + platformnév)',
                                'full'  => 'Teljes sor (ikon + link + leírás)',
                            ] ); ?></td>
                        </tr>
                        <?php self::field_num( 'va_social_icon_size', 'Ikon méret (px)', 14, 40 ); ?>
                    </table>
                </div>

                <?php submit_button( 'Social Media mentése' ); ?>
            </form>

            <div class="va-le-card" style="max-width:700px;margin-top:20px;">
                <div class="va-le-card-hdr">👁️ Előnézet</div>
                <div style="padding:12px 0;">
                    <p style="font-size:11px;color:var(--va-muted);margin-bottom:8px;">Ikonos változat:</p>
                    <?php echo va_social_bar( 'icons', 24 ); ?>
                    <p style="font-size:11px;color:var(--va-muted);margin:16px 0 8px;">Pill változat:</p>
                    <?php echo va_social_bar( 'pills', 20 ); ?>
                </div>
            </div>
        </div>
        <?php
    }
}
