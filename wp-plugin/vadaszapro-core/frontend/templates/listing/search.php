<?php
/**
 * Template: Keresés / szűrő rész + hirdetések AJAX listája
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$categories = get_terms( [ 'taxonomy' => 'va_category', 'hide_empty' => false, 'parent' => 0 ] );
$counties   = get_terms( [ 'taxonomy' => 'va_county',   'hide_empty' => false ] );
$conditions = get_terms( [ 'taxonomy' => 'va_condition','hide_empty' => false ] );

// URL paraméterek
$url_s         = sanitize_text_field( wp_unslash( $_GET['s']         ?? '' ) );
$url_cat       = intval( $_GET['cat']       ?? 0 );
$url_author_id = intval( $_GET['author_id'] ?? 0 );

wp_enqueue_script( 'va-frontend', VA_PLUGIN_URL . 'frontend/js/frontend.js', [ 'jquery' ], VA_VERSION, true );
wp_localize_script( 'va-frontend', 'VA_Data', [
    'ajax_url'         => admin_url( 'admin-ajax.php' ),
    'nonce'            => wp_create_nonce( 'va_user_nonce' ),
    'post_id'          => 0,
    'initial_s'        => $url_s,
    'initial_cat'      => $url_cat,
    'initial_author_id'=> $url_author_id,
]);
wp_enqueue_style( 'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );
?>
<div class="va-wrap">
    <?php va_display_flash(); ?>

    <!-- Szűrő sáv -->
    <div class="va-filter-bar">
        <div class="va-filter-bar__title">🔍 Hirdetések keresése</div>
        <form id="va-filter-form" data-post-type="va_listing">
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

                <div class="va-price-range">
                    <input type="number" id="va-min-price" class="va-input" placeholder="Ártól (Ft)" min="0">
                    <span>–</span>
                    <input type="number" id="va-max-price" class="va-input" placeholder="Árig (Ft)" min="0">
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
