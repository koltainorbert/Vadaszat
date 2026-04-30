<?php
/**
 * VadászApró Theme – functions.php
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* ══════════════════════════════════════════════════════
 * ⚡ SEBESSÉG – WordPress bloat eltávolítás
 * ══════════════════════════════════════════════════════ */
add_action( 'init', function () {
    // Emoji – felesleges JS+CSS minden oldalon
    remove_action( 'wp_head',             'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles',     'print_emoji_styles' );
    remove_action( 'admin_print_styles',  'print_emoji_styles' );
    remove_filter( 'the_content_feed',    'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss',    'wp_staticize_emoji' );
    remove_filter( 'wp_mail',             'wp_staticize_emoji_for_email' );

    // Felesleges head meta
    remove_action( 'wp_head', 'wp_generator' );             // WP verzió elrejtése (biztonság is)
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'wp_shortlink_wp_head' );
    remove_action( 'wp_head', 'feed_links',          2 );
    remove_action( 'wp_head', 'feed_links_extra',    3 );
    remove_action( 'wp_head', 'rest_output_link_wp_head' );
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
    remove_action( 'template_redirect', 'rest_output_link_header', 11 );
} );

// jQuery migrate – nincs rá szükség
add_action( 'wp_default_scripts', function ( $scripts ) {
    if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
        $scripts->registered['jquery']->deps = array_diff(
            $scripts->registered['jquery']->deps,
            [ 'jquery-migrate' ]
        );
    }
} );

// Összes script defer – kivéve admin és inline
add_filter( 'script_loader_tag', function ( $tag, $handle ) {
    if ( is_admin() ) return $tag;
    // Inline scriptek és már defer/async tagek ne duplázódjanak
    if ( str_contains( $tag, ' defer' ) || str_contains( $tag, ' async' ) ) return $tag;
    if ( ! str_contains( $tag, ' src=' ) ) return $tag;
    return str_replace( ' src=', ' defer src=', $tag );
}, 10, 2 );

// DNS prefetch + preconnect
add_action( 'wp_head', function () {
    echo '<link rel="preconnect" href="' . esc_url( home_url() ) . '">' . "\n";
    echo '<link rel="dns-prefetch" href="//s.gravatar.com">' . "\n";
}, 1 );

function va_build_square_favicon_from_attachment( int $attachment_id, int $size ): string {
    $upload = wp_get_upload_dir();
    if ( empty( $upload['basedir'] ) || empty( $upload['baseurl'] ) ) {
        return '';
    }

    $subdir = trailingslashit( $upload['basedir'] ) . 'va-favicons';
    if ( ! file_exists( $subdir ) ) {
        wp_mkdir_p( $subdir );
    }

    // Versionalt fajlnev, hogy a regi cache-elt ikon ne maradjon bent.
    $variant = 'safe3';
    $target_file = trailingslashit( $subdir ) . $attachment_id . '-' . $size . '-' . $variant . '.png';
    $target_url  = trailingslashit( $upload['baseurl'] ) . 'va-favicons/' . $attachment_id . '-' . $size . '-' . $variant . '.png';

    if ( file_exists( $target_file ) ) {
        return $target_url;
    }

    $src = get_attached_file( $attachment_id );
    if ( ! $src || ! file_exists( $src ) ) {
        return '';
    }

    $img_info = @getimagesize( $src );
    $mime = (string) ( $img_info['mime'] ?? '' );

    if ( function_exists( 'imagecreatetruecolor' ) && $mime !== '' ) {
        $source_image = null;
        if ( $mime === 'image/png' && function_exists( 'imagecreatefrompng' ) ) {
            $source_image = @imagecreatefrompng( $src );
        } elseif ( ( $mime === 'image/jpeg' || $mime === 'image/jpg' ) && function_exists( 'imagecreatefromjpeg' ) ) {
            $source_image = @imagecreatefromjpeg( $src );
        } elseif ( $mime === 'image/gif' && function_exists( 'imagecreatefromgif' ) ) {
            $source_image = @imagecreatefromgif( $src );
        } elseif ( $mime === 'image/webp' && function_exists( 'imagecreatefromwebp' ) ) {
            $source_image = @imagecreatefromwebp( $src );
        }

        if ( $source_image ) {
            $w = (int) imagesx( $source_image );
            $h = (int) imagesy( $source_image );

            if ( $w > 0 && $h > 0 ) {
                $side = min( $w, $h );
                $x = (int) floor( ( $w - $side ) / 2 );
                $y = (int) floor( ( $h - $side ) / 2 );

                $canvas = imagecreatetruecolor( $size, $size );
                imagealphablending( $canvas, false );
                imagesavealpha( $canvas, true );
                $transparent = imagecolorallocatealpha( $canvas, 0, 0, 0, 127 );
                imagefill( $canvas, 0, 0, $transparent );

                // 14% belso margot adunk, hogy a bal felső sarok biztosan ne vagodjon.
                $inset = max( 1, (int) round( $size * 0.14 ) );
                $inner = max( 1, $size - ( 2 * $inset ) );

                imagecopyresampled(
                    $canvas,
                    $source_image,
                    $inset,
                    $inset,
                    $x,
                    $y,
                    $inner,
                    $inner,
                    $side,
                    $side
                );

                $ok = imagepng( $canvas, $target_file, 6 );
                imagedestroy( $canvas );
                imagedestroy( $source_image );

                if ( $ok ) {
                    return $target_url;
                }
            } else {
                imagedestroy( $source_image );
            }
        }
    }

    // Fallback: ha GD nem elerheto, marad a WP image editor.
    $editor = wp_get_image_editor( $src );
    if ( is_wp_error( $editor ) ) {
        return '';
    }

    $size_data = $editor->get_size();
    $w = (int) ( $size_data['width'] ?? 0 );
    $h = (int) ( $size_data['height'] ?? 0 );
    if ( $w <= 0 || $h <= 0 ) {
        return '';
    }

    $side = min( $w, $h );
    $x = (int) floor( ( $w - $side ) / 2 );
    $y = (int) floor( ( $h - $side ) / 2 );

    $cropped = $editor->crop( $x, $y, $side, $side, $size, $size );
    if ( is_wp_error( $cropped ) ) {
        return '';
    }

    $saved = $editor->save( $target_file, 'image/png' );
    if ( is_wp_error( $saved ) ) {
        return '';
    }

    return $target_url;
}

function va_get_brand_favicon_urls(): array {
    $icon_url = trim( (string) get_option( 'va_brand_icon_url', '' ) );
    if ( $icon_url === '' ) {
        return [];
    }

    $urls = [
        '32'  => esc_url( $icon_url ),
        '180' => esc_url( $icon_url ),
    ];

    $attachment_id = attachment_url_to_postid( $icon_url );
    if ( ! $attachment_id ) {
        return $urls;
    }

    $icon32  = va_build_square_favicon_from_attachment( $attachment_id, 32 );
    $icon180 = va_build_square_favicon_from_attachment( $attachment_id, 180 );

    if ( $icon32 !== '' ) {
        $urls['32'] = esc_url( $icon32 );
    }
    if ( $icon180 !== '' ) {
        $urls['180'] = esc_url( $icon180 );
    }

    return $urls;
}

// Automata favicon a fejléc ikon URL alapján
add_action( 'wp_head', function () {
    $favicons = va_get_brand_favicon_urls();
    if ( empty( $favicons ) ) {
        return;
    }

    echo '<link rel="icon" sizes="32x32" href="' . esc_url( $favicons['32'] ) . '">' . "\n";
    echo '<link rel="shortcut icon" href="' . esc_url( $favicons['32'] ) . '">' . "\n";
    echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url( $favicons['180'] ) . '">' . "\n";
}, 2 );

add_filter( 'get_site_icon_url', function( $url ) {
    if ( ! empty( $url ) ) {
        return $url;
    }
    $favicons = va_get_brand_favicon_urls();
    return ! empty( $favicons['32'] ) ? esc_url( $favicons['32'] ) : $url;
}, 10, 1 );

function va_get_preview_width(): int {
    if ( is_admin() || ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
        return 0;
    }

    $vp = isset( $_GET['va_vp'] ) ? absint( (string) $_GET['va_vp'] ) : 0;
    if ( $vp < 280 || $vp > 2560 ) {
        return 0;
    }
    return $vp;
}

add_filter( 'body_class', function( array $classes ): array {
    if ( va_get_preview_width() > 0 ) {
        $classes[] = 'va-preview-active';
    }
    return $classes;
} );

