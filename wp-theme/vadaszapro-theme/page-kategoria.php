<?php
/**
 * page-kategoria.php – Vadász Kategóriák oldal
 * Automatikusan betöltődik a /kategoria URL-re (page slug = kategoria).
 */
get_header();

$categories_video = content_url( 'uploads/2026/04/1434963_Hunter_Autumn_1920x1080.mp4' );

/* ── Fotó tár — Unsplash valódi képek ─────────────────────────────────── */
function va_cat_icon( string $name ): string {
    $photos = [
        'Fegyverek'              => '1595590424283-b8f17842773f', // vadászpuska
        'Lőszer & Töltény'       => '1578496479914-7ef3b0193be3', // töltények
        'Optika & Elektronika'   => '1564419320461-6870880221ad', // távcső/optika
        'Kések & Eszközök'       => '1588392382834-a891154bca4d', // vadászkés
        'Ruházat'                => '1506905925346-21bda4d32df4', // terepszínű ruha
        'Felszerelés'            => '1553776590-89774f79bcd0',    // hátizsák/felszerelés
        'Trófea & Dísztárgy'     => '1484406566174-9da000fda645', // szarvas agancs
        'Vadászkutya'            => '1587300003388-59208cc962cb', // vadászkutya
        'Vadászterület & Bérlet' => '1448375240586-882707db888b', // erdő/terület
        'Jármű'                  => '1533591379926-3c7965df9d2f', // terepjáró
        'Ingatlan & Szállás'     => '1518780664697-55e3ad937233', // vadászkunyhó
        'Egyéb'                  => '1513836279014-a89f7a76ae86', // őszi erdő
    ];
    $id  = $photos[ $name ] ?? '1513836279014-a89f7a76ae86';
    $url = 'https://images.unsplash.com/photo-' . $id . '?w=220&h=220&fit=crop&crop=entropy&auto=format&q=80';
    return '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $name ) . '" loading="lazy">';
}

/* ── Top-level kategóriák lekérése ─────────────────────────────────────── */
$top_terms = get_terms( [
    'taxonomy'   => 'va_category',
    'parent'     => 0,
    'hide_empty' => false,
    'orderby'    => 'name',
    'order'      => 'ASC',
] );
if ( is_wp_error( $top_terms ) ) $top_terms = [];
?>

<div class="va-cat-page">

    <!-- ═══ HERO ════════════════════════════════════════════════════════ -->
    <section class="vcp-hero">
        <div class="vcp-hero__orb vcp-hero__orb--1"></div>
        <div class="vcp-hero__orb vcp-hero__orb--2"></div>
        <div class="vcp-hero__orb vcp-hero__orb--3"></div>
        <div class="vcp-hero__inner">
            <div class="vcp-hero__badge">
                <span class="vcp-hero__badge-dot"></span>
                Vadász Apróhirdetések
            </div>
            <h1 class="vcp-hero__title">
                Válassz<br><em>Kategóriát</em>
            </h1>
            <p class="vcp-hero__sub">
                Golyós puskáktól a trófea-alapzatokig – minden vadász felszerelésnél egy helyen
            </p>
            <div class="vcp-hero__stats">
                <div class="vcp-hero__stat">
                    <span class="vcp-hero__stat-n"><?php echo count($top_terms); ?></span>
                    <span class="vcp-hero__stat-l">Főkategória</span>
                </div>
                <div class="vcp-hero__stat-sep"></div>
                <div class="vcp-hero__stat">
                    <span class="vcp-hero__stat-n"><?php echo wp_count_posts('va_listing')->publish; ?></span>
                    <span class="vcp-hero__stat-l">Aktív hirdetés</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ═══ GRID ═════════════════════════════════════════════════════════ -->
    <section class="vcp-section">
        <?php if ( ! empty( $top_terms ) ) : ?>
        <div class="vcp-grid">
            <?php foreach ( $top_terms as $i => $term ) :
                $children = get_terms( [
                    'taxonomy'   => 'va_category',
                    'parent'     => $term->term_id,
                    'hide_empty' => false,
                    'orderby'    => 'name',
                ] );
                if ( is_wp_error( $children ) ) $children = [];
                $count = (int) $term->count;
            ?>
            <a href="<?php echo esc_url( get_term_link( $term ) ); ?>"
               class="vcc"
               style="--ci:<?php echo (int)$i; ?>; --cd:<?php echo (int)$i * 60; ?>ms">

                <!-- Glow blob -->
                <div class="vcc__glow"></div>

                <!-- Pulsing ring + icon -->
                <div class="vcc__icon-wrap">
                    <div class="vcc__ring vcc__ring--1"></div>
                    <div class="vcc__ring vcc__ring--2"></div>
                    <div class="vcc__icon">
                        <?php echo va_cat_icon( $term->name ); ?>
                    </div>
                </div>

                <!-- Info -->
                <div class="vcc__body">
                    <h3 class="vcc__name"><?php echo esc_html( $term->name ); ?></h3>

                    <div class="vcc__count">
                        <span class="vcc__dot"></span>
                        <?php echo $count > 0 ? esc_html($count) . ' hirdetés' : 'Hirdetés hamarosan'; ?>
                    </div>

                    <!-- Chipek — összes alkategória látszik -->
                    <?php if ( ! empty( $children ) ) : ?>
                    <div class="vcc__chips">
                        <?php foreach ( $children as $ch ) : ?>
                        <span class="vcc__chip"><?php echo esc_html( $ch->name ); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Footer -->
                <div class="vcc__foot">
                    <span class="vcc__foot-txt">Megtekintés</span>
                    <svg class="vcc__arrow" width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M3 9h12M11 5l4 4-4 4" stroke="currentColor" stroke-width="1.8"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <!-- Bottom accent bar -->
                <div class="vcc__bar"></div>

            </a>
            <?php endforeach; ?>
        </div>
        <?php else : ?>
        <div class="va-empty" style="padding:80px 0;">
            <div class="va-empty__icon">🎯</div>
            <div class="va-empty__text">A kategóriák hamarosan betöltődnek.</div>
        </div>
        <?php endif; ?>

        <div class="vcp-video">
            <video class="vcp-video__media" autoplay muted loop playsinline preload="auto" aria-hidden="true">
                <source src="<?php echo esc_url( $categories_video ); ?>" type="video/mp4">
            </video>
            <div class="vcp-video__overlay"></div>
            <div class="vcp-video__content">
                <span class="vcp-video__eyebrow">Kategória ajánló</span>
                <h2 class="vcp-video__title">Vadászat ősszel</h2>
                <p class="vcp-video__lead">Fedezd fel a kategóriákat, és találd meg gyorsan a hozzád illő felszerelést.</p>
            </div>
        </div>
    </section>

</div>

<?php get_footer(); ?>
