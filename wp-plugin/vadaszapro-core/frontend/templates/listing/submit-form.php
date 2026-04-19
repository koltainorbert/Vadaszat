<?php
/**
 * Template: Hirdetés feladás form
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ── Helper: egyes mező HTML kimenete ──────────────── */
if ( ! function_exists( 'self_render_listing_field' ) ) {
    function self_render_listing_field( string $key, string $ph, string $req_attr, array $categories, array $counties, array $conditions, array $ev = [] ): void {
        $val = $ev[ $key ] ?? '';
        switch ( $key ) {
            case 'title':
                echo '<input type="text" id="va-title" name="title" class="va-input" maxlength="150"' . $req_attr . ' placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
                break;
            case 'category':
                echo '<select name="category" class="va-select"' . $req_attr . '>';
                echo '<option value="">– Válasszon –</option>';
                foreach ( $categories as $cat ) {
                    $indent   = $cat->parent ? '&nbsp;&nbsp;' : '';
                    $selected = selected( (int) $val, $cat->term_id, false );
                    echo '<option value="' . esc_attr( $cat->term_id ) . '"' . $selected . '>' . $indent . esc_html( $cat->name ) . '</option>';
                }
                echo '</select>';
                break;
            case 'county':
                echo '<select name="county" class="va-select"' . $req_attr . '>';
                echo '<option value="">– Válasszon –</option>';
                foreach ( $counties as $county ) {
                    $selected = selected( (int) $val, $county->term_id, false );
                    echo '<option value="' . esc_attr( $county->term_id ) . '"' . $selected . '>' . esc_html( $county->name ) . '</option>';
                }
                echo '</select>';
                break;
            case 'condition':
                echo '<select name="condition" class="va-select">';
                echo '<option value="">– Válasszon –</option>';
                foreach ( $conditions as $cond ) {
                    $selected = selected( (int) $val, $cond->term_id, false );
                    echo '<option value="' . esc_attr( $cond->term_id ) . '"' . $selected . '>' . esc_html( $cond->name ) . '</option>';
                }
                echo '</select>';
                break;
            case 'location':
                echo '<input type="text" name="location" class="va-input" placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
                break;
            case 'brand':
                echo '<input type="text" name="brand" class="va-input" placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
                break;
            case 'model':
                echo '<input type="text" name="model" class="va-input" placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
                break;
            case 'caliber':
                echo '<input type="text" name="caliber" class="va-input" placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
                break;
            case 'year':
                echo '<input type="number" name="year" class="va-input" min="1800" max="' . date('Y') . '" placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
                break;
            case 'license_req':
                $checked = $val === '1' ? ' checked' : '';
                echo '<label class="va-check-label"><input type="checkbox" name="license_req" value="1"' . $checked . '> Fegyverengedély szükséges a vásárláshoz</label>';
                break;
            case 'price':
                echo '<input type="number" name="price" class="va-input" min="0" placeholder="' . $ph . '" value="' . esc_attr( (string) $val ) . '">';
                break;
            case 'price_type':
                $pt = (string) $val;
                echo '<select name="price_type" class="va-select">';
                foreach ( [ 'fixed' => 'Fix ár', 'negotiable' => 'Alkudható', 'free' => 'Ingyenes', 'on_request' => 'Érdeklődjön' ] as $k => $l ) {
                    echo '<option value="' . esc_attr( $k ) . '"' . selected( $pt, $k, false ) . '>' . esc_html( $l ) . '</option>';
                }
                echo '</select>';
                break;
            case 'description':
                echo '<textarea id="va-desc" name="description" class="va-textarea" rows="6"' . $req_attr . ' placeholder="' . $ph . '">' . esc_textarea( (string) $val ) . '</textarea>';
                break;
            case 'images':
                $max_img = absint( get_option( 'va_max_images_per_listing', 10 ) );
                ?>
                <div class="va-img-picker" id="va-img-picker">
                    <div class="va-img-grid" id="va-img-grid">
                        <button type="button" class="va-img-add" id="va-img-add">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="26" height="26"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            <span>Képek<br>hozzáadása</span>
                        </button>
                    </div>
                    <input type="file" id="va-img-file-input" accept="image/jpeg,image/png,image/webp" multiple style="display:none" data-max="<?php echo esc_attr( (string) $max_img ); ?>">
                    <input type="hidden" name="featured_image_index" id="va-featured-index" value="0">
                    <input type="hidden" name="keep_images" id="va-keep-images" value="">
                    <p class="va-img-hint">Húzd a képeket az átrendezéshez &bull; &#9733; = borítókép beállítása</p>
                </div>
                <?php
                break;
            case 'phone':
                echo '<input type="tel" name="phone" class="va-input" placeholder="' . $ph . '"' . $req_attr . ' value="' . esc_attr( (string) $val ) . '">';
                break;
            case 'email_show':
                echo '<label class="va-check-label" style="align-self:flex-end;">';
                echo '<input type="checkbox" name="email_show" value="1" checked>';
                echo ' E-mail cím megjelenítése a hirdetésben</label>';
                break;
        }
    }
}

