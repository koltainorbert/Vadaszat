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

        $all_plan_cfg  = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_all_plan_configs() : [];
        $user_plan    = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_user_plan( $user_id ) : 'basic';
        $rank_cards = [
            [ 'slug' => 'basic',    'qty' => 1,  'theme' => 'basic',    'tag' => 'Belépő',  'icon' => '<svg viewBox="0 0 24 24" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>' ],
            [ 'slug' => 'silver',   'qty' => 3,  'theme' => 'silver',   'tag' => 'Népszerű','icon' => '<svg viewBox="0 0 24 24" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><path d="M11 8v6M8 11h6"/></svg>' ],
            [ 'slug' => 'gold',     'qty' => 5,  'theme' => 'gold',     'tag' => 'Profi',   'icon' => '<svg viewBox="0 0 24 24" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>' ],
            [ 'slug' => 'platinum', 'qty' => 10, 'theme' => 'platinum', 'tag' => 'Prémium', 'icon' => '<svg viewBox="0 0 24 24" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4l4 8h12l4-8"/><path d="M6 12v8h12v-8"/><path d="M12 12v8"/></svg>' ],
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
                    $is_free       = ( $slug === 'basic' );
                    $is_active     = ( $slug === $user_plan );
                ?>
                <div class="va-pkg-card va-pkg-card--<?php echo esc_attr( $card['theme'] ); ?><?php echo $is_active ? ' va-pkg-card--active' : ''; ?>" data-qty="<?php echo esc_attr( (string) $qty ); ?>">
                    <?php if ( $is_active ): ?>
                    <div class="va-pkg-badge va-pkg-badge--active">✓ Jelenlegi</div>
                    <?php else: ?>
                    <div class="va-pkg-badge"><?php echo esc_html( $card['tag'] ); ?></div>
                    <?php endif; ?>
                    <div class="va-pkg-header">
                        <div class="va-pkg-icon-wrap"><?php echo $card['icon']; // SVG, no user input ?></div>
                        <div class="va-pkg-header-text">
                            <div class="va-pkg-rank"><?php echo esc_html( strtoupper( $plan_label ) ); ?></div>
                            <div class="va-pkg-qty"><?php echo esc_html( (string) $pkg['label'] ); ?></div>
                        </div>
                    </div>
                    <div class="va-pkg-price-block">
                        <?php if ( $is_free ): ?>
                        <div class="va-pkg-price va-pkg-price--free">Ingyenes</div>
                        <div class="va-pkg-unit">regisztrációval</div>
                        <?php else: ?>
                        <div class="va-pkg-price"><?php echo number_format( (int) $pkg['total'], 0, ',', ' ' ); ?><span>Ft</span></div>
                        <div class="va-pkg-unit"><?php echo number_format( (int) $pkg['unit_price'], 0, ',', ' ' ); ?> Ft / kredit</div>
                        <?php endif; ?>
                    </div>
                    <ul class="va-pkg-meta">
                        <li><?php echo esc_html( $plan_desc ); ?></li>
                        <?php if ( $plan_limit > 0 ): ?>
                        <li><?php echo $plan_basis === 'active' ? 'Max ' . esc_html( (string) $plan_limit ) . ' aktív hirdetés' : esc_html( (string) $plan_limit ) . ' hirdetés / hó'; ?></li>
                        <?php else: ?>
                        <li>Korlátlan hirdetés</li>
                        <?php endif; ?>
                        <li>Boost újratöltés: <?php echo esc_html( (string) max( 0, $plan_boost_cd ) ); ?> nap</li>
                    </ul>
                    <?php if ( $is_active ): ?>
                    <button type="button" class="va-pkg-buy-btn va-pkg-buy-btn--current" disabled>
                        Aktív csomag
                    </button>
                    <?php elseif ( $is_free ): ?>
                    <button type="button" class="va-pkg-buy-btn va-pkg-buy-btn--free" disabled>
                        Mindenki számára elérhető
                    </button>
                    <?php else: ?>
                    <button type="button" class="va-pkg-buy-btn" data-qty="<?php echo esc_attr( (string) $qty ); ?>" data-total="<?php echo esc_attr( (string) $pkg['total'] ); ?>">
                        Vásárlás →
                    </button>
                    <?php endif; ?>
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
