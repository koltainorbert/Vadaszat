<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="va-site-wrap<?php echo ! is_front_page() ? ' va-site-wrap--inner' : ''; ?>">

    <!-- ═══ Header ══════════════════════════════════════ -->
    <header class="va-header">
        <div class="va-header__inner">
            <!-- Logo -->
            <a href="<?php echo esc_url( home_url('/') ); ?>" class="va-logo">
                <?php if ( has_custom_logo() ):
                    the_custom_logo();
                else: ?>
                    <span class="va-logo__icon" aria-hidden="true">&#127993;</span>
                    <span class="va-logo__text">Vad&aacute;sz<span>Apr&oacute;</span><em class="va-logo__dot">vadaszapro.net</em></span>
                <?php endif; ?>
            </a>

            <!-- Navigáció -->
            <nav class="va-nav" id="va-main-nav">
                <?php
                $nav_items = apply_filters('va_nav_items', [
                    ['url' => home_url('/hirdetes'),  'label' => 'Hirdetések', 'class' => ''],
                    ['url' => home_url('/aukcio'),    'label' => '🔨 Aukciók', 'class' => 'va-nav__item--accent'],
                    ['url' => home_url('/kategoria'), 'label' => 'Kategóriák', 'class' => ''],
                    ['url' => home_url('/kapcsolat'), 'label' => 'Kapcsolat',  'class' => ''],
                ]);
                foreach ( $nav_items as $item ):
                    $cls = 'va-nav__item' . ( $item['class'] ? ' ' . $item['class'] : '' );
                ?>
                    <a href="<?php echo esc_url($item['url']); ?>" class="<?php echo esc_attr($cls); ?>"><?php echo esc_html($item['label']); ?></a>
                <?php endforeach; ?>
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
                        <a href="<?php echo esc_url( get_permalink($login_page) ); ?>" class="va-header__user-login">Bejelentkezés</a>
                    <?php endif; ?>
                    <?php if ($register_page): ?>
                        <a href="<?php echo esc_url( get_permalink($register_page) ); ?>" class="va-header__submit-btn">Regisztráció</a>
                    <?php endif; ?>
                <?php endif; ?>
                <button class="va-hamburger" id="va-hamburger" aria-label="Men&uuml;">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- ═══ VIDEO HERO (csak főoldalon) ════════════════════ -->
    <?php if ( is_front_page() ):
        $hero_video = get_option('va_hero_video_url') ?: content_url('uploads/2026/04/521380_Gun_Woman_1920x1080.mp4');
        $submit_page = get_page_by_path('va-hirdetes-feladas');
        $search_page = get_page_by_path('va-hirdetes-kereses');
        $listing_count = wp_count_posts('va_listing')->publish;
        $auction_count = wp_count_posts('va_auction')->publish;
        $user_count = count_users()['total_users'];
    ?>
    <div class="vh">
        <?php if ( $hero_video ): ?>
        <video class="vh__video" autoplay muted loop playsinline preload="auto"
               aria-hidden="true">
            <source src="<?php echo esc_url($hero_video); ?>" type="video/mp4">
        </video>
        <?php endif; ?>

        <div class="vh__overlay"></div>

        <div class="vh__content">
            <div class="vh__badge">&#127993; Magyarorsz&aacute;g #1 vad&aacute;sz apróhirdetése</div>
            <h2 class="vh__title">
                Vad&aacute;sz<span>Apró</span><br>
                &mdash; ahol a vad&aacute;szat él
            </h2>
            <p class="vh__sub">
                Fegyverek, felszerelések, vadászterületek egy helyen.
                Adj fel hirdetést percek alatt, találj vevőt azonnal.
            </p>
            <div class="vh__actions">
                <?php if ( $submit_page ): ?>
                <a href="<?php echo esc_url( get_permalink($submit_page) ); ?>" class="vh__btn vh__btn--primary">
                    &#43; Hirdet&eacute;s felad&aacute;sa
                </a>
                <?php endif; ?>
                <a href="<?php echo esc_url( $search_page ? get_permalink($search_page) : home_url('/hirdetes') ); ?>" class="vh__btn vh__btn--ghost">
                    Hirdet&eacute;sek b&ouml;ng&eacute;sz&eacute;se &rarr;
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="vh__stats">
            <div class="vh__stat">
                <span class="vh__stat-num"><?php echo esc_html( number_format_i18n($listing_count) ); ?>&#43;</span>
                <span class="vh__stat-label">aktív hirdetés</span>
            </div>
            <div class="vh__stat">
                <span class="vh__stat-num"><?php echo esc_html( number_format_i18n($auction_count) ); ?></span>
                <span class="vh__stat-label">aukció</span>
            </div>
            <div class="vh__stat">
                <span class="vh__stat-num"><?php echo esc_html( number_format_i18n($user_count) ); ?></span>
                <span class="vh__stat-label">regisztrált vadász</span>
            </div>
        </div>

        <!-- Scroll jel -->
        <div class="vh__scroll" aria-hidden="true">
            <div class="vh__scroll-line"></div>
            <div class="vh__scroll-dot"></div>
        </div>
    </div>
    <?php endif; ?>

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
                <span><?php echo esc_html($cat->name); ?></span>
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
