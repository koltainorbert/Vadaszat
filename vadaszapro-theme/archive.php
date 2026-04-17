<?php
/**
 * archive.php – CPT archívum (va_listing, va_auction)
 */
get_header();

$queried = get_queried_object();
$post_type = get_query_var('post_type');
if ( is_array($post_type) ) $post_type = reset($post_type);

if ( $post_type === 'va_auction' ) {
    $archive_title = '🔨 Aukciók';
} else {
    $archive_title = '🎯 Hirdetések';
}
?>
<div class="va-wrap">

    <div class="va-archive-header">
        <h1 class="va-archive-header__title"><?php echo esc_html($archive_title); ?></h1>
        <span class="va-archive-header__count"><?php echo esc_html($wp_query->found_posts); ?> találat</span>
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
            <div class="va-empty__text">Nincs megjeleníthető hirdetés.</div>
        </div>
    <?php endif; ?>

</div>
<?php get_footer(); ?>