add_action( 'admin_bar_menu', function( WP_Admin_Bar $admin_bar ) {
    if ( is_admin() || ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
    $base_url = home_url( $request_uri );

    $current_vp = va_get_preview_width();

    $admin_bar->add_node( [
        'id'    => 'va-breakpoint-preview',
        'title' => 'LBreakpoint' . ( $current_vp ? ' (' . $current_vp . 'px)' : '' ),
        'href'  => '#',
    ] );

    $presets = [
        'Desktop 1440' => 1440,
        'Laptop 1280'  => 1280,
        'Tablet 1024'  => 1024,
        'Tablet 820'   => 820,
        'Mobile 480'   => 480,
        'Mobile 390'   => 390,
        'Mobile 375'   => 375,
        'Mobile 320'   => 320,
    ];

    $admin_bar->add_node( [
        'id'    => 'va-admin-menu',
        'title' => 'VA Admin',
        'href'  => admin_url( 'admin.php?page=vadaszapro' ),
    ] );

    $admin_menu_items = [
        [ 'id' => 'va-admin-dashboard',  'title' => '📊 Irányítópult',    'href' => admin_url( 'admin.php?page=vadaszapro-dashboard' ) ],
        [ 'id' => 'va-admin-general',    'title' => '⚙️ Általános',        'href' => admin_url( 'admin.php?page=vadaszapro' ) ],
        [ 'id' => 'va-admin-design',     'title' => '🎨 Design',           'href' => admin_url( 'admin.php?page=vadaszapro-design' ) ],
        [ 'id' => 'va-admin-listings',   'title' => '📋 Hirdetések',       'href' => admin_url( 'admin.php?page=vadaszapro-hirdetes' ) ],
        [ 'id' => 'va-admin-users',      'title' => '👥 Felhasználók',     'href' => admin_url( 'admin.php?page=vadaszapro-users' ) ],
        [ 'id' => 'va-admin-stats',      'title' => '📈 Statisztika',      'href' => admin_url( 'admin.php?page=vadaszapro-stats' ) ],
    ];

    foreach ( $admin_menu_items as $item ) {
        $admin_bar->add_node( [
            'id'     => $item['id'],
            'parent' => 'va-admin-menu',
            'title'  => $item['title'],
            'href'   => esc_url( $item['href'] ),
        ] );
    }

    $admin_bar->add_node( [
        'id'    => 'va-breakpoint-preview',
        'title' => 'LBreakpoint' . ( $current_vp ? ' (' . $current_vp . 'px)' : '' ),
        'href'  => '#',
    ] );

    foreach ( $presets as $label => $width ) {
        $url = add_query_arg( 'va_vp', (string) $width, $base_url );
        $admin_bar->add_node( [
            'id'     => 'va-breakpoint-preview-' . $width,
            'parent' => 'va-breakpoint-preview',
            'title'  => $label . ' px',
            'href'   => esc_url( $url ),
        ] );
    }

    $admin_bar->add_node( [
        'id'     => 'va-breakpoint-preview-custom',
        'parent' => 'va-breakpoint-preview',
        'title'  => 'Egyedi szélesség (px)…',
        'href'   => '#',
        'meta'   => [ 'class' => 'va-breakpoint-preview-custom' ],
    ] );

    $admin_bar->add_node( [
        'id'     => 'va-breakpoint-preview-off',
        'parent' => 'va-breakpoint-preview',
        'title'  => 'Preview kikapcsolása',
        'href'   => esc_url( remove_query_arg( 'va_vp', $base_url ) ),
    ] );

    // Ha a root menü a LBreakpoint lesz, ügyeljünk rá, hogy a helyes szöveg jelenjen meg
    $admin_bar->add_node( [
        'id'    => 'lbreakpoint-label',
        'title' => 'LBreakpoint',
        'href'  => '#',
    ] );
}, 100 );

add_action( 'wp_head', function() {
    $vp = va_get_preview_width();
    if ( $vp <= 0 ) {
        return;
    }

    echo '<style id="va-breakpoint-preview-style">'
        . 'body.va-preview-active{background:#0b0b0b;}'
        . 'body.va-preview-active .va-site-wrap{max-width:' . (int) $vp . 'px;margin:0 auto;box-shadow:0 0 0 1px rgba(255,255,255,.1),0 14px 40px rgba(0,0,0,.6);min-height:100vh;background:rgb(6,6,6);}'
        . 'body.va-preview-active .va-site-wrap::before{content:"Preview: ' . (int) $vp . 'px";position:fixed;right:10px;bottom:10px;z-index:99999;background:rgba(0,0,0,.85);color:#fff;border:1px solid rgba(255,255,255,.2);padding:6px 10px;border-radius:6px;font-size:12px;font-family:Segoe UI,Arial,sans-serif;}'
        . '</style>';
}, 99 );

add_action( 'wp_footer', function() {
    if ( is_admin() || ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $vp = va_get_preview_width();
    $current = $vp > 0 ? $vp : 390;
    ?>
    <script>
    (function(){
      var custom = document.querySelector('#wp-admin-bar-va-breakpoint-preview-custom a');
      if(!custom) return;
      custom.addEventListener('click', function(ev){
        ev.preventDefault();
        var base = window.location.href.replace(/([?&])va_vp=\d+/g,'$1').replace(/[?&]$/,'');
        var val = window.prompt('Adj meg egy preview szélességet px-ben (280-2560):', '<?php echo (int) $current; ?>');
        if(val===null) return;
        var n = parseInt(val,10);
        if(!Number.isFinite(n) || n < 280 || n > 2560){
          window.alert('Érvénytelen érték. Engedélyezett: 280-2560 px.');
          return;
        }
        var hasQuery = base.indexOf('?') !== -1;
        window.location.href = base + (hasQuery ? '&' : '?') + 'va_vp=' + n;
      });
    })();
    </script>
    <?php
}, 100 );

/* ── Hero Carousel JS ─────────────────────────────── */
add_action( 'wp_footer', function() {
    if ( ! is_front_page() ) return;
    $bg_type = get_option( 'va_home_hero_bg_type', 'video' );
    if ( $bg_type !== 'carousel' ) return;
    ?>
    <script>
    (function(){
        var vh = document.querySelector('.vh--carousel');
        if(!vh) return;
        var speed    = parseInt(vh.dataset.speed    || 800,  10);
        var interval = parseInt(vh.dataset.interval || 5000, 10);
        var trans    = vh.dataset.transition || 'fade';
        vh.style.setProperty('--vh-speed',    speed+'ms');
        vh.style.setProperty('--vh-interval', interval+'ms');
        vh.classList.add('vh--transition-' + trans);

        var slides = Array.from(vh.querySelectorAll('.vh__slide'));
        var dots   = Array.from(vh.querySelectorAll('.vh__carousel-dot'));
        var current = 0;
        var timer = null;

        function goTo(next) {
            if(next === current || slides.length < 2) return;
            var leaving  = slides[current];
            var entering = slides[next];
            leaving.classList.add('vh__slide--leaving');
            entering.classList.add('vh__slide--entering');
            entering.classList.add('vh__slide--active');

            var onEnd = function(){
                leaving.classList.remove('vh__slide--active','vh__slide--leaving');
                entering.classList.remove('vh__slide--entering');
                entering.removeEventListener('animationend', onEnd);
            };
            entering.addEventListener('animationend', onEnd);

            // fallback ha nincs animáció (pl. fade-nél opacity átmenet)
            setTimeout(function(){
                leaving.classList.remove('vh__slide--active','vh__slide--leaving');
                entering.classList.remove('vh__slide--entering');
            }, speed + 50);

            if(dots[current]) dots[current].classList.remove('vh__carousel-dot--active');
            if(dots[next])    dots[next].classList.add('vh__carousel-dot--active');
            current = next;
        }

        function next() { goTo((current+1) % slides.length); }
        function prev() { goTo((current - 1 + slides.length) % slides.length); }

        function startTimer() { timer = setInterval(next, interval); }
        function resetTimer()  { clearInterval(timer); startTimer(); }

        var btnNext = vh.querySelector('.vh__carousel-arrow--next');
        var btnPrev = vh.querySelector('.vh__carousel-arrow--prev');
        if(btnNext) btnNext.addEventListener('click', function(){ next(); resetTimer(); });
        if(btnPrev) btnPrev.addEventListener('click', function(){ prev(); resetTimer(); });

        dots.forEach(function(dot, i){
            dot.addEventListener('click', function(){ goTo(i); resetTimer(); });
        });

        if(slides.length > 1) startTimer();
    })();
    </script>
    <?php
}, 101 );

/* ── Theme setup ──────────────────────────────────── */
add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'gallery', 'caption' ] );
    add_image_size( 'va-card',   600, 450, true );  // Hard crop 4:3 – kártyakép szabvány
    add_image_size( 'va-detail', 1200, 800, false ); // Arányőrző – részletoldal

    register_nav_menus([
        'primary'   => 'Főmenü',
        'footer'    => 'Footer menü',
    ]);
});

/* ── Widgetek ─────────────────────────────────────── */
add_action( 'widgets_init', function () {
    register_sidebar([ 'id' => 'va-sidebar-left',  'name' => 'Bal oldalsáv',   'before_widget' => '', 'after_widget' => '' ]);
    register_sidebar([ 'id' => 'va-sidebar-right', 'name' => 'Jobb oldalsáv',  'before_widget' => '', 'after_widget' => '' ]);
    register_sidebar([ 'id' => 'va-footer-1',      'name' => 'Footer widget 1','before_widget' => '', 'after_widget' => '' ]);
    register_sidebar([ 'id' => 'va-footer-2',      'name' => 'Footer widget 2','before_widget' => '', 'after_widget' => '' ]);
});

/* ── Enqueue ──────────────────────────────────────── */
add_action( 'wp_enqueue_scripts', function () {
    $theme_style_path = get_stylesheet_directory() . '/style.css';
    $theme_style_ver  = file_exists( $theme_style_path ) ? (string) filemtime( $theme_style_path ) : '3.0.2';

    wp_enqueue_style( 'va-theme', get_stylesheet_uri(), [], $theme_style_ver );

    // Egységes kártya/stílus az egész oldalon (archívum, kereső, kategória stb.)
    if ( defined( 'VA_PLUGIN_URL' ) ) {
        $frontend_css_path = defined( 'VA_PLUGIN_DIR' ) ? VA_PLUGIN_DIR . 'frontend/css/frontend.css' : '';
        $frontend_css_ver  = ( $frontend_css_path && file_exists( $frontend_css_path ) ) ? (string) filemtime( $frontend_css_path ) : VA_VERSION;
        wp_enqueue_style( 'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [ 'va-theme' ], $frontend_css_ver );

        // A kartya interakciokhoz (kedvencek, szuro, pagination) mindenhol elerheto frontend JS.
        $frontend_js_path = defined( 'VA_PLUGIN_DIR' ) ? VA_PLUGIN_DIR . 'frontend/js/frontend.js' : '';
        $frontend_js_ver  = ( $frontend_js_path && file_exists( $frontend_js_path ) ) ? (string) filemtime( $frontend_js_path ) : VA_VERSION;
        wp_enqueue_script( 'va-frontend', VA_PLUGIN_URL . 'frontend/js/frontend.js', [ 'jquery' ], $frontend_js_ver, true );
        wp_localize_script( 'va-frontend', 'VA_Data', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'va_user_nonce' ),
            'post_id'  => is_singular() ? get_the_ID() : 0,
        ] );
    }

});

