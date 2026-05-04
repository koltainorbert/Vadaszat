<?php
/**
 * page-sugo.php
 * Teljes, publikus hasznalati utmutato ugyfeleknek.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<section class="va-help-page" aria-labelledby="va-help-title">
    <style>
        .va-help-page {
            padding: calc(var(--nav) + 26px) 18px 56px;
            color: #fff;
        }
        .va-help-shell {
            max-width: 1160px;
            margin: 0 auto;
            border: 1px solid rgba(255, 0, 0, .24);
            border-radius: 26px;
            background:
                radial-gradient(circle at 1px 1px, rgba(255,255,255,.05) 1px, transparent 0) 0 0 / 15px 15px,
                linear-gradient(180deg, rgba(12,12,12,.98), rgba(6,6,6,.99));
            box-shadow: 0 26px 70px rgba(0,0,0,.55);
            overflow: hidden;
        }
        .va-help-hero {
            position: relative;
            padding: 34px 26px 24px;
            border-bottom: 1px solid rgba(255,255,255,.09);
            background:
                linear-gradient(120deg, rgba(255,0,0,.18), rgba(255,0,0,.03) 42%, transparent 80%),
                linear-gradient(180deg, rgba(16,16,16,.95), rgba(10,10,10,.92));
        }
        .va-help-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            letter-spacing: .12em;
            text-transform: uppercase;
            border: 1px solid rgba(255,0,0,.42);
            border-radius: 999px;
            padding: 8px 14px;
            color: #fff;
            background: rgba(255,0,0,.16);
            margin-bottom: 14px;
        }
        .va-help-hero h1 {
            margin: 0;
            font-size: clamp(1.7rem, 4vw, 2.8rem);
            line-height: 1.12;
            letter-spacing: .01em;
            text-wrap: balance;
        }
        .va-help-lead {
            margin-top: 12px;
            max-width: 980px;
            color: rgba(255,255,255,.86);
            line-height: 1.75;
            font-size: 1.01rem;
        }
        .va-help-quick {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }
        .va-help-quick a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 999px;
            padding: 9px 14px;
            font-size: 13px;
            background: rgba(255,255,255,.05);
            color: #fff;
            text-decoration: none;
            transition: .18s ease;
        }
        .va-help-quick a:hover {
            border-color: rgba(255,0,0,.5);
            background: rgba(255,0,0,.2);
            transform: translateY(-1px);
        }
        .va-help-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            padding: 18px 18px 0;
        }
        .va-help-section {
            border: 1px solid rgba(255,255,255,.09);
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.015));
            padding: 18px;
        }
        .va-help-section h2 {
            margin: 0 0 10px;
            font-size: 1.04rem;
            color: #fff;
            line-height: 1.35;
        }
        .va-help-section p,
        .va-help-section li {
            color: rgba(255,255,255,.88);
            line-height: 1.68;
            margin: 0;
        }
        .va-help-section ul,
        .va-help-section ol {
            margin: 0;
            padding-left: 20px;
        }
        .va-help-section li + li {
            margin-top: 6px;
        }
        .va-help-callout {
            margin: 18px;
            border: 1px solid rgba(255,0,0,.34);
            border-radius: 14px;
            padding: 14px 16px;
            background: rgba(255,0,0,.12);
            color: #fff;
            line-height: 1.68;
        }
        .va-help-link-row {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 12px;
        }
        .va-help-link-row a {
            display: inline-block;
            color: #fff;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 999px;
            padding: 8px 14px;
            background: rgba(255,255,255,.06);
            text-decoration: none;
            transition: .18s ease;
        }
        .va-help-link-row a:hover {
            border-color: rgba(255,0,0,.45);
            background: rgba(255,0,0,.18);
        }
        .va-help-divider {
            margin: 18px;
            border: 0;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .va-help-mini {
            margin: 0 18px 18px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }
        .va-help-mini-box {
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 12px;
            padding: 12px;
            background: rgba(255,255,255,.02);
            font-size: 13px;
            color: rgba(255,255,255,.84);
            line-height: 1.6;
        }
        .va-help-mini-box strong {
            display: block;
            color: #fff;
            margin-bottom: 3px;
        }
        @media (max-width: 900px) {
            .va-help-grid {
                grid-template-columns: 1fr;
            }
            .va-help-mini {
                grid-template-columns: 1fr;
            }
            .va-help-hero {
                padding: 24px 16px 18px;
            }
        }
    </style>

    <div class="va-help-shell">
        <header class="va-help-hero">
            <div class="va-help-kicker">Felhasznaloi utmutato</div>
            <h1 id="va-help-title">Teljes hasznalati utasitas a Vadaszapro oldalahoz</h1>
            <p class="va-help-lead">
                Ezen az oldalon osszegyujtottuk a latogatok szamara lathato funkciokat: hogyan keress, hogyan
                hirdess, hogyan licitalj, es hogyan kezeld a fiokodat. Csak olyan informacio szerepel itt,
                amit a felhasznalok is lathatnak az oldalon.
            </p>
            <div class="va-help-quick">
                <a href="<?php echo esc_url( home_url( '/va-hirdetes-kereses' ) ); ?>">Hirdetes kereses</a>
                <a href="<?php echo esc_url( home_url( '/va-hirdetes-feladas' ) ); ?>">Hirdetes feladas</a>
                <a href="<?php echo esc_url( home_url( '/va-fiok' ) ); ?>">Fiokom</a>
                <a href="<?php echo esc_url( home_url( '/kapcsolat' ) ); ?>">Kapcsolat</a>
            </div>
        </header>

        <div class="va-help-grid">
            <article class="va-help-section">
                <h2>1. Elso lepesek uj latogatoknak</h2>
                <ol>
                    <li>Nyisd meg a Hirdetes kereses oldalt, ahol az osszes talalatot listaban vagy racs nezetben latod.</li>
                    <li>Ha hirdetest szeretnel feladni vagy kedvenceket menteni, regisztralj, majd jelentkezz be.</li>
                    <li>A Fiokom oldalon utana egy helyen kezeled a sajat hirdeteseidet, licitjeidet es profilodat.</li>
                </ol>
                <div class="va-help-link-row">
                    <a href="<?php echo esc_url( home_url( '/va-regisztracio' ) ); ?>">Regisztracio</a>
                    <a href="<?php echo esc_url( home_url( '/va-bejelentkezes' ) ); ?>">Bejelentkezes</a>
                </div>
            </article>

            <article class="va-help-section">
                <h2>2. Kereses es szures lepesrol lepesre</h2>
                <ul>
                    <li>Kulcsszora, kategoriara, megyere es allapotra tudsz szurni.</li>
                    <li>Arszurovel minimum-maximum tartomanyt allithatsz.</li>
                    <li>Rendezes: legujabb, ar novekvo, ar csokkeno, legtobb megtekintes.</li>
                    <li>Atvalthatsz racs es lista nezet kozott, a neked kenyelmesebb bongeszeshez.</li>
                    <li>A Reszletes kereso panelben tobb jarmu-specifikus szuro is elerheto (pl. marka, modell, evjarat, uzemanyag).</li>
                </ul>
            </article>

            <article class="va-help-section">
                <h2>3. Mit latsz egy hirdetes adatlapjan?</h2>
                <ul>
                    <li>Kepek, cim, ar, helyszin, kategoria es egyeb adatok.</li>
                    <li>Leiras blokk, ahol a hirdeto reszletes informaciot adhatott meg.</li>
                    <li>Megtekintes szam es a hirdetes allapotara vonatkozo jelzesek.</li>
                    <li>Bejelentkezett felhasznaloknal Kedvencek gomb (sziv ikon) a gyors menteshez.</li>
                </ul>
            </article>

            <article class="va-help-section">
                <h2>4. Aukciok hasznalata</h2>
                <ul>
                    <li>Az Aukcio oldalon ugyanugy tudsz keresni es rendezni, mint a hirdeteseknel.</li>
                    <li>Egy aukcio adatlapjan latszik a kikiatasi ar, az aktualis licit, a licitek szama es a visszaszamlalas.</li>
                    <li>Licit csak bejelentkezve adhato le.</li>
                    <li>Aukciot kedvencekhez is hozzaadhatsz, ha be vagy jelentkezve.</li>
                </ul>
                <div class="va-help-link-row">
                    <a href="<?php echo esc_url( home_url( '/aukcio' ) ); ?>">Aukciok</a>
                </div>
            </article>

            <article class="va-help-section">
                <h2>5. Fiok, bejelentkezes, jelszo</h2>
                <ul>
                    <li>Regisztracional a kotelezo mezok csillaggal jeloltek.</li>
                    <li>Bejelentkezesnel kerheted az Emlkezz ram opciot.</li>
                    <li>Elfelejtett jelszo linken keresztul jelszo-visszaallito e-mail kerheto.</li>
                    <li>A Fiokom oldalon modosithato a profilnev, telefonszam, profilkep es jelszo.</li>
                </ul>
            </article>

            <article class="va-help-section">
                <h2>6. Hirdetes feladasa es szerkesztese</h2>
                <ul>
                    <li>A feladasi urlapon megadhato: cim, kategoria, megye, allapot, helyszin, ar, ar-tipus, leiras, telefonszam.</li>
                    <li>Jarmu hirdetesnel tovabbi mezok jelenhetnek meg (pl. marka, modell, kivitel, evjarat, kilometer).</li>
                    <li>Tobb kep toltheto fel, a boritokep kijelolheto, a kepsorrend athuzassal rendezheto.</li>
                    <li>A mar feladott hirdetes a Fiokom oldalon barmikor szerkesztheto vagy torolheto.</li>
                </ul>
                <div class="va-help-link-row">
                    <a href="<?php echo esc_url( home_url( '/va-hirdetes-feladas' ) ); ?>">Hirdetes feladas</a>
                </div>
            </article>

            <article class="va-help-section">
                <h2>7. Fiokom: mit lehet kezelni?</h2>
                <ul>
                    <li>Hirdeteseim: sajat listad, statuszokkal, szerkesztes/torles lehetoseggel.</li>
                    <li>Licitjeim: aukcios ajanlataid listaja (ha aukcio aktiv az oldalon).</li>
                    <li>Kedvenceim: mentett hirdetesek es figyelt aukciok egy helyen.</li>
                    <li>Profilom: szemelyes adatok, jelszocsere, profilkep.</li>
                </ul>
            </article>

            <article class="va-help-section">
                <h2>8. Biztonsagos hasznalat roviden</h2>
                <ul>
                    <li>Csak valos, ellenorizheto adatot adj meg hirdetesben es kapcsolatfelvetelkor.</li>
                    <li>Szemelyes talalkozot lehetoseg szerint nyilvanos helyen szervezz.</li>
                    <li>Gyanus ajanlatnal vagy atveres-gyanu eseten hasznald a Kapcsolat oldalt.</li>
                </ul>
                <div class="va-help-link-row">
                    <a href="<?php echo esc_url( home_url( '/kapcsolat' ) ); ?>">Kapcsolatfelvetel</a>
                </div>
            </article>
        </div>

        <hr class="va-help-divider">

        <div class="va-help-mini">
            <div class="va-help-mini-box">
                <strong>Ha nem talalsz menupontot</strong>
                Bizonyos funkciok idoszakosan vagy beallitas szerint ki lehetnek kapcsolva.
            </div>
            <div class="va-help-mini-box">
                <strong>Hibabejelentes</strong>
                A pontos oldal URL-jevel es rovid leirassal gyorsabban tudunk segiteni.
            </div>
            <div class="va-help-mini-box">
                <strong>Jogi oldalak</strong>
                Hasznalat elott erdemes elolvasni az ASZF es az adatvedelmi tajekoztatot.
            </div>
        </div>

        <div class="va-help-callout">
            Nem talalod, amit keresel? Irj a Kapcsolat oldalon. Minel pontosabban irod le,
            hogy melyik oldalon es milyen lepes utan akadtal el, annal gyorsabban tudunk segiteni.
            <div class="va-help-link-row">
                <a href="<?php echo esc_url( home_url( '/kapcsolat' ) ); ?>">Uzenet kuldese</a>
                <a href="<?php echo esc_url( home_url( '/aszf' ) ); ?>">ASZF</a>
                <a href="<?php echo esc_url( home_url( '/adatvedelmi-nyilatkozat' ) ); ?>">Adatvedelmi tajekoztato</a>
            </div>
        </div>
    </div>
</section>

<?php get_footer();
