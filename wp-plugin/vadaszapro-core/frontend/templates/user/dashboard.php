<?php
/**
 * Template: Felhasználói dashboard (fiókom oldal)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! is_user_logged_in() ) {
    wp_safe_redirect( wp_login_url( get_permalink() ) );
    exit;
}

wp_enqueue_style(  'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );
wp_enqueue_script( 'va-frontend', VA_PLUGIN_URL . 'frontend/js/frontend.js', [ 'jquery' ], VA_VERSION, true );
wp_localize_script( 'va-frontend', 'VA_Data', [
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce'    => wp_create_nonce( 'va_user_nonce' ),
    'post_id'  => 0,
]);

$user     = wp_get_current_user();
$auctions_enabled = function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : true;
$listings = va_get_user_listings( $user->ID );
$watchlist= va_get_user_watchlist( $user->ID );
$bids     = $auctions_enabled ? va_get_user_bids( $user->ID ) : [];
$phone    = get_user_meta( $user->ID, 'va_phone', true );
$submit_page = get_page_by_path( 'va-hirdetes-feladas' );
?>
<div class="va-wrap">
    <?php va_display_flash(); ?>

    <div class="va-dashboard">

        <!-- Bal navigáció -->
        <nav class="va-dashboard__nav">
            <span class="va-dashboard__nav-item active" data-tab="listings">📋 Hirdetéseim (<?php echo count( $listings ); ?>)</span>
            <?php if ( $auctions_enabled ): ?>
            <span class="va-dashboard__nav-item" data-tab="bids">🔨 Licitjeim (<?php echo count( $bids ); ?>)</span>
            <?php endif; ?>
            <span class="va-dashboard__nav-item" data-tab="watchlist">♥ Kedvenceim (<?php echo count( $watchlist ); ?>)</span>
            <span class="va-dashboard__nav-item" data-tab="profile">👤 Profilom</span>
            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="va-dashboard__nav-item" style="color:#ff0000;margin-top:20px;">🚪 Kijelentkezés</a>
        </nav>

        <!-- Tartalom -->
        <div class="va-dashboard__content">

            <!-- Tab: Hirdetéseim -->
            <div id="va-tab-listings" class="va-dashboard__section active">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                    <h2 class="va-dashboard__title">Hirdetéseim</h2>
                    <?php if ( $submit_page ): ?>
                        <a href="<?php echo esc_url( get_permalink( $submit_page ) ); ?>" class="va-btn va-btn--primary va-btn--sm">+ Új hirdetés</a>
                    <?php endif; ?>
                </div>

                <?php if ( $listings ): ?>
                <table class="va-user-listings-table" style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.1);">
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Cím</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Ár</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Státusz</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Dátum</th>
                            <th style="padding:8px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $listings as $l ):
                        $price    = get_post_meta( $l->ID, 'va_price', true );
                        $p_type   = get_post_meta( $l->ID, 'va_price_type', true ) ?: 'fixed';
                        $statuses = [
                            'publish' => '<span style="color:#00c850">● Aktív</span>',
                            'pending' => '<span style="color:#ffb400">● Jóváhagyásra vár</span>',
                            'draft'   => '<span style="color:#888">● Piszkozat</span>',
                        ];
                    ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:10px 8px;">
                            <a href="<?php echo esc_url( get_permalink( $l->ID ) ); ?>" style="color:#fff;font-weight:600;"><?php echo esc_html( $l->post_title ); ?></a>
                            <?php if ( get_post_meta( $l->ID, 'va_featured', true ) === '1' ): ?>
                                <span style="font-size:11px;background:#ffaa00;color:#000;padding:2px 6px;border-radius:3px;margin-left:6px;">Kiemelt</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:10px 8px;"><?php echo esc_html( va_format_price( $price, $p_type ) ); ?></td>
                        <td style="padding:10px 8px;"><?php echo $statuses[ $l->post_status ] ?? esc_html( $l->post_status ); ?></td>
                        <td style="padding:10px 8px;color:rgba(255,255,255,0.5);"><?php echo esc_html( get_the_date( 'Y.m.d', $l ) ); ?></td>
                        <td style="padding:10px 8px;text-align:right;">
                            <a href="<?php echo esc_url( get_edit_post_link( $l->ID ) ); ?>" class="va-btn va-btn--outline va-btn--sm">Szerk.</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="color:rgba(255,255,255,0.5);">Még nincs feladott hirdetésed.</p>
                    <?php if ( $submit_page ): ?>
                        <a href="<?php echo esc_url( get_permalink( $submit_page ) ); ?>" class="va-btn va-btn--primary">+ Hirdetés feladása</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if ( $auctions_enabled ): ?>
            <!-- Tab: Licitjeim -->
            <div id="va-tab-bids" class="va-dashboard__section">
                <h2 class="va-dashboard__title">Licitjeim</h2>
                <?php if ( $bids ): ?>
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.1);">
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Aukció</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Licit</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Dátum</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $bids as $bid ): ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                        <td style="padding:10px 8px;">
                            <a href="<?php echo esc_url( get_permalink( $bid->auction_id ) ); ?>" style="color:#fff;font-weight:600;">
                                <?php echo esc_html( $bid->post_title ); ?>
                            </a>
                        </td>
                        <td style="padding:10px 8px;font-weight:700;color:#ff0000;">
                            <?php echo esc_html( number_format( $bid->amount, 0, ',', ' ' ) . ' Ft' ); ?>
                        </td>
                        <td style="padding:10px 8px;color:rgba(255,255,255,0.5);">
                            <?php echo esc_html( date_i18n( 'Y.m.d H:i', strtotime( $bid->created_at ) ) ); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="color:rgba(255,255,255,0.5);">Még nem adtál le licitet.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Tab: Kedvenceim -->
            <div id="va-tab-watchlist" class="va-dashboard__section">
                <h2 class="va-dashboard__title">Kedvenceim</h2>
                <?php if ( $watchlist ): ?>
                <div class="va-grid">
                    <?php foreach ( $watchlist as $p ):
                        $post = $p;
                        va_template( 'listing/card', [ 'post' => $post ] );
                    endforeach; ?>
                </div>
                <?php else: ?>
                    <p style="color:rgba(255,255,255,0.5);">Még nincs mentett hirdetés.</p>
                <?php endif; ?>
            </div>

            <!-- Tab: Profilom -->
            <div id="va-tab-profile" class="va-dashboard__section">
                <h2 class="va-dashboard__title">Profilom szerkesztése</h2>
                <form method="post">
                    <?php wp_nonce_field( 'va_profile', 'va_profile_nonce' ); ?>
                    <input type="hidden" name="va_action" value="profile">

                    <div class="va-form-row">
                        <div class="va-form-group">
                            <label>Keresztnév</label>
                            <input type="text" name="profile_firstname" class="va-input" value="<?php echo esc_attr( $user->first_name ); ?>">
                        </div>
                        <div class="va-form-group">
                            <label>Vezetéknév</label>
                            <input type="text" name="profile_lastname" class="va-input" value="<?php echo esc_attr( $user->last_name ); ?>">
                        </div>
                    </div>

                    <div class="va-form-group">
                        <label>E-mail cím</label>
                        <input type="text" class="va-input" value="<?php echo esc_attr( $user->user_email ); ?>" disabled style="opacity:0.5;">
                        <p style="font-size:11px;color:rgba(255,255,255,0.4);">Az e-mail cím megváltoztatásához lépj kapcsolatba az adminisztrátorokkal.</p>
                    </div>

                    <div class="va-form-group">
                        <label>Telefonszám</label>
                        <input type="tel" name="profile_phone" class="va-input" value="<?php echo esc_attr( $phone ); ?>">
                    </div>

                    <div class="va-form-group">
                        <label>Bemutatkozás</label>
                        <textarea name="profile_bio" class="va-textarea"><?php echo esc_textarea( $user->description ); ?></textarea>
                    </div>

                    <hr style="border-color:rgba(255,255,255,0.1);margin:20px 0;">
                    <h3 style="font-size:14px;font-weight:700;margin-bottom:14px;">Jelszócsere (hagyd üresen ha nem változtatod)</h3>

                    <div class="va-form-row">
                        <div class="va-form-group">
                            <label>Új jelszó</label>
                            <input type="password" name="profile_newpass" class="va-input" autocomplete="new-password" minlength="8">
                        </div>
                        <div class="va-form-group">
                            <label>Új jelszó ismét</label>
                            <input type="password" name="profile_newpass2" class="va-input" autocomplete="new-password" minlength="8">
                        </div>
                    </div>

                    <button type="submit" class="va-btn va-btn--primary">Mentés</button>
                </form>
            </div>

        </div><!-- .va-dashboard__content -->
    </div><!-- .va-dashboard -->
</div>
