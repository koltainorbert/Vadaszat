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

// Plan adatok
$user_plan    = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_user_plan( $user->ID ) : 'basic';
$plan_configs = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::PLANS : [];
$plan_cfg     = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::get_plan_config( $user_plan, $user->ID ) : [];
$plan_check   = class_exists( 'VA_User_Roles' ) ? VA_User_Roles::can_post_listing( $user->ID ) : [ 'used' => 0, 'limit' => 0 ];
$boost_nonce  = wp_create_nonce( 'va_user_nonce' );
$ajax_url     = admin_url( 'admin-ajax.php' );
$seller_label = get_user_meta( $user->ID, 'va_seller_label', true );
$avatar_id    = (int) get_user_meta( $user->ID, 'va_profile_avatar_id', true );
$avatar_url   = $avatar_id ? wp_get_attachment_image_url( $avatar_id, 'thumbnail' ) : '';

// CRM statisztika adatelokeszites (valid, adminnal egyezo nezetseggel)
$crm_now_ts         = current_time( 'timestamp' );
$crm_total_listings = count( $listings );
$crm_total_views    = 0;
$crm_total_price    = 0.0;
$crm_priced_count   = 0;
$crm_active_count   = 0;
$crm_featured_count = 0;
$crm_boosted_count  = 0;
$crm_newpill_count  = 0;
$crm_last7_count    = 0;
$crm_last30_count   = 0;

$crm_status_counts = [
    'Aktiv'                  => 0,
    'Jovahagyasra var'       => 0,
    'Piszkozat'              => 0,
    'Privat/Szunet'          => 0,
    'Limit miatt leallitva'  => 0,
];

$crm_rows = [];
foreach ( $listings as $l ) {
    $valid_views = (int) get_post_meta( $l->ID, 'va_views', true );
    $price_raw   = get_post_meta( $l->ID, 'va_price', true );
    $price_type  = (string) get_post_meta( $l->ID, 'va_price_type', true );
    $is_featured = get_post_meta( $l->ID, 'va_featured', true ) === '1';
    $is_suspended = get_post_meta( $l->ID, 'va_is_suspended', true ) === '1';
    $suspended_by_plan = get_post_meta( $l->ID, 'va_suspended_by_plan', true ) === '1';

    $created_ts = strtotime( $l->post_date );
    if ( $created_ts >= ( $crm_now_ts - ( 7 * DAY_IN_SECONDS ) ) ) {
        $crm_last7_count++;
    }
    if ( $created_ts >= ( $crm_now_ts - ( 30 * DAY_IN_SECONDS ) ) ) {
        $crm_last30_count++;
    }

    $crm_total_views += $valid_views;

    if ( $is_featured ) {
        $crm_featured_count++;
    }
    if ( class_exists( 'VA_User_Roles' ) && VA_User_Roles::is_boosted( $l->ID ) ) {
        $crm_boosted_count++;
    }
    if ( class_exists( 'VA_User_Roles' ) && VA_User_Roles::is_new_pill( $l->ID ) ) {
        $crm_newpill_count++;
    }

    if ( is_numeric( $price_raw ) && $price_type !== 'ask' ) {
        $crm_total_price += (float) $price_raw;
        $crm_priced_count++;
    }

    if ( $l->post_status === 'publish' ) {
        $crm_active_count++;
        $crm_status_counts['Aktiv']++;
    } elseif ( $l->post_status === 'pending' ) {
        $crm_status_counts['Jovahagyasra var']++;
    } elseif ( $l->post_status === 'draft' ) {
        $crm_status_counts['Piszkozat']++;
    } elseif ( $suspended_by_plan ) {
        $crm_status_counts['Limit miatt leallitva']++;
    } else {
        $crm_status_counts['Privat/Szunet']++;
    }

    $crm_rows[] = [
        'id'      => (int) $l->ID,
        'title'   => (string) $l->post_title,
        'url'     => get_permalink( $l->ID ),
        'views'   => $valid_views,
        'date_ts' => $created_ts,
        'status'  => (string) $l->post_status,
    ];
}

usort( $crm_rows, static function( array $a, array $b ): int {
    return $b['views'] <=> $a['views'];
} );

$crm_top_rows      = array_slice( $crm_rows, 0, 5 );
$crm_avg_views     = $crm_total_listings > 0 ? ( $crm_total_views / $crm_total_listings ) : 0;
$crm_avg_price     = $crm_priced_count > 0 ? ( $crm_total_price / $crm_priced_count ) : 0;
$crm_watch_count   = count( $watchlist );
$crm_bid_count     = count( $bids );
$crm_plan_limit    = (int) ( $plan_check['limit'] ?? 0 );
$crm_plan_used     = (int) ( $plan_check['used'] ?? 0 );
$crm_plan_usage_pc = $crm_plan_limit > 0 ? min( 100, (int) round( ( $crm_plan_used / $crm_plan_limit ) * 100 ) ) : 0;

$crm_top_title = $crm_top_rows ? (string) $crm_top_rows[0]['title'] : 'Nincs adat';
$crm_top_views = $crm_top_rows ? (int) $crm_top_rows[0]['views'] : 0;

// Rendezés
$sort_by  = sanitize_key( $_GET['sort_by'] ?? 'date' );
$sort_dir = sanitize_key( $_GET['sort_dir'] ?? 'desc' );
if ( ! in_array( $sort_by, [ 'date', 'views', 'price' ], true ) ) $sort_by = 'date';
if ( ! in_array( $sort_dir, [ 'asc', 'desc' ], true ) ) $sort_dir = 'desc';

// Képszámok (batch)
$listing_ids_all = array_column( (array) $listings, 'ID' );
$gallery_meta_map = [];
foreach ( $listing_ids_all as $lid ) {
    $g = get_post_meta( $lid, 'va_gallery_ids', true );
    $gallery_meta_map[ $lid ] = count( array_filter( array_map( 'intval', explode( ',', (string) $g ) ) ) );
}

// Akciós árak + lejáratok per listing
$sale_prices = [];
$sale_ends   = [];
$active_sinces = [];
foreach ( $listing_ids_all as $lid ) {
    $sp = get_post_meta( $lid, 'va_sale_price', true );
    $sale_prices[ $lid ] = $sp ? floatval( $sp ) : 0.0;
    $se = get_post_meta( $lid, 'va_sale_price_end', true );
    $sale_ends[ $lid ] = $se ?: '';
    $as = (int) get_post_meta( $lid, 'va_active_since', true );
    $active_sinces[ $lid ] = $as ?: 0;
}

// Sort listings
usort( $listings, function( $a, $b ) use ( $sort_by, $sort_dir, $sale_prices ) {
    if ( $sort_by === 'views' ) {
        $av = (int) get_post_meta( $a->ID, 'va_views', true );
        $bv = (int) get_post_meta( $b->ID, 'va_views', true );
        $cmp = $av <=> $bv;
    } elseif ( $sort_by === 'price' ) {
        $ap = floatval( get_post_meta( $a->ID, 'va_price', true ) );
        $bp = floatval( get_post_meta( $b->ID, 'va_price', true ) );
        $cmp = $ap <=> $bp;
    } else {
        $cmp = strtotime( $a->post_date ) <=> strtotime( $b->post_date );
    }
    return $sort_dir === 'asc' ? $cmp : -$cmp;
} );

// Profil-teljességi %
$completeness_items = [
    'Megjelenítési név' => ! empty( $user->display_name ),
    'E-mail'            => ! empty( $user->user_email ),
    'Telefonszám'       => ! empty( $phone ),
    'Bemutatkozás'      => ! empty( $user->description ),
    'Profilkép'         => ! empty( $avatar_url ),
    'Aktív hirdetés'    => $crm_active_count > 0,
];
$completeness_done  = count( array_filter( $completeness_items ) );
$completeness_pct   = (int) round( $completeness_done / count( $completeness_items ) * 100 );

