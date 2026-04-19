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
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M24 12.073C24 5.404 18.627 0 12 0S0 5.404 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047v-2.66c0-3.025 1.791-4.697 4.533-4.697 1.313 0 2.686.236 2.686.236v2.97h-1.513c-1.491 0-1.956.931-1.956 1.886v2.265h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
                    </a>
                    <a href="https://wa.me/?text=<?php echo $share_title; ?>%20<?php echo $share_url; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--wa" aria-label="Megosztás WhatsApp-on">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                    <a href="https://t.me/share/url?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--tg" aria-label="Megosztás Telegramon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>" target="_blank" rel="noopener noreferrer" class="sl__share-btn sl__share-btn--tw" aria-label="Megosztás X-en">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
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

<script>
(function(){
    // Galeria
    document.querySelectorAll('.sl__thumb').forEach(function(t){
        t.addEventListener('click',function(){
            var mi = document.getElementById('sl-main-img');
            if(mi) mi.src = this.dataset.src;
            document.querySelectorAll('.sl__thumb').forEach(function(x){ x.classList.remove('sl__thumb--active'); });
            this.classList.add('sl__thumb--active');
        });
    });
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
        copyBtn.addEventListener('click', function(){
            var url = this.dataset.url;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function(){
                    copyBtn.classList.add('sl__share-btn--copied');
                    setTimeout(function(){ copyBtn.classList.remove('sl__share-btn--copied'); }, 2000);
                });
            }
        });
    }
})();
</script>

<?php get_footer(); ?>
