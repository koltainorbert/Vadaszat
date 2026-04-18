<?php
/**
 * Template: Hirdetés feladás form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$categories = get_terms( [ 'taxonomy' => 'va_category', 'hide_empty' => false ] );
$counties   = get_terms( [ 'taxonomy' => 'va_county',   'hide_empty' => false ] );
$conditions = get_terms( [ 'taxonomy' => 'va_condition','hide_empty' => false ] );

$free_limit = max( 0, absint( get_option( 'va_free_listings_limit', 1 ) ) );
$paid_price = max( 0, absint( get_option( 'va_listing_price_after_free', 1990 ) ) );

$user_listings_count = 0;
if ( is_user_logged_in() ) {
    global $wpdb;
    $user_listings_count = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts}
         WHERE post_type = %s
         AND post_author = %d
         AND post_status IN ('publish','pending','draft','future','private')",
        'va_listing',
        get_current_user_id()
    ) );
}

$remaining_free = $free_limit === 0 ? 9999 : max( 0, $free_limit - $user_listings_count );

wp_enqueue_style(  'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [], VA_VERSION );
wp_enqueue_script( 'va-submit',   VA_PLUGIN_URL . 'frontend/js/frontend.js',  [ 'jquery' ], VA_VERSION, true );
wp_localize_script( 'va-submit', 'VA_Data', [
    'ajax_url' => admin_url( 'admin-ajax.php' ),
    'nonce'    => wp_create_nonce( 'va_submit_listing' ),
    'post_id'  => 0,
]);
?>
<div class="va-wrap">
    <?php va_display_flash(); ?>

    <div id="va-submit-notice"></div>

    <form id="va-submit-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="va_submit_listing">
        <input type="hidden" name="nonce"  value="<?php echo esc_attr( wp_create_nonce( 'va_submit_listing' ) ); ?>">

        <h2 style="font-size:20px;font-weight:800;margin-bottom:22px;">📋 Hirdetés feladása</h2>

        <div class="va-notice va-notice--info" style="margin-bottom:16px;">
            <?php if ( $free_limit === 0 ): ?>
                Jelenleg korlátlan számú ingyenes hirdetés adható fel.
            <?php else: ?>
                <?php if ( $remaining_free > 0 ): ?>
                    Még <strong><?php echo esc_html( (string) $remaining_free ); ?> db</strong> ingyenes hirdetésed van. Utána minden új hirdetés díja: <strong><?php echo esc_html( number_format( $paid_price, 0, ',', ' ' ) ); ?> Ft</strong> (bankkártya).
                <?php else: ?>
                    Az ingyenes hirdetési limit elfogyott. A következő hirdetés díja: <strong><?php echo esc_html( number_format( $paid_price, 0, ',', ' ' ) ); ?> Ft</strong> (bankkártya).
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Alapadatok -->
        <div class="va-form-group">
            <label for="va-title">Hirdetés címe <span class="required">*</span></label>
            <input type="text" id="va-title" name="title" class="va-input" maxlength="150" required placeholder="pl. Beretta A400 sörétes puska">
        </div>

        <div class="va-form-row">
            <div class="va-form-group">
                <label>Kategória <span class="required">*</span></label>
                <select name="category" class="va-select" required>
                    <option value="">– Válasszon –</option>
                    <?php foreach ( $categories as $cat ): ?>
                        <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( str_repeat( '&nbsp;&nbsp;', $cat->parent ? 1 : 0 ) . $cat->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="va-form-group">
                <label>Megye <span class="required">*</span></label>
                <select name="county" class="va-select" required>
                    <option value="">– Válasszon –</option>
                    <?php foreach ( $counties as $county ): ?>
                        <option value="<?php echo esc_attr( $county->term_id ); ?>"><?php echo esc_html( $county->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="va-form-row">
            <div class="va-form-group">
                <label>Állapot</label>
                <select name="condition" class="va-select">
                    <option value="">– Válasszon –</option>
                    <?php foreach ( $conditions as $cond ): ?>
                        <option value="<?php echo esc_attr( $cond->term_id ); ?>"><?php echo esc_html( $cond->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="va-form-group">
                <label>Helyszín (város)</label>
                <input type="text" name="location" class="va-input" placeholder="pl. Budapest, Győr...">
            </div>
        </div>

        <!-- Termék részletek -->
        <h3 style="font-size:15px;font-weight:700;margin:20px 0 14px;color:rgba(255,255,255,0.6);text-transform:uppercase;font-size:12px;letter-spacing:1px;">Termék részletek</h3>

        <div class="va-form-row">
            <div class="va-form-group">
                <label>Márka / Gyártó</label>
                <input type="text" name="brand" class="va-input" placeholder="pl. Beretta, Sauer...">
            </div>
            <div class="va-form-group">
                <label>Modell / Típus</label>
                <input type="text" name="model" class="va-input" placeholder="pl. A400 Xcel">
            </div>
        </div>

        <div class="va-form-row">
            <div class="va-form-group">
                <label>Kaliber</label>
                <input type="text" name="caliber" class="va-input" placeholder="pl. 12/70, .308 Win">
            </div>
            <div class="va-form-group">
                <label>Gyártási év</label>
                <input type="number" name="year" class="va-input" min="1800" max="<?php echo date('Y'); ?>" placeholder="pl. 2018">
            </div>
        </div>

        <div class="va-form-group">
            <label class="va-check-label">
                <input type="checkbox" name="license_req" value="1">
                Fegyverengedély szükséges a vásárláshoz
            </label>
        </div>

        <!-- Ár -->
        <h3 style="font-size:15px;font-weight:700;margin:20px 0 14px;color:rgba(255,255,255,0.6);text-transform:uppercase;font-size:12px;letter-spacing:1px;">Ár</h3>

        <div class="va-form-row">
            <div class="va-form-group">
                <label>Ár (Ft)</label>
                <input type="number" name="price" class="va-input" min="0" placeholder="0">
            </div>
            <div class="va-form-group">
                <label>Árazás típusa</label>
                <select name="price_type" class="va-select">
                    <option value="fixed">Fix ár</option>
                    <option value="negotiable">Alkudható</option>
                    <option value="free">Ingyenes</option>
                    <option value="on_request">Érdeklődjön</option>
                </select>
            </div>
        </div>

        <!-- Leírás -->
        <div class="va-form-group">
            <label for="va-desc">Leírás <span class="required">*</span></label>
            <textarea id="va-desc" name="description" class="va-textarea" rows="6" required placeholder="Részletes leírás a tárgyról, állapotáról, esetleges hibákról..."></textarea>
        </div>

        <!-- Képek -->
        <div class="va-form-group">
            <label>Képek (max. <?php echo esc_html( get_option('va_max_images_per_listing', 10) ); ?> db, jpg/png/webp)</label>
            <input type="file" name="listing_images[]" class="va-input" accept="image/jpeg,image/png,image/webp" multiple>
            <p style="font-size:12px;color:rgba(255,255,255,0.4);margin-top:6px;">Az első kép lesz a borítókép. Max. 5 MB / kép.</p>
        </div>

        <!-- Kapcsolat -->
        <h3 style="font-size:15px;font-weight:700;margin:20px 0 14px;color:rgba(255,255,255,0.6);text-transform:uppercase;font-size:12px;letter-spacing:1px;">Kapcsolat</h3>

        <div class="va-form-row">
            <div class="va-form-group">
                <label>Telefonszám<?php echo get_option('va_require_phone','1') === '1' ? ' <span class="required">*</span>' : ''; ?></label>
                <input type="tel" name="phone" class="va-input" placeholder="+36 30 000 0000"
                    <?php echo get_option('va_require_phone','1') === '1' ? 'required' : ''; ?>>
            </div>
            <div class="va-form-group" style="align-self:flex-end;">
                <label class="va-check-label">
                    <input type="checkbox" name="email_show" value="1" checked>
                    E-mail cím megjelenítése a hirdetésben
                </label>
            </div>
        </div>

        <button type="submit" class="va-btn va-btn--primary va-btn--block" id="va-submit-btn">
            📤 Hirdetés feladása
        </button>

        <p style="font-size:12px;color:rgba(255,255,255,0.4);margin-top:12px;text-align:center;">
            <?php echo get_option('va_auto_publish_listings','0') === '1'
                ? 'A hirdetés azonnal megjelenik.'
                : 'A hirdetés moderátor jóváhagyása után jelenik meg.'; ?>
        </p>
    </form>
</div>

<script>
(function($){
    $('#va-submit-form').on('submit', function(e){
        e.preventDefault();
        var $btn = $('#va-submit-btn');
        $btn.prop('disabled', true).text('Feltöltés...');

        var formData = new FormData(this);

        $.ajax({
            url:         VA_Data.ajax_url,
            type:        'POST',
            data:        formData,
            processData: false,
            contentType: false,
            success: function(res){
                $btn.prop('disabled', false).text('📤 Hirdetés feladása');
                if(res.success){
                    $('#va-submit-notice').html('<div class="va-notice va-notice--success">' + res.data.message + '</div>');
                    if(res.data.permalink){
                        setTimeout(function(){ window.location.href = res.data.permalink; }, 2000);
                    }
                } else {
                    if (res.data && res.data.payment_required && res.data.payment_url) {
                        var amount = res.data.amount ? Number(res.data.amount).toLocaleString('hu-HU') + ' Ft' : '';
                        var html = '<div class="va-notice va-notice--warning">'
                            + res.data.message
                            + (amount ? '<br><strong>Fizetendő: ' + amount + '</strong>' : '')
                            + '<br><a href="' + res.data.payment_url + '" class="va-btn va-btn--primary" style="margin-top:10px;display:inline-flex;">Bankkártyás fizetés</a>'
                            + '</div>';
                        $('#va-submit-notice').html(html);
                    } else {
                        $('#va-submit-notice').html('<div class="va-notice va-notice--error">' + res.data.message + '</div>');
                    }
                }
            },
            error: function(){
                $btn.prop('disabled', false).text('📤 Hirdetés feladása');
                $('#va-submit-notice').html('<div class="va-notice va-notice--error">Hálózati hiba. Próbálja újra.</div>');
            }
        });
    });
})(jQuery);
</script>
