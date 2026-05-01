<?php
/**
 * Template: Keresés / szűrő rész + hirdetések AJAX listája
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$categories = get_terms( [ 'taxonomy' => 'va_category', 'hide_empty' => false, 'parent' => 0 ] );
$counties   = get_terms( [ 'taxonomy' => 'va_county',   'hide_empty' => false ] );
$conditions = get_terms( [ 'taxonomy' => 'va_condition','hide_empty' => false ] );

// URL paraméterek
$url_s           = sanitize_text_field( wp_unslash( $_GET['s']           ?? '' ) );
$url_q           = sanitize_text_field( wp_unslash( $_GET['q']           ?? '' ) ); // user_search módban
$url_cat         = intval( $_GET['cat']         ?? 0 );
$url_author_id   = intval( $_GET['author_id']   ?? 0 );
$url_user_search = ! empty( $_GET['user_search'] );
$allowed_post_types = [ 'va_listing' ];
if ( function_exists( 'va_auctions_enabled' ) && va_auctions_enabled() ) {
    $allowed_post_types[] = 'va_auction';
}
$url_post_type   = in_array( $_GET['post_type'] ?? '', $allowed_post_types, true ) ? $_GET['post_type'] : 'va_listing';

// ── Keresési oldal szövegek / opciók ────────────────────────────
$lp_filter_title    = (string) get_option( 'va_lp_filter_title_text', '🔍 Hirdetések keresése' );
$lp_kw_placeholder  = (string) get_option( 'va_lp_kw_placeholder', 'Kulcsszó...' );
$lp_cat_placeholder = (string) get_option( 'va_lp_cat_placeholder', '– Kategória –' );
$lp_co_placeholder  = (string) get_option( 'va_lp_county_placeholder', '– Megye –' );
$lp_cond_placeholder= (string) get_option( 'va_lp_cond_placeholder', '– Állapot –' );
$lp_slider_label    = (string) get_option( 'va_lp_slider_label_text', 'Ár szűrő' );
$lp_slider_max      = max( 100000000, (int) get_option( 'va_lp_slider_max', 100000000 ) );
$lp_slider_step     = max( 1, (int) get_option( 'va_lp_slider_step', 500 ) );
$lp_sort_default    = (string) get_option( 'va_lp_sort_default_lbl', 'Legújabb' );
$lp_sort_price_asc  = (string) get_option( 'va_lp_sort_price_asc_lbl', 'Ár: növekvő' );
$lp_sort_price_desc = (string) get_option( 'va_lp_sort_price_desc_lbl', 'Ár: csökkenő' );
$lp_sort_views      = (string) get_option( 'va_lp_sort_views_lbl', 'Legtöbb megtekintés' );
$lp_reset_btn       = (string) get_option( 'va_lp_reset_btn_text', 'Szűrők törlése' );
$lp_loader_text     = (string) get_option( 'va_lp_loader_text', 'Betöltés...' );
$lp_empty_text      = (string) get_option( 'va_lp_empty_text', 'Nincs találat.' );

// ── Felhasználó-kereső mód ────────────────────────────────────
if ( $url_user_search ) {
    wp_enqueue_style( 'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );
    $search_q  = $url_q ?: $url_s; // q= elsőbbséget élvez, fallback: s=
    $user_args = [
        'number'  => 50,
        'orderby' => 'display_name',
        'order'   => 'ASC',
    ];
    if ( $search_q ) {
        $user_args['search']         = '*' . $search_q . '*';
        $user_args['search_columns'] = [ 'user_login', 'display_name' ];
    }
    $users = get_users( $user_args );
    $search_page = get_page_by_path( 'va-hirdetes-kereses' );
    $search_url  = $search_page ? get_permalink( $search_page ) : home_url( '/va-hirdetes-kereses/' );
    ?>
    <div class="va-wrap">
        <?php va_display_flash(); ?>
        <div class="va-filter-bar" style="margin-bottom:24px;">
            <div class="va-filter-bar__title">👤 Felhasználók<?php echo ( $search_q ?? '' ) ? ' – <em>' . esc_html( $search_q ) . '</em>' : ''; ?></div>
        </div>
        <div class="va-user-grid">
        <?php if ( empty( $users ) ): ?>
            <p style="color:rgba(255,255,255,0.5);">Nem találtunk felhasználót.</p>
        <?php else: foreach ( $users as $u ):
            $avatar      = get_avatar_url( $u->ID, [ 'size' => 160 ] );
            $listing_url = add_query_arg( 'author_id', $u->ID, $search_url );
            $count       = count_user_posts( $u->ID, 'va_listing' );
        ?>
            <?php
                // Ha display_name email, használjuk a user_login-t
                $show_name = ( strpos( $u->display_name, '@' ) !== false )
                    ? $u->user_login
                    : $u->display_name;
            ?>
            <a class="va-user-card" href="<?php echo esc_url( $listing_url ); ?>">
                <img class="va-user-card__avatar" src="<?php echo esc_url( $avatar ); ?>" alt="" loading="lazy">
                <div class="va-user-card__name"><?php echo esc_html( $show_name ); ?></div>
                <div class="va-user-card__meta"><?php echo intval( $count ); ?> hirdetés</div>
            </a>
        <?php endforeach; endif; ?>
        </div>
    </div>
    <?php
    return; // ne futtassa a hirdetés-szűrő részt
}

wp_enqueue_script( 'va-frontend', VA_PLUGIN_URL . 'frontend/js/frontend.js', [ 'jquery' ], VA_VERSION, true );
wp_localize_script( 'va-frontend', 'VA_Data', [
    'ajax_url'         => admin_url( 'admin-ajax.php' ),
    'nonce'            => wp_create_nonce( 'va_user_nonce' ),
    'post_id'          => 0,
    'initial_s'        => $url_s,
    'initial_cat'      => $url_cat,
    'initial_author_id'=> $url_author_id,
    'initial_post_type'=> $url_post_type,
    'slider_max'       => $lp_slider_max,
    'slider_step'      => $lp_slider_step,
    'empty_text'       => $lp_empty_text,
]);
wp_enqueue_style( 'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );
?>
<div class="va-wrap">
    <?php va_display_flash(); ?>

    <!-- Szűrő sáv -->
    <div class="va-filter-bar">
        <div class="va-filter-bar__title"><?php echo esc_html( $lp_filter_title ); ?></div>
        <form id="va-filter-form" data-post-type="<?php echo esc_attr( $url_post_type ); ?>">
            <div class="va-filter-bar__grid">
                <input type="text" id="va-kw" class="va-input" placeholder="<?php echo esc_attr( $lp_kw_placeholder ); ?>" value="<?php echo esc_attr( $url_s ); ?>">

                <select id="va-cat" class="va-select">
                    <option value=""><?php echo esc_html( $lp_cat_placeholder ); ?></option>
                    <?php foreach ( $categories as $cat ): ?>
                        <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                        <?php $children = get_terms( [ 'taxonomy' => 'va_category', 'parent' => $cat->term_id, 'hide_empty' => false ] );
                        foreach ( $children as $child ): ?>
                            <option value="<?php echo esc_attr( $child->term_id ); ?>">&nbsp;&nbsp;↳ <?php echo esc_html( $child->name ); ?></option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>

                <select id="va-county" class="va-select">
                    <option value=""><?php echo esc_html( $lp_co_placeholder ); ?></option>
                    <?php foreach ( $counties as $county ): ?>
                        <option value="<?php echo esc_attr( $county->term_id ); ?>"><?php echo esc_html( $county->name ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="va-cond" class="va-select">
                    <option value=""><?php echo esc_html( $lp_cond_placeholder ); ?></option>
                    <?php foreach ( $conditions as $cond ): ?>
                        <option value="<?php echo esc_attr( $cond->term_id ); ?>"><?php echo esc_html( $cond->name ); ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="va-price-slider-wrap">
                    <div class="va-price-slider-labels">
                        <span><?php echo esc_html( $lp_slider_label ); ?></span>
                        <span class="va-price-slider-display"><span id="va-min-price-display">1</span> – <span id="va-max-price-display"><?php echo esc_html( number_format( $lp_slider_max, 0, ',', ' ' ) ); ?></span> Ft</span>
                    </div>
                    <div class="va-price-slider-track">
                        <input type="range" id="va-min-price" class="va-range" min="1" max="<?php echo esc_attr( $lp_slider_max ); ?>" step="<?php echo esc_attr( $lp_slider_step ); ?>" value="1">
                        <input type="range" id="va-max-price" class="va-range" min="1" max="<?php echo esc_attr( $lp_slider_max ); ?>" step="<?php echo esc_attr( $lp_slider_step ); ?>" value="<?php echo esc_attr( $lp_slider_max ); ?>">
                        <div class="va-range-fill" id="va-range-fill"></div>
                    </div>
                </div>

                <select id="va-sort" class="va-select">
                    <option value="date"><?php echo esc_html( $lp_sort_default ); ?></option>
                    <option value="price_asc"><?php echo esc_html( $lp_sort_price_asc ); ?></option>
                    <option value="price_desc"><?php echo esc_html( $lp_sort_price_desc ); ?></option>
                    <option value="views"><?php echo esc_html( $lp_sort_views ); ?></option>
                </select>
            </div>

            <div class="va-filter-bar__actions">
                <button type="button" id="va-filter-reset" class="va-btn va-btn--outline va-btn--sm"><?php echo esc_html( $lp_reset_btn ); ?></button>
                <span id="va-results-count" style="font-size:13px;color:rgba(255,255,255,0.5);align-self:center;"></span>
                <div class="va-view-toggle" style="margin-left:auto;display:flex;gap:10px;">
                    <button type="button" class="va-view-btn va-view-btn--grid" id="va-view-grid" title="Rács nézet" aria-label="Rács nézet">
                        <svg class="va-view-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect class="va-grid-c1" x="3" y="3" width="8" height="8" rx="1.5"/>
                            <rect class="va-grid-c2" x="13" y="3" width="8" height="8" rx="1.5"/>
                            <rect class="va-grid-c3" x="3" y="13" width="8" height="8" rx="1.5"/>
                            <rect class="va-grid-c4" x="13" y="13" width="8" height="8" rx="1.5"/>
                        </svg>
                    </button>
                    <button type="button" class="va-view-btn va-view-btn--list" id="va-view-list" title="Lista nézet" aria-label="Lista nézet">
                        <svg class="va-view-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <line class="va-list-l1" x1="3" y1="6" x2="21" y2="6"/>
                            <line class="va-list-l2" x1="3" y1="12" x2="21" y2="12"/>
                            <line class="va-list-l3" x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Loader -->
    <div id="va-listing-loader" style="display:none;text-align:center;padding:20px;">
        <?php echo esc_html( $lp_loader_text ); ?>
    </div>

    <!-- Eredmények -->
    <div id="va-listing-results" class="va-grid"></div>

    <!-- Pagination -->
    <div id="va-pagination" class="va-pagination"></div>
</div>
