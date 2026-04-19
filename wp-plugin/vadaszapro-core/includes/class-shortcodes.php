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
        wp_enqueue_script( 'jquery-ui-sortable' );
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

        $user_id    = get_current_user_id();
        $packages   = VA_Ajax::get_credit_packages();
        $credits    = absint( get_user_meta( $user_id, 'va_listing_credits', true ) );
        $nonce      = wp_create_nonce( 'va_buy_credits' );

        $return_to = isset( $_GET['va_return'] ) ? sanitize_key( (string) wp_unslash( $_GET['va_return'] ) ) : 'buy';
        if ( ! in_array( $return_to, [ 'buy', 'submit' ], true ) ) {
            $return_to = 'buy';
        }

        $base_price = (int) get_option( 'va_listing_price_after_free', 1990 );
        $packages_by_qty = [];
        foreach ( $packages as $pkg ) {
            $qty = (int) ( $pkg['qty'] ?? 0 );
            if ( $qty > 0 ) {
                $packages_by_qty[ $qty ] = $pkg;
            }
        }

        $all_plan_cfg = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_all_plan_configs() : [];
        $rank_cards = [
            [ 'slug' => 'basic',    'qty' => 1,  'theme' => 'basic',    'tag' => 'Belépő' ],
            [ 'slug' => 'silver',   'qty' => 3,  'theme' => 'silver',   'tag' => 'Népszerű' ],
            [ 'slug' => 'gold',     'qty' => 5,  'theme' => 'gold',     'tag' => 'Profi' ],
            [ 'slug' => 'platinum', 'qty' => 10, 'theme' => 'platinum', 'tag' => 'Prémium' ],
        ];

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

            <div class="va-credits-hero va-credits-hero--ranks">
                <div class="va-credits-eyebrow"><span class="va-credits-eyebrow-dot"></span>Átlátható csomagok</div>
                <h2 class="va-credits-title">Rang Alapú Vásárlás</h2>
                <p class="va-credits-sub">Válassz csomagot a rangok szerint, és fizess azonnal bankkártyával.</p>
                <p class="va-credits-sub">Jelenlegi kreditjeid: <strong class="va-credits-count"><?php echo esc_html( (string) $credits ); ?> db</strong></p>
                <?php if ( $return_to === 'submit' ): ?>
                <div class="va-notice va-notice--warning" style="margin:14px auto 0;max-width:860px;">A hirdetés feladás folytatásához válassz csomagot, fizetés után automatikusan visszairányítunk a feladáshoz.</div>
                <?php endif; ?>
            </div>

            <div class="va-pkg-grid">
                <?php foreach ( $rank_cards as $card ): ?>
                <?php
                    $slug = $card['slug'];
                    $qty  = (int) $card['qty'];
                    $cfg  = ( isset( $all_plan_cfg[ $slug ] ) && is_array( $all_plan_cfg[ $slug ] ) ) ? $all_plan_cfg[ $slug ] : [];
                    $pkg  = $packages_by_qty[ $qty ] ?? [
                        'qty'        => $qty,
                        'label'      => $qty . ' kredit',
                        'unit_price' => $base_price,
                        'total'      => $base_price * $qty,
                    ];
                    $plan_label    = (string) ( $cfg['label'] ?? ucfirst( $slug ) );
                    $plan_desc     = (string) ( $cfg['description'] ?? 'Hirdetési csomag' );
                    $plan_limit    = (int) ( $cfg['monthly_limit'] ?? 0 );
                    $plan_basis    = (string) ( $cfg['basis'] ?? 'monthly' );
                    $plan_boost_cd = (int) ( $cfg['boost_cooldown'] ?? 0 );
                ?>
                <div class="va-pkg-card va-pkg-card--rank va-pkg-card--<?php echo esc_attr( $card['theme'] ); ?>" data-qty="<?php echo esc_attr( (string) $qty ); ?>">
                    <div class="va-pkg-badge"><?php echo esc_html( $card['tag'] ); ?></div>
                    <div class="va-pkg-rank"><?php echo esc_html( strtoupper( $plan_label ) ); ?></div>
                    <div class="va-pkg-qty"><?php echo esc_html( (string) $pkg['label'] ); ?></div>
                    <div class="va-pkg-price"><?php echo number_format( (int) $pkg['total'], 0, ',', ' ' ); ?> Ft</div>
                    <div class="va-pkg-unit"><?php echo number_format( (int) $pkg['unit_price'], 0, ',', ' ' ); ?> Ft / kredit</div>
                    <ul class="va-pkg-meta">
                        <li><?php echo esc_html( $plan_desc ); ?></li>
                        <?php if ( $plan_limit > 0 ): ?>
                        <li>Keret: <?php echo esc_html( (string) $plan_limit ); ?> <?php echo $plan_basis === 'active' ? 'aktív hirdetés' : 'hirdetés / hó'; ?></li>
                        <?php else: ?>
                        <li>Keret: korlátlan</li>
                        <?php endif; ?>
                        <li>Boost: <?php echo esc_html( (string) max( 0, $plan_boost_cd ) ); ?> nap</li>
                    </ul>
                    <button type="button" class="va-btn va-btn--primary va-pkg-buy-btn" data-qty="<?php echo esc_attr( (string) $qty ); ?>" data-total="<?php echo esc_attr( (string) $pkg['total'] ); ?>">
                        Vásárlás
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <script>
        (function($){
            var NONCE  = '<?php echo esc_js( $nonce ); ?>';
            var RETURN_TO = '<?php echo esc_js( $return_to ); ?>';

            function doCheckout(qty) {
                $.post(VA_Data.ajax_url, {
                    action: 'va_buy_credits',
                    nonce:  NONCE,
                    qty:    qty,
                    return_to: RETURN_TO,
                }, function(res){
                    if (res.success && res.data.checkout_url) {
                        window.location.href = res.data.checkout_url;
                    } else {
                        $('#va-buy-notice').html('<div class="va-notice va-notice--error">' + (res.data ? res.data.message : 'Hiba') + '</div>');
                    }
                });
            }

            $('.va-pkg-buy-btn').on('click', function(){ doCheckout( $(this).data('qty') ); });
        })(jQuery);
        </script>
        <?php
        return ob_get_clean();
    }
}
