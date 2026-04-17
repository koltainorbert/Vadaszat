<?php
/**
 * Shortcode-ok: minden frontend blokk shortcode-ként is használható
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Shortcodes {

    public static function init() {
        $codes = [
            'va_login_form'      => 'render_login',
            'va_register_form'   => 'render_register',
            'va_user_dashboard'  => 'render_dashboard',
            'va_submit_listing'  => 'render_submit',
            'va_listing_search'  => 'render_search',
            'va_auction_list'    => 'render_auction_list',
            'va_ad_zone'         => 'render_ad_zone',
        ];

        foreach ( $codes as $tag => $method ) {
            add_shortcode( $tag, [ __CLASS__, $method ] );
        }
    }

    public static function render_login( $atts ) {
        ob_start();
        va_template( 'user/login-form' );
        return ob_get_clean();
    }

    public static function render_register( $atts ) {
        ob_start();
        va_template( 'user/register-form' );
        return ob_get_clean();
    }

    public static function render_dashboard( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p class="va-notice va-notice--info">A fiókod megtekintéséhez <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">jelentkezz be</a>.</p>';
        }
        ob_start();
        va_template( 'user/dashboard' );
        return ob_get_clean();
    }

    public static function render_submit( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p class="va-notice va-notice--info">Hirdetés feladásához <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">jelentkezz be</a>.</p>';
        }
        ob_start();
        va_template( 'listing/submit-form' );
        return ob_get_clean();
    }

    public static function render_search( $atts ) {
        ob_start();
        va_template( 'listing/search' );
        return ob_get_clean();
    }

    public static function render_auction_list( $atts ) {
        ob_start();
        va_template( 'auction/list' );
        return ob_get_clean();
    }

    public static function render_ad_zone( $atts ) {
        $atts = shortcode_atts( [ 'zone' => '' ], $atts );
        return VA_Ad_Zones::render( sanitize_key( $atts['zone'] ), false );
    }
}
