<?php
/**
 * single-va_listing.php - Hirdetes reszletes oldal (v2 - modern 2026)
 */

// ── Open Graph / Twitter Card meta tagok ────────────────
add_action( 'wp_head', function() {
    if ( ! is_singular( 'va_listing' ) ) return;

    $post_id = get_the_ID();

    // Cím
    $og_title = get_the_title( $post_id );

    // Leírás: post excerpt vagy description meta, 160 karakterre vágva
    $desc = get_post_meta( $post_id, 'va_description', true );
    if ( $desc === '' ) $desc = get_the_excerpt();
    $og_desc = wp_strip_all_tags( $desc );
    $og_desc = preg_replace( '/\s+/', ' ', $og_desc );
    $og_desc = trim( mb_substr( $og_desc, 0, 160 ) );

    // Kép: kiemelt kép (borítókép)
    $og_image     = '';
    $og_img_w     = '';
    $og_img_h     = '';
    $thumb_id = get_post_thumbnail_id( $post_id );
    if ( $thumb_id ) {
        $img_data = wp_get_attachment_image_src( $thumb_id, 'large' );
        if ( $img_data ) {
            $og_image = $img_data[0];
            $og_img_w = $img_data[1];
            $og_img_h = $img_data[2];
        }
    }

    // URL
    $og_url = get_permalink( $post_id );

    // Site neve
    $site_name = get_option( 'va_site_name', get_bloginfo('name') );

    echo "\n<!-- Open Graph / Social Share -->\n";
    echo '<meta property="og:type" content="product">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url( $og_url ) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '">' . "\n";
    if ( $og_desc !== '' ) {
        echo '<meta property="og:description" content="' . esc_attr( $og_desc ) . '">' . "\n";
        echo '<meta name="description" content="' . esc_attr( $og_desc ) . '">' . "\n";
    }
    if ( $og_image !== '' ) {
        echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
        if ( $og_img_w ) echo '<meta property="og:image:width" content="' . esc_attr( (string) $og_img_w ) . '">' . "\n";
        if ( $og_img_h ) echo '<meta property="og:image:height" content="' . esc_attr( (string) $og_img_h ) . '">' . "\n";
        echo '<meta property="og:image:alt" content="' . esc_attr( $og_title ) . '">' . "\n";
    }
    // Twitter Card
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . '">' . "\n";
    if ( $og_desc !== '' ) echo '<meta name="twitter:description" content="' . esc_attr( $og_desc ) . '">' . "\n";
    if ( $og_image !== '' ) echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";
    echo "<!-- /Open Graph -->\n\n";
}, 1 ); // priority 1 = wp_head legeleje

get_header();

if ( ! have_posts() ) { get_footer(); return; }
the_post();

$post_id     = get_the_ID();
$price       = get_post_meta( $post_id, 'va_price',       true );
$price_type  = get_post_meta( $post_id, 'va_price_type',  true ) ?: 'fixed';
$brand       = get_post_meta( $post_id, 'va_brand',       true );
$model       = get_post_meta( $post_id, 'va_model',       true );
$caliber     = get_post_meta( $post_id, 'va_caliber',     true );
$year        = get_post_meta( $post_id, 'va_year',        true );
$phone       = get_post_meta( $post_id, 'va_phone',       true );
$location    = get_post_meta( $post_id, 'va_location',    true );
$license_req = get_post_meta( $post_id, 'va_license_req', true );
$email_show  = get_post_meta( $post_id, 'va_email_show',  true );
$views       = va_display_views( $post_id );
$expires     = get_post_meta( $post_id, 'va_expires',     true );
$featured    = get_post_meta( $post_id, 'va_featured',    true ) === '1';
$categories  = get_the_terms( $post_id, 'va_category' );
$county      = get_the_terms( $post_id, 'va_county' );
$condition   = get_the_terms( $post_id, 'va_condition' );
$author      = get_userdata( get_the_author_meta('ID') );

// Kepek gyujtese
$att_args = [
    'post_type'      => 'attachment',
    'posts_per_page' => 12,
    'post_parent'    => $post_id,
    'post_mime_type' => 'image',
    'fields'         => 'ids',
    'no_found_rows'  => true,
];
$attachment_ids = get_posts( $att_args );
if ( has_post_thumbnail() ) {
    $thumb_id = get_post_thumbnail_id( $post_id );
    $attachment_ids = array_unique( array_merge( [ $thumb_id ], $attachment_ids ) );
}

