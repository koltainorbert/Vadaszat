<?php
/**
 * single-va_auction.php – Aukció részletes oldal
 */
get_header();
if ( !have_posts() ) { get_footer(); return; }
the_post();

$post_id     = get_the_ID();
$start_price = floatval( get_post_meta($post_id,'va_start_price',true) ?: 0 );
$current_bid = floatval( get_post_meta($post_id,'va_current_bid',true) ?: 0 );
$bid_count   = intval(   get_post_meta($post_id,'va_bid_count',  true) ?: 0 );
$min_step    = floatval( get_post_meta($post_id,'va_min_bid_step',true) ?: get_option('va_default_min_bid_step',500) );
$buyout      = floatval( get_post_meta($post_id,'va_buyout_price',true) ?: 0 );
$end         = get_post_meta($post_id,'va_auction_end',true);
$winner_id   = get_post_meta($post_id,'va_auction_winner',true);
$license_req = get_post_meta($post_id,'va_license_req',true);
$is_over     = va_is_auction_over($post_id);
$min_next    = max($start_price, $current_bid + $min_step);
$categories  = get_the_terms($post_id,'va_category');
$county      = get_the_terms($post_id,'va_county');

wp_enqueue_style(  'va-frontend', get_template_directory_uri().'/assets/css/frontend.css',[], '1.0.0' );
wp_enqueue_script( 'va-auction',  plugins_url('vadaszapro-core/frontend/js/auction.js', dirname(get_template_directory())), ['jquery'], '1.0.0', true );
wp_localize_script( 'va-auction','VA_Auction', [
    'ajax_url'   => admin_url('admin-ajax.php'),
    'nonce'      => wp_create_nonce('va_bid_nonce'),
    'strings'    => [
        'confirm_bid'  => 'Biztosan licitel ezzel az összeggel?',
    ],
]);
wp_localize_script( 'va-auction','VA_AuctionData', ['auction_id' => $post_id]);
wp_enqueue_script( 'va-frontend', get_template_directory_uri().'/assets/js/frontend.js', ['jquery'], '1.0.0', true );
wp_localize_script( 'va-frontend','VA_Data', ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('va_user_nonce'), 'post_id' => $post_id]);