// Tagság kora
$membership_days = (int) floor( ( time() - strtotime( $user->user_registered ) ) / 86400 );
?>
<div class="va-wrap">
    <?php va_display_flash(); ?>

    <div class="va-dashboard">

        <!-- Bal navigáció -->
        <nav class="va-dashboard__nav">
            <div class="va-dash-user-head">
                <div class="va-dash-user-head__avatar">
                    <?php if ( $avatar_url ): ?>
                        <img src="<?php echo esc_url( $avatar_url ); ?>" alt="Profilkép">
                    <?php else: ?>
                        <?php echo esc_html( strtoupper( mb_substr( $user->display_name ?: $user->user_login, 0, 1 ) ) ); ?>
                    <?php endif; ?>
                </div>
                <div class="va-dash-user-head__name"><?php echo esc_html( $user->display_name ?: $user->user_login ); ?></div>
            </div>
            <form method="post" enctype="multipart/form-data" class="va-dash-avatar-form" id="va-avatar-form">
                <?php wp_nonce_field( 'va_profile_avatar', 'va_profile_avatar_nonce' ); ?>
                <input type="hidden" name="va_action" value="profile_avatar">
                <input type="file" name="profile_avatar" id="va-avatar-input" class="va-dash-avatar-form__file" accept="image/jpeg,image/png,image/webp">
                <label for="va-avatar-input" class="va-dash-avatar-drop" id="va-avatar-drop">
                    <span class="va-dash-avatar-drop__icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3.5"/><path d="M20.8 13a9 9 0 1 1-1.4-4.8"/><path d="M17 3l3 3-3 3"/><line x1="20" y1="6" x2="14" y2="6"/></svg>
                    </span>
                    <span class="va-dash-avatar-drop__text" id="va-avatar-label">Kép kiválasztása</span>
                    <span class="va-dash-avatar-drop__hint">JPG, PNG, WEBP</span>
                </label>
                <?php if ( $avatar_url ): ?>
                <label class="va-dash-avatar-form__remove">
                    <input type="checkbox" name="profile_avatar_remove" value="1"> Profilkép törlése
                </label>
                <?php endif; ?>
                <button type="submit" class="va-dash-avatar-form__btn">Mentés</button>
            </form>
            <span class="va-dashboard__nav-item active" data-tab="listings"><span class="va-dashboard__nav-ico" aria-hidden="true">📋</span><span class="va-dashboard__nav-label">Hirdetéseim (<?php echo count( $listings ); ?>)</span></span>
            <?php if ( $auctions_enabled ): ?>
            <span class="va-dashboard__nav-item" data-tab="bids"><span class="va-dashboard__nav-ico" aria-hidden="true">🔨</span><span class="va-dashboard__nav-label">Licitjeim (<?php echo count( $bids ); ?>)</span></span>
            <?php endif; ?>
            <span class="va-dashboard__nav-item" data-tab="watchlist"><span class="va-dashboard__nav-ico" aria-hidden="true">♥</span><span class="va-dashboard__nav-label">Kedvenceim (<?php echo count( $watchlist ); ?>)</span></span>
            <span class="va-dashboard__nav-item" data-tab="profile"><span class="va-dashboard__nav-ico" aria-hidden="true">👤</span><span class="va-dashboard__nav-label">Profilom</span></span>
            <span class="va-dashboard__nav-item va-dashboard__nav-item--danger" data-tab="deleteaccount"><span class="va-dashboard__nav-ico" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg></span><span class="va-dashboard__nav-label">Fiók törlése</span></span>
            <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="va-dashboard__nav-item va-dashboard__nav-item--logout"><span class="va-dashboard__nav-ico" aria-hidden="true">🚪</span><span class="va-dashboard__nav-label">Kijelentkezés</span></a>

            <?php if ( $plan_cfg ): ?>
            <div class="va-dash-plan-badge" style="--pc:<?php echo esc_attr( $plan_cfg['color'] ); ?>;--pb:<?php echo esc_attr( $plan_cfg['bg'] ); ?>">
                <span class="va-dash-plan-icon"><?php echo esc_html( $plan_cfg['icon'] ); ?></span>
                <span class="va-dash-plan-label"><?php echo esc_html( $plan_cfg['label'] ); ?> Csomag</span>
                <?php if ( $plan_check['limit'] > 0 ): ?>
                <div class="va-dash-plan-usage">
                    <?php
                    $pu = $plan_check['used'];
                    $pl = $plan_check['limit'];
                    $pp = min( 100, (int) round( $pu / $pl * 100 ) );
                    $pc = $pp >= 100 ? '#ff4444' : ( $pp >= 80 ? '#ffb400' : '#00c850' );
                    ?>
                    <span><?php echo esc_html( $pu . '/' . $pl ); ?> hird.</span>
                    <div class="va-dash-plan-bar"><div style="width:<?php echo esc_attr( $pp ); ?>%;background:<?php echo esc_attr( $pc ); ?>"></div></div>
                </div>
                <?php endif; ?>
                <small>⭡ <?php echo esc_html( $plan_cfg['boost_cooldown'] ); ?> naponként emelhető</small>
                <?php if ( $user_plan === 'platinum' ): ?>
                <form method="post" class="va-dash-plan-label-form">
                    <?php wp_nonce_field( 'va_profile_label', 'va_profile_label_nonce' ); ?>
                    <input type="hidden" name="va_action" value="profile_label">
                    <label class="va-dash-plan-label-form__label">Saját rang címke</label>
                    <input type="text"
                           name="profile_seller_label"
                           class="va-dash-plan-label-form__input"
                           value="<?php echo esc_attr( $seller_label ); ?>"
                           placeholder="pl. Kereskedő, Viszonteladó"
                           maxlength="40">
                    <button type="submit" class="va-dash-plan-label-form__btn">Mentés</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
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

                <section class="va-crm" aria-label="Hirdetés statisztika">
                    <div class="va-crm__head">
                        <h3>Teljesitmeny iranyitopult</h3>
                        <p>Valid megtekintes + publikacios allapot + aktivitas egy helyen</p>
                    </div>

                    <div class="va-crm__kpis">
                        <article class="va-crm__kpi">
                            <span class="va-crm__kpi-label">Osszes hirdetes</span>
                            <strong class="va-crm__kpi-value"><?php echo esc_html( number_format( $crm_total_listings, 0, ',', ' ' ) ); ?></strong>
                            <small class="va-crm__kpi-sub">Aktiv: <?php echo esc_html( number_format( $crm_active_count, 0, ',', ' ' ) ); ?></small>
                        </article>
                        <article class="va-crm__kpi">
                            <span class="va-crm__kpi-label">Osszes valid megtekintes</span>
                            <strong class="va-crm__kpi-value"><?php echo esc_html( number_format( $crm_total_views, 0, ',', ' ' ) ); ?></strong>
                            <small class="va-crm__kpi-sub">Atlag/hirdetes: <?php echo esc_html( number_format( $crm_avg_views, 1, ',', ' ' ) ); ?></small>
                        </article>
                        <article class="va-crm__kpi">
                            <span class="va-crm__kpi-label">Atlagos ar</span>
                            <strong class="va-crm__kpi-value"><?php echo esc_html( number_format( $crm_avg_price, 0, ',', ' ' ) ); ?> Ft</strong>
                            <small class="va-crm__kpi-sub">Szamolt hirdetes: <?php echo esc_html( number_format( $crm_priced_count, 0, ',', ' ' ) ); ?></small>
                        </article>
                        <article class="va-crm__kpi">
                            <span class="va-crm__kpi-label">Top hirdetes</span>
                            <strong class="va-crm__kpi-value"><?php echo esc_html( number_format( $crm_top_views, 0, ',', ' ' ) ); ?></strong>
                            <small class="va-crm__kpi-sub"><?php echo esc_html( wp_trim_words( $crm_top_title, 6, '...' ) ); ?></small>
                        </article>
                        <article class="va-crm__kpi">
                            <span class="va-crm__kpi-label">Uj aktivitás</span>
                            <strong class="va-crm__kpi-value"><?php echo esc_html( number_format( $crm_last7_count, 0, ',', ' ' ) ); ?> / 7 nap</strong>
                            <small class="va-crm__kpi-sub"><?php echo esc_html( number_format( $crm_last30_count, 0, ',', ' ' ) ); ?> / 30 nap</small>
                        </article>
                        <article class="va-crm__kpi">
                            <span class="va-crm__kpi-label">Kapcsolodo mutatok</span>
                            <strong class="va-crm__kpi-value"><?php echo esc_html( number_format( $crm_watch_count, 0, ',', ' ' ) ); ?> kedvenc</strong>
                            <small class="va-crm__kpi-sub"><?php echo esc_html( number_format( $crm_bid_count, 0, ',', ' ' ) ); ?> licit | <?php echo esc_html( $crm_featured_count ); ?> kiemelt</small>
                        </article>
                    </div>

                    <div class="va-crm__panels">
                        <article class="va-crm__panel">
                            <h4>Statusz megoszlas</h4>
                            <div class="va-crm__status-list">
                                <?php foreach ( $crm_status_counts as $label => $count ):
                                    $pc = $crm_total_listings > 0 ? ( $count / $crm_total_listings ) * 100 : 0;
                                ?>
                                <div class="va-crm__status-row">
                                    <div class="va-crm__status-meta">
                                        <span><?php echo esc_html( $label ); ?></span>
                                        <strong><?php echo esc_html( number_format( $count, 0, ',', ' ' ) ); ?></strong>
                                    </div>
                                    <div class="va-crm__status-bar"><span style="width:<?php echo esc_attr( max( 4, round( $pc, 1 ) ) ); ?>%"></span></div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ( $crm_plan_limit > 0 ): ?>
                            <div class="va-crm__plan-use">
                                <div class="va-crm__plan-use-meta">
                                    <span>Csomagkihasznaltsag</span>
                                    <strong><?php echo esc_html( $crm_plan_used . '/' . $crm_plan_limit ); ?></strong>
                                </div>
                                <div class="va-crm__status-bar"><span style="width:<?php echo esc_attr( max( 4, $crm_plan_usage_pc ) ); ?>%"></span></div>
                            </div>
                            <?php endif; ?>
                        </article>

                        <article class="va-crm__panel">
                            <h4>Top 5 hirdetes (valid megtekintes)</h4>
                            <?php if ( $crm_top_rows ): ?>
                            <ol class="va-crm__top-list">
                                <?php foreach ( $crm_top_rows as $row ): ?>
                                <li>
                                    <a href="<?php echo esc_url( (string) $row['url'] ); ?>"><?php echo esc_html( (string) $row['title'] ); ?></a>
                                    <span><?php echo esc_html( number_format( (int) $row['views'], 0, ',', ' ' ) ); ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ol>
                            <?php else: ?>
                            <p class="va-crm__empty">Meg nincs eleg adat a toplistahoz.</p>
                            <?php endif; ?>

                            <div class="va-crm__badges">
                                <span>Kiemelt: <strong><?php echo esc_html( number_format( $crm_featured_count, 0, ',', ' ' ) ); ?></strong></span>
                                <span>Boost aktiv: <strong><?php echo esc_html( number_format( $crm_boosted_count, 0, ',', ' ' ) ); ?></strong></span>
                                <span>Uj pill aktiv: <strong><?php echo esc_html( number_format( $crm_newpill_count, 0, ',', ' ' ) ); ?></strong></span>
                            </div>
                        </article>
                    </div>
                </section>

                <?php if ( $listings ): ?>
                <!-- Sort controls -->
                <?php
                $cur_url = strtok( (string) $_SERVER['REQUEST_URI'], '?' );
                $make_sort_url = function( string $by ) use ( $cur_url, $sort_by, $sort_dir ): string {
                    $dir = ( $sort_by === $by && $sort_dir === 'desc' ) ? 'asc' : 'desc';
                    return esc_url( add_query_arg( [ 'sort_by' => $by, 'sort_dir' => $dir ], $cur_url ) );
                };
                $sort_arrow = function( string $by ) use ( $sort_by, $sort_dir ): string {
                    if ( $sort_by !== $by ) return '<span style="opacity:.3">↕</span>';
                    return $sort_dir === 'desc' ? '↓' : '↑';
                };
                ?>
                <div class="va-sort-bar">
                    <span style="font-size:11px;color:rgba(255,255,255,.4);margin-right:6px;">Rendezés:</span>
                    <a href="<?php echo $make_sort_url('date'); ?>" class="va-sort-btn <?php echo $sort_by==='date'?'active':''; ?>">Dátum <?php echo $sort_arrow('date'); ?></a>
                    <a href="<?php echo $make_sort_url('views'); ?>" class="va-sort-btn <?php echo $sort_by==='views'?'active':''; ?>">Nézettség <?php echo $sort_arrow('views'); ?></a>
                    <a href="<?php echo $make_sort_url('price'); ?>" class="va-sort-btn <?php echo $sort_by==='price'?'active':''; ?>">Ár <?php echo $sort_arrow('price'); ?></a>
                </div>

                <!-- Bulk toolbar -->
                <div class="va-bulk-toolbar" id="va-bulk-toolbar">
                    <label class="va-bulk-select-all-label">
                        <input type="checkbox" id="va-select-all"> Összes
                    </label>
                    <div class="va-bulk-dropdown" id="va-bulk-dropdown">
                        <button type="button" class="va-bulk-dropdown__toggle" id="va-bulk-dropdown-toggle">
                            <span id="va-bulk-dropdown-label">— Tömeges művelet —</span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div class="va-bulk-dropdown__menu" id="va-bulk-dropdown-menu" style="display:none;">
                            <button type="button" class="va-bulk-dropdown__item" data-value="" data-label="— Tömeges művelet —">— Tömeges művelet —</button>
                            <button type="button" class="va-bulk-dropdown__item" data-value="activate" data-label="✅ Aktiválás">✅ Aktiválás</button>
                            <button type="button" class="va-bulk-dropdown__item" data-value="suspend" data-label="⏸ Szüneteltetés">⏸ Szüneteltetés</button>
                            <button type="button" class="va-bulk-dropdown__item" data-value="price_change" data-label="💰 Ár módosítása">💰 Ár módosítása</button>
                            <button type="button" class="va-bulk-dropdown__item" data-value="delete" data-label="🗑 Törlés">🗑 Törlés</button>
                        </div>
                        <input type="hidden" id="va-bulk-action" value="">
                    </div>
                    <button class="va-btn va-btn--sm va-bulk-exec" id="va-bulk-exec" style="background:rgba(255,0,0,.18);border:1px solid rgba(255,0,0,.4);color:#fff;">Végrehajtás</button>
                    <span class="va-bulk-count" id="va-bulk-count" style="font-size:11px;color:rgba(255,255,255,.45);margin-left:6px;"></span>
                </div>

                <!-- Bulk ár módosítás panel -->
                <div class="va-bulk-price-panel" id="va-bulk-price-panel" style="display:none;">
                    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
                        <div>
                            <label class="va-bulk-price-label">Új ár (Ft)</label>
                            <input type="number" id="va-bulk-new-price" class="va-bulk-price-input" min="0" placeholder="pl. 1500000">
                        </div>
                        <div>
                            <label class="va-bulk-price-label">Akciós ár (opcionális, Ft)</label>
                            <input type="number" id="va-bulk-sale-price" class="va-bulk-price-input" min="0" placeholder="Üresen hagyd, ha nincs">
                        </div>
                        <div>
                            <label class="va-bulk-price-label">Akció vége (opcionális)</label>
                            <input type="text" id="va-bulk-sale-end" class="va-bulk-price-input" placeholder="YYYY-MM-DD" inputmode="numeric" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}">
                        </div>
                    </div>
                    <p style="font-size:11px;color:rgba(255,255,255,.4);margin:8px 0 0;">Az akciós ár minden kijelölt hirdetésre vonatkozik. A normál ár módosítása után külön akciós ár is állítható egyenként.</p>
                </div>

                <table class="va-user-listings-table" style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.1);">
                            <th style="padding:8px 4px;width:28px;"></th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Cím</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);white-space:nowrap;min-width:110px;">Ár</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Státusz</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Dátum</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Megtekintés</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Kiemelés</th>
                            <th style="padding:8px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $listings as $l ):
                        $price    = get_post_meta( $l->ID, 'va_price', true );
                        $p_type   = get_post_meta( $l->ID, 'va_price_type', true ) ?: 'fixed';
                        $valid_views = (int) get_post_meta( $l->ID, 'va_views', true );
                        $is_suspended    = get_post_meta( $l->ID, 'va_is_suspended', true ) === '1';
                        $suspended_by_plan = get_post_meta( $l->ID, 'va_suspended_by_plan', true ) === '1';
                        $suspended_at    = (int) get_post_meta( $l->ID, 'va_suspended_at', true );
                        $active_since_ts = isset( $active_sinces[ $l->ID ] ) ? $active_sinces[ $l->ID ] : (int) get_post_meta( $l->ID, 'va_active_since', true );
                        if ( ! $active_since_ts ) $active_since_ts = strtotime( $l->post_date );
                        $now             = current_time( 'timestamp' );
                        $can_suspend     = in_array( $user_plan, [ 'gold', 'platinum' ], true );
                        $buy_url         = home_url( '/va-kredit-vasarlas/' );
                        $days_running    = (int) max( 1, ceil( ( $now - $active_since_ts ) / 86400 ) );
                        $days_expiry     = max( 0, 30 - $days_running );
                        $img_count       = isset( $gallery_meta_map[ $l->ID ] ) ? (int) $gallery_meta_map[ $l->ID ] : 0;
                        $l_sale_price    = $sale_prices[ $l->ID ] ?? 0.0;
                        $l_sale_end      = $sale_ends[ $l->ID ] ?? '';
                        $statuses = [
                            'publish' => '<span style="color:#00c850">● Aktív</span>',
                            'pending' => '<span style="color:#ffb400">● Jóváhagyásra vár</span>',
                            'draft'   => '<span style="color:#888">● Piszkozat</span>',
                            'private' => $suspended_by_plan
                                ? '<span style="color:#ff4444;font-weight:600;">⏹ Leállítva</span>'
                                : ( $is_suspended
                                    ? '<span style="color:#ff9900">⏸ Szüneteltetve</span>'
                                    : '<span style="color:#888">● Privát</span>' ),
                        ];
                    ?>
                    <tr style="border-bottom:1px solid rgba(255,255,255,0.05);" class="va-listing-row" data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>">
                        <td style="padding:10px 4px;text-align:center;vertical-align:middle;">
                            <input type="checkbox" class="va-row-check" value="<?php echo esc_attr( (string) $l->ID ); ?>" style="cursor:pointer;width:15px;height:15px;accent-color:#ff2a2a;">
                        </td>
                        <td style="padding:10px 8px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <?php
                                $thumb_id  = get_post_thumbnail_id( $l->ID );
                                if ( ! $thumb_id ) {
                                    $gallery = get_post_meta( $l->ID, 'va_gallery_ids', true );
                                    $ids     = array_filter( array_map( 'intval', explode( ',', (string)$gallery ) ) );
                                    $thumb_id = $ids[0] ?? 0;
                                }
                                if ( $thumb_id ) :
                                    $thumb_url = wp_get_attachment_image_url( $thumb_id, 'thumbnail' );
                                ?>
                                <img src="<?php echo esc_url( $thumb_url ); ?>" alt="" style="width:52px;height:52px;object-fit:cover;border-radius:6px;flex-shrink:0;border:1px solid rgba(255,255,255,.1);">
                                <?php endif; ?>
                                <div style="min-width:0;">
                                    <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap;">
                                        <a href="<?php echo esc_url( get_permalink( $l->ID ) ); ?>" style="color:#fff;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;"><?php echo esc_html( $l->post_title ); ?></a>
                                        <?php if ( $img_count > 0 ): ?>
                                        <span class="va-img-count-badge"><svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><?php echo esc_html( $img_count ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( get_post_meta( $l->ID, 'va_featured', true ) === '1' ): ?>
                                        <span style="display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;background:linear-gradient(135deg,#3a2800,#1e1400);color:#ffc840;border:1px solid rgba(255,180,0,.5);box-shadow:0 0 8px rgba(255,160,0,.2);padding:2px 7px;border-radius:20px;"><svg width="9" height="9" viewBox="0 0 24 24" fill="#ffc840" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>Kiemelt</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="margin-top:3px;font-size:11px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <?php if ( $suspended_by_plan ): ?>
                                        <span style="color:#ff4444;">Limit felett – kredit szükséges</span>
                                    <?php elseif ( $is_suspended && $suspended_at ): ?>
                                        <span style="color:#ff9900;">⏸ Szüneteltetve: <?php echo esc_html( date_i18n( 'Y.m.d', $suspended_at ) ); ?></span>
                                    <?php else: ?>
                                        <span style="color:rgba(255,255,255,.35);">Fut: <?php echo esc_html( $days_running . ' napja' ); ?></span>
                                        <?php if ( $l->post_status === 'publish' && $days_expiry <= 7 ): ?>
                                        <span class="va-expiry-badge <?php echo $days_expiry <= 3 ? 'va-expiry-badge--critical' : 'va-expiry-badge--warn'; ?>">⏰ <?php echo $days_expiry > 0 ? esc_html( $days_expiry . ' nap múlva lejár' ) : 'Lejárt'; ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="padding:10px 8px;white-space:nowrap;min-width:110px;">
                            <?php if ( $l_sale_price > 0 ): ?>
                                <del style="color:rgba(255,255,255,.4);font-size:12px;"><?php echo esc_html( va_format_price( $price, $p_type ) ); ?></del><br>
                                <span style="color:#ff3030;font-weight:700;"><?php echo esc_html( number_format( $l_sale_price, 0, ',', ' ' ) . ' Ft' ); ?> <span style="font-size:10px;background:rgba(255,0,0,.2);border:1px solid rgba(255,0,0,.4);border-radius:4px;padding:1px 5px;">AKCIÓ</span></span>
                                <?php if ( $l_sale_end ): ?><br><span style="font-size:10px;color:rgba(255,255,255,.35);">–<?php echo esc_html( $l_sale_end ); ?></span><?php endif; ?>
                                <button class="va-sale-edit-btn va-sale-edit-btn--active" data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>" data-sale-price="<?php echo esc_attr( (string) $l_sale_price ); ?>" data-sale-end="<?php echo esc_attr( $l_sale_end ); ?>" title="Akciós ár szerkesztése">✎</button>
                            <?php else: ?>
                                <?php echo esc_html( va_format_price( $price, $p_type ) ); ?>
                                <button class="va-sale-edit-btn va-sale-edit-btn--idle" data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>" data-sale-price="" data-sale-end="" title="Akciós ár beállítása">✎</button>
                            <?php endif; ?>
                        </td>
                        <td style="padding:10px 8px;"><?php echo $statuses[ $l->post_status ] ?? esc_html( $l->post_status ); ?></td>
                        <td style="padding:10px 8px;color:rgba(255,255,255,0.5);"><?php echo esc_html( get_the_date( 'Y.m.d', $l ) ); ?></td>
                        <td style="padding:10px 8px;">
                            <span style="display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;letter-spacing:.02em;background:linear-gradient(135deg,rgba(255,0,0,.20),rgba(90,0,0,.20));color:#fff;border:1px solid rgba(255,0,0,.55);box-shadow:0 0 8px rgba(255,0,0,.18);padding:3px 9px;border-radius:999px;white-space:nowrap;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <?php echo esc_html( number_format( $valid_views, 0, ',', ' ' ) ); ?>
                            </span>
                        </td>
                        <td style="padding:10px 8px;">
                            <?php
                            // Boost gomb
                            if ( class_exists( 'VA_User_Roles' ) ) :
                                echo '<div class="va-pill-toggles">';
                                $boost_info = VA_User_Roles::can_boost( $user->ID, $l->ID );
                                $is_boosted_now = VA_User_Roles::is_boosted( $l->ID );
                                $is_new_pill_now = VA_User_Roles::is_new_pill( $l->ID );
                                if ( $is_boosted_now ):
                            ?>
                            <button class="va-boost-btn va-boost-btn--on"
                                    data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>"
                                    data-nonce="<?php echo esc_attr( $boost_nonce ); ?>"
                                    data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
                                    data-mode="remove"
                                    aria-pressed="true">
                                <span class="va-boost-btn__dot" aria-hidden="true"></span>Kiemelt: BE
                            </button>
                            <?php elseif ( $boost_info['can'] ):
                            ?>
                            <button class="va-boost-btn va-boost-btn--off"
                                    data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>"
                                    data-nonce="<?php echo esc_attr( $boost_nonce ); ?>"
                                    data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
                                    data-mode="boost"
                                    aria-pressed="false">
                                <span class="va-boost-btn__dot" aria-hidden="true"></span>Kiemelés: KI
                            </button>
                            <?php else:
                                $hrs = (int) ceil( $boost_info['seconds_remaining'] / 3600 );
                                $days_left = (int) ceil( $boost_info['seconds_remaining'] / 86400 );
                            ?>
                            <span class="va-boost-cd" style="font-size:11px;color:rgba(255,255,255,.35);" title="<?php echo esc_attr( $boost_info['cooldown_days'] . ' naponként emelhető' ); ?>">
                                &#9889; <?php echo $boost_info['seconds_remaining'] >= 86400 ? esc_html( $days_left . ' nap' ) : esc_html( $hrs . 'ó' ); ?>
                            </span>
                            <?php
                                endif;

                                if ( $is_new_pill_now ):
                            ?>
                            <button class="va-newpill-btn va-newpill-btn--on"
                                    data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>"
                                    data-nonce="<?php echo esc_attr( $boost_nonce ); ?>"
                                    data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
                                    data-mode="off"
                                    aria-pressed="true">
                                <span class="va-newpill-btn__dot" aria-hidden="true"></span>Új pill: BE
                            </button>
                            <?php else: ?>
                            <button class="va-newpill-btn va-newpill-btn--off"
                                    data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>"
                                    data-nonce="<?php echo esc_attr( $boost_nonce ); ?>"
                                    data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
                                    data-mode="on"
                                    aria-pressed="false">
                                <span class="va-newpill-btn__dot" aria-hidden="true"></span>Új pill: KI
                            </button>
                            <?php endif;
                                echo '</div>';
                            endif;
                            ?>
                        </td>
                        <td style="padding:10px 8px;text-align:right;white-space:nowrap;">
                            <?php
                            $edit_page = get_page_by_path('va-hirdetes-feladas');
                            $edit_url  = $edit_page
                                ? add_query_arg( 'edit', $l->ID, get_permalink( $edit_page ) )
                                : get_edit_post_link( $l->ID );
                            ?>
                            <?php if ( $suspended_by_plan ): ?>
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;flex-wrap:nowrap;">
                                <a href="<?php echo esc_url( $buy_url ); ?>" class="va-btn va-btn--sm" style="background:linear-gradient(135deg,rgba(255,60,60,.25),rgba(200,0,0,.25));border:1px solid rgba(255,60,60,.5);color:#ff6060;white-space:nowrap;font-weight:600;">Kredit vásárlás →</a>
                                <form method="post" style="margin:0;" onsubmit="return confirm('Biztosan törlöd ezt a hirdetést?');">
                                    <?php wp_nonce_field( 'va_delete_listing', 'va_delete_listing_nonce' ); ?>
                                    <input type="hidden" name="va_action" value="delete_listing">
                                    <input type="hidden" name="listing_id" value="<?php echo esc_attr( (string) $l->ID ); ?>">
                                    <button type="submit" class="va-btn va-btn--sm" style="background:rgba(255,42,42,.12);border:1px solid rgba(255,42,42,.4);color:#ff6060;white-space:nowrap;">Törlés</button>
                                </form>
                            </div>
                            <?php else: ?>
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px;flex-wrap:nowrap;">
                                <a href="<?php echo esc_url( $edit_url ); ?>" class="va-btn va-btn--sm" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.18);color:#fff;white-space:nowrap;">Szerkesztés</a>
                                <?php if ( $l->post_status === 'publish' ): ?>
                                <button class="va-refresh-btn va-btn va-btn--sm"
                                        data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>"
                                        data-nonce="<?php echo esc_attr( $boost_nonce ); ?>"
                                        data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
                                        title="Hirdetés frissítése (lista tetejére tol)"
                                        style="background:rgba(0,180,255,.1);border:1px solid rgba(0,180,255,.35);color:#60d0ff;white-space:nowrap;">↑ Frissítés</button>
                                <?php endif; ?>
                                <?php if ( $can_suspend && in_array( $l->post_status, [ 'publish', 'private' ], true ) ): ?>
                                <form method="post" style="margin:0;">
                                    <?php wp_nonce_field( 'va_suspend_listing', 'va_suspend_listing_nonce' ); ?>
                                    <input type="hidden" name="va_action" value="suspend_listing">
                                    <input type="hidden" name="listing_id" value="<?php echo esc_attr( (string) $l->ID ); ?>">
                                    <?php if ( $is_suspended ): ?>
                                        <button type="submit" class="va-btn va-btn--sm" style="background:rgba(0,200,80,.12);border:1px solid rgba(0,200,80,.4);color:#00c850;white-space:nowrap;">▶ Indítás</button>
                                    <?php else: ?>
                                        <button type="submit" class="va-btn va-btn--sm" style="background:rgba(255,153,0,.12);border:1px solid rgba(255,153,0,.4);color:#ff9900;white-space:nowrap;">⏸ Szünet</button>
                                    <?php endif; ?>
                                </form>
                                <?php endif; ?>
                                <form method="post" style="margin:0;" onsubmit="return confirm('Biztosan törlöd ezt a hirdetést? A képekkel együtt végleg eltűnik!');">
                                    <?php wp_nonce_field( 'va_delete_listing', 'va_delete_listing_nonce' ); ?>
                                    <input type="hidden" name="va_action" value="delete_listing">
                                    <input type="hidden" name="listing_id" value="<?php echo esc_attr( (string) $l->ID ); ?>">
                                    <button type="submit" class="va-btn va-btn--sm" style="background:rgba(255,42,42,.12);border:1px solid rgba(255,42,42,.4);color:#ff6060;white-space:nowrap;">Törlés</button>
                                </form>
                            </div>
                            <?php endif; ?>
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

                <!-- Profil teljességi sáv -->
                <div class="va-profile-completeness">
                    <div class="va-profile-completeness__head">
                        <span class="va-profile-completeness__label">Profil teljessége</span>
                        <span class="va-profile-completeness__pct"><?php echo esc_html( $completeness_pct ); ?>%</span>
                    </div>
                    <div class="va-profile-completeness__bar">
                        <div style="width:<?php echo esc_attr( $completeness_pct ); ?>%;background:<?php echo $completeness_pct >= 80 ? '#00c850' : ( $completeness_pct >= 50 ? '#ffb400' : '#ff4444' ); ?>"></div>
                    </div>
                    <div class="va-profile-completeness__items">
                    <?php foreach ( $completeness_items as $item_label => $item_done ): ?>
                        <span class="va-profile-completeness__item <?php echo $item_done ? 'done' : ''; ?>"><?php echo $item_done ? '✓' : '○'; ?> <?php echo esc_html( $item_label ); ?></span>
                    <?php endforeach; ?>
                    </div>
                </div>

                <!-- Trust badge-ek -->
                <div class="va-trust-badges">
                    <span class="va-trust-badge <?php echo $user->user_email ? 'active' : ''; ?>">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        E-mail megerősítve
                    </span>
                    <span class="va-trust-badge <?php echo $phone ? 'active' : ''; ?>">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.56 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Telefonszám megadva
                    </span>
                    <span class="va-trust-badge <?php echo $crm_active_count > 0 ? 'active' : ''; ?>">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        Aktív hirdető
                    </span>
                    <span class="va-trust-badge active" title="Regisztráció óta">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Tagság: <?php echo esc_html( $membership_days < 365 ? $membership_days . ' nap' : round( $membership_days / 365, 1 ) . ' év' ); ?>
                    </span>
                </div>

                <form method="post" enctype="multipart/form-data">
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
                        <label>Profilkép</label>
                        <div class="va-profile-avatar-editor">
                            <div class="va-profile-avatar-editor__preview">
                                <?php if ( $avatar_url ): ?>
                                    <img src="<?php echo esc_url( $avatar_url ); ?>" alt="Profilkép előnézet">
                                <?php else: ?>
                                    <?php echo esc_html( strtoupper( mb_substr( $user->display_name ?: $user->user_login, 0, 1 ) ) ); ?>
                                <?php endif; ?>
                            </div>
                            <div class="va-profile-avatar-editor__fields">
                                <input type="file" name="profile_avatar" class="va-input" accept="image/jpeg,image/png,image/webp">
                                <?php if ( $avatar_url ): ?>
                                    <label class="va-profile-avatar-editor__remove">
                                        <input type="checkbox" name="profile_avatar_remove" value="1"> Profilkép törlése
                                    </label>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="va-form-group">
                        <label>Bemutatkozás</label>
                        <textarea name="profile_bio" class="va-textarea"><?php echo esc_textarea( $user->description ); ?></textarea>
                    </div>

                    <?php if ( $user_plan === 'platinum' ): ?>
                    <div class="va-form-group">
                        <label>Rang / címke a hirdetésoldalakon
                            <span style="font-size:11px;font-weight:400;color:rgba(255,255,255,.45);margin-left:6px;">Platinum jogosultság</span>
                        </label>
                        <input type="text" name="profile_seller_label" class="va-input" value="<?php echo esc_attr( $seller_label ); ?>" placeholder="pl. Kereskedő, Viszonteladó – hagyd üresen ha nem kell" maxlength="40">
                        <p style="font-size:11px;color:rgba(255,255,255,0.4);margin-top:4px;">Ez jelenik meg a feladó blokkban a hirdetéseidnél. Üresen hagyva az alapértelmezett csomagcímke látszik.</p>
                    </div>
                    <?php endif; ?>

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

            <!-- Tab: Fiók törlése -->
            <div id="va-tab-deleteaccount" class="va-dashboard__section">
                <h2 class="va-dashboard__title" style="color:#ff6060;">Fiók törlése</h2>
                <div class="va-danger-zone">
                    <p class="va-danger-zone__desc">A fiók törlése <strong>visszafordíthatatlan</strong>! Összes hirdetésed, képeid és adataid végleg törlődnek.</p>
                    <p class="va-danger-zone__desc">A megerősítéshez írd be az alábbi mezőbe: <strong style="color:#ff6060;">TORLESEM</strong></p>
                    <form method="post">
                        <?php wp_nonce_field( 'va_delete_profile', 'va_delete_profile_nonce' ); ?>
                        <input type="hidden" name="va_action" value="delete_profile">
                        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:12px;">
                            <input id="va-confirm-del-input" type="text" name="confirm_delete" class="va-input va-danger-zone__input" placeholder="TORLESEM" autocomplete="off">
                            <button type="submit" class="va-btn va-danger-zone__submit" onclick="return this.form['confirm_delete'].value==='TORLESEM'||(alert('Írd be pontosan: TORLESEM'),false);">Fiók végleges törlése</button>
                        </div>
                    </form>
                </div>
            </div>

        </div><!-- .va-dashboard__content -->
    </div><!-- .va-dashboard -->
