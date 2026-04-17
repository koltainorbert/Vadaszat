<?php
/**
 * page.php – Normál WordPress oldal template
 * (regisztráció, bejelentkezés, dashboard, hirdetés feladás stb.)
 */
get_header(); ?>

<div class="va-wrap">
    <?php if (have_posts()): while (have_posts()): the_post(); ?>
        <?php the_content(); ?>
    <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>
