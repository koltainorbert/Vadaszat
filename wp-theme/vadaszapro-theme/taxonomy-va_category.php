<?php
/**
 * taxonomy-va_category.php – Vadász kategória archívum
 */
get_header();

$term = get_queried_object();
$icon = function_exists('va_category_icon') ? va_category_icon( (int) $term->term_id ) : '🎯';
$categories_video = get_option( 'va_category_video_url', content_url( 'uploads/2026/04/1434963_Hunter_Autumn_1920x1080.mp4' ) );
?>
<div class="va-wrap">

    <div class="va-archive-header">
        <h1 class="va-archive-header__title"><?php echo wp_kses_post( $icon ) . ' ' . esc_html( $term->name ); ?></h1>
        <?php if ( $term->description ): ?>
            <p class="va-archive-header__desc"><?php echo esc_html($term->description); ?></p>
        <?php endif; ?>
        <span class="va-archive-header__count"><?php echo esc_html($wp_query->found_posts); ?> hirdetés</span>
    </div>

    <?php if ( have_posts() ): ?>
        <div class="va-grid">
            <?php while ( have_posts() ): the_post(); ?>
                <?php va_template( 'listing/card', [ 'post' => get_post() ] ); ?>
            <?php endwhile; ?>
        </div>

        <div class="va-pagination">
            <?php echo paginate_links([
                'prev_text' => '← Előző',
                'next_text' => 'Következő →',
            ]); ?>
        </div>

    <?php else: ?>
        <div class="va-empty">
            <div class="va-empty__icon">🔍</div>
            <div class="va-empty__text">Ebben a kategóriában nincs hirdetés.</div>
        </div>
    <?php endif; ?>

    <div class="vcp-video" style="margin-top:26px;">
        <video class="vcp-video__media" autoplay muted loop playsinline preload="auto" aria-hidden="true">
            <source src="<?php echo esc_url( $categories_video ); ?>" type="video/mp4">
        </video>
        <div class="vcp-video__overlay"></div>
        <div class="vcp-video__content">
            <span class="vcp-video__eyebrow">Kategória ajánló</span>
            <h2 class="vcp-video__title"><?php echo esc_html( $term->name ); ?></h2>
            <p class="vcp-video__lead">A kiválasztott kategóriában böngészel, görgess tovább a friss ajánlatokért.</p>
        </div>
    </div>

</div>
<?php get_footer(); ?>
