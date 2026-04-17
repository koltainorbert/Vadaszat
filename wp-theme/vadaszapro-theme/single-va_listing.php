<?php
/**
 * single-va_listing.php – Hirdetés részletes oldal (v2 – modern 2026)
 */
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
$views       = intval( get_post_meta( $post_id, 'va_views', true ) );
$expires     = get_post_meta( $post_id, 'va_expires',     true );
$featured    = get_post_meta( $post_id, 'va_featured',    true ) === '1';
$categories  = get_the_terms( $post_id, 'va_category' );
$county      = get_the_terms( $post_id, 'va_county' );
$condition   = get_the_terms( $post_id, 'va_condition' );
$author      = get_userdata( get_the_author_meta('ID') );

// Képek gyűjtése
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

wp_localize_script( 'va-main', 'VA_Data', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce'    => wp_create_nonce('va_user_nonce'),
    'post_id'  => $post_id,
]);
?>

<div class="sl">

    <?php if ( $featured ): ?>
    <div class="sl__featured-bar">⭐ Kiemelt hirdetés</div>
    <?php endif; ?>

    <div class="sl__layout">

        <!-- ═══ BAL OSZLOP – galéria + leírás ══════════ -->
        <div class="sl__left">

            <!-- Galéria -->
            <div class="sl__gallery">
                <div class="sl__main-wrap">
                    <?php if ( ! empty($attachment_ids) ):
                        $main_url = wp_get_attachment_image_url( $attachment_ids[0], 'large' );
                    ?>
                        <img src="<?php echo esc_url($main_url); ?>"
                             id="sl-main-img" class="sl__main-img"
                             alt="<?php the_title_attribute(); ?>">
                    <?php else: ?>
                        <div class="sl__main-img sl__main-empty">🎯</div>
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

            <!-- Leírás -->
            <?php if ( get_the_content() ): ?>
            <div class="sl__card sl__desc">
                <div class="sl__card-title">Leírás</div>
                <div class="sl__desc-body"><?php the_content(); ?></div>
            </div>
            <?php endif; ?>

        </div>

        <!-- ═══ JOBB OSZLOP ══════════════════════════ -->
        <div class="sl__right">

            <!-- Kategória + cím + ár -->
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
                        <span>📍 <?php echo esc_html($county[0]->name); ?></span>
                    <?php endif; ?>
                    <?php if ( $location ): ?>
                        <span>🏙 <?php echo esc_html($location); ?></span>
                    <?php endif; ?>
                    <?php if ( $condition && !is_wp_error($condition) ): ?>
                        <span>📊 <?php echo esc_html($condition[0]->name); ?></span>
                    <?php endif; ?>
                    <span>👁 <?php echo esc_html($views); ?> megtekintés</span>
                    <span>🗓 <?php echo esc_html(get_the_date('Y. m. d.')); ?></span>
                    <?php if ( $expires ): ?>
                        <span class="<?php echo strtotime($expires) < time() ? 'sl__expired' : ''; ?>">
                            ⏱ Lejár: <?php echo esc_html($expires); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Paraméterek -->
            <?php if ( $brand || $model || $caliber || $year || $license_req === '1' ): ?>
            <div class="sl__card sl__params">
                <div class="sl__card-title">Részletek</div>
                <div class="sl__params-grid">
                    <?php if ($brand): ?>
                        <div class="sl__param-row">
                            <span class="sl__param-label">Márka</span>
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
                            <span class="sl__param-label">Gyártási év</span>
                            <span class="sl__param-val"><?php echo esc_html($year); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ( $license_req === '1' ): ?>
                    <div class="sl__license-warn">⚠️ Fegyverengedély szükséges</div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Feladó + kapcsolat -->
            <div class="sl__card sl__contact">
                <div class="sl__card-title">Feladó</div>
                <div class="sl__seller">
                    <div class="sl__seller-av">
                        <?php echo strtoupper( substr( $author ? $author->display_name : 'X', 0, 1 ) ); ?>
                    </div>
                    <div>
                        <div class="sl__seller-name"><?php echo esc_html($author ? $author->display_name : 'Ismeretlen'); ?></div>
                        <?php if ($author): ?>
                        <div class="sl__seller-since">Tag <?php echo esc_html(date('Y', strtotime($author->user_registered))); ?> óta</div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                $author_phone = $author ? get_user_meta($author->ID, 'va_phone', true) : '';
                $show_phone   = $phone ?: $author_phone;
                if ( $show_phone ): ?>
                    <button class="sl__btn sl__btn--phone" data-phone="<?php echo esc_attr($show_phone); ?>">
                        📞 Telefonszám megjelenítése
                    </button>
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','',$show_phone)); ?>"
                       class="sl__phone-reveal" id="sl-phone" style="display:none;">
                        <?php echo esc_html($show_phone); ?>
                    </a>
                <?php endif; ?>

                <?php if ( $email_show === '1' && $author ): ?>
                    <a href="mailto:<?php echo esc_attr($author->user_email); ?>" class="sl__btn sl__btn--email">
                        ✉️ Üzenet küldése
                    </a>
                <?php endif; ?>

                <?php if ( is_user_logged_in() ):
                    $watching = va_user_watches($post_id); ?>
                    <button class="sl__btn sl__btn--watch<?php echo $watching?' active':''; ?>"
                            data-post-id="<?php echo esc_attr($post_id); ?>">
                        <?php echo $watching ? '❤️ Kedvencekből eltávolítás' : '🤍 Mentés kedvencekbe'; ?>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Feladó többi hirdetése -->
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
                <div class="sl__card-title">Feladó további hirdetései</div>
                <?php while ( $other->have_posts() ): $other->the_post();
                    $p_id    = get_the_ID();
                    $p_price = get_post_meta($p_id,'va_price',true);
                    $p_type  = get_post_meta($p_id,'va_price_type',true);
                ?>
                    <a href="<?php the_permalink(); ?>" class="sl__more-item">
                        <?php if ( has_post_thumbnail() ):
                            echo get_the_post_thumbnail(null,[54,54],['class'=>'sl__more-img']);
                        else: ?>
                            <div class="sl__more-img sl__more-img--empty">🎯</div>
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
    // Galéria
    document.querySelectorAll('.sl__thumb').forEach(function(t){
        t.addEventListener('click',function(){
            var mi = document.getElementById('sl-main-img');
            if(mi) mi.src = this.dataset.src;
            document.querySelectorAll('.sl__thumb').forEach(function(x){ x.classList.remove('sl__thumb--active'); });
            this.classList.add('sl__thumb--active');
        });
    });
    // Telefonszám
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
                    b.textContent = b.classList.contains('active') ? '❤️ Kedvencekből eltávolítás' : '🤍 Mentés kedvencekbe';
                }
            });
        });
    });
})();
</script>

