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
    <?php $auctions_enabled = function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : true; ?>
    <?php
    $brand_name = trim( (string) get_option( 'va_site_name', 'VadászApró' ) );
    if ( $brand_name === '' ) {
        $brand_name = 'VadászApró';
    }
    $brand_icon  = get_option( 'va_brand_icon_url', '' );
    $header_logo = get_option( 'va_header_logo_url', '' );
    $hero_logo   = get_option( 'va_hero_logo_url', '' );
    $header_logo_h = max( 20, min( 120, absint( get_option( 'va_header_logo_height', 36 ) ) ) );
    $hero_logo_h   = max( 30, min( 260, absint( get_option( 'va_hero_logo_height', 72 ) ) ) );
    if ( $hero_logo === '' ) {
        $hero_logo = $header_logo;
    }
    ?>

    <!-- ═══ Header ══════════════════════════════════════ -->
    <header class="va-header">
        <div class="va-header__inner">
            <!-- Logo -->
            <a href="<?php echo esc_url( home_url('/') ); ?>" class="va-logo">
                <?php if ( ! empty( $header_logo ) ): ?>
                    <img src="<?php echo esc_url( $header_logo ); ?>" class="va-logo__img va-logo__img--header" style="height:<?php echo esc_attr( $header_logo_h ); ?>px;" alt="<?php echo esc_attr( $brand_name ); ?>" loading="eager" decoding="async">
                <?php elseif ( ! empty( $brand_icon ) ): ?>
                    <img src="<?php echo esc_url( $brand_icon ); ?>" class="va-logo__img va-logo__img--icon" style="height:<?php echo esc_attr( $header_logo_h ); ?>px;" alt="<?php echo esc_attr( $brand_name ); ?>" loading="eager" decoding="async">
                <?php else: ?>
                    <span class="va-logo__icon">🦌</span>
                <?php endif; ?>
                <span class="va-logo__text"><?php echo esc_html( $brand_name ); ?></span>
            </a>

            <!-- Navigáció -->
            <nav class="va-nav" id="va-main-nav">
                <?php
                $nav_items = apply_filters('va_nav_items', [
                    ['url' => home_url('/hirdetes'),  'label' => 'Hirdetések', 'class' => ''],
                    ['url' => home_url('/kategoria'), 'label' => 'Kategóriák', 'class' => ''],
                    ['url' => home_url('/kapcsolat'), 'label' => 'Kapcsolat',  'class' => ''],
                ]);
                if ( $auctions_enabled ) {
                    array_splice( $nav_items, 1, 0, [[
                        'url'   => home_url('/aukcio'),
                        'label' => '🔨 Aukciók',
                        'class' => 'va-nav__item--accent',
                    ]] );
                }
                foreach ( $nav_items as $item ):
                    $cls = 'va-nav__item' . ( $item['class'] ? ' ' . $item['class'] : '' );
                ?>
                    <a href="<?php echo esc_url($item['url']); ?>" class="<?php echo esc_attr($cls); ?>"><?php echo esc_html($item['label']); ?></a>
                <?php endforeach; ?>
            </nav>

            <!-- Kereső -->
            <form class="va-header__search" id="va-live-search-form" role="search" action="<?php echo esc_url( home_url('/va-hirdetes-kereses') ); ?>" method="get" autocomplete="off">
                <input class="va-header__search-input" id="va-live-search-input" type="text" name="s" placeholder="keresés…" autocomplete="off" value="<?php echo esc_attr( get_search_query() ); ?>">
                <button class="va-header__search-btn" type="submit" aria-label="Keresés"></button>
                <div class="va-search-dropdown" id="va-search-dropdown" hidden></div>
            </form>
            <script>
            (function(){
                var ajaxUrl = '<?php echo esc_url( admin_url('admin-ajax.php') ); ?>';
                var auctionsEnabled = <?php echo $auctions_enabled ? 'true' : 'false'; ?>;
                var input   = document.getElementById('va-live-search-input');
                var dropdown= document.getElementById('va-search-dropdown');
                var timer;

                function render(items) {
                    if (!items.length) { dropdown.hidden = true; return; }
                    dropdown.innerHTML = items.map(function(r){
                        return '<a class="va-sd__item" href="'+r.url+'">'
                            + (r.thumb ? '<img class="va-sd__thumb" src="'+r.thumb+'" alt="" loading="lazy">' : '<span class="va-sd__no-img"></span>')
                            + '<span class="va-sd__info"><span class="va-sd__title">'+r.title+'</span>'
                            + (r.price ? '<span class="va-sd__price">'+r.price+'</span>' : '')
                            + '</span>'
                            + '<span class="va-sd__badge va-sd__badge--'+r.type+'">'+(r.type==='va_auction'?'Aukció':r.type==='category'?'Kategória':r.type==='user'?'Felhasználó':'Hirdetés')+'</span>'
                            + '</a>';
                    }).join('') + '<a class="va-sd__all" href="#" id="va-sd-all-link">Összes találat →</a>';
                    // "Összes találat" link prioritás: kategória → aukció → user → kulcsszó
                    var catItem     = items.find(function(r){ return r.type === 'category'; });
                    var auctionItem = items.find(function(r){ return r.type === 'va_auction'; });
                    var userItem    = items.find(function(r){ return r.type === 'user'; });
                    var allLink     = dropdown.querySelector('#va-sd-all-link');
                    var baseUrl     = '<?php echo esc_url( home_url('/va-hirdetes-kereses') ); ?>';
                    if (catItem) {
                        allLink.href = catItem.url; // ?cat=ID → kategória összes hirdetése
                    } else if (auctionsEnabled && auctionItem) {
                        allLink.href = baseUrl + '?post_type=va_auction&s=' + encodeURIComponent(input.value); // aukciók listája
                    } else if (userItem) {
                        allLink.href = baseUrl + '?user_search=1&q=' + encodeURIComponent(input.value); // összes felhasználó
                    } else {
                        allLink.href = baseUrl + '?s=' + encodeURIComponent(input.value);
                    }
                    dropdown.hidden = false;
                }

                input.addEventListener('input', function(){
                    clearTimeout(timer);
                    var q = this.value.trim();
                    if (q.length < 2) { dropdown.hidden = true; return; }
                    timer = setTimeout(function(){
                        var fd = new FormData();
                        fd.append('action', 'va_live_search');
                        fd.append('q', q);
                        fetch(ajaxUrl, { method:'POST', body:fd })
                            .then(function(r){ return r.json(); })
                            .then(function(d){ if(d.success) render(d.data); });
                    }, 220);
                });

                document.addEventListener('click', function(e){
                    if (!document.getElementById('va-live-search-form').contains(e.target)) {
                        dropdown.hidden = true;
                    }
                });
            })();
            </script>

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
        $hero_video = get_option( 'va_home_hero_video_url', content_url( 'uploads/2026/04/521380_Gun_Woman_1920x1080.mp4' ) );
        $submit_page = get_page_by_path('va-hirdetes-feladas');
        $search_page = get_page_by_path('va-hirdetes-kereses');
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
            <?php if ( ! empty( $hero_logo ) ): ?>
                <img src="<?php echo esc_url( $hero_logo ); ?>" class="vh__logo" style="height:<?php echo esc_attr( $hero_logo_h ); ?>px;" alt="<?php echo esc_attr( $brand_name ); ?>" loading="eager" decoding="async">
            <?php endif; ?>
            <div class="vh__badge"><span class="vcp-hero__badge-dot"></span><?php echo $auctions_enabled ? 'Magyarorsz&aacute;g els&#337; vad&aacute;szati aukci&oacute;s hirdet&#337;oldala' : 'Magyarorsz&aacute;g els&#337; vad&aacute;szati hirdet&#337;oldala'; ?></div>
            <h2 class="vh__title">
                Vad&aacute;sz<span>Baz&aacute;r</span><br>
                <?php echo $auctions_enabled ? '&eacute;s Aukci&oacute;' : '&eacute;s Apr&oacute;hirdet&eacute;s'; ?>
            </h2>
            <p class="vh__sub">
                <?php echo $auctions_enabled ? 'Magyarorsz&aacute;g els&#337; vad&aacute;szati aukci&oacute;s hirdet&#337;oldala' : 'Magyarorsz&aacute;g els&#337; vad&aacute;szati hirdet&#337;oldala'; ?>
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

        <!-- Scroll jel -->
        <div class="vh__scroll" aria-hidden="true">
            <div class="vh__scroll-line"></div>
            <div class="vh__scroll-dot"></div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Kategória gyorsmenü (csak főoldalon + archívumban) -->
    <?php
    $archive_types = [ 'va_listing' ];
    if ( $auctions_enabled ) {
        $archive_types[] = 'va_auction';
    }
    ?>
    <?php if ( is_post_type_archive($archive_types) || is_tax(['va_category','va_county']) ): ?>
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

            <!-- Fő tartalom (ide jön a content) -->
            <div class="va-main-content">
                <?php if ( is_singular() ) va_breadcrumb(); ?>
                <?php if ( class_exists('VA_Ad_Zones') ) VA_Ad_Zones::render('content_top'); ?>
