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

    $target_file = trailingslashit( $subdir ) . $attachment_id . '-' . $size . '.png';
    $target_url  = trailingslashit( $upload['baseurl'] ) . 'va-favicons/' . $attachment_id . '-' . $size . '.png';

    if ( file_exists( $target_file ) ) {
        return $target_url;
    }

    $src = get_attached_file( $attachment_id );
    if ( ! $src || ! file_exists( $src ) ) {
        return '';
    }

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
    wp_enqueue_style( 'va-theme', get_stylesheet_uri(), [], '3.0.2' );

    // Egységes kártya/stílus az egész oldalon (archívum, kereső, kategória stb.)
    if ( defined( 'VA_PLUGIN_URL' ) ) {
        wp_enqueue_style( 'va-frontend', VA_PLUGIN_URL . 'frontend/css/frontend.css', [ 'va-theme' ], VA_VERSION );
    }
});

function va_design_font_map(): array {
    return [
        'system'        => [
            'stack'  => '-apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
            'google' => '',
        ],
        'inter'         => [ 'stack' => '"Inter", sans-serif', 'google' => 'Inter:wght@400;500;600;700;800;900' ],
        'roboto'        => [ 'stack' => '"Roboto", sans-serif', 'google' => 'Roboto:wght@400;500;700;900' ],
        'montserrat'    => [ 'stack' => '"Montserrat", sans-serif', 'google' => 'Montserrat:wght@400;500;600;700;800;900' ],
        'oswald'        => [ 'stack' => '"Oswald", sans-serif', 'google' => 'Oswald:wght@400;500;600;700' ],
        'merriweather'  => [ 'stack' => '"Merriweather", serif', 'google' => 'Merriweather:wght@400;700;900' ],
        'playfair'      => [ 'stack' => '"Playfair Display", serif', 'google' => 'Playfair+Display:wght@400;600;700;800;900' ],
        'lora'          => [ 'stack' => '"Lora", serif', 'google' => 'Lora:wght@400;500;600;700' ],
        'nunito'        => [ 'stack' => '"Nunito", sans-serif', 'google' => 'Nunito:wght@400;500;600;700;800;900' ],
        'source-sans-3' => [ 'stack' => '"Source Sans 3", sans-serif', 'google' => 'Source+Sans+3:wght@400;500;600;700;800;900' ],
        'pt-sans'       => [ 'stack' => '"PT Sans", sans-serif', 'google' => 'PT+Sans:wght@400;700' ],
        'raleway'       => [ 'stack' => '"Raleway", sans-serif', 'google' => 'Raleway:wght@400;500;600;700;800;900' ],
        'bebas-neue'    => [ 'stack' => '"Bebas Neue", sans-serif', 'google' => 'Bebas+Neue' ],
        'rubik'         => [ 'stack' => '"Rubik", sans-serif', 'google' => 'Rubik:wght@400;500;700;900' ],
        'dm-sans'       => [ 'stack' => '"DM Sans", sans-serif', 'google' => 'DM+Sans:wght@400;500;700;900' ],
        'work-sans'     => [ 'stack' => '"Work Sans", sans-serif', 'google' => 'Work+Sans:wght@400;500;600;700;800;900' ],
        'manrope'       => [ 'stack' => '"Manrope", sans-serif', 'google' => 'Manrope:wght@400;500;600;700;800' ],
        'fira-sans'     => [ 'stack' => '"Fira Sans", sans-serif', 'google' => 'Fira+Sans:wght@400;500;600;700;800;900' ],
        'ibm-plex-sans' => [ 'stack' => '"IBM Plex Sans", sans-serif', 'google' => 'IBM+Plex+Sans:wght@400;500;600;700' ],
        'noto-sans'     => [ 'stack' => '"Noto Sans", sans-serif', 'google' => 'Noto+Sans:wght@400;500;600;700;800;900' ],
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

add_action( 'wp_enqueue_scripts', function () {
    $font_keys = [
        'va_font_global',
        'va_font_headings',
        'va_font_header',
        'va_font_content',
        'va_font_footer',
    ];

    $font_map = va_design_font_map();
    $google_families = [];

    foreach ( $font_keys as $key ) {
        $slug = sanitize_key( (string) get_option( $key, 'system' ) );
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

    $css = ':root{' .
        '--a:' . esc_attr( $global_accent ) . ';' .
        '--a2:' . esc_attr( $global_accent ) . ';' .
        '--a3:' . esc_attr( $global_accent ) . ';' .
        '--t:' . esc_attr( $global_text ) . ';' .
        '--t2:' . esc_attr( $global_muted ) . ';' .
    '}' .
    'body{' .
        'font-family:' . esc_attr( $font_global ) . ';' .
        'background:' . esc_attr( $global_bg ) . ';' .
        'color:' . esc_attr( $global_text ) . ';' .
    '}' .
    'h1,h2,h3,h4,h5,h6{font-family:' . esc_attr( $font_headings ) . ';}' .
    '.va-header,.va-header *{' .
        'font-family:' . esc_attr( $font_header ) . ';' .
        'color:' . esc_attr( $header_text ) . ';' .
    '}' .
    '.va-header{background:' . esc_attr( $header_bg ) . ';border-bottom-color:' . esc_attr( $header_accent ) . ';}' .
    '.va-nav__item--accent,.va-header__submit-btn,.va-header__search-btn{background-color:' . esc_attr( $header_accent ) . ';border-color:' . esc_attr( $header_accent ) . ';}' .
    '.va-container,.va-content-layout,.va-main-content,.va-wrap,.va-cat-page,.va-contact-page{' .
        'background-color:' . esc_attr( $content_bg ) . ';' .
        'color:' . esc_attr( $content_text ) . ';' .
        'font-family:' . esc_attr( $font_content ) . ';' .
    '}' .
    '.va-container h1,.va-container h2,.va-container h3,.va-container h4,.va-container h5,.va-container h6,.va-wrap h1,.va-wrap h2,.va-wrap h3,.va-wrap h4,.va-wrap h5,.va-wrap h6{' .
        'color:' . esc_attr( $content_headings ) . ';' .
    '}' .
    '.va-container a,.va-wrap a,.va-contact-page a,.va-cat-page a{color:' . esc_attr( $content_links ) . ';}' .
    '.va-footer,.va-footer *{font-family:' . esc_attr( $font_footer ) . ';}' .
    '.va-footer{background:' . esc_attr( $footer_bg ) . ';color:' . esc_attr( $footer_text ) . ';}' .
    '.va-footer__col-title{color:' . esc_attr( $footer_headings ) . ';}' .
    '.va-footer__link,.va-footer__bottom a{color:' . esc_attr( $footer_links ) . ';}' ;

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