<?php get_footer(); ?>


if ( ! have_posts() ) { get_footer(); return; }
the_post();

$post_id     = get_the_ID();
$price       = get_post_meta( $post_id, 'va_price', true );
$price_type  = get_post_meta( $post_id, 'va_price_type', true ) ?: 'fixed';
$brand       = get_post_meta( $post_id, 'va_brand',   true );
$model       = get_post_meta( $post_id, 'va_model',   true );
$caliber     = get_post_meta( $post_id, 'va_caliber', true );
$year        = get_post_meta( $post_id, 'va_year',    true );
$phone       = get_post_meta( $post_id, 'va_phone',   true );
$location    = get_post_meta( $post_id, 'va_location', true );
$license_req = get_post_meta( $post_id, 'va_license_req', true );
$email_show  = get_post_meta( $post_id, 'va_email_show',  true );
$views       = intval( get_post_meta( $post_id, 'va_views', true ) );
$expires     = get_post_meta( $post_id, 'va_expires', true );
$categories  = get_the_terms( $post_id, 'va_category' );
$county      = get_the_terms( $post_id, 'va_county' );
$condition   = get_the_terms( $post_id, 'va_condition' );
$author      = get_userdata( get_the_author_meta('ID') );

wp_localize_script( 'va-main', 'VA_Data', [
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce'    => wp_create_nonce('va_user_nonce'),
    'post_id'  => $post_id,
]);
?>
<div class="va-wrap va-single">

    <!-- Galéria -->
    <div class="va-listing-detail__gallery">
        <?php if ( has_post_thumbnail() ): ?>
            <img src="<?php echo esc_url( get_the_post_thumbnail_url($post_id, 'va-detail') ); ?>"
                 class="va-listing-detail__main-img" id="va-main-img" alt="<?php the_title_attribute(); ?>">
        <?php else: ?>
            <div class="va-listing-detail__main-img" style="background:rgba(255,255,255,0.04);display:flex;align-items:center;justify-content:center;font-size:80px;">🎯</div>
        <?php endif; ?>

        <!-- Thumbnails -->
        <?php
        $attachment_ids = get_posts([
            'post_type'      => 'attachment',
            'posts_per_page' => 10,
            'post_parent'    => $post_id,
            'post_mime_type' => 'image',
        ]);
        if ( count($attachment_ids) > 1 ): ?>
        <div class="va-listing-detail__thumbs">
            <?php foreach ($attachment_ids as $att): ?>
                <img src="<?php echo esc_url( wp_get_attachment_thumb_url($att->ID) ); ?>"
                     class="va-listing-detail__thumb"
                     data-src="<?php echo esc_url( wp_get_attachment_url($att->ID) ); ?>"
                     alt="">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">
        <div>
            <!-- Cím + metaadatok -->
            <?php if ( get_post_meta($post_id, 'va_featured', true) === '1' ): ?>
                <span style="background:#ffaa00;color:#000;font-size:11px;font-weight:700;padding:3px 8px;border-radius:3px;">⭐ Kiemelt hirdetés</span>
            <?php endif; ?>
            <h1 class="va-listing-detail__title"><?php the_title(); ?></h1>
            <div class="va-listing-detail__price"><?php echo esc_html(va_format_price($price, $price_type)); ?></div>

            <div style="display:flex;flex-wrap:wrap;gap:12px;font-size:13px;color:rgba(255,255,255,0.5);margin-bottom:24px;">
                <?php if ($categories && !is_wp_error($categories)): ?>
                    <span>🏷 <?php echo esc_html($categories[0]->name); ?></span>
                <?php endif; ?>
                <?php if ($county && !is_wp_error($county)): ?>
                    <span>📍 <?php echo esc_html($county[0]->name); ?></span>
                <?php endif; ?>
                <?php if ($location): ?>
                    <span>🏙 <?php echo esc_html($location); ?></span>
                <?php endif; ?>
                <?php if ($condition && !is_wp_error($condition)): ?>
                    <span>📊 <?php echo esc_html($condition[0]->name); ?></span>
                <?php endif; ?>
                <span>👁 <?php echo esc_html($views); ?> megtekintés</span>
                <span>🗓 <?php echo esc_html(get_the_date('Y. F j.')); ?></span>
                <?php if ($expires): ?>
                    <span style="color:<?php echo strtotime($expires) < time() ? '#ff0000' : 'inherit'; ?>">
                        ⏱ Lejár: <?php echo esc_html($expires); ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Paraméterek -->
            <?php if ($brand || $model || $caliber || $year || $license_req): ?>
            <div class="va-listing-detail__params">
                <div class="va-listing-detail__params-title">Részletek</div>
                <div class="va-param-grid">
                    <?php if ($brand): ?>
                        <div class="va-param-item"><span class="va-param-item__label">Márka</span><span class="va-param-item__value"><?php echo esc_html($brand); ?></span></div>
                    <?php endif; ?>
                    <?php if ($model): ?>
                        <div class="va-param-item"><span class="va-param-item__label">Modell</span><span class="va-param-item__value"><?php echo esc_html($model); ?></span></div>
                    <?php endif; ?>
                    <?php if ($caliber): ?>
                        <div class="va-param-item"><span class="va-param-item__label">Kaliber</span><span class="va-param-item__value"><?php echo esc_html($caliber); ?></span></div>
                    <?php endif; ?>
                    <?php if ($year): ?>
                        <div class="va-param-item"><span class="va-param-item__label">Gyártási év</span><span class="va-param-item__value"><?php echo esc_html($year); ?></span></div>
                    <?php endif; ?>
                    <?php if ($license_req === '1'): ?>
                        <div class="va-param-item" style="grid-column:1/-1;color:#ffb400;">⚠️ Fegyverengedély szükséges</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Leírás -->
            <div class="va-listing-detail__desc">
                <?php the_content(); ?>
            </div>
        </div>

        <!-- Jobb panel: kapcsolat + watchlist -->
        <div>
            <div class="va-contact-box">
                <div style="font-size:12px;color:rgba(255,255,255,0.5);margin-bottom:8px;">Feladó</div>
                <div style="font-weight:700;font-size:15px;"><?php echo esc_html($author ? $author->display_name : ''); ?></div>

                <?php $author_phone = $author ? get_user_meta($author->ID, 'va_phone', true) : ''; ?>
                <?php $show_phone = $phone ?: $author_phone; ?>
                <?php if ($show_phone): ?>
                    <button class="va-contact-box__show-btn" data-phone="<?php echo esc_attr($show_phone); ?>" style="margin-top:12px;">
                        📞 Telefonszám megjelenítése
                    </button>
                <?php endif; ?>

                <?php if ($email_show === '1' && $author): ?>
                    <div style="margin-top:10px;font-size:13px;color:rgba(255,255,255,0.6);">
                        ✉️ <a href="mailto:<?php echo esc_attr($author->user_email); ?>" style="color:rgba(255,255,255,0.6);"><?php echo esc_html($author->user_email); ?></a>
                    </div>
                <?php endif; ?>

                <?php if (is_user_logged_in()): ?>
                    <button class="va-card__watchlist" data-post-id="<?php echo esc_attr($post_id); ?>"
                        style="position:static;display:flex;width:100%;justify-content:center;margin-top:14px;background:rgba(255,255,255,0.06);border-radius:6px;padding:10px;font-size:14px;<?php echo va_user_watches($post_id) ? 'color:#ff0000;' : ''; ?>">
                        ♥ <?php echo va_user_watches($post_id) ? 'Kedvencekből eltávolítás' : 'Mentés kedvencekbe'; ?>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Feladó többi hirdetése -->
            <?php if ($author): $other = new WP_Query(['post_type' => 'va_listing', 'post_status' => 'publish', 'author' => $author->ID, 'posts_per_page' => 3, 'post__not_in' => [$post_id]]);
            if ($other->have_posts()): ?>
            <div class="va-sidebar__widget" style="margin-top:16px;">
                <div class="va-sidebar__widget-title">Feladó további hirdetései</div>
                <?php while ($other->have_posts()): $other->the_post(); ?>
                    <a href="<?php the_permalink(); ?>" style="display:block;padding:8px 0;color:rgba(255,255,255,0.7);text-decoration:none;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.06);">
                        <?php the_title(); ?>
                    </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <?php endif; endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