wp_enqueue_script( 'va-frontend', VA_PLUGIN_URL . 'frontend/js/frontend.js', [ 'jquery' ], VA_VERSION, true );
wp_localize_script( 'va-frontend', 'VA_Data', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce'    => wp_create_nonce('va_user_nonce'),
    'post_id'  => $post_id,
]);
?>

<div class="sl">

    <?php if ( $featured ): ?>
    <div class="sl__featured-bar">Kiemelt hirdet&eacute;s</div>
    <?php endif; ?>

    <div class="sl__layout">

        <!-- BAL: galeria + leiras -->
        <div class="sl__left">

            <div class="sl__gallery">
                <div class="sl__main-wrap">
                    <?php if ( ! empty($attachment_ids) ):
                        $main_url = wp_get_attachment_image_url( $attachment_ids[0], 'large' );
                    ?>
                        <img src="<?php echo esc_url($main_url); ?>"
                             id="sl-main-img" class="sl__main-img"
                             alt="<?php the_title_attribute(); ?>">
                        <button type="button" class="sl__zoom-trigger" id="sl-zoom-trigger" aria-label="Kép nagyítása">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="17" height="17" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                            <span>Nagyítás</span>
                        </button>
                    <?php else: ?>
                        <div class="sl__main-img sl__main-empty">Nincs k&eacute;p</div>
                    <?php endif; ?>
                </div>

                <?php if ( count($attachment_ids) > 1 ): ?>
                <div class="sl__thumbs">
                    <?php foreach ( $attachment_ids as $i => $att_id ):
                        $t = wp_get_attachment_image_url( $att_id, 'thumbnail' );
                        $l = wp_get_attachment_image_url( $att_id, 'large' );
                    ?>
                        <img src="<?php echo esc_url($t); ?>"
                             class="sl__thumb<?php echo $i===0?' sl__thumb--active':''; ?>"
                             data-src="<?php echo esc_url($l); ?>" alt="">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Leiras -->
            <?php if ( get_the_content() ): ?>
            <div class="sl__card sl__desc">
                <div class="sl__card-title">Le&iacute;r&aacute;s</div>
                <div class="sl__desc-body"><?php the_content(); ?></div>
            </div>
            <?php endif; ?>

        </div><!-- .sl__left -->

        <!-- JOBB: fejlec + adatok + kontakt -->
        <div class="sl__right">

            <!-- Fejlec: kategoria + cim + ar -->
            <div class="sl__card sl__head">
                <?php if ( $categories && !is_wp_error($categories) ): ?>
                    <a href="<?php echo esc_url(get_term_link($categories[0])); ?>" class="sl__cat-pill">
                        <?php echo esc_html($categories[0]->name); ?>
                    </a>
                <?php endif; ?>
                <h1 class="sl__title"><?php the_title(); ?></h1>
                <div class="sl__price"><?php echo esc_html( va_format_price($price, $price_type) ); ?></div>

                <div class="sl__meta-row">
                    <?php if ( $county && !is_wp_error($county) ): ?>
                        <span>Helysz&iacute;n: <?php echo esc_html($county[0]->name); ?></span>
                    <?php endif; ?>
                    <?php if ( $condition && !is_wp_error($condition) ): ?>
                        <span>&Aacute;llapot: <?php echo esc_html($condition[0]->name); ?></span>
                    <?php endif; ?>
                    <span class="sl__views">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="15" height="15" style="vertical-align:-2px;margin-right:4px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg><?php echo esc_html( number_format($views, 0, ',', ' ') ); ?> megtekintés
                    </span>
                    <span>Feladva: <?php echo esc_html(get_the_date('Y. m. d.')); ?></span>
                    <?php if ( $expires ): ?>
                        <span class="<?php echo strtotime($expires) < time() ? 'sl__expired' : ''; ?>">
                            Lej&aacute;r: <?php echo esc_html($expires); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Parameterek -->
            <?php if ( $brand || $model || $caliber || $year || $license_req === '1' ): ?>
            <div class="sl__card sl__params">
                <div class="sl__card-title">R&eacute;szletek</div>
                <div class="sl__params-grid">
                    <?php if ($brand): ?>
                        <div class="sl__param-row">
                            <span class="sl__param-label">M&aacute;rka</span>
                            <span class="sl__param-val"><?php echo esc_html($brand); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($model): ?>
                        <div class="sl__param-row">
                            <span class="sl__param-label">Modell</span>
                            <span class="sl__param-val"><?php echo esc_html($model); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($caliber): ?>
                        <div class="sl__param-row">
                            <span class="sl__param-label">Kaliber</span>
                            <span class="sl__param-val"><?php echo esc_html($caliber); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($year): ?>
                        <div class="sl__param-row">
                            <span class="sl__param-label">Gy&aacute;rt&aacute;si &eacute;v</span>
                            <span class="sl__param-val"><?php echo esc_html($year); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ( $license_req === '1' ): ?>
                    <div class="sl__license-warn">Fegyverenged&eacute;ly sz&uuml;ks&eacute;ges</div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Felado + kapcsolat -->
            <div class="sl__card sl__contact">
                <div class="sl__card-title">Felad&oacute;</div>
                <div class="sl__seller">
                    <div class="sl__seller-av">
                        <?php echo strtoupper( substr( $author ? $author->display_name : 'X', 0, 1 ) ); ?>
                    </div>
                    <div>
                        <div class="sl__seller-name"><?php echo esc_html($author ? $author->display_name : 'Ismeretlen'); ?></div>
                        <?php if ($author): ?>
                        <div class="sl__seller-since">Tag <?php echo esc_html(date('Y', strtotime($author->user_registered))); ?> &oacute;ta</div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                $author_phone = $author ? get_user_meta($author->ID, 'va_phone', true) : '';
                $show_phone   = $phone ?: $author_phone;
                if ( $show_phone ): ?>
                    <button class="sl__btn sl__btn--phone" data-phone="<?php echo esc_attr($show_phone); ?>">
                        Telefonsz&aacute;m megjelen&iacute;t&eacute;se
                    </button>
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','',$show_phone)); ?>"
                       class="sl__phone-reveal" id="sl-phone" style="display:none;">
                        <?php echo esc_html($show_phone); ?>
                    </a>
                <?php endif; ?>

                <?php if ( $email_show === '1' && $author ): ?>
                    <a href="mailto:<?php echo esc_attr($author->user_email); ?>" class="sl__btn sl__btn--email">
                        E-mail &uuml;zenet k&uuml;ld&eacute;se
                    </a>
                <?php endif; ?>

                <?php if ( is_user_logged_in() ):
                    $watching = va_user_watches($post_id); ?>
                    <button class="sl__btn sl__btn--watch<?php echo $watching?' active':''; ?>"
                            data-post-id="<?php echo esc_attr($post_id); ?>">
                        <?php echo $watching ? 'Kedvencekb&#337;l elt&aacute;vol&iacute;t&aacute;s' : 'Ment&eacute;s kedvencekbe'; ?>
                    </button>
                <?php endif; ?>

                <!-- Megosztás -->
                <?php
                $share_url   = rawurlencode( get_permalink( $post_id ) );
                $share_title = rawurlencode( get_the_title( $post_id ) );
                ?>
                <div class="sl__share">
                    <span class="sl__share-label">Megosztás:</span>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--fb" aria-label="Megosztás Facebookon">
                        <?php echo function_exists( 'va_social_svg' ) ? va_social_svg( 'facebook', 18 ) : ''; ?>
                    </a>
                    <a href="https://wa.me/?text=<?php echo $share_title; ?>%20<?php echo $share_url; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--wa" aria-label="Megosztás WhatsApp-on">
                        <?php echo function_exists( 'va_social_svg' ) ? va_social_svg( 'whatsapp', 18 ) : ''; ?>
                    </a>
                    <a href="https://t.me/share/url?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--tg" aria-label="Megosztás Telegramon">
                        <?php echo function_exists( 'va_social_svg' ) ? va_social_svg( 'telegram', 18 ) : ''; ?>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--tw" aria-label="Megosztás X-en">
                        <?php echo function_exists( 'va_social_svg' ) ? va_social_svg( 'twitter', 18 ) : ''; ?>
                    </a>
                    <button class="sl__share-btn sl__share-btn--copy" id="sl-copy-link" aria-label="Link másolása" data-url="<?php echo esc_attr( get_permalink( $post_id ) ); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                    </button>
                </div>
            </div>

            <!-- Felado tovabbi hirdetesek -->
            <?php if ( $author ):
                $other = new WP_Query([
                    'post_type'      => 'va_listing',
                    'post_status'    => 'publish',
                    'author'         => $author->ID,
                    'posts_per_page' => 3,
                    'post__not_in'   => [$post_id],
                    'no_found_rows'  => true,
                ]);
                if ( $other->have_posts() ):
            ?>
            <div class="sl__card sl__more">
                <div class="sl__card-title">Felad&oacute; tov&aacute;bbi hirdet&eacute;sei</div>
                <?php while ( $other->have_posts() ): $other->the_post();
                    $p_id    = get_the_ID();
                    $p_price = get_post_meta($p_id,'va_price',true);
                    $p_type  = get_post_meta($p_id,'va_price_type',true);
                ?>
                    <a href="<?php the_permalink(); ?>" class="sl__more-item">
                        <?php if ( has_post_thumbnail() ):
                            echo get_the_post_thumbnail(null,[54,54],['class'=>'sl__more-img']);
                        else: ?>
                            <div class="sl__more-img sl__more-img--empty">&#128444;</div>
                        <?php endif; ?>
                        <div class="sl__more-info">
                            <div class="sl__more-title"><?php the_title(); ?></div>
                            <div class="sl__more-price"><?php echo esc_html(va_format_price($p_price,$p_type)); ?></div>
                        </div>
                    </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <?php endif; endif; ?>

        </div><!-- .sl__right -->
    </div><!-- .sl__layout -->