function va_design_font_map(): array {
    return [
        // ── Rendszer / web-safe (nem tölt Google-t) ─────────────────
        'system'             => [ 'stack' => '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif', 'google' => '' ],
        'arial'              => [ 'stack' => 'Arial, Helvetica, sans-serif', 'google' => '' ],
        'arial-black'        => [ 'stack' => '"Arial Black", Gadget, sans-serif', 'google' => '' ],
        'helvetica'          => [ 'stack' => 'Helvetica, Arial, sans-serif', 'google' => '' ],
        'verdana'            => [ 'stack' => 'Verdana, Geneva, sans-serif', 'google' => '' ],
        'tahoma'             => [ 'stack' => 'Tahoma, Geneva, sans-serif', 'google' => '' ],
        'trebuchet'          => [ 'stack' => '"Trebuchet MS", Helvetica, sans-serif', 'google' => '' ],
        'georgia'            => [ 'stack' => 'Georgia, "Times New Roman", serif', 'google' => '' ],
        'times'              => [ 'stack' => '"Times New Roman", Times, serif', 'google' => '' ],
        'courier'            => [ 'stack' => '"Courier New", Courier, monospace', 'google' => '' ],
        // ── Google – Sans-serif népszerű ─────────────────────────────
        'open-sans'          => [ 'stack' => '"Open Sans", sans-serif', 'google' => 'Open+Sans:wght@400;500;600;700;800' ],
        'poppins'            => [ 'stack' => '"Poppins", sans-serif', 'google' => 'Poppins:wght@400;500;600;700;800;900' ],
        'lato'               => [ 'stack' => '"Lato", sans-serif', 'google' => 'Lato:wght@400;700;900' ],
        'inter'              => [ 'stack' => '"Inter", sans-serif', 'google' => 'Inter:wght@400;500;600;700;800;900' ],
        'roboto'             => [ 'stack' => '"Roboto", sans-serif', 'google' => 'Roboto:wght@400;500;700;900' ],
        'nunito'             => [ 'stack' => '"Nunito", sans-serif', 'google' => 'Nunito:wght@400;500;600;700;800;900' ],
        'montserrat'         => [ 'stack' => '"Montserrat", sans-serif', 'google' => 'Montserrat:wght@400;500;600;700;800;900' ],
        'raleway'            => [ 'stack' => '"Raleway", sans-serif', 'google' => 'Raleway:wght@400;500;600;700;800;900' ],
        'source-sans-3'      => [ 'stack' => '"Source Sans 3", sans-serif', 'google' => 'Source+Sans+3:wght@400;500;600;700;800;900' ],
        'pt-sans'            => [ 'stack' => '"PT Sans", sans-serif', 'google' => 'PT+Sans:wght@400;700' ],
        'ubuntu'             => [ 'stack' => '"Ubuntu", sans-serif', 'google' => 'Ubuntu:wght@400;500;700' ],
        'rubik'              => [ 'stack' => '"Rubik", sans-serif', 'google' => 'Rubik:wght@400;500;700;900' ],
        'dm-sans'            => [ 'stack' => '"DM Sans", sans-serif', 'google' => 'DM+Sans:wght@400;500;700;900' ],
        'work-sans'          => [ 'stack' => '"Work Sans", sans-serif', 'google' => 'Work+Sans:wght@400;500;600;700;800;900' ],
        'manrope'            => [ 'stack' => '"Manrope", sans-serif', 'google' => 'Manrope:wght@400;500;600;700;800' ],
        'cabin'              => [ 'stack' => '"Cabin", sans-serif', 'google' => 'Cabin:wght@400;500;600;700' ],
        'barlow'             => [ 'stack' => '"Barlow", sans-serif', 'google' => 'Barlow:wght@400;500;600;700;800;900' ],
        'barlow-condensed'   => [ 'stack' => '"Barlow Condensed", sans-serif', 'google' => 'Barlow+Condensed:wght@400;500;600;700;800;900' ],
        'mulish'             => [ 'stack' => '"Mulish", sans-serif', 'google' => 'Mulish:wght@400;500;600;700;800;900' ],
        'quicksand'          => [ 'stack' => '"Quicksand", sans-serif', 'google' => 'Quicksand:wght@400;500;600;700' ],
        'josefin-sans'       => [ 'stack' => '"Josefin Sans", sans-serif', 'google' => 'Josefin+Sans:wght@400;600;700' ],
        'titillium-web'      => [ 'stack' => '"Titillium Web", sans-serif', 'google' => 'Titillium+Web:wght@400;600;700;900' ],
        'exo-2'              => [ 'stack' => '"Exo 2", sans-serif', 'google' => 'Exo+2:wght@400;500;600;700;800;900' ],
        'exo'                => [ 'stack' => '"Exo", sans-serif', 'google' => 'Exo:wght@400;500;600;700;800;900' ],
        'archivo'            => [ 'stack' => '"Archivo", sans-serif', 'google' => 'Archivo:wght@400;500;600;700;800;900' ],
        'outfit'             => [ 'stack' => '"Outfit", sans-serif', 'google' => 'Outfit:wght@400;500;600;700;800;900' ],
        'plus-jakarta-sans'  => [ 'stack' => '"Plus Jakarta Sans", sans-serif', 'google' => 'Plus+Jakarta+Sans:wght@400;500;600;700;800' ],
        'figtree'            => [ 'stack' => '"Figtree", sans-serif', 'google' => 'Figtree:wght@400;500;600;700;800;900' ],
        'syne'               => [ 'stack' => '"Syne", sans-serif', 'google' => 'Syne:wght@400;500;600;700;800' ],
        'space-grotesk'      => [ 'stack' => '"Space Grotesk", sans-serif', 'google' => 'Space+Grotesk:wght@400;500;600;700' ],
        'kanit'              => [ 'stack' => '"Kanit", sans-serif', 'google' => 'Kanit:wght@400;500;600;700' ],
        'jost'               => [ 'stack' => '"Jost", sans-serif', 'google' => 'Jost:wght@400;500;600;700;800;900' ],
        'urbanist'           => [ 'stack' => '"Urbanist", sans-serif', 'google' => 'Urbanist:wght@400;500;600;700;800;900' ],
        'fira-sans'          => [ 'stack' => '"Fira Sans", sans-serif', 'google' => 'Fira+Sans:wght@400;500;600;700;800;900' ],
        'ibm-plex-sans'      => [ 'stack' => '"IBM Plex Sans", sans-serif', 'google' => 'IBM+Plex+Sans:wght@400;500;600;700' ],
        'noto-sans'          => [ 'stack' => '"Noto Sans", sans-serif', 'google' => 'Noto+Sans:wght@400;500;600;700;800;900' ],
        // ── Google – Serif ───────────────────────────────────────────
        'merriweather'       => [ 'stack' => '"Merriweather", serif', 'google' => 'Merriweather:wght@400;700;900' ],
        'playfair'           => [ 'stack' => '"Playfair Display", serif', 'google' => 'Playfair+Display:wght@400;600;700;800;900' ],
        'lora'               => [ 'stack' => '"Lora", serif', 'google' => 'Lora:wght@400;500;600;700' ],
        'libre-baskerville'  => [ 'stack' => '"Libre Baskerville", serif', 'google' => 'Libre+Baskerville:wght@400;700' ],
        'crimson-text'       => [ 'stack' => '"Crimson Text", serif', 'google' => 'Crimson+Text:wght@400;600;700' ],
        'eb-garamond'        => [ 'stack' => '"EB Garamond", serif', 'google' => 'EB+Garamond:wght@400;500;600;700;800' ],
        'cormorant-garamond' => [ 'stack' => '"Cormorant Garamond", serif', 'google' => 'Cormorant+Garamond:wght@400;500;600;700' ],
        'spectral'           => [ 'stack' => '"Spectral", serif', 'google' => 'Spectral:wght@400;500;600;700;800' ],
        'oswald'             => [ 'stack' => '"Oswald", sans-serif', 'google' => 'Oswald:wght@400;500;600;700' ],
        // ── Google – Display / dekoratív ─────────────────────────────
        'abril-fatface'      => [ 'stack' => '"Abril Fatface", cursive', 'google' => 'Abril+Fatface' ],
        'righteous'          => [ 'stack' => '"Righteous", cursive', 'google' => 'Righteous' ],
        'bebas-neue'         => [ 'stack' => '"Bebas Neue", sans-serif', 'google' => 'Bebas+Neue' ],
        'comfortaa'          => [ 'stack' => '"Comfortaa", cursive', 'google' => 'Comfortaa:wght@400;500;600;700' ],
        'pacifico'           => [ 'stack' => '"Pacifico", cursive', 'google' => 'Pacifico' ],
        'anton'              => [ 'stack' => '"Anton", sans-serif', 'google' => 'Anton' ],
        'permanent-marker'   => [ 'stack' => '"Permanent Marker", cursive', 'google' => 'Permanent+Marker' ],
        'shadows-into-light' => [ 'stack' => '"Shadows Into Light", cursive', 'google' => 'Shadows+Into+Light' ],
    ];
}

function va_design_font_stack( string $slug ): string {
    $map = va_design_font_map();
    if ( ! isset( $map[ $slug ] ) ) {
        $slug = 'system';
    }
    return $map[ $slug ]['stack'];
}

