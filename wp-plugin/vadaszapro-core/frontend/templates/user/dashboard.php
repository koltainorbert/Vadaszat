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
            <form method="post" enctype="multipart/form-data" class="va-dash-avatar-form">
                <?php wp_nonce_field( 'va_profile_avatar', 'va_profile_avatar_nonce' ); ?>
                <input type="hidden" name="va_action" value="profile_avatar">
                <label class="va-dash-avatar-form__label">Profilkép feltöltés</label>
                <input type="file" name="profile_avatar" class="va-dash-avatar-form__file" accept="image/jpeg,image/png,image/webp">
                <?php if ( $avatar_url ): ?>
                <label class="va-dash-avatar-form__remove">
                    <input type="checkbox" name="profile_avatar_remove" value="1"> Profilkép törlése
                </label>
                <?php endif; ?>
                <button type="submit" class="va-dash-avatar-form__btn">Profilkép mentése</button>
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

                <?php if ( $listings ): ?>
                <table class="va-user-listings-table" style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.1);">
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Cím</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Ár</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Státusz</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Dátum</th>
                            <th style="text-align:left;padding:8px;color:rgba(255,255,255,0.5);">Kiemelés</th>
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
                            <?php
                            $edit_page = get_page_by_path('va-hirdetes-feladas');
                            $edit_url  = $edit_page
                                ? add_query_arg( 'edit', $l->ID, get_permalink( $edit_page ) )
                                : get_edit_post_link( $l->ID );
                            ?>
                            <?php
                            // Boost gomb
                            if ( class_exists( 'VA_User_Roles' ) ) :
                                $boost_info = VA_User_Roles::can_boost( $user->ID, $l->ID );
                                if ( $boost_info['can'] ):
                            ?>
                            <button class="va-boost-btn va-btn va-btn--accent va-btn--sm"
                                    data-post-id="<?php echo esc_attr( (string) $l->ID ); ?>"
                                    data-nonce="<?php echo esc_attr( $boost_nonce ); ?>"
                                    data-ajax-url="<?php echo esc_url( $ajax_url ); ?>"
                                    style="background:rgba(255,200,0,.15);border:1px solid #ffcc00;color:#ffcc00;">
                                ⚡ Előre
                            </button>
                            <?php else:
                                $hrs = (int) ceil( $boost_info['seconds_remaining'] / 3600 );
                                $days_left = round( $boost_info['seconds_remaining'] / 86400, 1 );
                            ?>
                            <span class="va-boost-cd" style="font-size:11px;color:rgba(255,255,255,.35);" title="<?php echo esc_attr( $boost_info['cooldown_days'] . ' naponként emelhető' ); ?>">
                                ⚡ <?php echo $boost_info['seconds_remaining'] >= 86400 ? esc_html( $days_left . ' nap' ) : esc_html( $hrs . 'ó' ); ?>
                            </span>
                            <?php
                                endif;
                            endif;
                            ?>
                        </td>
                        <td style="padding:10px 8px;text-align:right;">
                            <?php
                            $edit_page = get_page_by_path('va-hirdetes-feladas');
                            $edit_url  = $edit_page
                                ? add_query_arg( 'edit', $l->ID, get_permalink( $edit_page ) )
                                : get_edit_post_link( $l->ID );
                            ?>
                            <a href="<?php echo esc_url( $edit_url ); ?>" class="va-btn va-btn--outline va-btn--sm">Szerk.</a>
                            <form method="post" class="va-listing-delete-form" style="display:inline;" onsubmit="return confirm('Biztosan törlöd ezt a hirdetést? A képekkel együtt végleg eltűnik!');">
                                <?php wp_nonce_field( 'va_delete_listing', 'va_delete_listing_nonce' ); ?>
                                <input type="hidden" name="va_action" value="delete_listing">
                                <input type="hidden" name="listing_id" value="<?php echo esc_attr( (string) $l->ID ); ?>">
                                <button type="submit" class="va-btn va-btn--sm" style="background:rgba(255,42,42,.12);border:1px solid rgba(255,42,42,.4);color:#ff6060;">Törlés</button>
                            </form>
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
.va-dash-avatar-form__label { color:#fff;font-size:11px;font-weight:700; }
.va-dash-avatar-form__file {
    width:100%;
    font-size:11px;
    color:rgba(255,255,255,.78);
}
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

/* ── Boost gomb ── */
.va-boost-btn { cursor:pointer;font-size:12px; }
.va-boost-btn:disabled { opacity:.5;cursor:not-allowed; }

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
</style>

<script>
(function(){
    document.querySelectorAll('.va-boost-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            var postId   = this.dataset.postId;
            var nonce    = this.dataset.nonce;
            var ajaxUrl  = this.dataset.ajaxUrl;
            var self     = this;

            self.disabled = true;
            self.textContent = '⏳ Kiemelés...';

            var data = new URLSearchParams({
                action  : 'va_boost_listing',
                nonce   : nonce,
                post_id : postId
            });

            fetch(ajaxUrl, {
                method  : 'POST',
                headers : { 'Content-Type': 'application/x-www-form-urlencoded' },
                body    : data.toString()
            })
            .then(function(r){ return r.json(); })
            .then(function(res){
                if(res.success){
                    self.textContent = '✅ Kiemelt!';
                    self.style.borderColor   = '#00c850';
                    self.style.color         = '#00c850';
                    self.style.background    = 'rgba(0,200,80,.12)';
                } else {
                    self.disabled    = false;
                    self.textContent = '⚡ Előre';
                    alert(res.data && res.data.message ? res.data.message : 'Hiba történt.');
                }
            })
            .catch(function(){
                self.disabled    = false;
                self.textContent = '⚡ Előre';
            });
        });
    });
})();
</script>
