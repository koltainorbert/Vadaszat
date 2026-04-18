<?php
/**
 * taxonomy-va_category.php – Vadász kategória archívum
 */
get_header();

$term = get_queried_object();
$icon = function_exists('va_category_icon') ? va_category_icon( (int) $term->term_id ) : '🎯';
?>
<div class="va-wrap">

    <div class="va-archive-header">
        <h1 class="va-archive-header__title"><?php echo esc_html($icon . ' ' . $term->name); ?></h1>
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

</div>
<?php get_footer(); ?>