</div><!-- .sl -->

<?php if ( ! empty( $attachment_ids ) ): ?>
<div class="sl-viewer" id="sl-viewer" hidden>
    <button type="button" class="sl-viewer__close" id="sl-viewer-close" aria-label="Bezárás">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" width="22" height="22" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="sl-viewer__toolbar">
        <button type="button" class="sl-viewer__btn" id="sl-zoom-out" aria-label="Kicsinyítés">-</button>
        <button type="button" class="sl-viewer__btn" id="sl-zoom-reset" aria-label="Méret visszaállítása">100%</button>
        <button type="button" class="sl-viewer__btn" id="sl-zoom-in" aria-label="Nagyítás">+</button>
    </div>
    <div class="sl-viewer__stage" id="sl-viewer-stage">
        <img src="<?php echo esc_url( $main_url ); ?>" alt="<?php the_title_attribute(); ?>" class="sl-viewer__img" id="sl-viewer-img">
    </div>
</div>
<?php endif; ?>

<script>
(function(){
    var thumbs = Array.prototype.slice.call(document.querySelectorAll('.sl__thumb'));
    var mainImg = document.getElementById('sl-main-img');

    function syncMainImage(src) {
        if (!mainImg || !src) return;
        mainImg.src = src;
    }

    // Galeria
    thumbs.forEach(function(t){
        t.addEventListener('click',function(){
            syncMainImage(this.dataset.src);
            thumbs.forEach(function(x){ x.classList.remove('sl__thumb--active'); });
            this.classList.add('sl__thumb--active');
        });
    });

    // Profi kepnezegeto (zoom + drag)
    var viewer = document.getElementById('sl-viewer');
    var viewerImg = document.getElementById('sl-viewer-img');
    var stage = document.getElementById('sl-viewer-stage');
    var zoomTrigger = document.getElementById('sl-zoom-trigger');
    var zoomIn = document.getElementById('sl-zoom-in');
    var zoomOut = document.getElementById('sl-zoom-out');
    var zoomReset = document.getElementById('sl-zoom-reset');
    var closeBtn = document.getElementById('sl-viewer-close');
    var scale = 1;
    var tx = 0;
    var ty = 0;
    var dragging = false;
    var sx = 0;
    var sy = 0;

    function applyTransform() {
        if (!viewerImg) return;
        viewerImg.style.transform = 'translate(' + tx + 'px,' + ty + 'px) scale(' + scale + ')';
    }

    function setScale(nextScale) {
        scale = Math.max(1, Math.min(4, nextScale));
        if (scale === 1) { tx = 0; ty = 0; }
        applyTransform();
        if (zoomReset) zoomReset.textContent = Math.round(scale * 100) + '%';
    }

    function openViewer() {
        if (!viewer || !viewerImg || !mainImg) return;
        viewerImg.src = mainImg.src;
        viewer.hidden = false;
        document.body.classList.add('sl-viewer-open');
        setScale(1);
    }

    function closeViewer() {
        if (!viewer) return;
        viewer.hidden = true;
        document.body.classList.remove('sl-viewer-open');
    }

    if (zoomTrigger) {
        zoomTrigger.addEventListener('click', openViewer);
    }
    if (mainImg) {
        mainImg.addEventListener('click', openViewer);
    }

    if (closeBtn) closeBtn.addEventListener('click', closeViewer);
    if (viewer) {
        viewer.addEventListener('click', function(e){
            if (e.target === viewer) closeViewer();
        });
    }
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape') closeViewer();
    });

    if (zoomIn) zoomIn.addEventListener('click', function(){ setScale(scale + 0.25); });
    if (zoomOut) zoomOut.addEventListener('click', function(){ setScale(scale - 0.25); });
    if (zoomReset) zoomReset.addEventListener('click', function(){ setScale(1); });

    if (stage) {
        stage.addEventListener('wheel', function(e){
            e.preventDefault();
            setScale(scale + (e.deltaY < 0 ? 0.2 : -0.2));
        }, { passive: false });

        stage.addEventListener('mousedown', function(e){
            if (scale <= 1) return;
            dragging = true;
            sx = e.clientX - tx;
            sy = e.clientY - ty;
            stage.classList.add('is-dragging');
        });

        document.addEventListener('mousemove', function(e){
            if (!dragging) return;
            tx = e.clientX - sx;
            ty = e.clientY - sy;
            applyTransform();
        });

        document.addEventListener('mouseup', function(){
            dragging = false;
            stage.classList.remove('is-dragging');
        });
    }

    // Telefonszam
    var pb = document.querySelector('.sl__btn--phone');
    if(pb) pb.addEventListener('click',function(){
        var el = document.getElementById('sl-phone');
        if(el){ el.style.display='flex'; this.style.display='none'; }
    });
    // Watchlist
    document.querySelectorAll('.sl__btn--watch').forEach(function(btn){
        btn.addEventListener('click',function(){
            var b = this;
            fetch(VA_Data.ajax_url,{method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:'action=va_toggle_watchlist&nonce='+VA_Data.nonce+'&post_id='+VA_Data.post_id
            }).then(function(r){return r.json();}).then(function(d){
                if(d.success){
                    b.classList.toggle('active');
                    b.textContent = b.classList.contains('active')
                        ? 'Kedvencekb\u0151l elt\u00e1vol\u00edt\u00e1s'
                        : 'Ment\u00e9s kedvencekbe';
                }
            });
        });
    });
    // Link másolása
    var copyBtn = document.getElementById('sl-copy-link');
    if (copyBtn) {
        copyBtn.addEventListener('click', function(e){
            e.preventDefault();
            var url = this.dataset.url;
            function markCopied() {
                copyBtn.classList.add('sl__share-btn--copied');
                setTimeout(function(){ copyBtn.classList.remove('sl__share-btn--copied'); }, 2000);
                if (typeof window.va_toast === 'function') {
                    window.va_toast('Link kimásolva.', 'success');
                }
            }

            function markFailed() {
                if (typeof window.va_toast === 'function') {
                    window.va_toast('A másolás nem sikerült.', 'error');
                }
            }

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(url).then(markCopied).catch(markFailed);
                return;
            }

            try {
                var temp = document.createElement('textarea');
                temp.value = url;
                temp.setAttribute('readonly', 'readonly');
                temp.style.position = 'fixed';
                temp.style.opacity = '0';
                document.body.appendChild(temp);
                temp.select();
                temp.setSelectionRange(0, temp.value.length);
                var ok = document.execCommand('copy');
                document.body.removeChild(temp);
                if (ok) { markCopied(); } else { markFailed(); }
            } catch (err) {
                markFailed();
            }
        });
    }
})();
</script>

<?php get_footer(); ?>
