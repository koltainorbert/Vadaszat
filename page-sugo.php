<?php
/**
 * page-sugo.php
 * Publikus hasznalati utmutato ismeretlen ugyfeleknek.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<section class="va-help-page" aria-labelledby="va-help-title">
    <style>
        .va-help-page {
            padding: calc(var(--nav) + 22px) 20px 48px;
            color: #fff;
        }
        .va-help-wrap {
            max-width: 1120px;
            margin: 0 auto;
            border: 1px solid rgba(255, 0, 0, .25);
            border-radius: 20px;
            background:
                radial-gradient(circle at 1px 1px, rgba(255,255,255,.06) 1px, transparent 0) 0 0 / 16px 16px,
                linear-gradient(180deg, rgba(10,10,10,.96), rgba(6,6,6,.98));
            box-shadow: 0 20px 50px rgba(0,0,0,.45);
            overflow: hidden;
        }
        .va-help-head {
            padding: 28px 26px 18px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .va-help-badge {
            display: inline-block;
            font-size: 12px;
            letter-spacing: .12em;
            text-transform: uppercase;
            border: 1px solid rgba(255,0,0,.35);
            border-radius: 999px;
            padding: 7px 12px;
            color: #fff;
            background: rgba(255,0,0,.14);
            margin-bottom: 12px;
        }
        .va-help-head h1 {
            margin: 0 0 8px;
            font-size: clamp(1.7rem, 3.5vw, 2.4rem);
            line-height: 1.15;
        }
        .va-help-head p {
            margin: 0;
            color: rgba(255,255,255,.82);
            line-height: 1.7;
            max-width: 880px;
        }
        .va-help-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            padding: 18px;
        }
        .va-help-card {
            border: 1px solid rgba(255,255,255,.09);
            border-radius: 16px;
            background: rgba(255,255,255,.02);
            padding: 18px;
        }
        .va-help-card h2 {
            margin: 0 0 10px;
            font-size: 1.03rem;
            color: #fff;
        }
        .va-help-card p,
        .va-help-card li {
            color: rgba(255,255,255,.86);
            line-height: 1.65;
            margin: 0;
        }
        .va-help-card ul {
            margin: 0;
            padding-left: 20px;
        }
        .va-help-card li + li {
            margin-top: 4px;
        }
        .va-help-note {
            margin: 0 18px 18px;
            border: 1px solid rgba(255,0,0,.3);
            border-radius: 14px;
            padding: 14px 16px;
            background: rgba(255,0,0,.1);
            color: #fff;
            line-height: 1.6;
        }
        .va-help-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .va-help-links a {
            display: inline-block;
            text-decoration: none;
            color: #fff;
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 999px;
            padding: 8px 14px;
            background: rgba(255,255,255,.06);
        }
        .va-help-links a:hover {
            border-color: rgba(255,0,0,.45);
            background: rgba(255,0,0,.18);
        }
        @media (max-width: 900px) {
            .va-help-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="va-help-wrap">
        <header class="va-help-head">
            <div class="va-help-badge">Hasznalati guide</div>
            <h1 id="va-help-title">Ugyfel utmutato a Vadaszapro hasznalatahoz</h1>
            <p>
                Ez az oldal roviden bemutatja, hogyan tudja egy uj latogato biztonsagosan hasznalni a rendszert.
                Csak publikus, ugyfeleknek szolo informaciokat tartalmaz.
            </p>
        </header>

        <div class="va-help-grid">
            <article class="va-help-card">
                <h2>1. Mire valo az oldal?</h2>
                <p>
                    A Vadaszapro egy hirdetesi platform, ahol jarmuvekhez kapcsolodo hirdeteseket es aukciokat tud megtekinteni,
                    keresni, illetve regisztracio utan sajat hirdetest feladni.
                </p>
            </article>

            <article class="va-help-card">
                <h2>2. Hogyan keressek hirdetest?</h2>
                <ul>
                    <li>Nyissa meg a Hirdetesek oldalt.</li>
                    <li>Hasznalja a szuroket (kategoria, ar, allapot, helyszin).</li>
                    <li>Kattintson a kivalasztott hirdetesre a reszletekhez.</li>
                </ul>
            </article>

            <article class="va-help-card">
                <h2>3. Hogyan adhatok fel hirdetest?</h2>
                <ul>
                    <li>Regisztracio es bejelentkezes utan lepjen a Hirdetes feladasa oldalra.</li>
                    <li>Toltse ki a kotelezo mezoket valos adatokkal.</li>
                    <li>Ellenorizze az adatokat, majd kuldje be a hirdetest.</li>
                </ul>
            </article>

            <article class="va-help-card">
                <h2>4. Kapcsolatfelvetel es valaszido</h2>
                <p>
                    Kerdeseivel a Kapcsolat oldalon keresztul irhat nekunk. A bejovo megkereseseket
                    altalaban 24 oran belul feldolgozzuk.
                </p>
                <div class="va-help-links">
                    <a href="<?php echo esc_url( home_url( '/kapcsolat' ) ); ?>">Kapcsolat</a>
                </div>
            </article>

            <article class="va-help-card">
                <h2>5. Biztonsagi alapelvek</h2>
                <ul>
                    <li>Soha ne utaljon eloleget ellenorizetlen felhasznalonak.</li>
                    <li>Gyanus ajanlat vagy uzenet eseten ne adjon meg erzekeny adatot.</li>
                    <li>Szemelyes atvetelnel valasszon biztonsagos, nyilvanos helyet.</li>
                </ul>
            </article>

            <article class="va-help-card">
                <h2>6. Fontos jogi tudnivalok</h2>
                <p>
                    A platform hasznalata soran az ASZF es az Adatvedelmi tajekoztato az iranyado.
                    A szolgaltatas hasznalataval ezeket elfogadja.
                </p>
                <div class="va-help-links">
                    <a href="<?php echo esc_url( home_url( '/aszf' ) ); ?>">ASZF</a>
                    <a href="<?php echo esc_url( home_url( '/adatvedelmi-nyilatkozat' ) ); ?>">Adatvedelem</a>
                </div>
            </article>
        </div>

        <div class="va-help-note">
            Nem talalja, amit keres? Nezze meg a Kapcsolat oldalt, es irja meg roviden,
            miben tudunk segiteni.
        </div>
    </div>
</section>

<?php get_footer();
