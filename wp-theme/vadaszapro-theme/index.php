<?php
/**
 * index.php – Főoldal / fallback template
 */
get_header(); ?>

<!-- HERO szekció (csak főoldalon) -->
<?php if ( is_front_page() ): ?>
<div class="va-hero" style="margin: -28px -20px 28px;">
    <div class="va-hero__title">Magyarország <span>vadászati</span> apróhirdetési oldala</div>
    <div class="va-hero__sub">Fegyverek, lőszerek, optikák, felszerelések – vásárolj és adj el!</div>

    <div class="va-hero__search">
        <input type="text" placeholder="Mit keresel? (pl. Beretta, 12/70, Blaser...)" id="va-hero-search">
        <button onclick="window.location='<?php echo esc_url(home_url('/va-hirdetes-kereses')); ?>?s='+document.getElementById('va-hero-search').value">Keresés</button>
    </div>

    <div class="va-hero__stats">
        <?php
        $stats = [
            wp_count_posts('va_listing')->publish => 'Aktív hirdetés',
            wp_count_posts('va_auction')->publish  => 'Futó aukció',
            count_users()['total_users']            => 'Regisztrált felhasználó',
        ];
        foreach ($stats as $num => $label): ?>
        <div class="va-hero__stat">
            <span class="va-hero__stat-num"><?php echo number_format($num, 0, ',', ' '); ?></span>
            <span class="va-hero__stat-label"><?php echo esc_html($label); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Legújabb hirdetések -->
<?php if ( is_front_page() ): ?>
<div style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <h2 style="font-size:18px;font-weight:800;">🆕 Legújabb hirdetések</h2>
        <?php $search = get_page_by_path('va-hirdetes-kereses'); ?>
        <?php if ($search): ?>
            <a href="<?php echo esc_url(get_permalink($search)); ?>" style="color:#ff0000;font-size:13px;text-decoration:none;">Összes →</a>
        <?php endif; ?>
    </div>

    <?php $latest = new WP_Query(['post_type' => 'va_listing', 'post_status' => 'publish', 'posts_per_page' => 8, 'orderby' => 'date', 'order' => 'DESC']); ?>
    <?php if ($latest->have_posts()): ?>
        <div class="va-grid">
            <?php while ($latest->have_posts()): $latest->the_post();
                if (class_exists('VA_Shortcodes')) va_template('listing/card', ['post' => get_post()]);
            endwhile; wp_reset_postdata(); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Kiemelt hirdetések -->
<?php $featured = new WP_Query(['post_type' => 'va_listing', 'post_status' => 'publish', 'posts_per_page' => 4, 'meta_key' => 'va_featured', 'meta_value' => '1']);
if ($featured->have_posts()): ?>
<div style="margin-bottom:24px;">
    <h2 style="font-size:18px;font-weight:800;margin-bottom:16px;">⭐ Kiemelt hirdetések</h2>
    <div class="va-grid">
        <?php while ($featured->have_posts()): $featured->the_post();
            va_template('listing/card', ['post' => get_post()]);
        endwhile; wp_reset_postdata(); ?>
    </div>
</div>
<?php endif; ?>

<!-- Futó aukciók -->
<?php $auctions = new WP_Query(['post_type' => 'va_auction', 'post_status' => 'publish', 'posts_per_page' => 4, 'orderby' => 'date', 'order' => 'DESC']);
if ($auctions->have_posts()): ?>
<div style="margin-bottom:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <h2 style="font-size:18px;font-weight:800;">🔨 Futó aukciók</h2>
        <?php $ap = get_page_by_path('va-aukciok'); ?>
        <?php if ($ap): ?><a href="<?php echo esc_url(get_permalink($ap)); ?>" style="color:#ff0000;font-size:13px;text-decoration:none;">Összes →</a><?php endif; ?>
    </div>
    <div class="va-grid">
        <?php while ($auctions->have_posts()): $auctions->the_post();
            va_template('listing/card', ['post' => get_post()]);
        endwhile; wp_reset_postdata(); ?>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
    <!-- Archívum / single / page tartalom -->
    <?php if ( have_posts() ): while ( have_posts() ): the_post(); ?>
        <div class="va-wrap">
            <?php the_content(); ?>
        </div>
    <?php endwhile; endif; ?>
<?php endif; ?>

<?php get_footer(); ?>
