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
$url_post_type   = in_array( $_GET['post_type'] ?? '', [ 'va_listing', 'va_auction' ], true ) ? $_GET['post_type'] : 'va_listing';

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
            <a class="va-user-card" href="<?php echo esc_url( $listing_url ); ?>">
                <img class="va-user-card__avatar" src="<?php echo esc_url( $avatar ); ?>" alt="" loading="lazy">
                <div class="va-user-card__name"><?php echo esc_html( $u->display_name ); ?></div>
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
]);
wp_enqueue_style( 'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );
?>
<div class="va-wrap">
    <?php va_display_flash(); ?>

    <!-- Szűrő sáv -->
    <div class="va-filter-bar">
        <div class="va-filter-bar__title">🔍 Hirdetések keresése</div>
        <form id="va-filter-form" data-post-type="<?php echo esc_attr( $url_post_type ); ?>">
            <div class="va-filter-bar__grid">
                <input type="text" id="va-kw" class="va-input" placeholder="Kulcsszó..." value="<?php echo esc_attr( $url_s ); ?>">

                <select id="va-cat" class="va-select">
                    <option value="">– Kategória –</option>
                    <?php foreach ( $categories as $cat ): ?>
                        <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                        <?php $children = get_terms( [ 'taxonomy' => 'va_category', 'parent' => $cat->term_id, 'hide_empty' => false ] );
                        foreach ( $children as $child ): ?>
                            <option value="<?php echo esc_attr( $child->term_id ); ?>">&nbsp;&nbsp;↳ <?php echo esc_html( $child->name ); ?></option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>

                <select id="va-county" class="va-select">
                    <option value="">– Megye –</option>
                    <?php foreach ( $counties as $county ): ?>
                        <option value="<?php echo esc_attr( $county->term_id ); ?>"><?php echo esc_html( $county->name ); ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="va-cond" class="va-select">
                    <option value="">– Állapot –</option>
                    <?php foreach ( $conditions as $cond ): ?>
                        <option value="<?php echo esc_attr( $cond->term_id ); ?>"><?php echo esc_html( $cond->name ); ?></option>
                    <?php endforeach; ?>
                </select>

                <div class="va-price-slider-wrap">
                    <div class="va-price-slider-labels">
                        <span>&#193;r szűrő</span>
                        <span class="va-price-slider-display"><span id="va-min-price-display">0</span> – <span id="va-max-price-display">5 000 000</span> Ft</span>
                    </div>
                    <div class="va-price-slider-track">
                        <input type="range" id="va-min-price" class="va-range" min="0" max="5000000" step="500" value="0">
                        <input type="range" id="va-max-price" class="va-range" min="0" max="5000000" step="500" value="5000000">
                        <div class="va-range-fill" id="va-range-fill"></div>
                    </div>
                </div>

                <select id="va-sort" class="va-select">
                    <option value="date">Legújabb</option>
                    <option value="price_asc">Ár: növekvő</option>
                    <option value="price_desc">Ár: csökkenő</option>
                    <option value="views">Legtöbb megtekintés</option>
                </select>
            </div>

            <div class="va-filter-bar__actions">
                <button type="button" id="va-filter-reset" class="va-btn va-btn--outline va-btn--sm">Szűrők törlése</button>
                <span id="va-results-count" style="font-size:13px;color:rgba(255,255,255,0.5);align-self:center;"></span>
                <div style="margin-left:auto;display:flex;gap:8px;">
                    <button type="button" class="va-btn va-btn--outline va-btn--sm" id="va-view-grid" title="Rács nézet">⊞</button>
                    <button type="button" class="va-btn va-btn--outline va-btn--sm" id="va-view-list" title="Lista nézet">☰</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Loader -->
    <div id="va-listing-loader" style="display:none;text-align:center;padding:20px;color:rgba(255,255,255,0.5);">
        Betöltés...
    </div>

    <!-- Eredmények -->
    <div id="va-listing-results" class="va-grid"></div>

    <!-- Pagination -->
    <div id="va-pagination" class="va-pagination"></div>
</div>