function va_design_css_color( string $value, string $fallback ): string {
    $value = trim( $value );
    if ( $value === '' ) {
        return $fallback;
    }
    // Egyszerű whitelist: #hex vagy rgb/rgba/hsl/hsla vagy named.
    if ( preg_match( '/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $value ) ) {
        return $value;
    }
    if ( preg_match( '/^(rgb|rgba|hsl|hsla)\([^\)]*\)$/i', $value ) ) {
        return $value;
    }
    if ( preg_match( '/^[a-zA-Z]{3,20}$/', $value ) ) {
        return $value;
    }
    return $fallback;
}

function va_design_int_option( string $key, int $default, int $min, int $max ): int {
    $val = absint( get_option( $key, $default ) );
    if ( $val < $min ) {
        return $min;
    }
    if ( $val > $max ) {
        return $max;
    }
    return $val;
}

function va_design_float_option( string $key, float $default, float $min, float $max ): float {
    $raw = str_replace( ',', '.', (string) get_option( $key, (string) $default ) );
    $val = (float) $raw;
    if ( $val < $min ) {
        return $min;
    }
    if ( $val > $max ) {
        return $max;
    }
    return $val;
}

function va_design_weight_option( string $key, string $default ): string {
    $val = preg_replace( '/[^0-9]/', '', (string) get_option( $key, $default ) );
    if ( ! in_array( $val, [ '300', '400', '500', '600', '700', '800', '900' ], true ) ) {
        return $default;
    }
    return $val;
}

function va_design_fluid_px( int $desktop_px, float $mobile_ratio, string $vw ): string {
    $mobile_px = max( 8, (int) round( $desktop_px * $mobile_ratio ) );
    return 'clamp(' . $mobile_px . 'px, ' . $vw . ', ' . $desktop_px . 'px)';
}

function va_design_scaled_ratio( float $base_ratio, int $factor_percent ): float {
    $ratio = $base_ratio * ( $factor_percent / 100 );
    if ( $ratio < 0.45 ) {
        return 0.45;
    }
    if ( $ratio > 0.98 ) {
        return 0.98;
    }
    return $ratio;
}

add_action( 'wp_enqueue_scripts', function () {
    $font_defaults = [
        'va_font_global'   => 'system',
        'va_font_headings' => 'montserrat',
        'va_font_header'   => 'montserrat',
        'va_font_content'  => 'source-sans-3',
        'va_font_footer'   => 'source-sans-3',
    ];

    $font_map = va_design_font_map();
    $google_families = [];

    foreach ( $font_defaults as $key => $default_slug ) {
        $slug = sanitize_key( (string) get_option( $key, $default_slug ) );
        if ( isset( $font_map[ $slug ] ) && $font_map[ $slug ]['google'] !== '' ) {
            $google_families[] = $font_map[ $slug ]['google'];
        }
    }

    $google_families = array_values( array_unique( $google_families ) );
    if ( ! empty( $google_families ) ) {
        $fonts_url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $google_families ) . '&display=swap';
        wp_enqueue_style( 'va-custom-fonts', esc_url_raw( $fonts_url ), [], null );
    }

    $font_global   = va_design_font_stack( sanitize_key( (string) get_option( 'va_font_global', 'system' ) ) );
    $font_headings = va_design_font_stack( sanitize_key( (string) get_option( 'va_font_headings', 'montserrat' ) ) );
    $font_header   = va_design_font_stack( sanitize_key( (string) get_option( 'va_font_header', 'montserrat' ) ) );
    $font_content  = va_design_font_stack( sanitize_key( (string) get_option( 'va_font_content', 'source-sans-3' ) ) );
    $font_footer   = va_design_font_stack( sanitize_key( (string) get_option( 'va_font_footer', 'source-sans-3' ) ) );

    $global_bg     = va_design_css_color( (string) get_option( 'va_color_global_bg', '#060606' ), '#060606' );
    $global_text   = va_design_css_color( (string) get_option( 'va_color_global_text', '#ffffff' ), '#ffffff' );
    $global_muted  = va_design_css_color( (string) get_option( 'va_color_global_muted', 'rgba(255,255,255,.65)' ), 'rgba(255,255,255,.65)' );
    $global_accent = va_design_css_color( (string) get_option( 'va_color_global_accent', '#ff0000' ), '#ff0000' );

    $header_bg     = va_design_css_color( (string) get_option( 'va_color_header_bg', 'rgba(6,4,4,.82)' ), 'rgba(6,4,4,.82)' );
    $header_text   = va_design_css_color( (string) get_option( 'va_color_header_text', '#ffffff' ), '#ffffff' );
    $header_accent = va_design_css_color( (string) get_option( 'va_color_header_accent', '#ff0000' ), '#ff0000' );

    $content_bg       = va_design_css_color( (string) get_option( 'va_color_content_bg', '#060606' ), '#060606' );
    $content_text     = va_design_css_color( (string) get_option( 'va_color_content_text', '#ffffff' ), '#ffffff' );
    $content_headings = va_design_css_color( (string) get_option( 'va_color_content_headings', '#ffffff' ), '#ffffff' );
    $content_links    = va_design_css_color( (string) get_option( 'va_color_content_links', '#ff4444' ), '#ff4444' );

    $footer_bg       = va_design_css_color( (string) get_option( 'va_color_footer_bg', '#0a0a0a' ), '#0a0a0a' );
    $footer_text     = va_design_css_color( (string) get_option( 'va_color_footer_text', 'rgba(255,255,255,.72)' ), 'rgba(255,255,255,.72)' );
    $footer_headings = va_design_css_color( (string) get_option( 'va_color_footer_headings', '#ffffff' ), '#ffffff' );
    $footer_links    = va_design_css_color( (string) get_option( 'va_color_footer_links', '#ff4444' ), '#ff4444' );

    $home_hero_badge     = va_design_int_option( 'va_size_home_hero_badge', 11, 8, 32 );
    $home_hero_title     = va_design_int_option( 'va_size_home_hero_title', 64, 24, 120 );
    $home_hero_sub       = va_design_int_option( 'va_size_home_hero_sub', 19, 10, 42 );
    $home_hero_btn       = va_design_int_option( 'va_size_home_hero_btn', 15, 10, 28 );
    $kat_hero_badge      = va_design_int_option( 'va_size_kat_hero_badge', 10, 8, 32 );
    $kat_hero_title      = va_design_int_option( 'va_size_kat_hero_title', 56, 20, 110 );
    $kat_hero_sub        = va_design_int_option( 'va_size_kat_hero_sub', 15, 10, 40 );
    $kat_hero_stat_num   = va_design_int_option( 'va_size_kat_hero_stat_num', 20, 10, 44 );
    $kat_hero_stat_label = va_design_int_option( 'va_size_kat_hero_stat_label', 10, 8, 24 );
    $tax_hero_badge      = va_design_int_option( 'va_size_tax_hero_badge', 11, 8, 32 );
    $tax_hero_title      = va_design_int_option( 'va_size_tax_hero_title', 48, 18, 100 );
    $tax_hero_lead       = va_design_int_option( 'va_size_tax_hero_lead', 16, 10, 40 );
    $tax_hero_count      = va_design_int_option( 'va_size_tax_hero_count', 14, 10, 34 );
    $contact_hero_badge  = va_design_int_option( 'va_size_contact_hero_badge', 11, 8, 32 );
    $contact_hero_title  = va_design_int_option( 'va_size_contact_hero_title', 62, 20, 120 );
    $contact_hero_lead   = va_design_int_option( 'va_size_contact_hero_lead', 16, 10, 40 );

    $lh_home_hero_title    = va_design_float_option( 'va_lh_home_hero_title', 1.05, 0.8, 2.4 );
    $lh_home_hero_sub      = va_design_float_option( 'va_lh_home_hero_sub', 1.60, 0.8, 2.8 );
    $lh_kat_hero_title     = va_design_float_option( 'va_lh_kat_hero_title', 1.06, 0.8, 2.4 );
    $lh_kat_hero_sub       = va_design_float_option( 'va_lh_kat_hero_sub', 1.70, 0.8, 2.8 );
    $lh_tax_hero_title     = va_design_float_option( 'va_lh_tax_hero_title', 1.05, 0.8, 2.4 );
    $lh_tax_hero_lead      = va_design_float_option( 'va_lh_tax_hero_lead', 1.75, 0.8, 2.8 );
    $lh_contact_hero_title = va_design_float_option( 'va_lh_contact_hero_title', 1.02, 0.8, 2.4 );
    $lh_contact_hero_lead  = va_design_float_option( 'va_lh_contact_hero_lead', 1.80, 0.8, 2.8 );

    $header_brand_size   = va_design_int_option( 'va_size_header_brand', 18, 10, 44 );
    $header_nav_size     = va_design_int_option( 'va_size_header_nav', 14, 10, 34 );
    $header_search_size  = va_design_int_option( 'va_size_header_search', 14, 10, 30 );
    $header_btn_size     = va_design_int_option( 'va_size_header_btn', 13, 10, 30 );
    $header_brand_weight = va_design_weight_option( 'va_weight_header_brand', '800' );
    $header_nav_weight   = va_design_weight_option( 'va_weight_header_nav', '600' );

    $footer_title_size   = va_design_int_option( 'va_size_footer_title', 13, 10, 34 );
    $footer_link_size    = va_design_int_option( 'va_size_footer_link', 13, 10, 30 );
    $footer_bottom_size  = va_design_int_option( 'va_size_footer_bottom', 12, 10, 28 );
    $footer_title_weight = va_design_weight_option( 'va_weight_footer_title', '700' );
    $footer_link_weight  = va_design_weight_option( 'va_weight_footer_link', '500' );

    $mobile_factor_hero   = va_design_int_option( 'va_mobile_factor_hero', 100, 70, 120 );
    $mobile_factor_header = va_design_int_option( 'va_mobile_factor_header', 100, 70, 120 );
    $mobile_factor_footer = va_design_int_option( 'va_mobile_factor_footer', 100, 70, 120 );

    $hf_header_height            = va_design_int_option( 'va_hf_header_height', 66, 50, 120 );
    $hf_header_max_width         = va_design_int_option( 'va_hf_header_max_width', 1480, 960, 2200 );
    $hf_header_padding_x         = va_design_int_option( 'va_hf_header_padding_x', 32, 0, 80 );
    $hf_header_padding_top       = va_design_int_option( 'va_hf_header_padding_top', 6, 0, 30 );
    $hf_header_padding_bottom    = va_design_int_option( 'va_hf_header_padding_bottom', 10, 0, 30 );
    $hf_header_gap               = va_design_int_option( 'va_hf_header_gap', 0, 0, 40 );
    $hf_header_bg_opacity        = va_design_float_option( 'va_hf_header_bg_opacity', 0.82, 0, 1 );
    $hf_header_bg_opacity_scroll = va_design_float_option( 'va_hf_header_bg_opacity_scrolled', 0.88, 0, 1 );
    $hf_header_blur              = va_design_int_option( 'va_hf_header_blur', 16, 0, 40 );
    $hf_header_blur_scroll       = va_design_int_option( 'va_hf_header_blur_scrolled', 20, 0, 44 );
    $hf_header_shadow_alpha      = va_design_float_option( 'va_hf_header_shadow_alpha', 0.70, 0, 1 );
    $hf_header_color_base        = va_design_css_color( (string) get_option( 'va_hf_header_color_base', '#050505' ), '#050505' );
    $hf_header_color_alt         = va_design_css_color( (string) get_option( 'va_hf_header_color_alt', '#140909' ), '#140909' );
    $hf_header_border_color      = va_design_css_color( (string) get_option( 'va_hf_header_border_color', '#ff2a2a' ), '#ff2a2a' );
    $hf_header_shadow_color      = va_design_css_color( (string) get_option( 'va_hf_header_shadow_color', 'rgba(0,0,0,.72)' ), 'rgba(0,0,0,.72)' );
    $hf_header_glow_color        = va_design_css_color( (string) get_option( 'va_hf_header_glow_color', 'rgba(255,0,0,.24)' ), 'rgba(255,0,0,.24)' );

    $hf_search_max_width         = va_design_int_option( 'va_hf_header_search_max_width', 460, 220, 760 );
    $hf_search_height            = va_design_int_option( 'va_hf_header_search_height', 42, 30, 64 );
    $hf_search_radius            = va_design_int_option( 'va_hf_header_search_radius', 30, 8, 999 );
    $hf_search_border_alpha      = va_design_float_option( 'va_hf_header_search_border_alpha', 0.14, 0, 1 );
    $hf_search_bg_alpha          = va_design_float_option( 'va_hf_header_search_bg_alpha', 0.02, 0, 1 );
    $hf_search_hover_alpha       = va_design_float_option( 'va_hf_header_search_hover_border_alpha', 0.38, 0, 1 );
    $hf_search_focus_alpha       = va_design_float_option( 'va_hf_header_search_focus_border_alpha', 0.52, 0, 1 );
    $hf_search_icon_size         = va_design_int_option( 'va_hf_header_search_icon_size', 16, 10, 28 );
    $hf_search_icon_bg_alpha     = va_design_float_option( 'va_hf_header_search_icon_bg_alpha', 0.14, 0, 1 );
    $hf_search_icon_bg_hover     = va_design_float_option( 'va_hf_header_search_icon_bg_hover_alpha', 0.22, 0, 1 );
    $hf_search_glow_color        = va_design_css_color( (string) get_option( 'va_hf_header_search_glow_color', 'rgba(255,0,0,.18)' ), 'rgba(255,0,0,.18)' );
    $hf_search_btn_size          = max( 24, min( 44, $hf_search_icon_size + 12 ) );

    $hf_btn_radius               = va_design_int_option( 'va_hf_header_btn_radius', 999, 8, 999 );
    $hf_btn_pad_y                = va_design_int_option( 'va_hf_header_btn_pad_y', 8, 4, 20 );
    $hf_btn_pad_x                = va_design_int_option( 'va_hf_header_btn_pad_x', 20, 8, 40 );
    $hf_btn_glow_alpha           = va_design_float_option( 'va_hf_header_btn_glow_alpha', 0.40, 0, 1 );
    $hf_btn_glow_color           = va_design_css_color( (string) get_option( 'va_hf_header_btn_glow_color', 'rgba(255,0,0,.52)' ), 'rgba(255,0,0,.52)' );
    $hf_user_border_alpha        = va_design_float_option( 'va_hf_header_user_border_alpha', 0.12, 0, 1 );
    $hf_user_bg_alpha            = va_design_float_option( 'va_hf_header_user_bg_alpha', 0.06, 0, 1 );
    $hf_mobile_show_search       = get_option( 'va_hf_header_mobile_show_search', '0' ) === '1';
    $hf_mobile_show_submit       = get_option( 'va_hf_header_mobile_show_submit', '0' ) === '1';

    $hf_footer_top_padding       = va_design_int_option( 'va_hf_footer_top_padding', 48, 12, 120 );
    $hf_footer_bottom_padding    = va_design_int_option( 'va_hf_footer_bottom_padding', 24, 8, 80 );
    $hf_footer_grid_gap          = va_design_int_option( 'va_hf_footer_grid_gap', 32, 8, 80 );
    $hf_footer_col_min_width     = va_design_int_option( 'va_hf_footer_col_min_width', 160, 120, 420 );
    $hf_footer_title_gap         = va_design_int_option( 'va_hf_footer_title_gap', 12, 4, 36 );
    $hf_footer_link_pad_y        = va_design_int_option( 'va_hf_footer_link_pad_y', 4, 0, 20 );
    $hf_footer_bottom_top_pad    = va_design_int_option( 'va_hf_footer_bottom_top_padding', 20, 6, 40 );
    $hf_footer_border_alpha      = va_design_float_option( 'va_hf_footer_border_alpha', 0.07, 0, 1 );
    $hf_footer_bottom_border     = va_design_float_option( 'va_hf_footer_bottom_border_alpha', 0.07, 0, 1 );
    $hf_footer_max_width         = va_design_int_option( 'va_hf_footer_max_width', 1400, 800, 2200 );
    $hf_footer_color_base        = va_design_css_color( (string) get_option( 'va_hf_footer_color_base', '#0a0a0a' ), '#0a0a0a' );
    $hf_footer_color_alt         = va_design_css_color( (string) get_option( 'va_hf_footer_color_alt', '#150707' ), '#150707' );
    $hf_footer_border_color      = va_design_css_color( (string) get_option( 'va_hf_footer_border_color', '#ff2a2a' ), '#ff2a2a' );
    $hf_footer_shadow_color      = va_design_css_color( (string) get_option( 'va_hf_footer_shadow_color', 'rgba(0,0,0,.36)' ), 'rgba(0,0,0,.36)' );
    $hf_footer_glow_color        = va_design_css_color( (string) get_option( 'va_hf_footer_glow_color', 'rgba(255,0,0,.14)' ), 'rgba(255,0,0,.14)' );
    $hf_footer_link_hover_color  = va_design_css_color( (string) get_option( 'va_hf_footer_link_hover_color', '#ffffff' ), '#ffffff' );

    // Layout builder (Divi/Porto jellegu) opciok
    $layout_preset               = sanitize_key( (string) get_option( 'va_layout_preset', 'porto' ) );
    if ( ! in_array( $layout_preset, [ 'porto', 'divi', 'custom' ], true ) ) {
        $layout_preset = 'porto';
    }

    $layout_page_max_width       = va_design_int_option( 'va_layout_page_max_width', 1400, 960, 2200 );
    $layout_container_pad_x      = va_design_int_option( 'va_layout_container_pad_x', 20, 0, 80 );
    $layout_container_pad_m      = va_design_int_option( 'va_layout_container_pad_x_mobile', 12, 0, 40 );
    $layout_main_pad_y           = va_design_int_option( 'va_layout_main_pad_y', 20, 0, 80 );
    $layout_main_pad_x           = va_design_int_option( 'va_layout_main_pad_x', 24, 0, 80 );
    $layout_home_pad_x           = va_design_int_option( 'va_layout_home_main_pad_x', 28, 0, 100 );
    $layout_content_gap          = va_design_int_option( 'va_layout_content_gap', 0, 0, 64 );
    $layout_sidebar_width        = va_design_int_option( 'va_layout_right_sidebar_width', 340, 220, 520 );
    $layout_sidebar_top          = va_design_int_option( 'va_layout_right_sidebar_sticky_top', 48, 0, 180 );
    $layout_show_sidebar         = get_option( 'va_layout_show_right_sidebar', '1' ) === '1';

    $layout_cols_desktop         = va_design_int_option( 'va_layout_grid_cols_desktop', 4, 1, 6 );
    $layout_cols_tablet          = va_design_int_option( 'va_layout_grid_cols_tablet', 2, 1, 4 );
    $layout_cols_mobile          = va_design_int_option( 'va_layout_grid_cols_mobile', 1, 1, 2 );
    $layout_grid_gap             = va_design_int_option( 'va_layout_grid_gap', 14, 4, 40 );
    $layout_bp_desktop_tablet    = va_design_int_option( 'va_layout_bp_desktop_tablet', 1200, 680, 2000 );
    $layout_bp_tablet_mobile     = va_design_int_option( 'va_layout_bp_tablet_mobile', 560, 320, 1200 );
    $layout_bp_sidebar_hide      = va_design_int_option( 'va_layout_bp_sidebar_hide', 1100, 480, 1800 );

    $layout_card_radius          = va_design_int_option( 'va_layout_card_radius', 6, 0, 28 );
    $layout_card_border_alpha    = va_design_float_option( 'va_layout_card_border_alpha', 0.08, 0, 1 );
    $layout_card_pad_y           = va_design_int_option( 'va_layout_card_padding_y', 14, 6, 40 );
    $layout_card_pad_x           = va_design_int_option( 'va_layout_card_padding_x', 14, 6, 40 );
    $layout_card_title_size      = va_design_int_option( 'va_layout_card_title_size', 15, 12, 28 );
    $layout_card_price_size      = va_design_int_option( 'va_layout_card_price_size', 17, 12, 36 );
    $layout_card_meta_size       = va_design_int_option( 'va_layout_card_meta_size', 12, 10, 20 );
    $layout_card_hover_lift      = va_design_int_option( 'va_layout_card_hover_lift', 2, 0, 16 );
    $layout_card_shadow_strength = va_design_int_option( 'va_layout_card_shadow_strength', 35, 0, 100 );
    $layout_card_shadow_red      = va_design_int_option( 'va_layout_card_shadow_red', 16, 0, 100 );
    $layout_widget_radius        = va_design_int_option( 'va_layout_widget_radius', 10, 0, 28 );
    $layout_widget_padding       = va_design_int_option( 'va_layout_widget_padding', 16, 6, 40 );

    $layout_card_img_ratio = (string) get_option( 'va_layout_card_img_ratio', '4/3' );
    if ( ! in_array( $layout_card_img_ratio, [ '4/3', '16/10', '1/1', '3/2' ], true ) ) {
        $layout_card_img_ratio = '4/3';
    }

    // Preset finomhangolas (Divi / Porto mintara)
    if ( $layout_preset === 'divi' ) {
        $layout_grid_gap = max( $layout_grid_gap, 18 );
        $layout_card_radius = max( $layout_card_radius, 10 );
        $layout_card_hover_lift = max( $layout_card_hover_lift, 4 );
        $layout_content_gap = max( $layout_content_gap, 14 );
    } elseif ( $layout_preset === 'porto' ) {
        $layout_grid_gap = min( $layout_grid_gap, 16 );
        $layout_card_radius = min( $layout_card_radius, 8 );
        $layout_card_hover_lift = min( $layout_card_hover_lift, 3 );
    }

    // Fluid, reszponzív méretkimenet
    $home_hero_badge_css    = va_design_fluid_px( $home_hero_badge, va_design_scaled_ratio( 0.90, $mobile_factor_hero ), '1.4vw' );
    $home_hero_title_css    = va_design_fluid_px( $home_hero_title, va_design_scaled_ratio( 0.58, $mobile_factor_hero ), '7.6vw' );
    $home_hero_sub_css      = va_design_fluid_px( $home_hero_sub, va_design_scaled_ratio( 0.82, $mobile_factor_hero ), '2.8vw' );
    $home_hero_btn_css      = va_design_fluid_px( $home_hero_btn, va_design_scaled_ratio( 0.86, $mobile_factor_hero ), '2.2vw' );

    $kat_hero_badge_css     = va_design_fluid_px( $kat_hero_badge, va_design_scaled_ratio( 0.90, $mobile_factor_hero ), '1.3vw' );
    $kat_hero_title_css     = va_design_fluid_px( $kat_hero_title, va_design_scaled_ratio( 0.58, $mobile_factor_hero ), '6.8vw' );
    $kat_hero_sub_css       = va_design_fluid_px( $kat_hero_sub, va_design_scaled_ratio( 0.84, $mobile_factor_hero ), '2.2vw' );
    $kat_hero_stat_num_css  = va_design_fluid_px( $kat_hero_stat_num, va_design_scaled_ratio( 0.84, $mobile_factor_hero ), '2.8vw' );
    $kat_hero_stat_lbl_css  = va_design_fluid_px( $kat_hero_stat_label, va_design_scaled_ratio( 0.90, $mobile_factor_hero ), '1.5vw' );

    $tax_hero_badge_css     = va_design_fluid_px( $tax_hero_badge, va_design_scaled_ratio( 0.90, $mobile_factor_hero ), '1.3vw' );
    $tax_hero_title_css     = va_design_fluid_px( $tax_hero_title, va_design_scaled_ratio( 0.62, $mobile_factor_hero ), '6.0vw' );
    $tax_hero_lead_css      = va_design_fluid_px( $tax_hero_lead, va_design_scaled_ratio( 0.84, $mobile_factor_hero ), '2.3vw' );
    $tax_hero_count_css     = va_design_fluid_px( $tax_hero_count, va_design_scaled_ratio( 0.88, $mobile_factor_hero ), '2.0vw' );

    $contact_hero_badge_css = va_design_fluid_px( $contact_hero_badge, va_design_scaled_ratio( 0.90, $mobile_factor_hero ), '1.3vw' );
    $contact_hero_title_css = va_design_fluid_px( $contact_hero_title, va_design_scaled_ratio( 0.60, $mobile_factor_hero ), '7.0vw' );
    $contact_hero_lead_css  = va_design_fluid_px( $contact_hero_lead, va_design_scaled_ratio( 0.84, $mobile_factor_hero ), '2.3vw' );

    $header_brand_css       = va_design_fluid_px( $header_brand_size, va_design_scaled_ratio( 0.90, $mobile_factor_header ), '2.4vw' );
    $header_nav_css         = va_design_fluid_px( $header_nav_size, va_design_scaled_ratio( 0.90, $mobile_factor_header ), '2.0vw' );
    $header_search_css      = va_design_fluid_px( $header_search_size, va_design_scaled_ratio( 0.90, $mobile_factor_header ), '1.9vw' );
    $header_btn_css         = va_design_fluid_px( $header_btn_size, va_design_scaled_ratio( 0.90, $mobile_factor_header ), '1.9vw' );

    $footer_title_css       = va_design_fluid_px( $footer_title_size, va_design_scaled_ratio( 0.92, $mobile_factor_footer ), '1.9vw' );
    $footer_link_css        = va_design_fluid_px( $footer_link_size, va_design_scaled_ratio( 0.92, $mobile_factor_footer ), '1.8vw' );
    $footer_bottom_css      = va_design_fluid_px( $footer_bottom_size, va_design_scaled_ratio( 0.92, $mobile_factor_footer ), '1.7vw' );

    $css = ':root{' .
        '--a:' . $global_accent . ';' .
        '--a2:' . $global_accent . ';' .
        '--a3:' . $global_accent . ';' .
        '--t:' . $global_text . ';' .
        '--t2:' . $global_muted . ';' .
        '--nav:' . $hf_header_height . 'px;' .
    '}' .
    'body{' .
        'font-family:' . $font_global . ';' .
        'background:' . $global_bg . ';' .
        'color:' . $global_text . ';' .
    '}' .
    'h1,h2,h3,h4,h5,h6{font-family:' . $font_headings . ';}' .
    '.va-header,.va-header *{' .
        'font-family:' . $font_header . ';' .
        'color:' . $header_text . ';' .
    '}' .
    '.va-header{' .
        'height:' . $hf_header_height . 'px;' .
        'background-color:rgba(6,4,4,' . $hf_header_bg_opacity . ');' .
        'background-image:linear-gradient(120deg,' . $hf_header_color_base . ',' . $hf_header_color_alt . ');' .
        'background-blend-mode:overlay;' .
        'backdrop-filter:blur(' . $hf_header_blur . 'px) saturate(1.4);' .
        '-webkit-backdrop-filter:blur(' . $hf_header_blur . 'px) saturate(1.4);' .
        'border-bottom:1px solid ' . $hf_header_border_color . ';' .
        'box-shadow:0 1px 30px ' . $hf_header_glow_color . ';' .
    '}' .
    '.va-header.scrolled{' .
        'background-color:rgba(6,4,4,' . $hf_header_bg_opacity_scroll . ');' .
        'backdrop-filter:blur(' . $hf_header_blur_scroll . 'px) saturate(1.4);' .
        '-webkit-backdrop-filter:blur(' . $hf_header_blur_scroll . 'px) saturate(1.4);' .
        'box-shadow:0 1px 60px rgba(0,0,0,' . $hf_header_shadow_alpha . '),0 0 42px ' . $hf_header_shadow_color . ';' .
    '}' .
    '.va-header__inner{' .
        'max-width:' . $hf_header_max_width . 'px;' .
        'padding:' . $hf_header_padding_top . 'px ' . $hf_header_padding_x . 'px ' . $hf_header_padding_bottom . 'px;' .
        'gap:' . $hf_header_gap . 'px;' .
    '}' .
    '.va-header__inner .va-header__search{max-width:' . $hf_search_max_width . 'px;}' .
    '.va-header__search{' .
        'height:' . $hf_search_height . 'px;' .
        'border-radius:' . $hf_search_radius . 'px;' .
        'border-color:rgba(255,255,255,' . $hf_search_border_alpha . ');' .
        'background:rgba(255,255,255,' . $hf_search_bg_alpha . ');' .
    '}' .
    '.va-header__search:hover{border-color:rgba(255,255,255,' . $hf_search_hover_alpha . ');box-shadow:none;}' .
    '.va-header__search:focus-within{border-color:rgba(255,255,255,' . $hf_search_focus_alpha . ');box-shadow:none;}' .
    '.va-header__search-btn{' .
        'width:' . $hf_search_btn_size . 'px;' .
        'height:' . $hf_search_btn_size . 'px;' .
        'background-color:rgba(255,255,255,' . $hf_search_icon_bg_alpha . ');' .
    '}' .
    '.va-header__search-btn:hover{background-color:rgba(255,255,255,' . $hf_search_icon_bg_hover . ');}' .
    '.va-header__search-btn::before{' .
        'width:' . $hf_search_icon_size . 'px;' .
        'height:' . $hf_search_icon_size . 'px;' .
        'background-size:' . $hf_search_icon_size . 'px ' . $hf_search_icon_size . 'px;' .
    '}' .
    '.va-header__submit-btn,.va-header__user,.va-header__user-login{' .
        'border-radius:' . $hf_btn_radius . 'px;' .
        'padding:' . $hf_btn_pad_y . 'px ' . $hf_btn_pad_x . 'px;' .
    '}' .
    '.va-header__submit-btn{box-shadow:0 0 24px rgba(255,0,0,' . $hf_btn_glow_alpha . '),0 0 24px ' . $hf_btn_glow_color . ';}' .
    '.va-header__user{' .
        'background:rgba(255,255,255,' . $hf_user_bg_alpha . ');' .
        'border-color:rgba(255,255,255,' . $hf_user_border_alpha . ');' .
    '}' .
    '.va-nav__item--accent,.va-header__submit-btn{background-color:' . $header_accent . ';border-color:' . $header_accent . ';}' .
    '.va-container,.va-content-layout,.va-main-content,.va-wrap,.va-cat-page,.va-contact-page{' .
        'background-color:' . $content_bg . ';' .
        'color:' . $content_text . ';' .
        'font-family:' . $font_content . ';' .
    '}' .
    '.va-container{max-width:' . $layout_page_max_width . 'px;padding:0 ' . $layout_container_pad_x . 'px;}' .
    '@media (max-width:400px){.va-container{padding:0 ' . $layout_container_pad_m . 'px;}}' .
    '.va-content-layout{gap:' . $layout_content_gap . 'px;}' .
    '.va-main-content{padding:' . $layout_main_pad_y . 'px ' . $layout_main_pad_x . 'px;}' .
    '.home-template .va-main-content,.home .va-main-content,.front-page .va-main-content{padding:' . $layout_main_pad_y . 'px ' . $layout_home_pad_x . 'px;}' .
    '.va-sidebar.va-sidebar--right{width:' . $layout_sidebar_width . 'px;top:calc(var(--nav) + ' . $layout_sidebar_top . 'px);height:calc(100vh - var(--nav) - ' . $layout_sidebar_top . 'px);display:' . ( $layout_show_sidebar ? 'block' : 'none' ) . ';}' .
    '.va-grid{grid-template-columns:repeat(' . $layout_cols_desktop . ',minmax(0,1fr));gap:' . $layout_grid_gap . 'px;}' .
    '@media (max-width:' . $layout_bp_desktop_tablet . 'px){.va-grid{grid-template-columns:repeat(' . $layout_cols_tablet . ',minmax(0,1fr));}}' .
    '@media (max-width:' . $layout_bp_tablet_mobile . 'px){.va-grid{grid-template-columns:repeat(' . $layout_cols_mobile . ',minmax(0,1fr));}}' .
    '@media (max-width:' . $layout_bp_sidebar_hide . 'px){.va-sidebar.va-sidebar--right{display:none !important;}.va-main-content{padding:' . $layout_main_pad_y . 'px ' . max( 10, min( 20, $layout_main_pad_x ) ) . 'px;}}' .
    '.va-card{border-radius:' . $layout_card_radius . 'px;border-color:rgba(255,255,255,' . $layout_card_border_alpha . ');}' .
    '.va-card:hover{transform:translateY(-' . $layout_card_hover_lift . 'px);box-shadow:0 8px 30px rgba(0,0,0,' . ( $layout_card_shadow_strength / 100 ) . '),0 0 20px rgba(255,0,0,' . ( $layout_card_shadow_red / 100 ) . ');}' .
    '.va-card__body{padding:' . $layout_card_pad_y . 'px ' . $layout_card_pad_x . 'px;}' .
    '.va-card__title{font-size:' . $layout_card_title_size . 'px;}' .
    '.va-card__price{font-size:' . $layout_card_price_size . 'px;}' .
    '.va-card__meta{font-size:' . $layout_card_meta_size . 'px;}' .
    '.va-card__img-wrap{aspect-ratio:' . $layout_card_img_ratio . ';}' .
    '.va-sidebar__widget{border-radius:' . $layout_widget_radius . 'px;padding:' . $layout_widget_padding . 'px;}' .
    '.va-container h1,.va-container h2,.va-container h3,.va-container h4,.va-container h5,.va-container h6,.va-wrap h1,.va-wrap h2,.va-wrap h3,.va-wrap h4,.va-wrap h5,.va-wrap h6{' .
        'color:' . $content_headings . ';' .
    '}' .
    '.va-container a,.va-wrap a,.va-contact-page a,.va-cat-page a{color:' . $content_links . ';}' .
    '.va-footer,.va-footer *{font-family:' . $font_footer . ';}' .
    '.va-footer{' .
        'background-color:' . $footer_bg . ';' .
        'background-image:linear-gradient(140deg,' . $hf_footer_color_base . ',' . $hf_footer_color_alt . ');' .
        'color:' . $footer_text . ';' .
        'padding:' . $hf_footer_top_padding . 'px 20px ' . $hf_footer_bottom_padding . 'px;' .
        'border-top:1px solid ' . $hf_footer_border_color . ';' .
        'box-shadow:inset 0 12px 40px ' . $hf_footer_glow_color . ', inset 0 1px 0 ' . $hf_footer_shadow_color . ';' .
    '}' .
    '.va-footer__grid{' .
        'grid-template-columns:repeat(auto-fit,minmax(' . $hf_footer_col_min_width . 'px,1fr));' .
        'gap:' . $hf_footer_grid_gap . 'px;' .
        'max-width:' . $hf_footer_max_width . 'px;' .
    '}' .
    '.va-footer__col-title{margin-bottom:' . $hf_footer_title_gap . 'px;}' .
    '.va-footer__link{padding:' . $hf_footer_link_pad_y . 'px 0;}' .
    '.va-footer__bottom{' .
        'max-width:' . $hf_footer_max_width . 'px;' .
        'padding-top:' . $hf_footer_bottom_top_pad . 'px;' .
        'border-top:1px solid rgba(255,255,255,' . $hf_footer_bottom_border . ');' .
    '}' .
    '.va-footer__col-title{color:' . $footer_headings . ';}' .
    '.va-footer__link,.va-footer__bottom a{color:' . $footer_links . ';}' .
    '.va-footer__link:hover,.va-footer__bottom a:hover{color:' . $hf_footer_link_hover_color . ';}';

    // Hero badge + gomb színek
    $hero_badge_bg          = va_design_css_color( (string) get_option( 'va_color_hero_badge_bg',          'rgba(6,6,6,.56)' ),          'rgba(6,6,6,.56)' );
    $hero_badge_border      = va_design_css_color( (string) get_option( 'va_color_hero_badge_border',      'rgba(255,0,0,.55)' ),        'rgba(255,0,0,.55)' );
    $hero_badge_text        = va_design_css_color( (string) get_option( 'va_color_hero_badge_text',        '#ffffff' ),                  '#ffffff' );
    $hero_title_color       = va_design_css_color( (string) get_option( 'va_color_hero_title',             '#ffffff' ),                  '#ffffff' );
    $hero_sub_color         = va_design_css_color( (string) get_option( 'va_color_hero_sub',               'rgba(255,255,255,.80)' ),    'rgba(255,255,255,.80)' );
    $hero_btn_primary_bg    = va_design_css_color( (string) get_option( 'va_color_hero_btn_primary_bg',    '#ff0000' ),                  '#ff0000' );
    $hero_btn_primary_hover = va_design_css_color( (string) get_option( 'va_color_hero_btn_primary_hover', '#cc0000' ),                  '#cc0000' );
    $hero_btn_primary_text  = va_design_css_color( (string) get_option( 'va_color_hero_btn_primary_text',  '#ffffff' ),                  '#ffffff' );
    $hero_btn_primary_glow  = va_design_css_color( (string) get_option( 'va_color_hero_btn_primary_glow',  'rgba(255,0,0,.45)' ),        'rgba(255,0,0,.45)' );
    $hero_btn_ghost_bg      = va_design_css_color( (string) get_option( 'va_color_hero_btn_ghost_bg',      'rgba(255,255,255,.08)' ),    'rgba(255,255,255,.08)' );
    $hero_btn_ghost_border  = va_design_css_color( (string) get_option( 'va_color_hero_btn_ghost_border',  'rgba(255,255,255,.22)' ),    'rgba(255,255,255,.22)' );
    $hero_btn_ghost_hover   = va_design_css_color( (string) get_option( 'va_color_hero_btn_ghost_hover',   'rgba(255,255,255,.15)' ),    'rgba(255,255,255,.15)' );
    $hero_btn_ghost_text    = va_design_css_color( (string) get_option( 'va_color_hero_btn_ghost_text',    '#ffffff' ),                  '#ffffff' );
    $header_submit_hover_bg   = va_design_css_color( (string) get_option( 'va_color_header_submit_hover_bg',   '#cc0000' ),              '#cc0000' );
    $header_submit_hover_text = va_design_css_color( (string) get_option( 'va_color_header_submit_hover_text', '#ffffff' ),              '#ffffff' );
    $header_nav_link          = va_design_css_color( (string) get_option( 'va_color_header_nav_link',          '#ffffff' ),              '#ffffff' );
    $header_nav_hover         = va_design_css_color( (string) get_option( 'va_color_header_nav_hover',         '#ff2020' ),              '#ff2020' );
    $header_login_hover_bg    = va_design_css_color( (string) get_option( 'va_color_header_login_hover_bg',    'rgba(255,255,255,.08)' ), 'rgba(255,255,255,.08)' );
    $header_login_hover_text  = va_design_css_color( (string) get_option( 'va_color_header_login_hover_text',  '#ffffff' ),              '#ffffff' );
    $header_register_hover_bg = va_design_css_color( (string) get_option( 'va_color_header_register_hover_bg', '#cc0000' ),              '#cc0000' );
    $header_register_hover_text = va_design_css_color( (string) get_option( 'va_color_header_register_hover_text', '#ffffff' ),          '#ffffff' );

    // Hero méretek – összes oldal
    $css .= '.vh__badge{font-size:' . $home_hero_badge_css . ' !important;background:' . $hero_badge_bg . ' !important;border-color:' . $hero_badge_border . ' !important;color:' . $hero_badge_text . ' !important;}' .
    '.vh__title{font-size:' . $home_hero_title_css . ' !important;line-height:' . $lh_home_hero_title . ' !important;color:' . $hero_title_color . ' !important;}' .
    '.vh__sub{font-size:' . $home_hero_sub_css . ' !important;line-height:' . $lh_home_hero_sub . ' !important;color:' . $hero_sub_color . ' !important;}' .
    '.vh__btn{font-size:' . $home_hero_btn_css . ' !important;}' .
    '.vh__btn--primary{background:' . $hero_btn_primary_bg . ' !important;color:' . $hero_btn_primary_text . ' !important;box-shadow:0 0 28px ' . $hero_btn_primary_glow . ' !important;}' .
    '.vh__btn--primary:hover{background:' . $hero_btn_primary_hover . ' !important;}' .
    '.vh__btn--ghost{background:' . $hero_btn_ghost_bg . ' !important;border-color:' . $hero_btn_ghost_border . ' !important;color:' . $hero_btn_ghost_text . ' !important;}' .
    '.vh__btn--ghost:hover{background:' . $hero_btn_ghost_hover . ' !important;}' .
    '.va-nav__item--accent:hover,.va-header__submit-btn:hover{background-color:' . $header_submit_hover_bg . ' !important;color:' . $header_submit_hover_text . ' !important;}' .
    '.va-nav__item a,.va-nav__item button{color:' . $header_nav_link . ' !important;}' .
    '.va-nav__item a:hover,.va-nav__item button:hover{color:' . $header_nav_hover . ' !important;}' .
    '.va-header__user-login:hover{background:' . $header_login_hover_bg . ' !important;color:' . $header_login_hover_text . ' !important;}' .
    '.va-header__register-btn:hover{background:' . $header_register_hover_bg . ' !important;color:' . $header_register_hover_text . ' !important;}' .

    '.vcp-hero__badge{font-size:' . $kat_hero_badge_css . ' !important;}' .
    '.vcp-hero__title{font-size:' . $kat_hero_title_css . ' !important;line-height:' . $lh_kat_hero_title . ' !important;}' .
    '.vcp-hero__sub{font-size:' . $kat_hero_sub_css . ' !important;line-height:' . $lh_kat_hero_sub . ' !important;}' .
    '.vcp-hero__stat-n{font-size:' . $kat_hero_stat_num_css . ' !important;}' .
    '.vcp-hero__stat-l{font-size:' . $kat_hero_stat_lbl_css . ' !important;}' .

    '.vcp-video__eyebrow{font-size:' . $tax_hero_badge_css . ' !important;}' .
    '.vcp-video__title{font-size:' . $tax_hero_title_css . ' !important;line-height:' . $lh_tax_hero_title . ' !important;}' .
    '.vcp-video__lead{font-size:' . $tax_hero_lead_css . ' !important;line-height:' . $lh_tax_hero_lead . ' !important;}' .
    '.va-archive-header__count{font-size:' . $tax_hero_count_css . ' !important;}' .

    '.va-contact-page__eyebrow{font-size:' . $contact_hero_badge_css . ' !important;}' .
    '.va-contact-page__title{font-size:' . $contact_hero_title_css . ' !important;line-height:' . $lh_contact_hero_title . ' !important;}' .
    '.va-contact-page__lead{font-size:' . $contact_hero_lead_css . ' !important;line-height:' . $lh_contact_hero_lead . ' !important;}' .

    // Fejléc elemek méretek/típusok
    '.va-logo__text{font-size:' . $header_brand_css . ' !important;font-weight:' . $header_brand_weight . ' !important;}' .
    '.va-nav__item{font-size:' . $header_nav_css . ' !important;font-weight:' . $header_nav_weight . ' !important;}' .
    '.va-header__search-input{font-size:' . $header_search_css . ' !important;}' .
    '.va-header__submit-btn,.va-header__user,.va-header__user-login{font-size:' . $header_btn_css . ' !important;}' .

    // Lábléc elemek méretek/típusok
    '.va-footer__col-title{font-size:' . $footer_title_css . ' !important;font-weight:' . $footer_title_weight . ' !important;}' .
    '.va-footer__link{font-size:' . $footer_link_css . ' !important;font-weight:' . $footer_link_weight . ' !important;}' .
    '.va-footer__bottom{font-size:' . $footer_bottom_css . ' !important;}' .
    '@media (max-width:960px){' .
        '.va-header__search{display:' . ( $hf_mobile_show_search ? 'flex' : 'none' ) . ' !important;}' .
        '.va-header__submit-btn{display:' . ( $hf_mobile_show_submit ? 'inline-flex' : 'none' ) . ' !important;}' .
    '}' ;

    // Hero dekoratív elemek: bal csík, badge-dot, scroll jelző – accent színnel
    $css .=
    '.vh__overlay::before{background:linear-gradient(to bottom,transparent,' . $global_accent . ' 40%,' . $global_accent . ' 60%,transparent) !important;}' .
    '.vcp-hero__badge-dot{background:' . $global_accent . ' !important;}' .
    '.vh__scroll-line{background:linear-gradient(to bottom,transparent,' . $global_accent . ') !important;}' .
    '.vh__scroll-dot{background:' . $global_accent . ' !important;box-shadow:0 0 6px ' . $global_accent . ' !important;}';

    wp_add_inline_style( 'va-theme', $css );
}, 20 );