$categories = get_terms( [ 'taxonomy' => 'va_category', 'hide_empty' => false ] );
$counties   = get_terms( [ 'taxonomy' => 'va_county',   'hide_empty' => false ] );
$conditions = get_terms( [ 'taxonomy' => 'va_condition','hide_empty' => false ] );

/* ── Edit mód felismerés ───────────────────────────── */
$edit_post_id = 0;
$edit_post    = null;
$edit_meta    = [];
$edit_mode    = false;
if ( is_user_logged_in() && isset( $_GET['edit'] ) ) {
    $maybe_id = absint( $_GET['edit'] );
    $maybe_post = get_post( $maybe_id );
    if ( $maybe_post && $maybe_post->post_type === 'va_listing' && (int) $maybe_post->post_author === get_current_user_id() ) {
        $edit_post_id = $maybe_id;
        $edit_post    = $maybe_post;
        $edit_mode    = true;
        $edit_meta    = [
            'title'       => $maybe_post->post_title,
            'description' => $maybe_post->post_content,
            'price'       => get_post_meta( $maybe_id, 'va_price',       true ),
            'price_type'  => get_post_meta( $maybe_id, 'va_price_type',  true ),
            'phone'       => get_post_meta( $maybe_id, 'va_phone',       true ),
            'location'    => get_post_meta( $maybe_id, 'va_location',    true ),
            'brand'       => get_post_meta( $maybe_id, 'va_brand',       true ),
            'model'       => get_post_meta( $maybe_id, 'va_model',       true ),
            'caliber'     => get_post_meta( $maybe_id, 'va_caliber',     true ),
            'year'        => get_post_meta( $maybe_id, 'va_year',        true ),
            'license_req' => get_post_meta( $maybe_id, 'va_license_req', true ),
            'category'    => (int) ( wp_get_post_terms( $maybe_id, 'va_category', ['fields'=>'ids'] )[0] ?? 0 ),
            'county'      => (int) ( wp_get_post_terms( $maybe_id, 'va_county',   ['fields'=>'ids'] )[0] ?? 0 ),
            'condition'   => (int) ( wp_get_post_terms( $maybe_id, 'va_condition',['fields'=>'ids'] )[0] ?? 0 ),
        ];
        // Meglévő képek
        $edit_gallery = array_map('absint', (array) get_post_meta( $maybe_id, 'va_gallery', true ));
        $edit_thumb   = (int) get_post_thumbnail_id( $maybe_id );
    }
}

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
    'ajax_url'    => admin_url( 'admin-ajax.php' ),
    'nonce'       => wp_create_nonce( $edit_mode ? 'va_update_listing' : 'va_submit_listing' ),
    'post_id'     => $edit_post_id,
    'edit_mode'   => $edit_mode,
    'edit_images' => $edit_mode ? array_map( function( $id ) {
        $src = wp_get_attachment_image_url( $id, 'thumbnail' );
        return [ 'id' => $id, 'url' => $src ?: '' ];
    }, $edit_gallery ?? [] ) : [],
    'edit_thumb'  => $edit_mode ? $edit_thumb : 0,
]);
?>
<div class="va-wrap">
    <?php va_display_flash(); ?>

    <div id="va-submit-notice"></div>

    <form id="va-submit-form" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'va_update_listing' : 'va_submit_listing'; ?>">
        <input type="hidden" name="nonce"  value="<?php echo esc_attr( wp_create_nonce( $edit_mode ? 'va_update_listing' : 'va_submit_listing' ) ); ?>">
        <?php if ( $edit_mode ): ?>
        <input type="hidden" name="post_id" value="<?php echo esc_attr( (string) $edit_post_id ); ?>">
        <?php endif; ?>

        <h2 style="font-size:20px;font-weight:800;margin-bottom:22px;"><?php echo $edit_mode ? '✏️ Hirdetés szerkesztése' : '📋 Hirdetés feladása'; ?></h2>

        <?php if ( ! $edit_mode ): ?>
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
        <?php endif; ?>

        <?php
        // Dinamikus form mezők VA_Form_Builder config alapján
        $fb_form    = 'va_listing_submit';
        $fb_fields  = VA_Form_Builder::get_fields( $fb_form );
        usort( $fb_fields, fn( $a, $b ) => (int)( $a['order'] ?? 99 ) - (int)( $b['order'] ?? 99 ) );

        // Csoportok: szekciókat nyit ha szükséges
        $section_groups = [
            'brand'       => 'Termék részletek',
            'price'       => 'Ár',
            'phone'       => 'Kapcsolat',
        ];
        $opened_sections = [];

        // Párba rakandó mezők (2-oszlopos sor)
        $pair_groups = [
            ['category', 'county'],
            ['condition','location'],
            ['brand',    'model'],
            ['caliber',  'year'],
            ['price',    'price_type'],
            ['phone',    'email_show'],
        ];
        $pair_map = [];
        foreach ( $pair_groups as $pair ) {
            $pair_map[ $pair[0] ] = $pair[1];
            $pair_map[ $pair[1] ] = $pair[0]; // partner
        }
        $rendered_keys = [];

        foreach ( $fb_fields as $field ):
            $fkey = (string)( $field['key'] ?? '' );
            if ( in_array( $fkey, $rendered_keys, true ) ) continue;
            if ( empty( $field['enabled'] ) ) {
                $rendered_keys[] = $fkey;
                continue;
            }

            $label = esc_html( (string)( $field['label'] ?? $fkey ) );
            $ph    = esc_attr( (string)( $field['placeholder'] ?? '' ) );
            $req   = ! empty( $field['required'] );
            $req_html = $req ? ' <span class="required">*</span>' : '';
            $req_attr = $req ? ' required' : '';

            // Szekció fejléc
            if ( isset( $section_groups[ $fkey ] ) && ! isset( $opened_sections[ $fkey ] ) ) {
                $opened_sections[ $fkey ] = true;
                echo '<h3 style="font-size:12px;font-weight:700;margin:20px 0 14px;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:1px;">'
                    . esc_html( $section_groups[ $fkey ] ) . '</h3>';
            }

            // Pár-sor logika
            $partner_key = $pair_map[ $fkey ] ?? null;
            $partner_field = null;
            if ( $partner_key ) {
                foreach ( $fb_fields as $pf ) {
                    if ( ( $pf['key'] ?? '' ) === $partner_key && ! empty( $pf['enabled'] ) ) {
                        $partner_field = $pf;
                        break;
                    }
                }
            }

            if ( $partner_field && ! in_array( $partner_key, $rendered_keys, true ) ):
                // 2 oszlopos sor
                $rendered_keys[] = $fkey;
                $rendered_keys[] = $partner_key;
                $p2_label   = esc_html( (string)( $partner_field['label'] ?? $partner_key ) );
                $p2_ph      = esc_attr( (string)( $partner_field['placeholder'] ?? '' ) );
                $p2_req     = ! empty( $partner_field['required'] );
                $p2_req_html = $p2_req ? ' <span class="required">*</span>' : '';
                $p2_req_attr = $p2_req ? ' required' : '';
                echo '<div class="va-form-row">';
                // Mező 1
                echo '<div class="va-form-group">';
                echo "<label>{$label}{$req_html}</label>";
                self_render_listing_field( $fkey, $ph, $req_attr, $categories, $counties, $conditions, $edit_meta );
                echo '</div>';
                // Mező 2
                echo '<div class="va-form-group">';
                echo "<label>{$p2_label}{$p2_req_html}</label>";
                self_render_listing_field( $partner_key, $p2_ph, $p2_req_attr, $categories, $counties, $conditions, $edit_meta );
                echo '</div>';
                echo '</div>';
            else:
                $rendered_keys[] = $fkey;
                // Teljes soros mező
                echo '<div class="va-form-group">';
                echo "<label>{$label}{$req_html}</label>";
                self_render_listing_field( $fkey, $ph, $req_attr, $categories, $counties, $conditions, $edit_meta );
                echo '</div>';
            endif;
        endforeach;
        ?>

        <button type="submit" class="va-btn va-btn--primary va-btn--block" id="va-submit-btn">
            <?php echo $edit_mode ? '💾 Változások mentése' : '📤 Hirdetés feladása'; ?>
        </button>

        <p style="font-size:12px;color:rgba(255,255,255,0.4);margin-top:12px;text-align:center;">
            <?php echo get_option('va_auto_publish_listings','0') === '1'
                ? 'A hirdetés azonnal megjelenik.'
                : 'A hirdetés moderátor jóváhagyása után jelenik meg.'; ?>
        </p>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
