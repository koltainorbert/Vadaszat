<?php
/**
 * Taxonómiák: Kategória, Megye, Állapot (Hirdetés + Aukció)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VA_Taxonomy {

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_all' ] );
    }

    public static function register_all() {

        /* Kategória */
        register_taxonomy( 'va_category', [ 'va_listing', 'va_auction' ], [
            'labels'        => [
                'name'          => 'Kategóriák',
                'singular_name' => 'Kategória',
                'menu_name'     => 'Kategóriák',
            ],
            'hierarchical'  => true,
            'public'        => true,
            'rewrite'       => [ 'slug' => 'kategoria' ],
            'show_in_rest'  => true,
        ]);

        /* Megye */
        register_taxonomy( 'va_county', [ 'va_listing', 'va_auction' ], [
            'labels'        => [
                'name'          => 'Megyék',
                'singular_name' => 'Megye',
                'menu_name'     => 'Megyék',
            ],
            'hierarchical'  => false,
            'public'        => true,
            'rewrite'       => [ 'slug' => 'megye' ],
            'show_in_rest'  => true,
        ]);

        /* Állapot */
        register_taxonomy( 'va_condition', [ 'va_listing', 'va_auction' ], [
            'labels'        => [
                'name'          => 'Állapot',
                'singular_name' => 'Állapot',
                'menu_name'     => 'Állapot',
            ],
            'hierarchical'  => false,
            'public'        => true,
            'rewrite'       => [ 'slug' => 'allapot' ],
            'show_in_rest'  => true,
        ]);

        self::insert_default_terms();
    }

    private static function insert_default_terms() {

        /* Kategóriák */
        $categories = [
            'Fegyverek'              => [
                'Golyós puska',
                'Sörétes puska',
                'Vegyescsövű puska',
                'Maroklőfegyver',
                'Légfegyver',
                'Hatástalanított',
                'Egyéb fegyver',
            ],
            'Lőszer & Töltény'       => [
                'Golyós lőszer',
                'Sörétes lőszer',
                'Légpuska lőszer',
            ],
            'Optika & Elektronika'   => [
                'Céltávcsövek',
                'Éjjellátó',
                'Hőkamera',
                'Vadkamera',
                'Vadászlámpa',
            ],
            'Kések & Eszközök'       => [
                'Kések',
                'Kürtök & Sípok',
            ],
            'Ruházat'                => [
                'Vadász ruházat',
                'Cipő & Bakancs',
                'Egyéb ruházat',
            ],
            'Felszerelés'            => [
                'Vadász felszerelés',
                'Sportlövő felszerelés',
            ],
            'Trófea & Dísztárgy'     => [
                'Trófeák',
                'Dísztárgyak',
            ],
            'Vadászkutya'            => [],
            'Vadászterület & Bérlet' => [
                'Vadászati lehetőség',
                'Vadkárelhárítás',
            ],
            'Jármű'                  => [],
            'Ingatlan & Szállás'     => [
                'Ingatlan',
                'Szállás',
            ],
            'Egyéb'                  => [
                'Takarmány',
                'Könyv & Folyóirat',
                'Vadászati hagyaték',
                'Állás',
                'Csere',
                'Szolgáltatás',
            ],
        ];

        foreach ( $categories as $parent => $children ) {
            if ( ! term_exists( $parent, 'va_category' ) ) {
                $parent_term = wp_insert_term( $parent, 'va_category' );
                $parent_id   = is_wp_error( $parent_term ) ? 0 : $parent_term['term_id'];
            } else {
                $t         = get_term_by( 'name', $parent, 'va_category' );
                $parent_id = $t ? $t->term_id : 0;
            }

            foreach ( $children as $child ) {
                if ( ! term_exists( $child, 'va_category' ) ) {
                    wp_insert_term( $child, 'va_category', [ 'parent' => $parent_id ] );
                }
            }
        }

        /* Állapot */
        $conditions = [ 'Új', 'Használt – Kiváló', 'Használt – Jó', 'Használt – Közepes', 'Sérült / Alkatrésznek' ];
        foreach ( $conditions as $c ) {
            if ( ! term_exists( $c, 'va_condition' ) ) {
                wp_insert_term( $c, 'va_condition' );
            }
        }

        /* Megyék */
        $counties = [
            'Bács-Kiskun','Baranya','Békés','Borsod-Abaúj-Zemplén','Csongrád-Csanád',
            'Fejér','Győr-Moson-Sopron','Hajdú-Bihar','Heves','Jász-Nagykun-Szolnok',
            'Komárom-Esztergom','Nógrád','Pest','Somogy','Szabolcs-Szatmár-Bereg',
            'Tolna','Vas','Veszprém','Zala','Budapest',
        ];
        foreach ( $counties as $county ) {
            if ( ! term_exists( $county, 'va_county' ) ) {
                wp_insert_term( $county, 'va_county' );
            }
        }
    }
}
