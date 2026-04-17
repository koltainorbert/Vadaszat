<?php
/**
 * single-va_listing.php – Hirdetés részletes oldal
 */
get_header();

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
            <?php $other = new WP_Query(['post_type' => 'va_listing', 'post_status' => 'publish', 'author' => $author->ID, 'posts_per_page' => 3, 'post__not_in' => [$post_id]]);
            if ($other->have_posts()): ?>
            <div class="va-sidebar__widget" style="margin-top:16px;">
                <div class="va-sidebar__widget-title">Feladó további hirdetései</div>
                <?php while ($other->have_posts()): $other->the_post(); ?>
                    <a href="<?php the_permalink(); ?>" style="display:block;padding:8px 0;color:rgba(255,255,255,0.7);text-decoration:none;font-size:13px;border-bottom:1px solid rgba(255,255,255,0.06);">
                        <?php the_title(); ?>
                    </a>
                <?php endwhile; wp_reset_postdata(); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