(function($){
    /* ══ Képkezelő ═══════════════════════════════════════ */
    let _files   = [];   // { file: File|null, id: string, existing_id: int|null, url: string|null }[]
    let _maxImg  = 10;
    let _featured = 0;

    const $picker    = $('#va-img-picker');
    const $grid      = $('#va-img-grid');
    const $input     = $('#va-img-file-input');
    const $featIdx   = $('#va-featured-index');
    const $keepInput = $('#va-keep-images');

    _maxImg = parseInt( $input.data('max') || 10 );

    // Edit mód: meglévő képek betöltése
    var editImages = VA_Data.edit_images || [];
    var editThumb  = parseInt( VA_Data.edit_thumb ) || 0;
    editImages.forEach(function(img, idx) {
        if (!img.url) return;
        _files.push({ file: null, id: 'existing_' + img.id, existing_id: img.id, url: img.url });
        if (img.id === editThumb) _featured = idx;
    });
    if (_files.length) renderGrid();

    /* ── Fájl hozzáadása ─────────────────────────────── */
    function addFiles(newFiles) {
        for (let f of newFiles) {
            if (_files.length >= _maxImg) break;
            if (!['image/jpeg','image/png','image/webp'].includes(f.type)) continue;
            if (f.size > 5 * 1024 * 1024) { alert(f.name + ' – túl nagy (max 5 MB)!'); continue; }
            _files.push({ file: f, id: 'img_' + Date.now() + '_' + Math.random().toString(36).slice(2), existing_id: null, url: null });
        }
        renderGrid();
    }

    /* ── Megtartandó meglévő képek frissítése ─────────── */
    function updateKeepImages() {
        var keep = _files.filter(function(f){ return f.existing_id; }).map(function(f){ return f.existing_id; });
        $keepInput.val(keep.join(','));
    }

    /* ── Grid renderelése ────────────────────────────── */
    function renderGrid() {
        $grid.empty();

        // Biztosítjuk hogy _featured valid
        if (_files.length > 0 && _featured >= _files.length) _featured = 0;
        $featIdx.val(_featured);
        updateKeepImages();

        _files.forEach((item, idx) => {
            const url = item.url ? item.url : URL.createObjectURL(item.file);
            const isFeat = idx === _featured;
            const starSvg = '<svg viewBox="0 0 24 24" fill="currentColor" width="13" height="13"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
            const xSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" width="13" height="13"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
            const $card = $(`
                <div class="va-img-card${isFeat ? ' va-img-card--featured' : ''}" data-id="${item.id}">
                    <img src="${url}" class="va-img-card__thumb" draggable="false" alt="">
                    <div class="va-img-card__overlay">
                        <button type="button" class="va-img-feat-btn" title="Borítókép beállítása">${starSvg}</button>
                        <button type="button" class="va-img-del-btn" title="Törlés">${xSvg}</button>
                    </div>
                    ${isFeat ? '<div class="va-img-card__label">Borítókép</div>' : ''}
                </div>
            `);

            // Törlés
            $card.find('.va-img-del-btn').on('click', function(){
                _files.splice(idx, 1);
                if (_featured >= _files.length) _featured = 0;
                renderGrid();
            });

            // Főkép
            $card.find('.va-img-feat-btn').on('click', function(){
                _featured = idx;
                renderGrid();
            });

            $grid.append($card);
        });

        // "+ Képek hozzáadása" gomb a grid végére
        if (_files.length < _maxImg) {
            const $addBtn = $('<button type="button" class="va-img-add"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" width="26" height="26"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg><span>Képek<br>hozzáadása</span></button>');
            $addBtn.on('click', function(){ $input.trigger('click'); });
            $grid.append($addBtn);
        }

        // Sortable – jQuery UI (mint az admin)
        if (_files.length > 1) {
            $grid.sortable({
                items: '.va-img-card',
                tolerance: 'pointer',
                cursor: 'grabbing',
                placeholder: 'va-img-ph',
                forcePlaceholderSize: true,
                stop: function() {
                    // Olvassuk vissza a DOM sorrendet _files-ba
                    const newOrder = [];
                    let newFeaturedId = _files[_featured] ? _files[_featured].id : null;
                    $grid.find('.va-img-card').each(function() {
                        const id = $(this).data('id');
                        const found = _files.find(function(f){ return f.id === id; });
                        if (found) newOrder.push(found);
                    });
                    _files = newOrder;
                    if (newFeaturedId) {
                        _featured = _files.findIndex(function(f){ return f.id === newFeaturedId; });
                        if (_featured < 0) _featured = 0;
                    }
                    $featIdx.val(_featured);
                    renderGrid();
                }
            });
        }
    }

    /* ── Drag & drop a gridre ───────────────────────── */
    $grid.on('dragover', function(e){ e.preventDefault(); $(this).addClass('va-img-grid--hover'); });
    $grid.on('dragleave', function(){ $(this).removeClass('va-img-grid--hover'); });
    $grid.on('drop', function(e){
        e.preventDefault();
        $(this).removeClass('va-img-grid--hover');
        addFiles(e.originalEvent.dataTransfer.files);
    });

    /* ── Statikus "+ gomb" kattintás (első renderelés előtt) ── */
    $grid.on('click', '.va-img-add', function(){ $input.trigger('click'); });

    /* ── Fájl input ──────────────────────────────────── */
    $input.on('change', function(){ addFiles(this.files); this.value = ''; });

    /* ══ Form submit ═════════════════════════════════════ */
    $('#va-submit-form').on('submit', function(e){
        e.preventDefault();
        var $btn    = $('#va-submit-btn');
        var editMode = !! VA_Data.edit_mode;
        $btn.prop('disabled', true).text('Feltöltés...');

        var formData = new FormData(this);

        // Csak az új (File objektumos) képek feltöltése
        _files.forEach(function(item){
            if (item.file) {
                formData.append('listing_images[]', item.file, item.file.name);
            }
        });

        // Featured kép: meglévő ID vagy index az új képek között
        var featItem = _files[_featured];
        if (featItem && featItem.existing_id) {
            formData.set('featured_existing_id', featItem.existing_id);
            formData.set('featured_image_index', -1);
        } else {
            // Hány meglévő kép van előtte?
            var newIdx = 0;
            for (var i = 0; i < _featured; i++) {
                if (!_files[i].existing_id) newIdx++;
            }
            formData.set('featured_image_index', newIdx);
        }

        $.ajax({
            url:         VA_Data.ajax_url,
            type:        'POST',
            data:        formData,
            processData: false,
            contentType: false,
            success: function(res){
                $btn.prop('disabled', false).text(editMode ? '💾 Változások mentése' : '📤 Hirdetés feladása');
                if(res.success){
                    $('#va-submit-notice').html('<div class="va-notice va-notice--success">' + res.data.message + '</div>');
                    if(res.data.permalink){
                        setTimeout(function(){ window.location.href = res.data.permalink; }, 2000);
                    }
                } else {
                    if (res.data && res.data.need_credits) {
                        // Kredit szükséges → csomagvásárló megjelenítése
                        var price = res.data.paid_price ? Number(res.data.paid_price).toLocaleString('hu-HU') + ' Ft' : '';
                        var buyPage = '<?php echo esc_js( home_url( '/va-kredit-vasarlas/' ) ); ?>';
                        var html = '<div class="va-notice va-notice--warning" style="padding:18px;">'
                            + '<strong>Elfogyott az ingyenes hirdetési kereted.</strong><br>'
                            + (price ? 'Egy hirdetés ára: <strong>' + price + '</strong><br>' : '')
                            + '<a href="' + buyPage + '" class="va-btn va-btn--primary" style="margin-top:12px;display:inline-flex;">🛒 Hirdetési csomag vásárlása</a>'
                            + '</div>';
                        $('#va-submit-notice').html(html);
                    } else if (res.data && res.data.payment_required && res.data.payment_url) {
                        var amount = res.data.amount ? Number(res.data.amount).toLocaleString('hu-HU') + ' Ft' : '';
                        var html2 = '<div class="va-notice va-notice--warning">'
                            + res.data.message
                            + (amount ? '<br><strong>Fizetendő: ' + amount + '</strong>' : '')
                            + '<br><a href="' + res.data.payment_url + '" class="va-btn va-btn--primary" style="margin-top:10px;display:inline-flex;">Bankkártyás fizetés</a>'
                            + '</div>';
                        $('#va-submit-notice').html(html2);
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
}); // DOMContentLoaded
</script>
