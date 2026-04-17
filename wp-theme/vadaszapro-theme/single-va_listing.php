<?php
/**
 * single-va_listing.php Ă˘â€šÂ¬Ă˘â‚¬Ĺ› HirdetĂ„â€šs rĂ„â€šszletes oldal (v2 Ă˘â€šÂ¬Ă˘â‚¬Ĺ› modern 2026)
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

// KĂ„â€špek gyĂ„Ä…jtĂ„â€šse
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
    <div class="sl__featured-bar"> Kiemelt hirdetĂ„â€šs</div>
    <?php endif; ?>

    <div class="sl__layout">

        <!-- Ă˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬Ë BAL OSZLOP Ă˘â€šÂ¬Ă˘â‚¬Ĺ› galĂ„â€šria + leĂ„â€šrĂ„â€šĂ‹â€ˇs Ă˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬Ë -->
        <div class="sl__left">

            <!-- GalĂ„â€šria -->
            <div class="sl__gallery">
                <div class="sl__main-wrap">
                    <?php if ( ! empty($attachment_ids) ):
                        $main_url = wp_get_attachment_image_url( $attachment_ids[0], 'large' );
                    ?>
                        <img src="<?php echo esc_url($main_url); ?>"
                             id="sl-main-img" class="sl__main-img"
                             alt="<?php the_title_attribute(); ?>">
                    <?php else: ?>
                        <div class="sl__main-img sl__main-empty">Ă„â€ÄąĹźÄąËťÄąÂ»</div>
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

            <!-- LeĂ„â€šrĂ„â€šĂ‹â€ˇs -->
            <?php if ( get_the_content() ): ?>
            <div class="sl__card sl__desc">
                <div class="sl__card-title">LeĂ„â€šrĂ„â€šĂ‹â€ˇs</div>
                <div class="sl__desc-body"><?php the_content(); ?></div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Ă˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬Ë JOBB OSZLOP Ă˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬ËĂ˘â‚¬Ë -->
        <div class="sl__right">

            <!-- KategĂ„â€šÄąâ€šria + cĂ„â€šm + Ă„â€šĂ‹â€ˇr -->
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
                        <span>Ă„â€ÄąĹźĂ˘â‚¬Ĺ›ÄąÂ¤ <?php echo esc_html($county[0]->name); ?></span>
                    <?php endif; ?>
                    <?php if ( $location ): ?>
                        <span>Ă„â€ÄąĹźÄąÄ…Ă˘â€žË <?php echo esc_html($location); ?></span>
                    <?php endif; ?>
                    <?php if ( $condition && !is_wp_error($condition) ): ?>
                        <span>Ă„â€ÄąĹźĂ˘â‚¬Ĺ›ÄąÂ  <?php echo esc_html($condition[0]->name); ?></span>
                    <?php endif; ?>
                    <span>Ă„â€ÄąĹźĂ˘â‚¬Â <?php echo esc_html($views); ?> megtekintĂ„â€šs</span>
                    <span>Ă„â€ÄąĹźĂ˘â‚¬â€ťĂ˘â‚¬Ĺ› <?php echo esc_html(get_the_date('Y. m. d.')); ?></span>
                    <?php if ( $expires ): ?>
                        <span class="<?php echo strtotime($expires) < time() ? 'sl__expired' : ''; ?>">
                            ÄąÄ… LejĂ„â€šĂ‹â€ˇr: <?php echo esc_html($expires); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ParamĂ„â€šterek -->
            <?php if ( $brand || $model || $caliber || $year || $license_req === '1' ): ?>
            <div class="sl__card sl__params">
                <div class="sl__card-title">RĂ„â€šszletek</div>
                <div class="sl__params-grid">
                    <?php if ($brand): ?>
                        <div class="sl__param-row">
                            <span class="sl__param-label">MĂ„â€šĂ‹â€ˇrka</span>
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
                            <span class="sl__param-label">GyĂ„â€šĂ‹â€ˇrtĂ„â€šĂ‹â€ˇsi Ă„â€šv</span>
                            <span class="sl__param-val"><?php echo esc_html($year); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ( $license_req === '1' ): ?>
                    <div class="sl__license-warn">Fegyverengedely szukseges</div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- FeladĂ„â€šÄąâ€š + kapcsolat -->
            <div class="sl__card sl__contact">
                <div class="sl__card-title">FeladĂ„â€šÄąâ€š</div>
                <div class="sl__seller">
                    <div class="sl__seller-av">
                        <?php echo strtoupper( substr( $author ? $author->display_name : 'X', 0, 1 ) ); ?>
                    </div>
                    <div>
                        <div class="sl__seller-name"><?php echo esc_html($author ? $author->display_name : 'Ismeretlen'); ?></div>
                        <?php if ($author): ?>
                        <div class="sl__seller-since">Tag <?php echo esc_html(date('Y', strtotime($author->user_registered))); ?> ota</div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                $author_phone = $author ? get_user_meta($author->ID, 'va_phone', true) : '';
                $show_phone   = $phone ?: $author_phone;
                if ( $show_phone ): ?>
                    <button class="sl__btn sl__btn--phone" data-phone="<?php echo esc_attr($show_phone); ?>">
                        Telefonszam megjelenitese
                    </button>
                    <a href="tel:<?php echo esc_attr(preg_replace('/[^+0-9]/','',$show_phone)); ?>"
                       class="sl__phone-reveal" id="sl-phone" style="display:none;">
                        <?php echo esc_html($show_phone); ?>
                    </a>
                <?php endif; ?>

                <?php if ( $email_show === '1' && $author ): ?>
                    <a href="mailto:<?php echo esc_attr($author->user_email); ?>" class="sl__btn sl__btn--email">
                        Äąâ€şĂ˘â‚¬Â°Ă„ĹąÄąÄ… Ă„â€šÄąâ€şzenet kĂ„â€šĂ„ËťldĂ„â€šse
                    </a>
                <?php endif; ?>

                <?php if ( is_user_logged_in() ):
                    $watching = va_user_watches($post_id); ?>
                    <button class="sl__btn sl__btn--watch<?php echo $watching?' active':''; ?>"
                            data-post-id="<?php echo esc_attr($post_id); ?>">
                        <?php echo $watching ? ('Kedvencekbol eltavolitas') : ('Mentes kedvencekbe'); ?>
                    </button>
                <?php endif; ?>
            </div>

            <!-- FeladĂ„â€šÄąâ€š tĂ„â€šbbi hirdetĂ„â€šse -->
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
                <div class="sl__card-title">FeladĂ„â€šÄąâ€š tovĂ„â€šĂ‹â€ˇbbi hirdetĂ„â€šsei</div>
                <?php while ( $other->have_posts() ): $other->the_post();
                    $p_id    = get_the_ID();
                    $p_price = get_post_meta($p_id,'va_price',true);
                    $p_type  = get_post_meta($p_id,'va_price_type',true);
                ?>
                    <a href="<?php the_permalink(); ?>" class="sl__more-item">
                        <?php if ( has_post_thumbnail() ):
                            echo get_the_post_thumbnail(null,[54,54],['class'=>'sl__more-img']);
                        else: ?>
                            <div class="sl__more-img sl__more-img--empty">Ă„â€ÄąĹźÄąËťÄąÂ»</div>
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
    // GalĂ„â€šria
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
                    b.textContent = b.classList.contains('active') ? 'ÄąÄ„Ă„ĹąÄąÄ… KedvencekbĂ„Ä…Ă˘â‚¬Âl eltĂ„â€šĂ‹â€ˇvolĂ„â€štĂ„â€šĂ‹â€ˇs' : 'Ă„â€ÄąĹźÄąÂ¤ MentĂ„â€šs kedvencekbe';
                }
            });
        });
    });
})();
</script>

<?php get_footer(); ?>