global $wpdb;
$bid_history = $wpdb->get_results($wpdb->prepare(
    "SELECT b.amount, b.created_at, u.display_name
     FROM {$wpdb->prefix}va_bids b
     JOIN {$wpdb->users} u ON u.ID = b.user_id
     WHERE b.auction_id = %d
     ORDER BY b.amount DESC LIMIT 10",
    $post_id
));
?>
<div class="va-wrap va-single">

    <div style="display:grid;grid-template-columns:1fr 340px;gap:28px;align-items:start;">
        <div>
            <!-- Galéria -->
            <div class="va-listing-detail__gallery" style="margin-bottom:22px;">
                <?php if (has_post_thumbnail()): ?>
                    <img src="<?php echo esc_url(get_the_post_thumbnail_url($post_id,'va-detail')); ?>"
                         class="va-listing-detail__main-img" id="va-main-img" alt="<?php the_title_attribute(); ?>">
                <?php else: ?>
                    <div class="va-listing-detail__main-img" style="background:rgba(255,255,255,0.04);display:flex;align-items:center;justify-content:center;font-size:80px;">🔨</div>
                <?php endif; ?>
            </div>

            <!-- Metaadatok -->
            <h1 class="va-listing-detail__title"><?php the_title(); ?></h1>
            <div style="display:flex;flex-wrap:wrap;gap:12px;font-size:13px;color:rgba(255,255,255,0.5);margin-bottom:20px;">
                <?php if ($categories && !is_wp_error($categories)): ?>
                    <span>🏷 <?php echo esc_html($categories[0]->name); ?></span>
                <?php endif; ?>
                <?php if ($county && !is_wp_error($county)): ?>
                    <span>📍 <?php echo esc_html($county[0]->name); ?></span>
                <?php endif; ?>
                <?php if ($license_req === '1'): ?><span style="color:#ffb400;">⚠️ Fegyverengedély</span><?php endif; ?>
            </div>

            <!-- Leírás -->
            <div class="va-listing-detail__desc"><?php the_content(); ?></div>
        </div>

        <!-- Jobb panel: aukció box -->
        <div>
            <div class="va-auction-box">
                <div class="va-auction-box__title">🔨 Aukció</div>

                <?php if (!$is_over && !$winner_id): ?>
                    <!-- Visszaszámlálás -->
                    <?php if ($end): ?>
                    <div style="font-size:12px;color:rgba(255,255,255,0.5);margin-bottom:6px;">Aukció vége:</div>
                    <div class="va-auction-box__countdown"><?php echo va_auction_countdown($post_id); ?></div>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="background:rgba(255,0,0,0.1);border:1px solid #ff0000;border-radius:6px;padding:10px;text-align:center;margin-bottom:14px;font-weight:700;">
                        ⌛ Az aukció lejárt
                    </div>
                    <?php if ($winner_id):
                        $winner = get_userdata((int)$winner_id); ?>
                        <div style="color:rgba(255,255,255,0.7);font-size:13px;margin-bottom:12px;">Nyertes: <strong><?php echo esc_html($winner ? $winner->display_name : '–'); ?></strong></div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Aktuális licit -->
                <div style="font-size:12px;color:rgba(255,255,255,0.5);margin-bottom:4px;">Kikiáltási ár</div>
                <div style="font-size:15px;font-weight:600;margin-bottom:12px;"><?php echo esc_html(number_format($start_price,0,',',' ').' Ft'); ?></div>

                <div style="font-size:12px;color:rgba(255,255,255,0.5);margin-bottom:4px;">Aktuális licit</div>
                <div class="va-auction-box__current" id="va-current-bid">
                    <?php echo esc_html($current_bid ? number_format($current_bid,0,',',' ').' Ft' : number_format($start_price,0,',',' ').' Ft (kikiáltási)'); ?>
                </div>

                <div class="va-auction-box__bid-count" id="va-bid-count">
                    <?php echo esc_html($bid_count); ?> licit
                </div>

                <?php if ($buyout > 0 && !$is_over): ?>
                <div style="font-size:12px;color:#00c850;margin-bottom:14px;">
                    💰 Azonnal megveszem: <strong><?php echo esc_html(number_format($buyout,0,',',' ').' Ft'); ?></strong>
                </div>
                <?php endif; ?>

                <!-- Licit form -->
                <?php if (!$is_over && !$winner_id): ?>
                    <div id="va-bid-notice"></div>
                    <?php if (is_user_logged_in()): ?>
                    <div class="va-auction-box__input-row" id="va-bid-form">
                        <input type="number" id="va-min-bid" class="va-input"
                               min="<?php echo esc_attr($min_next); ?>"
                               value="<?php echo esc_attr($min_next); ?>"
                               step="<?php echo esc_attr($min_step); ?>">
                        <button id="va-bid-submit" class="va-btn va-btn--primary">Licitálok</button>
                    </div>
                    <p id="va-min-bid-hint" style="font-size:11px;color:rgba(255,255,255,0.4);margin-top:6px;">
                        Min. licit: <?php echo esc_html(number_format($min_next,0,',',' ').' Ft'); ?>
                    </p>
                    <?php else:
                        $login_enabled = get_option( 'va_enable_login', '1' ) === '1';
                        $login_page = get_page_by_path('va-bejelentkezes'); ?>
                    <?php if ( $login_enabled ): ?>
                    <a href="<?php echo esc_url($login_page ? get_permalink($login_page) . '?redirect_to=' . urlencode(get_permalink()) : wp_login_url(get_permalink())); ?>"
                       class="va-btn va-btn--primary va-btn--block">Bejelentkezés a licitáláshoz</a>
                    <?php else: ?>
                    <div class="va-notice va-notice--warning">A bejelentkezés jelenleg ki van kapcsolva, ezért most nem lehet licitálni.</div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <div id="va-auction-over" style="display:none;text-align:center;padding:10px;color:rgba(255,255,255,0.5);">Az aukció lezárult.</div>
                <?php endif; ?>

                <!-- Watchlist -->
                <?php if (is_user_logged_in()): ?>
                <button class="va-card__watchlist" data-post-id="<?php echo esc_attr($post_id); ?>"
                    style="position:static;display:flex;width:100%;justify-content:center;margin-top:12px;background:rgba(255,255,255,0.06);border-radius:6px;padding:10px;font-size:13px;<?php echo va_user_watches($post_id) ? 'color:#ff0000;' : ''; ?>">
                    ♥ <?php echo va_user_watches($post_id) ? 'Figyelés eltávolítása' : 'Aukció figyelése'; ?>
                </button>
                <?php endif; ?>
            </div>

            <!-- Licit történet -->
            <?php if ($bid_history): ?>
            <div class="va-auction-box__history" style="background:rgba(14,14,14,0.8);border:1px solid rgba(255,255,255,0.08);border-radius:6px;padding:16px;margin-top:16px;">
                <div style="font-size:12px;font-weight:700;color:rgba(255,255,255,0.4);text-transform:uppercase;margin-bottom:10px;">Licittörténet</div>
                <?php foreach ($bid_history as $bid): ?>
                <div class="va-bid-row">
                    <span class="va-bid-row__amount"><?php echo esc_html(number_format($bid->amount,0,',',' ').' Ft'); ?></span>
                    <span class="va-bid-row__user"><?php echo esc_html($bid->display_name); ?></span>
                    <span class="va-bid-row__user"><?php echo esc_html(date_i18n('m.d H:i', strtotime($bid->created_at))); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
