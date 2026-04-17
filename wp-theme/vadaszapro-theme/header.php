<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="va-site-wrap">

    <!-- ═══ Fejléc teteje: reklám ═══════════════════════ -->
    <?php if ( class_exists('VA_Ad_Zones') ) VA_Ad_Zones::render('header_top'); ?>

    <!-- ═══ Header ══════════════════════════════════════ -->
    <header class="va-header">
        <div class="va-header__inner">
            <!-- Logo -->
            <a href="<?php echo esc_url( home_url('/') ); ?>" class="va-logo">
                <?php if ( has_custom_logo() ):
                    the_custom_logo();
                else: ?>
                    <span class="va-logo__text">Vadász<span>Apró</span></span>
                <?php endif; ?>
            </a>

            <!-- Navigáció -->
            <nav class="va-nav" id="va-main-nav">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container'      => false,
                    'items_wrap'     => '%3$s',
                    'fallback_cb'    => function() {
                        $search_page  = get_page_by_path('va-hirdetes-kereses');
                        $auction_page = get_page_by_path('va-aukciok');
                        echo '<a href="' . esc_url( $search_page  ? get_permalink($search_page)  : home_url('/hirdetes') ) . '" class="va-nav__item">Hirdetések</a>';
                        echo '<a href="' . esc_url( $auction_page ? get_permalink($auction_page) : home_url('/aukcio') ) . '" class="va-nav__item va-nav__item--accent">🔨 Aukciók</a>';
                        echo '<a href="' . esc_url( home_url('/kategoria') ) . '" class="va-nav__item">Kategóriák</a>';
                        echo '<a href="' . esc_url( home_url('/kapcsolat') ) . '" class="va-nav__item">Kapcsolat</a>';
                    },
                    'link_before'    => '',
                    'link_after'     => '',
                    'walker'         => class_exists('VA_Nav_Walker') ? new VA_Nav_Walker() : null,
                ]);
                ?>
            </nav>

            <!-- Jobb oldal -->
            <div class="va-header__right">
                <?php if ( is_user_logged_in() ):
                    $user        = wp_get_current_user();
                    $dashboard   = get_page_by_path('va-fiok');
                    $submit_page = get_page_by_path('va-hirdetes-feladas');
                ?>
                    <?php if ( $submit_page ): ?>
                        <a href="<?php echo esc_url( get_permalink($submit_page) ); ?>" class="va-header__submit-btn">+ Hirdetés feladása</a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url( $dashboard ? get_permalink($dashboard) : home_url() ); ?>" class="va-header__user">
                        👤 <?php echo esc_html( $user->display_name ); ?>
                    </a>
                <?php else:
                    $login_page    = get_page_by_path('va-bejelentkezes');
                    $register_page = get_page_by_path('va-regisztracio');
                ?>
                    <?php if ($login_page): ?>
                        <a href="<?php echo esc_url( get_permalink($login_page) ); ?>" class="va-header__user">Bejelentkezés</a>
                    <?php endif; ?>
                    <?php if ($register_page): ?>
                        <a href="<?php echo esc_url( get_permalink($register_page) ); ?>" class="va-header__submit-btn">Regisztráció</a>
                    <?php endif; ?>
                <?php endif; ?>
                <button class="va-hamburger" id="va-hamburger" aria-label="Menü">☰</button>
            </div>
        </div>
    </header>

    <!-- Fejléc alatti reklám -->
    <?php if ( class_exists('VA_Ad_Zones') ) VA_Ad_Zones::render('header_bottom'); ?>

    <!-- Kategória gyorsmenü (csak főoldalon + archívumban) -->
    <?php if ( is_front_page() || is_post_type_archive(['va_listing','va_auction']) || is_tax(['va_category','va_county']) ): ?>
    <div class="va-cat-bar">
        <div class="va-cat-bar__inner">
            <?php $top_cats = get_terms(['taxonomy' => 'va_category', 'parent' => 0, 'hide_empty' => false, 'number' => 10]);
            foreach ($top_cats as $cat):
                $icon = va_category_icon($cat->term_id);
            ?>
            <a href="<?php echo esc_url( get_term_link($cat) ); ?>" class="va-cat-item">
                <span class="va-cat-item__icon"><?php echo $icon; ?></span>
                <?php echo esc_html($cat->name); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tartalom wrapper -->
    <main class="va-container">
        <div class="va-content-layout">

            <!-- Bal oldalsáv -->
            <aside class="va-sidebar va-sidebar--left">
                <?php if ( class_exists('VA_Ad_Zones') ) VA_Ad_Zones::render('sidebar_left'); ?>
            </aside>

            <!-- Fő tartalom (ide jön a content) -->
            <div class="va-main-content">
                <?php if ( is_singular() ) va_breadcrumb(); ?>
                <?php if ( class_exists('VA_Ad_Zones') ) VA_Ad_Zones::render('content_top'); ?>