/* ── Alapoldalak automatikus létrehozása (egyszer fut) ── */
add_action( 'wp_loaded', function () {
    if ( get_option( 'va_pages_created_v4' ) ) return;
    $pages = [
        'kategoria'           => 'Kategóriák',
        'kapcsolat'           => 'Kapcsolat',
        'va-hirdetes-kereses' => 'Hirdetés keresés',
        'va-hirdetes-feladas' => 'Hirdetés feladás',
        'va-bejelentkezes'    => 'Bejelentkezés',
        'va-regisztracio'     => 'Regisztráció',
        'va-fiok'             => 'Fiókom',
    ];
    if ( function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : true ) {
        $pages['va-aukciok'] = 'Aukciók';
    }
    foreach ( $pages as $slug => $title ) {
        if ( ! get_page_by_path( $slug ) ) {
            wp_insert_post( [
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ] );
        }
    }
    update_option( 'va_pages_created_v4', '1' );
} );

/* ── Kapcsolat űrlap – email küldés wp_mail + SMTP ───────── */
add_action( 'admin_post_nopriv_va_contact_form', 'va_handle_contact_form' );
add_action( 'admin_post_va_contact_form', 'va_handle_contact_form' );

function va_handle_contact_form(): void {
    $redirect = wp_get_referer();
    if ( ! $redirect ) {
        $contact_page = get_page_by_path( 'kapcsolat' );
        $redirect = $contact_page ? get_permalink( $contact_page ) : home_url( '/kapcsolat/' );
    }

    if ( strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' ) {
        wp_safe_redirect( $redirect );
        exit;
    }

    if ( ! empty( $_POST['va_company'] ) ) {
        wp_safe_redirect( add_query_arg( 'contact_status', 'ok', $redirect ) );
        exit;
    }

    if ( ! isset( $_POST['va_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['va_contact_nonce'] ) ), 'va_contact_form' ) ) {
        wp_safe_redirect( add_query_arg( 'contact_status', 'nonce', $redirect ) );
        exit;
    }

    $name    = sanitize_text_field( wp_unslash( $_POST['va_name'] ?? '' ) );
    $email   = sanitize_email( wp_unslash( $_POST['va_email'] ?? '' ) );
    $phone   = sanitize_text_field( wp_unslash( $_POST['va_phone'] ?? '' ) );
    $subject = sanitize_text_field( wp_unslash( $_POST['va_subject'] ?? '' ) );
    $message = trim( (string) wp_unslash( $_POST['va_message'] ?? '' ) );

    if ( $name === '' || ! is_email( $email ) || $phone === '' || $subject === '' || $message === '' ) {
        wp_safe_redirect( add_query_arg( 'contact_status', 'invalid', $redirect ) );
        exit;
    }

    $recipient = get_option( 'admin_email' );
    $site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
    $mail_subject = '[' . $site_name . '] Kapcsolati üzenet: ' . $subject;
    $mail_body = "Új kapcsolatfelvételi üzenet érkezett a weboldalról.\n\n"
        . "Név: {$name}\n"
        . "E-mail: {$email}\n"
        . "Telefonszám: {$phone}\n"
        . "Tárgy: {$subject}\n\n"
        . "Üzenet:\n"
        . wp_strip_all_tags( $message ) . "\n";

    $headers = [
        'Reply-To: ' . $name . ' <' . $email . '>',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    $sent = wp_mail( $recipient, $mail_subject, $mail_body, $headers );

    wp_safe_redirect( add_query_arg( 'contact_status', $sent ? 'ok' : 'error', $redirect ) );
    exit;
}

/* ── Custom login/register page átirányítás ──────── */
add_action( 'template_redirect', function () {
    if ( is_page() && get_option( 'va_maintenance_mode', '0' ) === '1' && ! current_user_can( 'administrator' ) ) {
        wp_die( esc_html( get_option( 'va_maintenance_msg', 'Karbantartás alatt.' ) ), 'Karbantartás', 503 );
    }
});

/* ── Breadcrumb ──────────────────────────────────── */
function va_breadcrumb(): void {
    echo '<nav class="va-breadcrumb" aria-label="Breadcrumb"><ul>';
    echo '<li><a href="' . esc_url( home_url() ) . '">Főoldal</a></li>';
    if ( is_singular( 'va_listing' ) ) {
        echo '<li><a href="' . esc_url( get_post_type_archive_link( 'va_listing' ) ) . '">Hirdetések</a></li>';
        echo '<li>' . esc_html( get_the_title() ) . '</li>';
    } elseif ( ( function_exists( 'va_auctions_enabled' ) ? va_auctions_enabled() : true ) && is_singular( 'va_auction' ) ) {
        echo '<li><a href="' . esc_url( get_post_type_archive_link( 'va_auction' ) ) . '">Aukciók</a></li>';
        echo '<li>' . esc_html( get_the_title() ) . '</li>';
    } elseif ( is_page() ) {
        echo '<li>' . esc_html( get_the_title() ) . '</li>';
    }
    echo '</ul></nav>';
}

/* ── Kategória emoji ikonok ───────────────────────── */
function va_category_icon( int $term_id ): string {
    $svg = [
        // Fegyverek
        'golyos-puska'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 14h13l2-4h2l1 2v2h-1M3 14v2h2v-2M7 16a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0"/><path d="M16 10V8h2"/></svg>',
        'soretes-puska'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2 13h14l3-5h2v5h-2M2 13v3h3v-3M6 16a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0"/></svg>',
        'vegyescsovu'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2 12h14l3-4h2v4h-2M2 12v3h3v-3M6 15a1.5 1.5 0 1 0 3 0 1.5 1.5 0 0 0-3 0"/><line x1="16" y1="10" x2="16" y2="14"/></svg>',
        'maroklofegyver'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 4h8l2 4H6zM6 8v8h3v-3h5v3h2V8"/><path d="M9 13v3M14 4V2"/></svg>',
        'egyeb-fegyver'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="3"/><path d="M12 2v4M12 18v4M2 12h4M18 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M5.6 18.4l2.8-2.8M15.6 8.4l2.8-2.8"/></svg>',
        'loszer'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="9" y="8" width="6" height="12" rx="1"/><path d="M10 8V5a2 2 0 0 1 4 0v3"/><line x1="12" y1="12" x2="12" y2="16"/></svg>',
        'kesek'           => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 20 L18 4 L20 6 L10 20 Z"/><path d="M6 20 L9 17"/></svg>',
        // Optika
        'tavcsovek'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="7"/><path d="M16 16 L22 22" stroke-linecap="round"/></svg>',
        'ejjellato'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="7" cy="12" r="4"/><circle cx="17" cy="12" r="4"/><line x1="11" y1="12" x2="13" y2="12"/><path d="M3 12H1M23 12h-2M7 6V4M17 6V4"/></svg>',
        'hokamera'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="6" width="16" height="12" rx="2"/><path d="M18 9l4-2v10l-4-2"/><circle cx="10" cy="12" r="3"/></svg>',
        'vadkamera'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="7" width="20" height="14" rx="2"/><circle cx="12" cy="14" r="4"/><path d="M16 3l-4 4-4-4"/></svg>',
        'vadaszlampa'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 2h6l2 8H7z"/><rect x="7" y="10" width="10" height="3" rx="1"/><line x1="12" y1="13" x2="12" y2="22"/><line x1="8" y1="22" x2="16" y2="22"/></svg>',
        // Ruházat
        'vadasz-ruhazat'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M20 4L16 6 12 4 8 6 4 4v14h16z"/><path d="M8 6v14M16 6v14"/></svg>',
        'cipo-bakancs'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M2 18h14l4-6H8L5 7H2z"/><line x1="2" y1="18" x2="20" y2="18"/></svg>',
        'egyeb-ruhazat'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 3C10 3 8 5 8 7H4l2 4h12l2-4h-4c0-2-2-4-4-4z"/><rect x="6" y="11" width="12" height="10" rx="1"/></svg>',
        // Felszerelés
        'vadasz-felsz'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 20V10l8-8 8 8v10H4z"/><rect x="9" y="14" width="6" height="6"/></svg>',
        'sportlovo-felsz' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="8" r="4"/><path d="M8 14l-2 8h12l-2-8"/></svg>',
        // Kiegészítők
        'trofea'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 4h14v8a7 7 0 0 1-14 0V4z"/><path d="M5 6H2v4a3 3 0 0 0 3 3M19 6h3v4a3 3 0 0 1-3 3"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="8" y1="22" x2="16" y2="22"/></svg>',
        'vadasz-kutya'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 12c0-4 2-7 6-7h4c3 0 5 2 6 5l2 4-4 1-1 4H10l-1-4-3-1z"/><circle cx="15" cy="9" r="1" fill="currentColor"/></svg>',
        'vadasz-lehetoseg'=> '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
        'vadkarelharias'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke-linejoin="round"/></svg>',
        'szallas'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 22V8l9-6 9 6v14H3z"/><rect x="9" y="14" width="6" height="8"/></svg>',
        'ingatlan'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="9" width="18" height="13" rx="1"/><path d="M1 9l11-7 11 7"/><line x1="9" y1="22" x2="9" y2="13"/><line x1="15" y1="22" x2="15" y2="13"/></svg>',
        'jarmu'           => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 17H3V9l3-5h10l3 5v8h-2"/><circle cx="7.5" cy="17" r="2"/><circle cx="16.5" cy="17" r="2"/><line x1="9.5" y1="17" x2="14.5" y2="17"/></svg>',
        'takarmany'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2C8 2 5 6 5 10c0 5 3 8 7 10 4-2 7-5 7-10 0-4-3-8-7-8z"/><line x1="12" y1="6" x2="12" y2="14"/><line x1="9" y1="9" x2="15" y2="9"/></svg>',
        'konyv'           => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 19V5a2 2 0 0 1 2-2h14"/><path d="M20 19H6a2 2 0 0 0 0 4h14V3"/></svg>',
        'disztargy'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg>',
        'kurtok-sipok'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>',
        'szolgaltatas'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 1 1-14.14 0"/></svg>',
        'allas'           => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>',
        'csere'           => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M7 16V4m0 0L3 8m4-4l4 4"/><path d="M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>',
        'hagyatek'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>',
        'egyeb'           => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="1"/><circle cx="6" cy="12" r="1"/><circle cx="18" cy="12" r="1"/></svg>',
    ];

    $term = get_term( $term_id, 'va_category' );
    if ( is_wp_error( $term ) || ! $term ) {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="1"/><circle cx="6" cy="12" r="1"/><circle cx="18" cy="12" r="1"/></svg>';
    }
    return $svg[ $term->slug ] ?? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="1"/><circle cx="6" cy="12" r="1"/><circle cx="18" cy="12" r="1"/></svg>';
}
