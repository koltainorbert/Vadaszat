<?php
/**
 * page.php – Normál WordPress oldal template
 * (regisztráció, bejelentkezés, dashboard, hirdetés feladás stb.)
 */
get_header(); ?>

<div class="va-wrap">
    <?php if (have_posts()): while (have_posts()): the_post(); ?>
        <?php
        $content = trim( (string) get_the_content() );

        // Fail-safe: ha a kereső oldal tartalma üres, akkor is jelenjen meg a kereső modul.
        if ( is_page( 'va-hirdetes-kereses' ) && $content === '' ) {
            echo do_shortcode( '[va_listing_search]' );
        } else {
            the_content();
        }
        ?>
    <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>
