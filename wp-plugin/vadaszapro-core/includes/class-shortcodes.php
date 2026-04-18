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
            'va_buy_credits'     => 'render_buy_credits',
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
        if ( function_exists( 'va_auctions_enabled' ) && ! va_auctions_enabled() ) {
            return '<p class="va-notice va-notice--info">Az aukció funkció jelenleg ki van kapcsolva.</p>';
        }

        ob_start();
        va_template( 'auction/list' );
        return ob_get_clean();
    }

    public static function render_ad_zone( $atts ) {
        $atts = shortcode_atts( [ 'zone' => '' ], $atts );
        return VA_Ad_Zones::render( sanitize_key( $atts['zone'] ), false );
    }

    public static function render_buy_credits( $atts ) {
        if ( ! is_user_logged_in() ) {
            return '<p class="va-notice va-notice--info">Csomag vásárláshoz <a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">jelentkezz be</a>.</p>';
        }

        $packages   = VA_Ajax::get_credit_packages();
        $credits    = absint( get_user_meta( get_current_user_id(), 'va_listing_credits', true ) );
        $nonce      = wp_create_nonce( 'va_buy_credits' );

        ob_start();
        wp_enqueue_style(  'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );
        wp_enqueue_script( 'va-frontend',  VA_PLUGIN_URL . 'frontend/js/frontend.js',  [ 'jquery' ], VA_VERSION, true );
        wp_localize_script( 'va-frontend', 'VA_Data', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => $nonce,
        ]);
        ?>
        <div class="va-wrap">
            <?php va_display_flash(); ?>
            <div id="va-buy-notice"></div>

            <div class="va-credits-hero">
                <h2 class="va-credits-title">🎯 Hirdetési csomag vásárlása</h2>
                <p class="va-credits-sub">Jelenlegi kreditjeid: <strong class="va-credits-count"><?php echo esc_html( (string) $credits ); ?> db</strong></p>
            </div>

            <div class="va-pkg-grid">
                <?php foreach ( $packages as $pkg ): ?>
                <div class="va-pkg-card <?php echo $pkg['qty'] === 5 ? 'va-pkg-card--popular' : ''; ?>" data-qty="<?php echo esc_attr( (string) $pkg['qty'] ); ?>">
                    <?php if ( $pkg['badge'] ): ?><div class="va-pkg-badge"><?php echo esc_html( $pkg['badge'] ); ?></div><?php endif; ?>
                    <?php if ( $pkg['qty'] === 5 ): ?><div class="va-pkg-popular-label">⭐ Legnépszerűbb</div><?php endif; ?>
                    <div class="va-pkg-qty"><?php echo esc_html( $pkg['label'] ); ?></div>
                    <div class="va-pkg-price"><?php echo number_format( $pkg['total'], 0, ',', ' ' ); ?> Ft</div>
                    <div class="va-pkg-unit"><?php echo number_format( $pkg['unit_price'], 0, ',', ' ' ); ?> Ft / db</div>
                    <button type="button" class="va-btn va-btn--primary va-pkg-buy-btn" data-qty="<?php echo esc_attr( (string) $pkg['qty'] ); ?>" data-total="<?php echo esc_attr( (string) $pkg['total'] ); ?>">
                        Megveszem
                    </button>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="va-pkg-custom">
                <h3 style="font-size:14px;font-weight:700;margin-bottom:10px;">Egyedi mennyiség</h3>
                <div class="va-pkg-custom-row">
                    <input type="number" id="va-custom-qty" min="1" max="100" value="2" class="va-input" style="width:100px;">
                    <span class="va-pkg-custom-total" id="va-custom-total"></span>
                    <button type="button" class="va-btn va-btn--primary" id="va-custom-buy-btn">Vásárlás</button>
                </div>
            </div>
        </div>

        <script>
        (function($){
            var BASE   = <?php echo (int) get_option( 'va_listing_price_after_free', 1990 ); ?>;
            var NONCE  = '<?php echo esc_js( $nonce ); ?>';
            var PACKAGES = <?php echo wp_json_encode( $packages ); ?>;

            function findPrice(qty) {
                var up = BASE, tot = BASE * qty;
                for (var i = PACKAGES.length - 1; i >= 0; i--) {
                    if (qty >= PACKAGES[i].qty) { up = PACKAGES[i].unit_price; tot = up * qty; break; }
                }
                return { unit: up, total: tot };
            }

            // Egyedi mennyiség → ár frissítés
            function updateCustomTotal() {
                var qty = parseInt($('#va-custom-qty').val()) || 1;
                var p = findPrice(qty);
                $('#va-custom-total').text(p.total.toLocaleString('hu-HU') + ' Ft (' + p.unit.toLocaleString('hu-HU') + ' Ft/db)');
            }
            $('#va-custom-qty').on('input', updateCustomTotal);
            updateCustomTotal();

            function doCheckout(qty) {
                $.post(VA_Data.ajax_url, {
                    action: 'va_buy_credits',
                    nonce:  NONCE,
                    qty:    qty,
                }, function(res){
                    if (res.success && res.data.checkout_url) {
                        window.location.href = res.data.checkout_url;
                    } else {
                        $('#va-buy-notice').html('<div class="va-notice va-notice--error">' + (res.data ? res.data.message : 'Hiba') + '</div>');
                    }
                });
            }

            $('.va-pkg-buy-btn').on('click', function(){ doCheckout( $(this).data('qty') ); });
            $('#va-custom-buy-btn').on('click', function(){ doCheckout( parseInt($('#va-custom-qty').val()) || 1 ); });
        })(jQuery);
        </script>
        <?php
        return ob_get_clean();
    }
}
