<?php
/**
 * page-kategoria.php – Vadász Kategóriák oldal
 * Automatikusan betöltődik a /kategoria URL-re (page slug = kategoria).
 */
get_header();

/* ── SVG ikon tár ──────────────────────────────────────────────────────── */
function va_cat_icon( string $name ): string {
    switch ( $name ) {

        case 'Fegyverek':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="4" y="27" width="44" height="7" rx="3.5" fill="#ff1a1a"/>
                <rect x="21" y="18" width="17" height="9" rx="3" fill="#cc0000"/>
                <rect x="23" y="15" width="3" height="5" rx="1.5" fill="#aa0000"/>
                <rect x="33" y="15" width="3" height="5" rx="1.5" fill="#aa0000"/>
                <path d="M48 27 L48 34 L60 37 L62 33 L56 27 Z" fill="#ff1a1a"/>
                <rect x="32" y="34" width="9" height="12" rx="2.5" fill="#cc0000"/>
                <path d="M25 34 Q27 41 30 34" stroke="#ff4444" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                <rect x="2" y="26" width="5" height="9" rx="2" fill="#aa0000"/>
                <circle cx="29" cy="22" r="1.5" fill="rgba(255,255,255,0.5)"/>
            </svg>';

        case 'Lőszer & Töltény':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 22 Q16 14 20 12 Q24 14 24 22 L24 46 L16 46 Z" fill="#ff2020"/>
                <rect x="16" y="44" width="8" height="7" rx="1.5" fill="#880000"/>
                <path d="M28 19 Q28 11 32 9 Q36 11 36 19 L36 46 L28 46 Z" fill="#ff1a1a"/>
                <rect x="28" y="44" width="8" height="7" rx="1.5" fill="#880000"/>
                <path d="M40 22 Q40 14 44 12 Q48 14 48 22 L48 46 L40 46 Z" fill="#ff2020"/>
                <rect x="40" y="44" width="8" height="7" rx="1.5" fill="#880000"/>
            </svg>';

        case 'Optika & Elektronika':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="32" cy="32" r="22" stroke="#ff1a1a" stroke-width="2.5"/>
                <circle cx="32" cy="32" r="13" stroke="#ff1a1a" stroke-width="1.5" stroke-dasharray="3 2.5" fill="rgba(255,0,0,0.04)"/>
                <line x1="32" y1="8"  x2="32" y2="20" stroke="#ff1a1a" stroke-width="2.2" stroke-linecap="round"/>
                <line x1="32" y1="44" x2="32" y2="56" stroke="#ff1a1a" stroke-width="2.2" stroke-linecap="round"/>
                <line x1="8"  y1="32" x2="20" y2="32" stroke="#ff1a1a" stroke-width="2.2" stroke-linecap="round"/>
                <line x1="44" y1="32" x2="56" y2="32" stroke="#ff1a1a" stroke-width="2.2" stroke-linecap="round"/>
                <circle cx="32" cy="32" r="4.5" fill="#ff0000"/>
                <circle cx="32" cy="32" r="1.8" fill="#fff" opacity=".9"/>
            </svg>';

        case 'Kések & Eszközök':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 56 L44 10 L48 14 L22 60 Z" fill="#ff1a1a"/>
                <path d="M44 10 L50 16 L48 20 L38 12 Z" fill="#aa0000"/>
                <rect x="14" y="52" width="13" height="8" rx="2.5" transform="rotate(-45 20.5 56)" fill="#cc0000"/>
                <line x1="46" y1="12" x2="52" y2="6" stroke="#ff4444" stroke-width="3" stroke-linecap="round"/>
                <path d="M18 56 L46 14" stroke="rgba(255,160,160,0.15)" stroke-width="1"/>
            </svg>';

        case 'Ruházat':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M22 16 L16 26 L16 52 L48 52 L48 26 L42 16 L38 20 L32 18 L26 20 Z" fill="#ff1a1a"/>
                <path d="M22 16 L10 24 L16 26" fill="none" stroke="#cc0000" stroke-width="2" stroke-linejoin="round"/>
                <path d="M42 16 L54 24 L48 26" fill="none" stroke="#cc0000" stroke-width="2" stroke-linejoin="round"/>
                <rect x="27" y="18" width="10" height="14" rx="2" fill="#aa0000"/>
                <line x1="32" y1="32" x2="32" y2="52" stroke="rgba(255,255,255,.12)" stroke-width="1.5"/>
                <rect x="18" y="37" width="10" height="7" rx="2" fill="rgba(0,0,0,.25)"/>
                <rect x="36" y="37" width="10" height="7" rx="2" fill="rgba(0,0,0,.25)"/>
            </svg>';

        case 'Felszerelés':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18 26 L16 54 L48 54 L46 26 Z" fill="#ff1a1a"/>
                <path d="M22 26 Q32 16 42 26" stroke="#ff1a1a" stroke-width="4.5" fill="none" stroke-linecap="round"/>
                <rect x="26" y="17" width="12" height="11" rx="3" fill="#cc0000"/>
                <rect x="20" y="34" width="24" height="10" rx="3" fill="#cc0000"/>
                <line x1="20" y1="48" x2="44" y2="48" stroke="rgba(255,255,255,.12)" stroke-width="1.5"/>
                <rect x="30" y="38" width="4" height="2" rx="1" fill="#ff4444"/>
            </svg>';

        case 'Trófea & Dísztárgy':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M32 56 L28 38 L16 24 L12 10" stroke="#ff1a1a" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M22 30 L10 22" stroke="#ff1a1a" stroke-width="3" fill="none" stroke-linecap="round"/>
                <path d="M18 20 L14 8" stroke="#ff1a1a" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                <path d="M24 26 L18 10" stroke="#ff1a1a" stroke-width="2" fill="none" stroke-linecap="round"/>
                <path d="M32 56 L36 38 L48 24 L52 10" stroke="#ff1a1a" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M42 30 L54 22" stroke="#ff1a1a" stroke-width="3" fill="none" stroke-linecap="round"/>
                <path d="M46 20 L50 8" stroke="#ff1a1a" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                <path d="M40 26 L46 10" stroke="#ff1a1a" stroke-width="2" fill="none" stroke-linecap="round"/>
                <ellipse cx="32" cy="56" rx="7" ry="4.5" fill="#ff1a1a"/>
            </svg>';

        case 'Vadászkutya':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="36" cy="38" rx="16" ry="10" fill="#ff1a1a"/>
                <circle cx="20" cy="28" r="10" fill="#ff1a1a"/>
                <ellipse cx="13" cy="20" rx="5" ry="7.5" transform="rotate(-20 13 20)" fill="#cc0000"/>
                <ellipse cx="27" cy="19" rx="4" ry="6.5" transform="rotate(18 27 19)" fill="#cc0000"/>
                <ellipse cx="12" cy="32" rx="5.5" ry="3.5" fill="#cc0000"/>
                <circle cx="18" cy="26" r="2.8" fill="#fff" opacity=".9"/>
                <circle cx="18.6" cy="26.2" r="1.4" fill="#1a0808"/>
                <path d="M52 38 Q62 28 58 16" stroke="#ff1a1a" stroke-width="4.5" fill="none" stroke-linecap="round"/>
                <rect x="22" y="46" width="5" height="11" rx="2.5" fill="#cc0000"/>
                <rect x="30" y="46" width="5" height="11" rx="2.5" fill="#cc0000"/>
                <rect x="38" y="46" width="5" height="11" rx="2.5" fill="#cc0000"/>
                <rect x="44" y="44" width="5" height="11" rx="2.5" fill="#cc0000"/>
            </svg>';

        case 'Vadászterület & Bérlet':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 54 L20 20 L36 54 Z" fill="rgba(255,0,0,.3)"/>
                <path d="M24 54 L42 10 L60 54 Z" fill="#ff1a1a"/>
                <path d="M7 54 L7 40 L4 40 L7 32 L10 40 L7 40" fill="#ff2020" stroke="#ff2020" stroke-width=".5"/>
                <path d="M55 54 L55 44 L52 44 L55 37 L58 44 L55 44" fill="#ff2020" stroke="#ff2020" stroke-width=".5"/>
                <circle cx="51" cy="18" r="5.5" fill="#ff4444"/>
                <line x1="51" y1="10" x2="51" y2="12.5" stroke="#ff4444" stroke-width="2" stroke-linecap="round"/>
                <line x1="51" y1="23.5" x2="51" y2="26" stroke="#ff4444" stroke-width="2" stroke-linecap="round"/>
                <line x1="43" y1="18" x2="45.5" y2="18" stroke="#ff4444" stroke-width="2" stroke-linecap="round"/>
                <line x1="56.5" y1="18" x2="59" y2="18" stroke="#ff4444" stroke-width="2" stroke-linecap="round"/>
            </svg>';

        case 'Jármű':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M4 40 L8 28 L18 24 L46 24 L56 28 L60 40 L60 48 L4 48 Z" fill="#ff1a1a"/>
                <path d="M16 28 L20 16 L44 16 L48 28 Z" fill="#cc0000"/>
                <path d="M20 28 L23 18 L41 18 L44 28 Z" fill="rgba(200,230,255,.2)"/>
                <circle cx="16" cy="48" r="9" fill="#111" stroke="#ff1a1a" stroke-width="3"/>
                <circle cx="48" cy="48" r="9" fill="#111" stroke="#ff1a1a" stroke-width="3"/>
                <circle cx="16" cy="48" r="3.5" fill="#ff1a1a"/>
                <circle cx="48" cy="48" r="3.5" fill="#ff1a1a"/>
                <rect x="53" y="34" width="6" height="5" rx="1.5" fill="#ffcc00" opacity=".85"/>
                <rect x="5"  y="34" width="6" height="5" rx="1.5" fill="#ffcc00" opacity=".85"/>
            </svg>';

        case 'Ingatlan & Szállás':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6 34 L32 10 L58 34 Z" fill="#ff1a1a"/>
                <rect x="12" y="34" width="40" height="22" fill="#cc0000"/>
                <rect x="26" y="42" width="12" height="14" rx="2" fill="#880000"/>
                <rect x="14" y="38" width="9" height="8" rx="1.5" fill="rgba(200,230,255,.25)"/>
                <rect x="41" y="38" width="9" height="8" rx="1.5" fill="rgba(200,230,255,.25)"/>
                <rect x="40" y="20" width="7" height="14" rx="1" fill="#aa0000"/>
                <circle cx="36" cy="50" r="1.8" fill="#ff4040"/>
                <line x1="12" y1="42" x2="52" y2="42" stroke="rgba(0,0,0,.18)" stroke-width="1.2"/>
                <line x1="12" y1="47" x2="52" y2="47" stroke="rgba(0,0,0,.18)" stroke-width="1.2"/>
            </svg>';

        default: /* Egyéb */
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="32" cy="32" r="22" stroke="#ff1a1a" stroke-width="2.5" fill="rgba(255,0,0,.04)"/>
                <circle cx="32" cy="32" r="13" stroke="#ff1a1a" stroke-width="1" stroke-dasharray="3 3" opacity=".5"/>
                <circle cx="32" cy="16" r="4.5" fill="#ff1a1a"/>
                <circle cx="44" cy="38" r="4.5" fill="#ff1a1a"/>
                <circle cx="20" cy="38" r="4.5" fill="#ff1a1a"/>
                <path d="M32 20.5 L42.5 34.5 L21.5 34.5 Z" stroke="#ff1a1a" stroke-width="1.5" fill="rgba(255,0,0,.1)"/>
                <circle cx="32" cy="32" r="3" fill="#ff0000"/>
            </svg>';
    }
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

                    <?php if ( ! empty( $children ) ) : ?>
                    <div class="vcc__chips">
                        <?php foreach ( array_slice( $children, 0, 3 ) as $ch ) : ?>
                        <span class="vcc__chip"><?php echo esc_html( $ch->name ); ?></span>
                        <?php endforeach; ?>
                        <?php if ( count( $children ) > 3 ) : ?>
                        <span class="vcc__chip vcc__chip--more">+<?php echo count($children) - 3; ?></span>
                        <?php endif; ?>
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
    </section>

</div>

<?php get_footer(); ?>
