<?php
/**
 * page-kategoria.php – Vadász Kategóriák oldal
 * Automatikusan betöltődik a /kategoria URL-re (page slug = kategoria).
 */
get_header();

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
                <!-- Csőtár / magazin -->
                <rect x="28" y="31.5" width="8" height="13" rx="2" fill="#cc0000"/>
                <!-- Ravasz szekrény -->
                <rect x="22" y="23" width="18" height="10" rx="2.5" fill="#cc0000"/>
                <!-- Ravasz hurok -->
                <path d="M28 33.5 Q32 42 36 33.5" stroke="#ff5555" stroke-width="2.2" fill="none" stroke-linecap="round"/>
                <!-- Ravasz -->
                <line x1="32" y1="33" x2="32" y2="39" stroke="#ff8888" stroke-width="1.8" stroke-linecap="round"/>
                <!-- Távcső -->
                <rect x="24" y="18" width="14" height="6.5" rx="3.25" fill="#aa0000"/>
                <line x1="27" y1="21.25" x2="35" y2="21.25" stroke="rgba(255,200,200,.4)" stroke-width="1"/>
                <!-- Tus (stock) -->
                <path d="M12 26 L12 31.5 L7 37 L7 44 L13 44 L14 37 L14 31.5" fill="#ff1a1a"/>
                <!-- Tolózár fogantyú -->
                <path d="M38 23 L38 17 L43 17" stroke="#ff4444" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <!-- Fénypont távcső üveg -->
                <circle cx="31" cy="21.25" r="1.6" fill="rgba(180,220,255,.35)"/>
            </svg>';

        /* ── LŐSZER: álló puska-tölténykép ── */
        case 'Lőszer & Töltény':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Bal töltény -->
                <path d="M18 48 L18 26 Q18 18 22 16 Q26 18 26 26 L26 48 Z" fill="#cc0000"/>
                <rect x="18" y="45" width="8" height="7" rx="1.5" fill="#880000"/>
                <ellipse cx="22" cy="16.5" r="4" ry="5" fill="#ff1a1a"/>
                <!-- Középső töltény (nagyobb, előtérben) -->
                <path d="M28 50 L28 24 Q28 14 32 12 Q36 14 36 24 L36 50 Z" fill="#ff1a1a"/>
                <rect x="28" y="47" width="8" height="8" rx="1.5" fill="#880000"/>
                <ellipse cx="32" cy="12.5" r="4.5" ry="5.5" fill="#ff3333"/>
                <!-- Jobb töltény -->
                <path d="M38 48 L38 26 Q38 18 42 16 Q46 18 46 26 L46 48 Z" fill="#cc0000"/>
                <rect x="38" y="45" width="8" height="7" rx="1.5" fill="#880000"/>
                <ellipse cx="42" cy="16.5" r="4" ry="5" fill="#ff1a1a"/>
                <!-- Fény highlight -->
                <line x1="30" y1="16" x2="30" y2="45" stroke="rgba(255,255,255,.12)" stroke-width="1.5" stroke-linecap="round"/>
            </svg>';

        /* ── OPTIKA: vadász távcső (riflescope) ── */
        case 'Optika & Elektronika':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Fő távcső test -->
                <rect x="10" y="27" width="44" height="10" rx="5" fill="#cc0000"/>
                <!-- Hátsó lencse harang -->
                <ellipse cx="13" cy="32" rx="7" ry="12" fill="#ff1a1a"/>
                <!-- Első lencse harang -->
                <ellipse cx="51" cy="32" rx="6" ry="9" fill="#ff1a1a"/>
                <!-- Dioptra gyűrű -->
                <rect x="26" y="25" width="5" height="14" rx="2.5" fill="#ff1a1a"/>
                <!-- Lencse tükröződés (hátsó) -->
                <ellipse cx="13" cy="32" rx="4.5" ry="8.5" fill="rgba(100,150,255,.18)"/>
                <ellipse cx="13" cy="29" rx="2" ry="2.5" fill="rgba(255,255,255,.2)"/>
                <!-- Lencse tükröződés (első) -->
                <ellipse cx="51" cy="32" rx="3.5" ry="6" fill="rgba(100,150,255,.15)"/>
                <!-- Kereszthaj (reticle) belső körben -->
                <line x1="51" y1="27" x2="51" y2="37" stroke="rgba(255,80,80,.7)" stroke-width="1.2"/>
                <line x1="46" y1="32" x2="56" y2="32" stroke="rgba(255,80,80,.7)" stroke-width="1.2"/>
                <circle cx="51" cy="32" r="2" stroke="rgba(255,80,80,.8)" stroke-width="1" fill="none"/>
                <!-- Torony (elevation knob) -->
                <rect x="29" y="19" width="7" height="7" rx="1.5" fill="#aa0000"/>
                <line x1="32.5" y1="20" x2="32.5" y2="26" stroke="rgba(255,255,255,.3)" stroke-width="1.5" stroke-linecap="round"/>
            </svg>';

        /* ── KÉSEK: vadászkés részletesen ── */
        case 'Kések & Eszközök':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Penge (blade) -->
                <path d="M16 52 L50 12 L54 16 L22 54 Z" fill="#ff1a1a"/>
                <!-- Penge él highlight -->
                <path d="M16 52 L50 12" stroke="rgba(255,255,255,.22)" stroke-width="1.2" stroke-linecap="round"/>
                <!-- Hátgerincvonal (spine) -->
                <path d="M22 54 L54 16 L56 18 L24 56 Z" fill="#cc0000"/>
                <!-- Keresztvas (guard) -->
                <rect x="17" y="48" width="12" height="4.5" rx="2.25" transform="rotate(-45 23 50.25)" fill="#880000"/>
                <!-- Markolat (handle) -->
                <path d="M8 58 L18 49 L23 54 L13 63 Z" fill="#aa0000" rx="3"/>
                <!-- Markolat szegecs -->
                <circle cx="13" cy="55" r="1.8" fill="#ff4444"/>
                <circle cx="16" cy="58" r="1.8" fill="#ff4444"/>
                <!-- Hegycsúcs fény -->
                <circle cx="52" cy="14" r="2" fill="rgba(255,200,200,.5)"/>
            </svg>';

        /* ── RUHÁZAT: vadászdzseki ── */
        case 'Ruházat':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Dzseki test -->
                <path d="M20 20 L10 30 L10 54 L54 54 L54 30 L44 20 L38 24 L32 22 L26 24 Z" fill="#ff1a1a"/>
                <!-- Bal ujj -->
                <path d="M10 30 L4 36 L4 50 L10 50 L10 30" fill="#cc0000"/>
                <!-- Jobb ujj -->
                <path d="M54 30 L60 36 L60 50 L54 50 L54 30" fill="#cc0000"/>
                <!-- Gallér bal -->
                <path d="M20 20 L10 30 L18 28 L26 20" fill="#aa0000"/>
                <!-- Gallér jobb -->
                <path d="M44 20 L54 30 L46 28 L38 20" fill="#aa0000"/>
                <!-- Cipzár -->
                <line x1="32" y1="22" x2="32" y2="54" stroke="rgba(255,255,255,.2)" stroke-width="2" stroke-linecap="round"/>
                <!-- Cipzár fogak -->
                <rect x="30.5" y="26" width="3" height="2" rx=".5" fill="rgba(255,255,255,.25)"/>
                <rect x="30.5" y="31" width="3" height="2" rx=".5" fill="rgba(255,255,255,.25)"/>
                <rect x="30.5" y="36" width="3" height="2" rx=".5" fill="rgba(255,255,255,.25)"/>
                <!-- Bal zseb -->
                <rect x="13" y="38" width="12" height="9" rx="2" fill="rgba(0,0,0,.2)" stroke="rgba(255,255,255,.1)" stroke-width="1"/>
                <!-- Jobb zseb -->
                <rect x="39" y="38" width="12" height="9" rx="2" fill="rgba(0,0,0,.2)" stroke="rgba(255,255,255,.1)" stroke-width="1"/>
                <!-- Váll varrás -->
                <line x1="20" y1="20" x2="10" y2="30" stroke="rgba(0,0,0,.25)" stroke-width="1.5"/>
                <line x1="44" y1="20" x2="54" y2="30" stroke="rgba(0,0,0,.25)" stroke-width="1.5"/>
            </svg>';

        /* ── FELSZERELÉS: vadász hátizsák ── */
        case 'Felszerelés':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Fő zsák -->
                <rect x="14" y="22" width="36" height="36" rx="6" fill="#ff1a1a"/>
                <!-- Tető (top lid) -->
                <rect x="16" y="15" width="32" height="12" rx="5" fill="#cc0000"/>
                <!-- Váll pántos hurok felső -->
                <path d="M20 20 Q20 10 26 10 L38 10 Q44 10 44 20" stroke="#cc0000" stroke-width="5" fill="none" stroke-linecap="round"/>
                <!-- Fő zseb -->
                <rect x="18" y="32" width="28" height="18" rx="4" fill="#cc0000"/>
                <!-- Fő zseb cipzár -->
                <line x1="18" y1="38" x2="46" y2="38" stroke="rgba(255,255,255,.2)" stroke-width="1.5" stroke-dasharray="3 2"/>
                <!-- Kis első zseb -->
                <rect x="22" y="50" width="20" height="6" rx="3" fill="#aa0000"/>
                <!-- Csat -->
                <rect x="28" y="26" width="8" height="5" rx="1.5" fill="#aa0000"/>
                <line x1="30" y1="28.5" x2="34" y2="28.5" stroke="rgba(255,255,255,.3)" stroke-width="1.5" stroke-linecap="round"/>
                <!-- Oldal gyűrűk -->
                <circle cx="14" cy="34" r="3" stroke="#aa0000" stroke-width="2.5" fill="none"/>
                <circle cx="50" cy="34" r="3" stroke="#aa0000" stroke-width="2.5" fill="none"/>
            </svg>';

        /* ── TRÓFEA: szarvas agancsos koponya ── */
        case 'Trófea & Dísztárgy':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Bal agancs fő szár -->
                <path d="M27 38 Q22 28 18 18 Q14 10 16 6" stroke="#ff1a1a" stroke-width="4.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <!-- Bal agancs ágak -->
                <path d="M22 24 Q16 20 12 14" stroke="#ff1a1a" stroke-width="3" fill="none" stroke-linecap="round"/>
                <path d="M20 18 Q14 16 12 10" stroke="#ff1a1a" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                <path d="M18 13 Q16 8 18 4" stroke="#ff1a1a" stroke-width="2" fill="none" stroke-linecap="round"/>
                <!-- Jobb agancs fő szár -->
                <path d="M37 38 Q42 28 46 18 Q50 10 48 6" stroke="#ff1a1a" stroke-width="4.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <!-- Jobb agancs ágak -->
                <path d="M42 24 Q48 20 52 14" stroke="#ff1a1a" stroke-width="3" fill="none" stroke-linecap="round"/>
                <path d="M44 18 Q50 16 52 10" stroke="#ff1a1a" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                <path d="M46 13 Q48 8 46 4" stroke="#ff1a1a" stroke-width="2" fill="none" stroke-linecap="round"/>
                <!-- Koponya alap (euro mount pajzs) -->
                <path d="M20 40 Q20 48 32 52 Q44 48 44 40 Q44 34 32 34 Q20 34 20 40 Z" fill="#ff1a1a"/>
                <!-- Orr/orrüreg -->
                <ellipse cx="28" cy="44" rx="3" ry="4" fill="#880000"/>
                <ellipse cx="36" cy="44" rx="3" ry="4" fill="#880000"/>
                <!-- Homlok vonal -->
                <line x1="25" y1="38" x2="39" y2="38" stroke="rgba(0,0,0,.2)" stroke-width="1.5"/>
                <!-- Pajzs talpléc -->
                <rect x="22" y="50" width="20" height="5" rx="2.5" fill="#cc0000"/>
            </svg>';

        /* ── VADÁSZKUTYA: pointer kutya állás ── */
        case 'Vadászkutya':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Test -->
                <ellipse cx="34" cy="38" rx="17" ry="9" fill="#ff1a1a"/>
                <!-- Fej -->
                <circle cx="16" cy="30" r="10" fill="#ff1a1a"/>
                <!-- Orrcsúcs (muzzle) -->
                <ellipse cx="8" cy="34" rx="6" ry="4.5" fill="#cc0000"/>
                <!-- Orr -->
                <ellipse cx="5" cy="33" rx="2.5" ry="2" fill="#550000"/>
                <!-- Szem -->
                <circle cx="13" cy="28" r="3" fill="white"/>
                <circle cx="13.5" cy="28.5" r="1.8" fill="#1a0808"/>
                <circle cx="14.2" cy="27.8" r=".7" fill="white"/>
                <!-- Fül -->
                <path d="M20 22 Q26 14 22 10 Q16 12 14 20" fill="#cc0000"/>
                <!-- Mellső emelt láb (pointer póz) -->
                <path d="M22 42 L18 54" stroke="#cc0000" stroke-width="5" stroke-linecap="round"/>
                <!-- Mellső láb hátsó -->
                <path d="M26 45 L24 58" stroke="#cc0000" stroke-width="5" stroke-linecap="round"/>
                <!-- Hátsó lábak -->
                <path d="M42 44 L44 58" stroke="#cc0000" stroke-width="5" stroke-linecap="round"/>
                <path d="M48 42 L52 56" stroke="#cc0000" stroke-width="5" stroke-linecap="round"/>
                <!-- Farok (felfelé tartva, pointer) -->
                <path d="M50 34 Q58 26 60 18" stroke="#ff1a1a" stroke-width="4" fill="none" stroke-linecap="round"/>
                <!-- Nyak -->
                <path d="M22 32 Q28 36 22 42" stroke="#ff1a1a" stroke-width="3" fill="none"/>
            </svg>';

        /* ── VADÁSZTERÜLET: fenyőerdő + helyszín gombostű ── */
        case 'Vadászterület & Bérlet':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Talaj -->
                <rect x="4" y="54" width="56" height="5" rx="2.5" fill="#880000"/>
                <!-- Bal fa -->
                <polygon points="12,54 18,54 15,44" fill="#cc0000"/>
                <polygon points="10,48 20,48 15,36" fill="#ff1a1a"/>
                <polygon points="11,42 19,42 15,28" fill="#cc0000"/>
                <rect x="14" y="54" width="2" height="4" fill="#880000"/>
                <!-- Középső fa (nagyobb) -->
                <polygon points="27,54 37,54 32,40" fill="#cc0000"/>
                <polygon points="24,46 40,46 32,30" fill="#ff1a1a"/>
                <polygon points="25,38 39,38 32,18" fill="#cc0000"/>
                <rect x="31" y="54" width="3" height="5" fill="#880000"/>
                <!-- Jobb fa -->
                <polygon points="46,54 52,54 49,44" fill="#cc0000"/>
                <polygon points="44,48 54,48 49,36" fill="#ff1a1a"/>
                <polygon points="45,42 53,42 49,28" fill="#cc0000"/>
                <rect x="48" y="54" width="2" height="4" fill="#880000"/>
                <!-- Helyszín gombostű (map pin) -->
                <circle cx="49" cy="14" r="8" fill="#ff0000"/>
                <circle cx="49" cy="14" r="3.5" fill="white"/>
                <path d="M49 22 L49 30" stroke="#ff0000" stroke-width="2.5" stroke-linecap="round"/>
            </svg>';

        /* ── JÁRMŰ: terepjáró / quad ── */
        case 'Jármű':
            return '<svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Kerék bal -->
                <circle cx="14" cy="46" r="10" fill="#222"/>
                <circle cx="14" cy="46" r="6.5" stroke="#ff1a1a" stroke-width="3" fill="none"/>
                <circle cx="14" cy="46" r="2.5" fill="#ff1a1a"/>
                <!-- Kerék jobb -->
                <circle cx="50" cy="46" r="10" fill="#222"/>
                <circle cx="50" cy="46" r="6.5" stroke="#ff1a1a" stroke-width="3" fill="none"/>
                <circle cx="50" cy="46" r="2.5" fill="#ff1a1a"/>
                <!-- Alvaz -->
                <rect x="10" y="36" width="44" height="12" rx="3" fill="#cc0000"/>
                <!-- Karosszéria -->
                <path d="M8 36 L14 20 L50 20 L56 36 Z" fill="#ff1a1a"/>
                <!-- Szélvédő -->
                <path d="M18 36 L22 22 L42 22 L46 36 Z" fill="rgba(180,220,255,.25)"/>
                <!-- Tető -->
                <rect x="20" y="14" width="24" height="8" rx="3" fill="#cc0000"/>
                <!-- Fényszóró bal -->
                <ellipse cx="10" cy="30" rx="4" ry="3" fill="#ffdd88" opacity=".85"/>
                <!-- Fényszóró jobb -->
                <ellipse cx="54" cy="30" rx="4" ry="3" fill="#ffdd88" opacity=".85"/>
                <!-- Ajtó vonal -->
                <line x1="32" y1="20" x2="32" y2="36" stroke="rgba(0,0,0,.2)" stroke-width="1.5"/>
                <!-- Kilincs -->
                <rect x="36" y="28" width="5" height="2.5" rx="1.25" fill="rgba(0,0,0,.3)"/>
            </svg>';

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
    </section>

</div>

<?php get_footer(); ?>
