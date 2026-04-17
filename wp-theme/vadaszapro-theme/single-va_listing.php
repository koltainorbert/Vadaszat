<?php
/**
 * single-va_listing.php â€“ HirdetĂ©s rĂ©szletes oldal (v2 â€“ modern 2026)
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

// KĂ©pek gyĹ±jtĂ©se
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
    <div class="sl__featured-bar">â­ Kiemelt hirdetĂ©s</div>
    <?php endif; ?>

    <div class="sl__layout">

        <!-- â•â•â• BAL OSZLOP â€“ galĂ©ria + leĂ­rĂˇs â•â•â•â•â•â•â•â•â•â• -->
        <div class="sl__left">

            <!-- GalĂ©ria -->
            <div class="sl__gallery">
                <div class="sl__main-wrap">
                    <?php if ( ! empty($attachment_ids) ):
                        $main_url = wp_get_attachment_image_url( $attachment_ids[0], 'large' );
                    ?>
                        <img src="<?php echo esc_url($main_url); ?>"
                             id="sl-main-img" class="sl__main-img"
                             alt="<?php the_title_attribute(); ?>">
                    <?php else: ?>
                        <div class="sl__main-img sl__main-empty">đźŽŻ</div>
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

            <!-- LeĂ­rĂˇs -->
            <?php if ( get_the_content() ): ?>
            <div class="sl__card sl__desc">
                <div class="sl__card-title">LeĂ­rĂˇs</div>
                <div class="sl__desc-body"><?php the_content(); ?></div>
            </div>
            <?php endif; ?>

        </div>

        <!-- â•â•â• JOBB OSZLOP â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• -->
        <div class="sl__right">

            <!-- KategĂłria + cĂ­m + Ăˇr -->
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
                        <span>đź“Ť <?php echo esc_html($county[0]->name); ?></span>
                    <?php endif; ?>
                    <?php if ( $location ): ?>
                        <span>đźŹ™ <?php echo esc_html($location); ?></span>
                    <?php endif; ?>
                    <?php if ( $condition && !is_wp_error($condition) ): ?>
                        <span>đź“Š <?php echo esc_html($condition[0]->name); ?></span>
                    <?php endif; ?>
                    <span>đź‘ <?php echo esc_html($views); ?> megtekintĂ©s</span>
                    <span>đź—“ <?php echo esc_html(get_the_date('Y. m. d.')); ?></span>
                    <?php if ( $expires ): ?>
                        <span class="<?php echo strtotime($expires) < time() ? 'sl__expired' : ''; ?>">
                            âŹ± LejĂˇr: <?php echo esc_html($expires); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ParamĂ©terek -->
            <?php if ( $brand || $model || $caliber || $year || $license_req === '1' ): ?>
            <div class="sl__card sl__params">
                <div class="sl__card-title">RĂ©szletek</div>
                <div class="sl__params-grid">
                    <?php if ($brand): ?>
                        <div class="sl__param-row">
                            <span class="sl__param-label">MĂˇrka</span>
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
                            <span class="sl__param-label">GyĂˇrtĂˇsi Ă©v</span>
                            <span class="sl__param-val"><?php echo esc_html($year); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ( $license_req === '1' ): ?>
                    <div class="sl__license-warn">âš ď¸Ź FegyverengedĂ©ly szĂĽksĂ©ges</div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- FeladĂł + kapcsolat -->
            <div class="sl__card sl__contact">
                <div class="sl__card-title">FeladĂł</div>
                <div class="sl__seller">
                    <div class="sl__seller-av">
                        <?php echo strtoupper( substr( $author ? $author->display_name : 'X', 0, 1 ) ); ?>
                    </div>
                    <div>
                        <div class="sl__seller-name"><?php echo esc_html($author ? $author->display_name : 'Ismeretlen'); ?></div>
                        <?php if ($author): ?>
                        <div class="sl__seller-since">Tag <?php echo esc_html(date('Y', strtotime($author->user_registered))); ?> Ăłta</div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                $author_phone = $author ? get_user_meta($author->ID, 'va_phone', true) : '';
                $show_phone   = $phone ?: $author_phone;
                if ( $show_phone ): ?>
                    <button class="sl__btn sl__btn--phone" data-phone="<?php echo esc_attr($show_phone); ?>">
                        đź“ž TelefonszĂˇm megjelenĂ­tĂ©se
                    </button>
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','',$show_phone)); ?>"
                       class="sl__phone-reveal" id="sl-phone" style="display:none;">
                        <?php echo esc_html($show_phone); ?>
                    </a>
                <?php endif; ?>

                <?php if ( $email_show === '1' && $author ): ?>
                    <a href="mailto:<?php echo esc_attr($author->user_email); ?>" class="sl__btn sl__btn--email">
                        âś‰ď¸Ź Ăśzenet kĂĽldĂ©se
                    </a>
                <?php endif; ?>

                <?php if ( is_user_logged_in() ):
                    $watching = va_user_watches($post_id); ?>
                    <button class="sl__btn sl__btn--watch<?php echo $watching?' active':''; ?>"
                            data-post-id="<?php echo esc_attr($post_id); ?>">
                        <?php echo $watching ? 'âť¤ď¸Ź KedvencekbĹ‘l eltĂˇvolĂ­tĂˇs' : 'đź¤Ť MentĂ©s kedvencekbe'; ?>
                    </button>
                <?php endif; ?>
            </div>

            <!-- FeladĂł tĂ¶bbi hirdetĂ©se -->
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
                <div class="sl__card-title">FeladĂł tovĂˇbbi hirdetĂ©sei</div>
                <?php while ( $other->have_posts() ): $other->the_post();
                    $p_id    = get_the_ID();
                    $p_price = get_post_meta($p_id,'va_price',true);
                    $p_type  = get_post_meta($p_id,'va_price_type',true);
                ?>
                    <a href="<?php the_permalink(); ?>" class="sl__more-item">
                        <?php if ( has_post_thumbnail() ):
                            echo get_the_post_thumbnail(null,[54,54],['class'=>'sl__more-img']);
                        else: ?>
                            <div class="sl__more-img sl__more-img--empty">đźŽŻ</div>
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
    // GalĂ©ria
    document.querySelectorAll('.sl__thumb').forEach(function(t){
        t.addEventListener('click',function(){
            var mi = document.getElementById('sl-main-img');
            if(mi) mi.src = this.dataset.src;
            document.querySelectorAll('.sl__thumb').forEach(function(x){ x.classList.remove('sl__thumb--active'); });
            this.classList.add('sl__thumb--active');
        });
    });
    // TelefonszĂˇm
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
                    b.textContent = b.classList.contains('active') ? 'âť¤ď¸Ź KedvencekbĹ‘l eltĂˇvolĂ­tĂˇs' : 'đź¤Ť MentĂ©s kedvencekbe';
                }
            });
        });
    });
})();
</script>

<?php get_footer(); ?>
