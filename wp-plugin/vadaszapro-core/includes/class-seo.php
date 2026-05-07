<?php
/**
 * Központi SEO réteg:
 * - Meta leírás, canonical, OG, Twitter
 * - JSON-LD schema (WebSite, Organization, Breadcrumb, ItemList, Product/Auction)
 * - XML sitemap index + rész-sitemapok
 * - robots.txt + noindex vezérlés kereső/404 oldalakra
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_SEO {

    const SITEMAP_PER_PAGE = 100;

    public static function init(): void {
        if ( is_admin() ) {
            add_filter( 'robots_txt', [ __CLASS__, 'filter_robots_txt' ], 10, 2 );
            return;
        }

        add_action( 'init', [ __CLASS__, 'register_sitemap_routes' ] );
        add_filter( 'query_vars', [ __CLASS__, 'register_query_vars' ] );
        add_action( 'template_redirect', [ __CLASS__, 'maybe_render_sitemap' ], 0 );

        add_action( 'wp_head', [ __CLASS__, 'render_head_meta' ], 1 );
        add_action( 'wp_head', [ __CLASS__, 'render_schema' ], 90 );

        add_filter( 'wp_robots', [ __CLASS__, 'filter_wp_robots' ] );
        add_filter( 'robots_txt', [ __CLASS__, 'filter_robots_txt' ], 10, 2 );

        // Rank Math felülírhatja az OG/Twitter title-t, ezért közvetlenül ide is bekötjük.
        add_filter( 'rank_math/opengraph/facebook/title', [ __CLASS__, 'rank_math_social_title' ] );
        add_filter( 'rank_math/opengraph/twitter/title', [ __CLASS__, 'rank_math_social_title' ] );
        add_filter( 'rank_math/opengraph/facebook/description', [ __CLASS__, 'rank_math_social_description' ] );
        add_filter( 'rank_math/opengraph/twitter/description', [ __CLASS__, 'rank_math_social_description' ] );
    }

    public static function rank_math_social_title( $title ): string {
        return self::social_title( is_string( $title ) ? $title : wp_get_document_title() );
    }

    public static function rank_math_social_description( $description ): string {
        if ( is_singular( 'va_listing' ) ) {
            return self::meta_description();
        }
        return is_string( $description ) && $description !== '' ? $description : self::meta_description();
    }

    public static function register_sitemap_routes(): void {
        add_rewrite_rule( '^sitemap\.xml$', 'index.php?va_sitemap=index', 'top' );
        add_rewrite_rule( '^sitemap-([a-z0-9_-]+)-([0-9]+)\.xml$', 'index.php?va_sitemap=post&va_sitemap_type=$matches[1]&va_sitemap_page=$matches[2]', 'top' );
        add_rewrite_rule( '^sitemap-tax-([a-z0-9_-]+)-([0-9]+)\.xml$', 'index.php?va_sitemap=tax&va_sitemap_type=$matches[1]&va_sitemap_page=$matches[2]', 'top' );
    }

    public static function register_query_vars( array $vars ): array {
        $vars[] = 'va_sitemap';
        $vars[] = 'va_sitemap_type';
        $vars[] = 'va_sitemap_page';
        return $vars;
    }

    public static function maybe_render_sitemap(): void {
        $mode = (string) get_query_var( 'va_sitemap' );
        if ( $mode === '' ) return;

        if ( $mode === 'index' ) {
            self::render_sitemap_index();
        }

        $type = sanitize_key( (string) get_query_var( 'va_sitemap_type' ) );
        $page = max( 1, (int) get_query_var( 'va_sitemap_page' ) );

        if ( $mode === 'post' && $type !== '' ) {
            self::render_posttype_sitemap( $type, $page );
        }

        if ( $mode === 'tax' && $type !== '' ) {
            self::render_taxonomy_sitemap( $type, $page );
        }

        status_header( 404 );
        nocache_headers();
        exit;
    }

    private static function xml_header(): void {
        status_header( 200 );
        header( 'Content-Type: application/xml; charset=UTF-8' );
        nocache_headers();
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    }

    private static function public_post_types(): array {
        $types = get_post_types( [ 'public' => true ], 'names' );
        $types = array_values( array_filter( $types, static function( $pt ) {
            return $pt !== 'attachment';
        } ) );

        $priority = [ 'va_listing', 'va_auction', 'page', 'post' ];
        usort( $types, static function( $a, $b ) use ( $priority ) {
            $pa = array_search( $a, $priority, true );
            $pb = array_search( $b, $priority, true );
            $pa = $pa === false ? 99 : $pa;
            $pb = $pb === false ? 99 : $pb;
            return $pa <=> $pb;
        } );

        return $types;
    }

    private static function public_taxonomies(): array {
        return array_values( get_taxonomies( [ 'public' => true ], 'names' ) );
    }

    private static function post_type_page_count( string $post_type ): int {
        $counts = wp_count_posts( $post_type );
        $published = isset( $counts->publish ) ? (int) $counts->publish : 0;
        if ( $published <= 0 ) return 0;
        return (int) ceil( $published / self::SITEMAP_PER_PAGE );
    }

    private static function taxonomy_page_count( string $taxonomy ): int {
        $count = (int) wp_count_terms( [
            'taxonomy'   => $taxonomy,
            'hide_empty' => true,
        ] );
        if ( $count <= 0 ) return 0;
        return (int) ceil( $count / self::SITEMAP_PER_PAGE );
    }

    private static function sitemap_index_url_for_post_type( string $post_type, int $page ): string {
        return home_url( '/sitemap-' . rawurlencode( $post_type ) . '-' . $page . '.xml' );
    }

    private static function sitemap_index_url_for_taxonomy( string $taxonomy, int $page ): string {
        return home_url( '/sitemap-tax-' . rawurlencode( $taxonomy ) . '-' . $page . '.xml' );
    }

    private static function lastmod_for_post_type( string $post_type ): string {
        global $wpdb;
        $val = $wpdb->get_var( $wpdb->prepare(
            "SELECT post_modified_gmt FROM {$wpdb->posts}
             WHERE post_type = %s AND post_status = 'publish'
             ORDER BY post_modified_gmt DESC LIMIT 1",
            $post_type
        ) );

        if ( ! $val ) return gmdate( 'c' );
        return gmdate( 'c', strtotime( (string) $val . ' UTC' ) );
    }

    public static function render_sitemap_index(): void {
        self::xml_header();
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ( self::public_post_types() as $pt ) {
            $pages = self::post_type_page_count( $pt );
            if ( $pages < 1 ) continue;
            $lastmod = self::lastmod_for_post_type( $pt );
            for ( $i = 1; $i <= $pages; $i++ ) {
                echo '<sitemap>';
                echo '<loc>' . esc_url( self::sitemap_index_url_for_post_type( $pt, $i ) ) . '</loc>';
                echo '<lastmod>' . esc_html( $lastmod ) . '</lastmod>';
                echo '</sitemap>';
            }
        }

        foreach ( self::public_taxonomies() as $tax ) {
            $pages = self::taxonomy_page_count( $tax );
            if ( $pages < 1 ) continue;
            $lastmod = gmdate( 'c' );
            for ( $i = 1; $i <= $pages; $i++ ) {
                echo '<sitemap>';
                echo '<loc>' . esc_url( self::sitemap_index_url_for_taxonomy( $tax, $i ) ) . '</loc>';
                echo '<lastmod>' . esc_html( $lastmod ) . '</lastmod>';
                echo '</sitemap>';
            }
        }

        echo '</sitemapindex>';
        exit;
    }

    public static function render_posttype_sitemap( string $post_type, int $page ): void {
        if ( ! post_type_exists( $post_type ) ) {
            status_header( 404 );
            exit;
        }

        $q = new WP_Query( [
            'post_type'              => $post_type,
            'post_status'            => 'publish',
            'posts_per_page'         => self::SITEMAP_PER_PAGE,
            'paged'                  => $page,
            'orderby'                => 'modified',
            'order'                  => 'DESC',
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ] );

        self::xml_header();
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ( $q->posts as $post ) {
            $loc = get_permalink( $post );
            if ( ! $loc ) continue;
            $mod_gmt = get_post_modified_time( 'U', true, $post );
            echo '<url>';
            echo '<loc>' . esc_url( $loc ) . '</loc>';
            if ( $mod_gmt ) {
                echo '<lastmod>' . esc_html( gmdate( 'c', (int) $mod_gmt ) ) . '</lastmod>';
            }
            echo '</url>';
        }
        echo '</urlset>';
        wp_reset_postdata();
        exit;
    }

    public static function render_taxonomy_sitemap( string $taxonomy, int $page ): void {
        if ( ! taxonomy_exists( $taxonomy ) ) {
            status_header( 404 );
            exit;
        }

        $terms = get_terms( [
            'taxonomy'   => $taxonomy,
            'hide_empty' => true,
            'number'     => self::SITEMAP_PER_PAGE,
            'offset'     => ( $page - 1 ) * self::SITEMAP_PER_PAGE,
            'orderby'    => 'term_id',
            'order'      => 'ASC',
        ] );

        self::xml_header();
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $url = get_term_link( $term );
                if ( is_wp_error( $url ) ) continue;
                echo '<url>';
                echo '<loc>' . esc_url( $url ) . '</loc>';
                echo '<lastmod>' . esc_html( gmdate( 'c' ) ) . '</lastmod>';
                echo '</url>';
            }
        }
        echo '</urlset>';
        exit;
    }

    public static function filter_wp_robots( array $robots ): array {
        if ( is_search() || is_404() ) {
            $robots['noindex'] = true;
            $robots['nofollow'] = true;
        }
        return $robots;
    }

    public static function filter_robots_txt( string $output, bool $public ): string {
        $sitemap_line = "Sitemap: " . home_url( '/sitemap.xml' );
        if ( strpos( $output, $sitemap_line ) === false ) {
            $output = trim( $output ) . "\n" . $sitemap_line . "\n";
        }
        return $output;
    }

    private static function should_render_meta(): bool {
        if ( is_admin() ) return false;
        if ( is_feed() ) return false;
        if ( is_trackback() ) return false;
        return true;
    }

    private static function current_canonical(): string {
        if ( is_singular() ) {
            $url = get_permalink();
            return is_string( $url ) ? $url : home_url( '/' );
        }

        if ( is_home() ) {
            $page_for_posts = (int) get_option( 'page_for_posts' );
            if ( $page_for_posts > 0 ) {
                $url = get_permalink( $page_for_posts );
                if ( is_string( $url ) && $url !== '' ) return $url;
            }
        }

        if ( is_post_type_archive() || is_tax() || is_category() || is_tag() || is_author() || is_date() ) {
            $url = get_pagenum_link( 1 );
            return is_string( $url ) ? $url : home_url( '/' );
        }

        return home_url( add_query_arg( [], $GLOBALS['wp']->request ?? '' ) );
    }

    private static function fallback_image_url(): string {
        $logo_id = (int) get_theme_mod( 'custom_logo' );
        if ( $logo_id > 0 ) {
            $logo = wp_get_attachment_image_url( $logo_id, 'full' );
            if ( $logo ) return $logo;
        }

        if ( has_site_icon() ) {
            $icon = get_site_icon_url( 512 );
            if ( $icon ) return $icon;
        }

        return '';
    }

    private static function meta_image_url(): string {
        if ( is_singular() && has_post_thumbnail() ) {
            $img = get_the_post_thumbnail_url( get_queried_object_id(), 'full' );
            if ( $img ) return $img;
        }
        return self::fallback_image_url();
    }

    private static function meta_description(): string {
        if ( is_singular( 'va_listing' ) ) {
            $id = get_queried_object_id();
            $title = get_the_title( $id );
            $price = get_post_meta( $id, 'va_price', true );
            $price_txt = is_numeric( $price ) ? number_format( (float) $price, 0, ',', ' ' ) . ' Ft' : 'ár egyeztetéssel';
            $county = wp_get_post_terms( $id, 'va_county', [ 'fields' => 'names' ] );
            $county_txt = ! empty( $county[0] ) ? (string) $county[0] : 'Magyarország';

            $desc = $title . ' - ' . $price_txt . '. ' . $county_txt . ' területén elérhető vadászati apróhirdetés.';
            return wp_strip_all_tags( $desc );
        }

        if ( is_singular( 'va_auction' ) ) {
            $id = get_queried_object_id();
            $title = get_the_title( $id );
            $end = (int) get_post_meta( $id, 'va_auction_end', true );
            $end_txt = $end > 0 ? date_i18n( 'Y.m.d H:i', $end ) : 'hamarosan';
            return wp_strip_all_tags( $title . ' - VadászApró aukció. Lejárat: ' . $end_txt . '.' );
        }

        if ( is_singular() ) {
            $excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', get_queried_object_id() ) ), 28, '...' );
            return wp_strip_all_tags( $excerpt );
        }

        if ( is_post_type_archive( 'va_listing' ) ) {
            return 'Vadászati apróhirdetések országosan: fegyver, optika, felszerelés, jármű és kiegészítők. Friss hirdetések naponta.';
        }

        if ( is_post_type_archive( 'va_auction' ) ) {
            return 'Aktuális vadászati aukciók valós licittel, folyamatosan frissülő kínálattal és részletes termékadatokkal.';
        }

        if ( is_tax() || is_category() || is_tag() ) {
            $term = get_queried_object();
            if ( $term instanceof WP_Term ) {
                $d = term_description( $term );
                if ( is_string( $d ) && trim( wp_strip_all_tags( $d ) ) !== '' ) {
                    return wp_trim_words( wp_strip_all_tags( $d ), 28, '...' );
                }
                return $term->name . ' kategória vadászati hirdetések és ajánlatok.';
            }
        }

        return 'VadászApró - vadászati hirdetések, aukciók és felszerelések egy helyen.';
    }

    private static function listing_social_title( int $post_id ): string {
        $title = wp_strip_all_tags( (string) get_the_title( $post_id ) );
        $price_raw = get_post_meta( $post_id, 'va_price', true );
        $price_type = (string) get_post_meta( $post_id, 'va_price_type', true );
        $sale_raw = get_post_meta( $post_id, 'va_sale_price', true );
        $sale_end_raw = (string) get_post_meta( $post_id, 'va_sale_price_end', true );

        $base_price_txt = '';
        if ( is_numeric( $price_raw ) && $price_type !== 'ask' ) {
            $base_price_txt = number_format( (float) $price_raw, 0, ',', ' ' ) . ' Ft';
        }

        $sale_active = false;
        if ( is_numeric( $sale_raw ) && (float) $sale_raw > 0 ) {
            $sale_active = true;
            if ( $sale_end_raw !== '' ) {
                $sale_end_ts = strtotime( $sale_end_raw . ' 23:59:59' );
                if ( $sale_end_ts && $sale_end_ts < current_time( 'timestamp' ) ) {
                    $sale_active = false;
                }
            }
        }

        if ( $sale_active ) {
            $sale_price_txt = number_format( (float) $sale_raw, 0, ',', ' ' ) . ' Ft';
            if ( $base_price_txt !== '' ) {
                return $title . ' | Eladó | Akciós ár: ' . $sale_price_txt . ' (eredeti: ' . $base_price_txt . ')';
            }
            return $title . ' | Eladó | Akciós ár: ' . $sale_price_txt;
        }

        if ( $base_price_txt !== '' ) {
            return $title . ' | Eladó | Ár: ' . $base_price_txt;
        }

        return $title . ' | Eladó';
    }

    private static function social_title( string $default_title ): string {
        if ( is_singular( 'va_listing' ) ) {
            $id = get_queried_object_id();
            if ( $id > 0 ) {
                return self::listing_social_title( $id );
            }
        }

        return $default_title;
    }

    public static function render_head_meta(): void {
        if ( ! self::should_render_meta() ) return;

        $title = wp_get_document_title();
        $social_title = self::social_title( $title );
        $desc  = self::meta_description();
        $url   = self::current_canonical();
        $img   = self::meta_image_url();
        $site  = get_bloginfo( 'name' );
        $robots = ( is_search() || is_404() ) ? 'noindex, nofollow, max-image-preview:large' : 'index, follow, max-image-preview:large';

        echo "\n";
        echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
        echo '<meta name="robots" content="' . esc_attr( $robots ) . '">' . "\n";
        echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
        echo '<meta property="og:locale" content="hu_HU">' . "\n";
        echo '<meta property="og:type" content="' . esc_attr( is_singular() ? 'article' : 'website' ) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr( $social_title ) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr( $site ) . '">' . "\n";
        if ( $img !== '' ) {
            echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
        }
        echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr( $social_title ) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
        if ( $img !== '' ) {
            echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";
        }

        echo '<link rel="alternate" href="' . esc_url( $url ) . '" hreflang="hu-HU">' . "\n";
        echo '<link rel="alternate" href="' . esc_url( $url ) . '" hreflang="x-default">' . "\n";
    }

    private static function breadcrumb_graph(): array {
        $items = [];
        $pos = 1;
        $items[] = [
            '@type' => 'ListItem',
            'position' => $pos++,
            'name' => 'Főoldal',
            'item' => home_url( '/' ),
        ];

        if ( is_singular( 'va_listing' ) ) {
            $archive = get_post_type_archive_link( 'va_listing' );
            if ( $archive ) {
                $items[] = [ '@type' => 'ListItem', 'position' => $pos++, 'name' => 'Hirdetések', 'item' => $archive ];
            }
            $items[] = [ '@type' => 'ListItem', 'position' => $pos++, 'name' => get_the_title(), 'item' => get_permalink() ];
        } elseif ( is_singular( 'va_auction' ) ) {
            $archive = get_post_type_archive_link( 'va_auction' );
            if ( $archive ) {
                $items[] = [ '@type' => 'ListItem', 'position' => $pos++, 'name' => 'Aukciók', 'item' => $archive ];
            }
            $items[] = [ '@type' => 'ListItem', 'position' => $pos++, 'name' => get_the_title(), 'item' => get_permalink() ];
        } elseif ( is_tax() || is_category() || is_tag() ) {
            $term = get_queried_object();
            if ( $term instanceof WP_Term ) {
                $term_link = get_term_link( $term );
                if ( ! is_wp_error( $term_link ) ) {
                    $items[] = [ '@type' => 'ListItem', 'position' => $pos++, 'name' => $term->name, 'item' => $term_link ];
                }
            }
        } elseif ( is_singular() ) {
            $items[] = [ '@type' => 'ListItem', 'position' => $pos++, 'name' => get_the_title(), 'item' => get_permalink() ];
        }

        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    private static function itemlist_graph_for_archive(): ?array {
        if ( ! is_post_type_archive( 'va_listing' ) && ! is_tax( 'va_category' ) ) return null;

        $q = new WP_Query( [
            'post_type'              => 'va_listing',
            'post_status'            => 'publish',
            'posts_per_page'         => 10,
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ] );

        if ( empty( $q->posts ) ) return null;

        $elements = [];
        $i = 1;
        foreach ( $q->posts as $p ) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $i++,
                'url' => get_permalink( $p ),
                'name' => get_the_title( $p ),
            ];
        }
        wp_reset_postdata();

        return [
            '@type' => 'ItemList',
            'name' => 'Friss hirdetések',
            'itemListElement' => $elements,
        ];
    }

    private static function product_graph_for_listing(): ?array {
        if ( ! is_singular( 'va_listing' ) ) return null;

        $id = get_queried_object_id();
        $price = get_post_meta( $id, 'va_price', true );
        $price_type = (string) get_post_meta( $id, 'va_price_type', true );
        $image = get_the_post_thumbnail_url( $id, 'full' );

        $graph = [
            '@type' => 'Product',
            'name' => get_the_title( $id ),
            'url' => get_permalink( $id ),
            'description' => wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $id ) ), 40, '...' ),
        ];

        if ( $image ) {
            $graph['image'] = [ $image ];
        }

        if ( is_numeric( $price ) && $price_type !== 'ask' ) {
            $graph['offers'] = [
                '@type' => 'Offer',
                'priceCurrency' => 'HUF',
                'price' => (float) $price,
                'availability' => 'https://schema.org/InStock',
                'url' => get_permalink( $id ),
            ];
        }

        return $graph;
    }

    private static function auction_graph(): ?array {
        if ( ! is_singular( 'va_auction' ) ) return null;

        $id = get_queried_object_id();
        $start = (float) get_post_meta( $id, 'va_auction_start_price', true );
        $endts = (int) get_post_meta( $id, 'va_auction_end', true );
        $img = get_the_post_thumbnail_url( $id, 'full' );

        $g = [
            '@type' => 'Auction',
            'name' => get_the_title( $id ),
            'url' => get_permalink( $id ),
            'description' => wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $id ) ), 40, '...' ),
        ];

        if ( $img ) {
            $g['image'] = [ $img ];
        }

        if ( $start > 0 ) {
            $offer = [
                '@type' => 'Offer',
                'priceCurrency' => 'HUF',
                'price' => $start,
                'url' => get_permalink( $id ),
            ];
            if ( $endts > 0 ) {
                $offer['priceValidUntil'] = gmdate( 'c', $endts );
            }
            $g['offers'] = $offer;
        }

        return $g;
    }

    public static function render_schema(): void {
        if ( ! self::should_render_meta() ) return;

        $site_name = get_bloginfo( 'name' );
        $home = home_url( '/' );
        $logo = self::fallback_image_url();

        $graph = [];

        $website = [
            '@type' => 'WebSite',
            '@id' => $home . '#website',
            'url' => $home,
            'name' => $site_name,
            'inLanguage' => 'hu-HU',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => home_url( '/?s={search_term_string}' ),
                'query-input' => 'required name=search_term_string',
            ],
        ];
        $graph[] = $website;

        $org = [
            '@type' => 'Organization',
            '@id' => $home . '#organization',
            'name' => $site_name,
            'url' => $home,
        ];
        if ( $logo !== '' ) {
            $org['logo'] = [
                '@type' => 'ImageObject',
                'url' => $logo,
            ];
        }
        $graph[] = $org;

        $graph[] = self::breadcrumb_graph();

        $itemlist = self::itemlist_graph_for_archive();
        if ( $itemlist ) $graph[] = $itemlist;

        $product = self::product_graph_for_listing();
        if ( $product ) $graph[] = $product;

        $auction = self::auction_graph();
        if ( $auction ) $graph[] = $auction;

        $data = [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];

        echo "\n";
        echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
    }
}