</div>

<div class="va-sale-modal-overlay" id="va-sale-modal-overlay" role="dialog" aria-modal="true" aria-label="Akciós ár beállítása">
    <div class="va-sale-modal">
        <h3>Akciós ár szerkesztése</h3>
        <input type="hidden" id="va-sale-modal-post-id">
        <label>Akciós ár (Ft) — 0 = törlés</label>
        <input type="number" id="va-sale-modal-price" min="0" placeholder="pl. 1200000">
        <label>Akció vége (opcionális)</label>
        <input type="text" id="va-sale-modal-end" placeholder="YYYY-MM-DD" inputmode="numeric" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}">
        <div class="va-sale-modal__actions">
            <button class="va-sale-modal__remove" id="va-sale-modal-remove">Akció törlése</button>
            <button class="va-sale-modal__cancel" id="va-sale-modal-cancel">Mégse</button>
            <button class="va-sale-modal__save" id="va-sale-modal-save">Mentés</button>
        </div>
    </div>
</div>

<style>
.va-dash-user-head {
    padding:14px;
    border-bottom:1px solid rgba(255,255,255,.08);
    display:flex;
    align-items:center;
    gap:10px;
    min-width:0;
    overflow:hidden;
}
.va-dash-user-head__avatar {
    width:52px;
    height:52px;
    min-width:52px;
    border-radius:50%;
    overflow:hidden;
    background:rgba(255,255,255,.08);
    border:2px solid rgba(255,255,255,.18);
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
    font-size:18px;
    font-weight:800;
    flex-shrink:0;
}
.va-dash-user-head__avatar img { width:100% !important;height:100% !important;object-fit:cover;display:block;border-radius:50%;max-width:none !important;max-height:none !important; }
.va-dash-user-head__name { color:#fff;font-size:12px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;min-width:0; }

.va-dash-avatar-form {
    padding:10px 14px 12px;
    border-bottom:1px solid rgba(255,255,255,.08);
    display:flex;
    flex-direction:column;
    gap:8px;
}
.va-dash-avatar-form__file { display:none; }
.va-dash-avatar-drop {
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:4px;
    padding:12px 8px;
    border:1px dashed rgba(255,255,255,.2);
    border-radius:10px;
    background:rgba(255,255,255,.04);
    cursor:pointer;
    transition:.2s ease;
    text-align:center;
}
.va-dash-avatar-drop:hover,
.va-dash-avatar-drop.drag-over {
    border-color:#ff0000;
    background:rgba(255,0,0,.07);
}
.va-dash-avatar-drop__icon { color:rgba(255,255,255,.45);line-height:1; }
.va-dash-avatar-drop:hover .va-dash-avatar-drop__icon,
.va-dash-avatar-drop.drag-over .va-dash-avatar-drop__icon { color:#ff4444; }
.va-dash-avatar-drop__text { font-size:11px;font-weight:700;color:#fff; }
.va-dash-avatar-drop__hint { font-size:10px;color:rgba(255,255,255,.35); }
.va-dash-avatar-form__remove { font-size:11px;color:rgba(255,255,255,.66);display:flex;gap:6px;align-items:center; }
.va-dash-avatar-form__btn {
    width:100%;
    height:32px;
    border-radius:999px;
    border:1px solid rgba(255,42,42,.45);
    background:rgba(255,42,42,.14);
    color:#fff;
    font-weight:700;
    font-size:12px;
    cursor:pointer;
    transition:.2s ease;
}
.va-dash-avatar-form__btn:hover { background:rgba(255,42,42,.24); }

/* ── Plan badge a navban ── */
.va-dash-plan-badge {
    margin-top:auto;padding:12px 14px;
    background:rgba(255,255,255,.05);
    border-top:1px solid rgba(255,255,255,.08);
    border-radius:0 0 0 10px;
}
.va-dash-plan-icon  { font-size:20px;display:block;margin-bottom:4px; }
.va-dash-plan-label { font-size:12px;font-weight:700;color:var(--pc,#fff);display:block; }
.va-dash-plan-usage { margin-top:6px; }
.va-dash-plan-usage > span { font-size:11px;color:rgba(255,255,255,.5); }
.va-dash-plan-bar {
    height:3px;background:rgba(255,255,255,.1);border-radius:2px;margin-top:3px;
}
.va-dash-plan-bar > div { height:3px;border-radius:2px;transition:width .3s; }
.va-dash-plan-badge small { font-size:10px;color:rgba(255,255,255,.3);display:block;margin-top:6px; }
.va-dash-plan-label-form {
    margin-top:10px;
    padding-top:10px;
    border-top:1px solid rgba(255,255,255,.08);
    display:flex;
    flex-direction:column;
    gap:8px;
}
.va-dash-plan-label-form__label { font-size:11px;font-weight:700;color:#fff; }
.va-dash-plan-label-form__input {
    width:100%;
    height:34px;
    border-radius:9px;
    border:1px solid rgba(255,255,255,.15);
    background:rgba(255,255,255,.06);
    color:#fff;
    padding:0 10px;
    font-size:12px;
}
.va-dash-plan-label-form__input:focus {
    outline:none;
    border-color:#ff2a2a;
    box-shadow:0 0 0 3px rgba(255,42,42,.16);
}
.va-dash-plan-label-form__btn {
    height:32px;
    border-radius:999px;
    border:1px solid rgba(255,42,42,.45);
    background:rgba(255,42,42,.14);
    color:#fff;
    font-size:12px;
    font-weight:700;
    cursor:pointer;
    transition:.2s ease;
}
.va-dash-plan-label-form__btn:hover { background:rgba(255,42,42,.24); }

.va-profile-avatar-editor { display:flex;align-items:center;gap:16px; }
.va-profile-avatar-editor__preview {
    width:88px;height:88px;border-radius:50%;overflow:hidden;
    border:2px solid rgba(255,255,255,.2);background:rgba(255,255,255,.06);
    display:flex;align-items:center;justify-content:center;
    color:#fff;font-size:28px;font-weight:800;flex-shrink:0;
}
.va-profile-avatar-editor__preview img { width:100% !important;height:100% !important;object-fit:cover;display:block;max-width:none !important;max-height:none !important; }
.va-profile-avatar-editor__fields { flex:1;display:flex;flex-direction:column;gap:8px; }
.va-profile-avatar-editor__remove { font-size:12px;color:rgba(255,255,255,.7);display:flex;align-items:center;gap:7px; }

/* ── CRM stat blokk ── */
.va-crm {
    margin:0 0 18px;
    padding:14px;
    border:1px solid rgba(255,255,255,.08);
    border-radius:12px;
    background:
        radial-gradient(circle at 18% -10%, rgba(255,0,0,.20), transparent 40%),
        linear-gradient(160deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
}
.va-crm__head h3 {
    margin:0;
    color:#fff;
    font-size:15px;
    letter-spacing:.02em;
}
.va-crm__head p {
    margin:4px 0 0;
    color:rgba(255,255,255,.58);
    font-size:12px;
}
.va-crm__kpis {
    margin-top:12px;
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:10px;
}
.va-crm__kpi {
    border:1px solid rgba(255,255,255,.10);
    border-radius:10px;
    padding:10px;
    background:rgba(0,0,0,.22);
}
.va-crm__kpi-label {
    display:block;
    font-size:11px;
    color:rgba(255,255,255,.55);
    margin-bottom:6px;
}
.va-crm__kpi-value {
    display:block;
    color:#fff;
    font-size:19px;
    font-weight:800;
    line-height:1.12;
}
.va-crm__kpi-sub {
    display:block;
    margin-top:5px;
    color:rgba(255,255,255,.66);
    font-size:11px;
}
.va-crm__panels {
    margin-top:12px;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:10px;
}
.va-crm__panel {
    border:1px solid rgba(255,255,255,.10);
    border-radius:10px;
    padding:10px;
    background:rgba(0,0,0,.22);
}
.va-crm__panel h4 {
    margin:0 0 10px;
    font-size:12px;
    font-weight:800;
    color:#fff;
    letter-spacing:.02em;
    text-transform:uppercase;
}
.va-crm__status-list {
    display:flex;
    flex-direction:column;
    gap:8px;
}
.va-crm__status-meta {
    display:flex;
    align-items:center;
    justify-content:space-between;
    font-size:11px;
    color:rgba(255,255,255,.78);
}
.va-crm__status-meta strong { color:#fff; }
.va-crm__status-bar {
    width:100%;
    height:7px;
    border-radius:999px;
    background:rgba(255,255,255,.10);
    overflow:hidden;
}
.va-crm__status-bar > span {
    display:block;
    height:100%;
    border-radius:inherit;
    background:linear-gradient(90deg,#ff2a2a,#ff8a00);
}
.va-crm__plan-use { margin-top:12px; }
.va-crm__plan-use-meta {
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:6px;
    font-size:11px;
    color:rgba(255,255,255,.78);
}
.va-crm__plan-use-meta strong { color:#fff; }
.va-crm__top-list {
    margin:0;
    padding-left:18px;
    display:flex;
    flex-direction:column;
    gap:8px;
}
.va-crm__top-list li {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
}
.va-crm__top-list a {
    color:#fff;
    font-size:12px;
    text-decoration:none;
    max-width:74%;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}
.va-crm__top-list a:hover { color:#ff6666; }
.va-crm__top-list span {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:56px;
    height:22px;
    padding:0 8px;
    border-radius:999px;
    border:1px solid rgba(255,0,0,.45);
    background:rgba(255,0,0,.14);
    color:#fff;
    font-size:11px;
    font-weight:700;
}
.va-crm__badges {
    margin-top:12px;
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}
.va-crm__badges span {
    display:inline-flex;
    align-items:center;
    gap:4px;
    padding:3px 8px;
    border-radius:999px;
    border:1px solid rgba(255,255,255,.14);
    background:rgba(255,255,255,.05);
    color:rgba(255,255,255,.75);
    font-size:10px;
}
.va-crm__badges strong { color:#fff; }
.va-crm__empty { margin:0;color:rgba(255,255,255,.5);font-size:12px; }
@media (max-width: 980px) {
    .va-crm__kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .va-crm__panels { grid-template-columns:1fr; }
}
@media (max-width: 640px) {
    .va-crm { padding:12px; }
    .va-crm__kpis { grid-template-columns:1fr; }
}

/* ── Boost toggle ── */
.va-pill-toggles {
    display:flex;
    align-items:center;
    gap:6px;
    flex-wrap:nowrap;
}
.va-boost-btn {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    min-width:106px;
    height:30px;
    padding:0 10px;
    border-radius:999px;
    border:1px solid transparent;
    font-size:11px;
    font-weight:700;
    line-height:1;
    letter-spacing:.02em;
    white-space:nowrap;
    cursor:pointer;
    transition:all .2s ease;
}
.va-boost-btn__dot {
    width:7px;
    height:7px;
    border-radius:50%;
    background:currentColor;
    box-shadow:0 0 8px currentColor;
}
.va-boost-btn--on {
    color:#00d46a;
    border-color:#00b85c;
    background:linear-gradient(135deg,rgba(0,212,106,.18),rgba(0,120,64,.14));
}
.va-boost-btn--off {
    color:#ffcc00;
    border-color:#ffcc00;
    background:linear-gradient(135deg,rgba(255,204,0,.18),rgba(160,110,0,.14));
}
.va-boost-btn:hover { filter:brightness(1.08); transform:translateY(-1px); }
.va-boost-btn:disabled { opacity:.65;cursor:not-allowed;transform:none; }

.va-newpill-btn {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    min-width:106px;
    height:30px;
    padding:0 10px;
    border-radius:999px;
    border:1px solid transparent;
    font-size:11px;
    font-weight:700;
    line-height:1;
    letter-spacing:.02em;
    white-space:nowrap;
    cursor:pointer;
    transition:all .2s ease;
}
.va-newpill-btn__dot {
    width:7px;
    height:7px;
    border-radius:50%;
    background:currentColor;
    box-shadow:0 0 8px currentColor;
}
.va-newpill-btn--on {
    color:#ffffff;
    border-color:#ff2a2a;
    background:linear-gradient(135deg,rgba(255,42,42,.9),rgba(180,0,0,.9));
}
.va-newpill-btn--off {
    color:#ff8a8a;
    border-color:#ff3a3a;
    background:linear-gradient(135deg,rgba(255,42,42,.18),rgba(110,0,0,.16));
}
.va-newpill-btn:hover { filter:brightness(1.06); transform:translateY(-1px); }
.va-newpill-btn:disabled { opacity:.65;cursor:not-allowed;transform:none; }

/* ── Veszélyes zóna ── */
.va-danger-zone {
    padding:20px;
    border:1px solid rgba(255,42,42,.25);
    border-radius:10px;
    background:rgba(255,42,42,.05);
}
.va-danger-zone__desc  { font-size:13px;color:rgba(255,255,255,.65);margin:0 0 8px; }
.va-danger-zone__input { max-width:200px;border-color:rgba(255,42,42,.4) !important; }
.va-danger-zone__submit {
    background:rgba(255,42,42,.18);
    border:1px solid rgba(255,42,42,.5);
    color:#ff6060;
    white-space:nowrap;
    padding:0 16px;
    height:40px;
    border-radius:8px;
    font-weight:700;
    cursor:pointer;
    transition:.2s ease;
}
.va-danger-zone__submit:hover { background:rgba(255,42,42,.32); }
.va-dashboard__nav-item--danger { color:#ff6060 !important; }
.va-dashboard__nav-item--danger .va-dashboard__nav-ico svg { stroke:#ff6060; }
.va-dashboard__nav-item--danger:hover,
.va-dashboard__nav-item--danger.active { background:rgba(255,42,42,.1) !important; }

/* ── Sort bar ── */
.va-sort-bar {
    display:flex;align-items:center;gap:6px;margin-bottom:10px;flex-wrap:wrap;
}
.va-sort-btn {
    display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:999px;
    border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.05);color:rgba(255,255,255,.6);
    font-size:11px;font-weight:600;text-decoration:none;transition:.15s ease;
}
.va-sort-btn:hover { border-color:rgba(255,0,0,.4);color:#fff; }
.va-sort-btn.active { border-color:rgba(255,0,0,.5);background:rgba(255,0,0,.12);color:#fff; }

/* ── Bulk toolbar ── */
.va-bulk-toolbar {
    display:flex;align-items:center;gap:8px;flex-wrap:wrap;
    margin-bottom:10px;padding:8px 12px;
    border:1px solid rgba(255,255,255,.08);border-radius:10px;
    background:rgba(255,255,255,.03);
}
.va-bulk-select-all-label { font-size:12px;color:#fff;font-weight:600;display:flex;align-items:center;gap:6px;cursor:pointer; }

/* ── Custom bulk dropdown ── */
.va-bulk-dropdown { position:relative; }
.va-bulk-dropdown__toggle {
    display:flex;align-items:center;gap:8px;
    height:30px;padding:0 10px;border-radius:8px;
    border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.07);
    color:#fff;font-size:12px;cursor:pointer;min-width:180px;white-space:nowrap;
    transition:border-color .15s,background .15s;
}
.va-bulk-dropdown__toggle:hover,
.va-bulk-dropdown.open .va-bulk-dropdown__toggle {
    border-color:rgba(255,42,42,.5);background:rgba(255,42,42,.1);
}
.va-bulk-dropdown__toggle span { flex:1;text-align:left; }
.va-bulk-dropdown__toggle svg { flex-shrink:0;transition:transform .2s; }
.va-bulk-dropdown.open .va-bulk-dropdown__toggle svg { transform:rotate(180deg); }
.va-bulk-dropdown__menu {
    position:absolute;top:calc(100% + 4px);left:0;z-index:900;
    min-width:100%;border-radius:10px;
    border:1px solid rgba(255,255,255,.12);
    background:rgb(14,14,14);
    box-shadow:0 12px 40px rgba(0,0,0,.7);
    overflow:hidden;
}
.va-bulk-dropdown__item {
    display:block;width:100%;text-align:left;
    padding:9px 14px;font-size:13px;color:#fff;
    background:none;border:none;cursor:pointer;
    transition:background .12s;white-space:nowrap;
}
.va-bulk-dropdown__item:hover { background:rgba(255,42,42,.15);color:#fff; }
.va-bulk-dropdown__item.selected { color:#ff4444;background:rgba(255,0,0,.08); }

/* ── Bulk price panel ── */
.va-bulk-price-panel {
    margin-bottom:12px;padding:14px;border:1px solid rgba(255,200,0,.2);border-radius:10px;
    background:rgba(255,200,0,.04);
}
.va-bulk-price-label { display:block;font-size:11px;font-weight:700;color:rgba(255,255,255,.6);margin-bottom:4px; }
.va-bulk-price-input {
    height:32px;padding:0 10px;border-radius:8px;border:1px solid rgba(255,255,255,.15);
    background:rgba(255,255,255,.07);color:#fff;font-size:13px;min-width:140px;
}
.va-bulk-price-input:focus { outline:none;border-color:#ff2a2a; }

/* ── Akciós ár inline edit gomb ── */
.va-sale-edit-btn {
    display:inline-flex;align-items:center;justify-content:center;
    width:24px;height:24px;margin-left:6px;
    border-radius:6px;border:1px solid rgba(255,255,255,.25);
    background:rgba(255,255,255,.06);color:#fff;
    cursor:pointer;font-size:14px;line-height:1;vertical-align:middle;
    transition:.15s;border-color:.15s,background:.15s,transform:.15s;
}
.va-sale-edit-btn:hover { transform:translateY(-1px); }
.va-sale-edit-btn--active {
    border-color:rgba(255,0,0,.55);
    background:rgba(255,0,0,.18);
    color:#ff7777;
}
.va-sale-edit-btn--idle {
    border-color:rgba(255,255,255,.35);
    background:rgba(255,255,255,.08);
    color:rgba(255,255,255,.95);
}

/* ── Akciós ár középre popup (blur háttér) ── */
.va-sale-modal-overlay {
    display:none;
    position:fixed;
    inset:0;
    z-index:9999;
    align-items:center;
    justify-content:center;
    background:rgba(0,0,0,.45);
    backdrop-filter:blur(8px);
}
.va-sale-modal-overlay.open { display:flex; }
.va-sale-modal {
    width:min(92vw, 430px);
    border:1px solid rgba(255,255,255,.14);
    border-radius:16px;
    padding:18px;
    background:linear-gradient(180deg, rgba(18,18,18,.98), rgba(8,8,8,.98));
    box-shadow:0 22px 60px rgba(0,0,0,.65), 0 0 0 1px rgba(255,0,0,.12) inset;
}
.va-sale-modal h3 { margin:0 0 12px; font-size:16px; color:#fff; }
.va-sale-modal label { display:block; font-size:11px; font-weight:700; color:rgba(255,255,255,.55); margin:10px 0 4px; }
.va-sale-modal input {
    width:100%;
    height:36px;
    padding:0 12px;
    border-radius:8px;
    border:1px solid rgba(255,255,255,.16);
    background:rgba(255,255,255,.06);
    color:#fff;
    font-size:13px;
    box-sizing:border-box;
}
.va-sale-modal input:focus {
    outline:none;
    border-color:rgba(255,0,0,.55);
    box-shadow:0 0 0 3px rgba(255,0,0,.14);
}
.va-sale-modal__actions {
    display:flex;
    align-items:center;
    gap:8px;
    margin-top:14px;
}
.va-sale-modal__save {
    margin-left:auto;
    height:36px;
    padding:0 16px;
    border-radius:8px;
    border:1px solid rgba(255,0,0,.55);
    background:rgba(255,0,0,.2);
    color:#fff;
    font-weight:700;
    cursor:pointer;
}
.va-sale-modal__remove {
    height:36px;
    padding:0 14px;
    border-radius:8px;
    border:1px solid rgba(255,42,42,.35);
    background:rgba(255,42,42,.08);
    color:#ff8b8b;
    cursor:pointer;
}
.va-sale-modal__cancel {
    height:36px;
    padding:0 14px;
    border-radius:8px;
    border:1px solid rgba(255,255,255,.16);
    background:rgba(255,255,255,.05);
    color:rgba(255,255,255,.75);
    cursor:pointer;
}

/* ── Lejárat badge ── */
.va-expiry-badge {
    display:inline-flex;align-items:center;gap:3px;padding:1px 6px;border-radius:999px;
    font-size:10px;font-weight:700;
}
.va-expiry-badge--warn { background:rgba(255,153,0,.15);border:1px solid rgba(255,153,0,.4);color:#ffb400; }
.va-expiry-badge--critical { background:rgba(255,42,42,.18);border:1px solid rgba(255,42,42,.5);color:#ff5555; }

/* ── Képszám badge ── */
.va-img-count-badge {
    display:inline-flex;align-items:center;gap:3px;padding:1px 6px;border-radius:999px;
    font-size:10px;font-weight:700;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.55);
}

/* ── Profil completeness ── */
.va-profile-completeness {
    margin-bottom:20px;padding:14px;border:1px solid rgba(255,255,255,.08);
    border-radius:12px;background:rgba(255,255,255,.03);
}
.va-profile-completeness__head { display:flex;justify-content:space-between;margin-bottom:8px; }
.va-profile-completeness__label { font-size:12px;font-weight:700;color:#fff; }
.va-profile-completeness__pct { font-size:12px;font-weight:800;color:#fff; }
.va-profile-completeness__bar {
    height:5px;border-radius:999px;background:rgba(255,255,255,.1);overflow:hidden;margin-bottom:10px;
}
.va-profile-completeness__bar > div { height:100%;border-radius:inherit;transition:width .4s; }
.va-profile-completeness__items { display:flex;flex-wrap:wrap;gap:6px; }
.va-profile-completeness__item {
    font-size:11px;color:rgba(255,255,255,.4);padding:2px 8px;border-radius:999px;
    border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);
}
.va-profile-completeness__item.done { color:#00c850;border-color:rgba(0,200,80,.3);background:rgba(0,200,80,.07); }

/* ── Trust badge-ek ── */
.va-trust-badges { display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px; }
.va-trust-badge {
    display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:999px;
    border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);
    color:rgba(255,255,255,.35);font-size:11px;font-weight:600;
}
.va-trust-badge.active { color:#fff;border-color:rgba(0,200,80,.4);background:rgba(0,200,80,.09); }
.va-trust-badge.active svg { stroke:#00c850; }
</style>

<script>
(function(){
    document.querySelectorAll('.va-boost-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            var postId   = this.dataset.postId;
            var nonce    = this.dataset.nonce;
            var ajaxUrl  = this.dataset.ajaxUrl;
            var mode     = this.dataset.mode || 'toggle';
            var self     = this;
            var originalText = self.textContent;

            self.disabled = true;
            self.textContent = 'Mentés...';

            var data = new URLSearchParams({
                action  : 'va_boost_listing',
                nonce   : nonce,
                post_id : postId,
                mode    : mode
            });

            fetch(ajaxUrl, {
                method  : 'POST',
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
                body    : data.toString()
            })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if(res.success){
                    self.disabled = false;
                    if (res.data && res.data.removed) {
                        self.dataset.mode = 'boost';
                        self.setAttribute('aria-pressed', 'false');
                        self.classList.remove('va-boost-btn--on');
                        self.classList.add('va-boost-btn--off');
                        self.innerHTML = '<span class="va-boost-btn__dot" aria-hidden="true"></span>Kiemelés: KI';
                    } else {
                        self.dataset.mode = 'remove';
                        self.setAttribute('aria-pressed', 'true');
                        self.classList.remove('va-boost-btn--off');
                        self.classList.add('va-boost-btn--on');
                        self.innerHTML = '<span class="va-boost-btn__dot" aria-hidden="true"></span>Kiemelt: BE';
                    }
                } else {
                    self.disabled    = false;
                    self.textContent = originalText;
                    alert(res.data && res.data.message ? res.data.message : 'Hiba történt.');
                }
            })
            .catch(function(){
                self.disabled    = false;
                self.textContent = originalText;
            });
        });
    });

    document.querySelectorAll('.va-newpill-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            var postId   = this.dataset.postId;
            var nonce    = this.dataset.nonce;
            var ajaxUrl  = this.dataset.ajaxUrl;
            var mode     = this.dataset.mode || 'toggle';
            var self     = this;
            var originalText = self.textContent;

            self.disabled = true;
            self.textContent = 'Mentés...';

            var data = new URLSearchParams({
                action  : 'va_toggle_new_pill',
                nonce   : nonce,
                post_id : postId,
                mode    : mode
            });

            fetch(ajaxUrl, {
                method  : 'POST',
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
                body    : data.toString()
            })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if(res.success){
                    self.disabled = false;
                    if (res.data && res.data.active) {
                        self.dataset.mode = 'off';
                        self.setAttribute('aria-pressed', 'true');
                        self.classList.remove('va-newpill-btn--off');
                        self.classList.add('va-newpill-btn--on');
                        self.innerHTML = '<span class="va-newpill-btn__dot" aria-hidden="true"></span>Új pill: BE';
                    } else {
                        self.dataset.mode = 'on';
                        self.setAttribute('aria-pressed', 'false');
                        self.classList.remove('va-newpill-btn--on');
                        self.classList.add('va-newpill-btn--off');
                        self.innerHTML = '<span class="va-newpill-btn__dot" aria-hidden="true"></span>Új pill: KI';
                    }
                } else {
                    self.disabled = false;
                    self.textContent = originalText;
                    alert(res.data && res.data.message ? res.data.message : 'Hiba történt.');
                }
            })
            .catch(function(){
                self.disabled = false;
                self.textContent = originalText;
            });
        });
    });

    // Avatar upload zone
    var avatarInput = document.getElementById('va-avatar-input');
    var avatarDrop  = document.getElementById('va-avatar-drop');
    var avatarLabel = document.getElementById('va-avatar-label');
    if (avatarInput && avatarDrop) {
        avatarInput.addEventListener('change', function(){
            var name = this.files && this.files[0] ? this.files[0].name : 'Kép kiválasztása';
            avatarLabel.textContent = name.length > 20 ? name.substring(0,18)+'…' : name;
        });
        avatarDrop.addEventListener('dragover', function(e){ e.preventDefault(); this.classList.add('drag-over'); });
        avatarDrop.addEventListener('dragleave', function(){ this.classList.remove('drag-over'); });
        avatarDrop.addEventListener('drop', function(e){
            e.preventDefault(); this.classList.remove('drag-over');
            if (e.dataTransfer.files.length) {
                avatarInput.files = e.dataTransfer.files;
                var name = e.dataTransfer.files[0].name;
                avatarLabel.textContent = name.length > 20 ? name.substring(0,18)+'…' : name;
            }
        });
    }

    var _nonce  = '<?php echo esc_js( $boost_nonce ); ?>';
    var _ajaxUrl= '<?php echo esc_js( $ajax_url ); ?>';

    function normalizeIsoDateText(raw) {
        var v = (raw || '').trim();
        if (!v) return '';
        v = v.replace(/\./g, '-').replace(/\//g, '-');
        if (!/^\d{4}-\d{2}-\d{2}$/.test(v)) return null;
        return v;
    }

    /* ── Select all checkbox ── */
    var selectAll = document.getElementById('va-select-all');
    var bulkCount = document.getElementById('va-bulk-count');
    function updateBulkCount() {
        var checked = document.querySelectorAll('.va-row-check:checked').length;
        if (bulkCount) bulkCount.textContent = checked > 0 ? checked + ' kijelölve' : '';
    }
    if (selectAll) {
        selectAll.addEventListener('change', function(){
            document.querySelectorAll('.va-row-check').forEach(function(cb){ cb.checked = selectAll.checked; });
            updateBulkCount();
        });
    }
    document.querySelectorAll('.va-row-check').forEach(function(cb){
        cb.addEventListener('change', updateBulkCount);
    });

    /* ── Custom bulk dropdown ── */
    var bulkDropdown     = document.getElementById('va-bulk-dropdown');
    var bulkDropdownMenu = document.getElementById('va-bulk-dropdown-menu');
    var bulkDropdownToggle = document.getElementById('va-bulk-dropdown-toggle');
    var bulkDropdownLabel= document.getElementById('va-bulk-dropdown-label');
    var bulkActionInput  = document.getElementById('va-bulk-action');
    var bulkPricePanel   = document.getElementById('va-bulk-price-panel');

    function closeBulkDropdown() {
        if (bulkDropdown) bulkDropdown.classList.remove('open');
        if (bulkDropdownMenu) bulkDropdownMenu.style.display = 'none';
    }

    if (bulkDropdownToggle) {
        bulkDropdownToggle.addEventListener('click', function(e){
            e.stopPropagation();
            var isOpen = bulkDropdown && bulkDropdown.classList.contains('open');
            if (isOpen) {
                closeBulkDropdown();
            } else {
                if (bulkDropdown) bulkDropdown.classList.add('open');
                if (bulkDropdownMenu) bulkDropdownMenu.style.display = 'block';
            }
        });
    }

    document.addEventListener('click', function(e){
        if (bulkDropdown && !bulkDropdown.contains(e.target)) closeBulkDropdown();
    });

    if (bulkDropdownMenu) {
        bulkDropdownMenu.querySelectorAll('.va-bulk-dropdown__item').forEach(function(item){
            item.addEventListener('click', function(){
                var val   = this.dataset.value || '';
                var label = this.dataset.label || this.textContent;
                if (bulkActionInput) bulkActionInput.value = val;
                if (bulkDropdownLabel) bulkDropdownLabel.textContent = label;
                bulkDropdownMenu.querySelectorAll('.va-bulk-dropdown__item').forEach(function(i){ i.classList.remove('selected'); });
                this.classList.add('selected');
                closeBulkDropdown();
                if (bulkPricePanel) bulkPricePanel.style.display = val === 'price_change' ? 'block' : 'none';
            });
        });
    }

    /* ── Bulk exec ── */
    var bulkExecBtn = document.getElementById('va-bulk-exec');
    if (bulkExecBtn) {
        bulkExecBtn.addEventListener('click', function(){
            var action = bulkActionInput ? bulkActionInput.value : '';
            if (!action) { alert('Válassz műveletet!'); return; }
            var ids = Array.from(document.querySelectorAll('.va-row-check:checked')).map(function(cb){ return cb.value; });
            if (!ids.length) { alert('Jelölj ki legalább egy hirdetést!'); return; }
            if (action === 'delete' && !confirm('Biztosan törlöd a kijelölt ' + ids.length + ' hirdetést?')) return;

            var params = new URLSearchParams({
                action: 'va_bulk_listings',
                nonce: _nonce,
                bulk_action: action
            });
            ids.forEach(function(id){ params.append('listing_ids[]', id); });

            if (action === 'price_change') {
                var newPrice = document.getElementById('va-bulk-new-price') ? document.getElementById('va-bulk-new-price').value : '';
                if (!newPrice) { alert('Add meg az új árat!'); return; }
                params.set('new_price', newPrice);
                // bulk sale price
                var saleP = document.getElementById('va-bulk-sale-price') ? document.getElementById('va-bulk-sale-price').value : '';
                var saleERaw = document.getElementById('va-bulk-sale-end') ? document.getElementById('va-bulk-sale-end').value : '';
                var saleE = normalizeIsoDateText(saleERaw);
                if (saleE === null) { alert('Akció vége dátum formátum: YYYY-MM-DD'); return; }
                if (saleP) {
                    ids.forEach(function(id){
                        var sp = new URLSearchParams({
                            action: 'va_set_sale_price', nonce: _nonce,
                            post_id: id, sale_price: saleP, sale_end: saleE
                        });
                        fetch(_ajaxUrl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:sp.toString() });
                    });
                }
            }

            bulkExecBtn.disabled = true;
            bulkExecBtn.textContent = 'Folyamatban...';
            fetch(_ajaxUrl, {
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body: params.toString()
            })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.success) {
                    alert(res.data.updated + ' hirdetés módosítva.');
                    location.reload();
                } else {
                    alert((res.data && res.data.message) || 'Hiba történt.');
                    bulkExecBtn.disabled = false;
                    bulkExecBtn.textContent = 'Végrehajtás';
                }
            })
            .catch(function(){
                bulkExecBtn.disabled = false;
                bulkExecBtn.textContent = 'Végrehajtás';
            });
        });
    }

    /* ── Refresh listing ── */
    document.querySelectorAll('.va-refresh-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            var postId = this.dataset.postId;
            var self = this;
            self.disabled = true;
            self.textContent = '…';
            var params = new URLSearchParams({ action:'va_refresh_listing', nonce:_nonce, post_id:postId });
            fetch(_ajaxUrl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:params.toString() })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if (res.success) {
                    self.textContent = '✓ Frissítve';
                    setTimeout(function(){ self.textContent = '↑ Frissítés'; self.disabled = false; }, 2000);
                } else {
                    alert((res.data && res.data.message) || 'Hiba.');
                    self.disabled = false; self.textContent = '↑ Frissítés';
                }
            })
            .catch(function(){ self.disabled = false; self.textContent = '↑ Frissítés'; });
        });
    });

    /* ── Sale price modal edit ── */
    var saleOverlay = document.getElementById('va-sale-modal-overlay');
    var salePostId  = document.getElementById('va-sale-modal-post-id');
    var salePrice   = document.getElementById('va-sale-modal-price');
    var saleEnd     = document.getElementById('va-sale-modal-end');
    var saleSaveBtn = document.getElementById('va-sale-modal-save');
    var saleRemBtn  = document.getElementById('va-sale-modal-remove');
    var saleCancel  = document.getElementById('va-sale-modal-cancel');

    function openSaleModal(postId, curPrice, curEnd) {
        if (!saleOverlay) return;
        salePostId.value = postId || '';
        salePrice.value  = curPrice || '';
        saleEnd.value    = curEnd || '';
        saleOverlay.classList.add('open');
        if (salePrice) salePrice.focus();
    }
    function closeSaleModal() {
        if (saleOverlay) saleOverlay.classList.remove('open');
        if (saleSaveBtn) { saleSaveBtn.disabled = false; saleSaveBtn.textContent = 'Mentés'; }
    }

    function saveSalePrice(postId, price, endDate) {
        if (!postId) return;
        var saleEndNorm = normalizeIsoDateText(endDate);
        if (saleEndNorm === null) { alert('Akció vége dátum formátum: YYYY-MM-DD'); return; }
        var params = new URLSearchParams({
            action: 'va_set_sale_price', nonce: _nonce,
            post_id: postId, sale_price: price, sale_end: saleEndNorm || ''
        });
        if (saleSaveBtn) { saleSaveBtn.disabled = true; saleSaveBtn.textContent = 'Mentés...'; }
        fetch(_ajaxUrl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:params.toString() })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res.success) {
                closeSaleModal();
                location.reload();
            } else {
                alert((res.data && res.data.message) || 'Hiba.');
                if (saleSaveBtn) { saleSaveBtn.disabled = false; saleSaveBtn.textContent = 'Mentés'; }
            }
        })
        .catch(function(){
            if (saleSaveBtn) { saleSaveBtn.disabled = false; saleSaveBtn.textContent = 'Mentés'; }
        });
    }

    document.querySelectorAll('.va-sale-edit-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            openSaleModal(this.dataset.postId, this.dataset.salePrice, this.dataset.saleEnd);
        });
    });

    if (saleCancel) saleCancel.addEventListener('click', closeSaleModal);
    if (saleOverlay) {
        saleOverlay.addEventListener('click', function(e){
            if (e.target === saleOverlay) closeSaleModal();
        });
    }
    if (saleSaveBtn) {
        saleSaveBtn.addEventListener('click', function(){
            saveSalePrice(salePostId ? salePostId.value : '', salePrice ? salePrice.value : 0, saleEnd ? saleEnd.value : '');
        });
    }
    if (saleRemBtn) {
        saleRemBtn.addEventListener('click', function(){
            if (!confirm('Törlöd az akciós árat?')) return;
            saveSalePrice(salePostId ? salePostId.value : '', 0, '');
        });
    }

})();
</script>
