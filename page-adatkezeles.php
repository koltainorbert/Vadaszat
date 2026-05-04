<?php
/**
 * page-adatkezeles.php
 * Adatkezelési tájékoztató (GDPR) – publikus oldal.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<section class="va-help-page" aria-labelledby="va-adatk-title">
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
        .va-help-section--wide {
            grid-column: 1 / -1;
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
        .va-help-section p + p {
            margin-top: 8px;
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
        .va-adatk-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-top: 8px;
        }
        .va-adatk-table th,
        .va-adatk-table td {
            text-align: left;
            padding: 8px 10px;
            border: 1px solid rgba(255,255,255,.10);
            color: rgba(255,255,255,.88);
            line-height: 1.5;
        }
        .va-adatk-table th {
            background: rgba(255,255,255,.06);
            color: #fff;
            font-weight: 600;
        }
        .va-adatk-table tr:nth-child(even) td {
            background: rgba(255,255,255,.025);
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
            .va-help-section--wide {
                grid-column: auto;
            }
        }
    </style>

    <div class="va-help-shell">
        <header class="va-help-hero">
            <div class="va-help-kicker">GDPR · 2016/679 EU rendelet</div>
            <h1 id="va-adatk-title">Adatkezelési tájékoztató</h1>
            <p class="va-help-lead">
                A weingartnerauto.hu (Weingartner Auto) elkötelezett az Ön személyes adatainak védelme iránt.
                Ez a tájékoztató leírja, milyen adatokat gyűjtünk, miért, meddig őrizzük meg, és milyen
                jogok illetik meg Önt az Általános Adatvédelmi Rendelet (GDPR) alapján.
            </p>
            <div class="va-help-quick">
                <a href="#adatkezelo">Adatkezelő</a>
                <a href="#kezelt-adatok">Kezelt adatok</a>
                <a href="#jogalapok">Jogalapok</a>
                <a href="#megorzesi-ido">Megőrzési idő</a>
                <a href="#jogok">Az Ön jogai</a>
                <a href="#sutik">Sütik</a>
                <a href="<?php echo esc_url( home_url( '/kapcsolat' ) ); ?>">Kapcsolat</a>
            </div>
        </header>

        <div class="va-help-grid">

            <!-- 1. Adatkezelő -->
            <article class="va-help-section" id="adatkezelo">
                <h2>1. Az adatkezelő adatai</h2>
                <ul>
                    <li><strong>Cégnév:</strong> Weingartner György Egyéni Vállalkozó (Weingartner Auto)</li>
                    <li><strong>Székhely:</strong> 8412 Veszprém, Alsó-Újsor utca 31.</li>
                    <li><strong>E-mail:</strong> <?php echo esc_html( get_option( 'va_contact_email', 'weingartnertrans@gmail.com' ) ); ?></li>
                    <li><strong>Telefon:</strong> <?php echo esc_html( get_option( 'va_billing_phone', '+36 20 943 8636' ) ); ?></li>
                    <li><strong>Weboldal:</strong> weingartnerauto.hu</li>
                </ul>
            </article>

            <!-- 2. Kezelt adatok -->
            <article class="va-help-section" id="kezelt-adatok">
                <h2>2. Kezelt személyes adatok köre</h2>
                <ul>
                    <li><strong>Kapcsolatfelvételkor:</strong> név, e-mail cím, üzenet szövege</li>
                    <li><strong>Regisztráció esetén:</strong> felhasználónév, e-mail cím, jelszó (titkosítva)</li>
                    <li><strong>Hirdetés feladásakor:</strong> cím, leírás, ár, elérhetőség (az Ön által megadott adatok)</li>
                    <li><strong>Látogatási adatok:</strong> IP-cím, böngésző típusa, meglátogatott oldalak (sütikezelés útján)</li>
                </ul>
            </article>

            <!-- 3. Adatkezelés célja -->
            <article class="va-help-section" id="jogalapok">
                <h2>3. Adatkezelés célja és jogalapja</h2>
                <ul>
                    <li><strong>Kapcsolattartás:</strong> az Ön megkeresésének megválaszolása (GDPR 6. cikk (1) b) pont)</li>
                    <li><strong>Felhasználói fiók:</strong> regisztráció és belépés biztosítása (szerződéses jogalap)</li>
                    <li><strong>Hirdetés kezelés:</strong> a hirdetési szolgáltatás nyújtása (szerződéses jogalap)</li>
                    <li><strong>Sütikezelés:</strong> weboldal működtetése, analitika (hozzájárulás alapján)</li>
                    <li><strong>Jogi kötelezettség:</strong> számviteli, adózási előírások teljesítése</li>
                </ul>
            </article>

            <!-- 4. Megőrzési idő -->
            <article class="va-help-section" id="megorzesi-ido">
                <h2>4. Az adatok megőrzési ideje</h2>
                <ul>
                    <li><strong>Kapcsolatfelvételi üzenetek:</strong> 2 év a megkeresés lezárásától</li>
                    <li><strong>Felhasználói fiók adatai:</strong> a fiók törléséig, legfeljebb 5 év inaktivitás után</li>
                    <li><strong>Hirdetések:</strong> a hirdetés lejáratától számított 1 év</li>
                    <li><strong>Számlázási adatok:</strong> 8 év (számviteli törvény alapján)</li>
                    <li><strong>Sütik:</strong> süti típustól függően – részletek a Süti tájékoztatóban</li>
                </ul>
            </article>

            <!-- 5. Adattovábbítás -->
            <article class="va-help-section">
                <h2>5. Adattovábbítás, adatfeldolgozók</h2>
                <ul>
                    <li><strong>Tárhelyszolgáltató:</strong> szerverüzemeltetési célból, az EGT területén</li>
                    <li><strong>E-mail szolgáltató:</strong> értesítések kiküldéséhez</li>
                    <li><strong>Fizetési szolgáltató:</strong> bankkártyás fizetés esetén (saját adatkezeléssel)</li>
                    <li>Harmadik félnek személyes adatot <strong>nem adunk át</strong>, kivéve jogszabályi kötelezettség esetén.</li>
                </ul>
            </article>

            <!-- 6. Az érintett jogai -->
            <article class="va-help-section" id="jogok">
                <h2>6. Az Ön jogai (GDPR 15–22. cikk)</h2>
                <ul>
                    <li><strong>Hozzáférés:</strong> tájékoztatást kérhet a kezelt adatairól</li>
                    <li><strong>Helyesbítés:</strong> kérheti pontatlan adatai javítását</li>
                    <li><strong>Törlés („elfeledtetés"):</strong> kérheti adatai törlését, ha nincs kötelező megőrzési ok</li>
                    <li><strong>Korlátozás:</strong> kérheti az adatkezelés korlátozását</li>
                    <li><strong>Hordozhatóság:</strong> adatait géppel olvasható formátumban megkaphatja</li>
                    <li><strong>Tiltakozás:</strong> tiltakozhat az adatkezelés ellen</li>
                    <li><strong>Hozzájárulás visszavonása:</strong> bármikor visszavonhatja korábbi hozzájárulását</li>
                </ul>
                <div class="va-help-link-row">
                    <a href="<?php echo esc_url( home_url( '/kapcsolat' ) ); ?>">Jog érvényesítése – Kapcsolat</a>
                </div>
            </article>

            <!-- 7. Panasz -->
            <article class="va-help-section">
                <h2>7. Panaszbenyújtás</h2>
                <p>Ha úgy véli, hogy az adatkezelés sérti a GDPR-t, panaszt nyújthat be a felügyeleti hatósághoz:</p>
                <ul style="margin-top:8px;">
                    <li><strong>NAIH</strong> – Nemzeti Adatvédelmi és Információszabadság Hatóság</li>
                    <li>Cím: 1055 Budapest, Falk Miksa utca 9–11.</li>
                    <li>Web: <strong>naih.hu</strong></li>
                    <li>E-mail: ugyfelszolgalat@naih.hu</li>
                </ul>
            </article>

            <!-- 8. Sütik -->
            <article class="va-help-section va-help-section--wide" id="sutik">
                <h2>8. Sütik (cookie-k)</h2>
                <p>A weingartnerauto.hu az alábbi sütiket használja:</p>
                <table class="va-adatk-table" style="margin-top:12px;">
                    <thead>
                        <tr>
                            <th>Süti neve</th>
                            <th>Típus</th>
                            <th>Cél</th>
                            <th>Megőrzési idő</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>wordpress_logged_in_*</td>
                            <td>Szükséges</td>
                            <td>Bejelentkezési munkamenet fenntartása</td>
                            <td>Munkamenet</td>
                        </tr>
                        <tr>
                            <td>wp-settings-*</td>
                            <td>Szükséges</td>
                            <td>WordPress admin beállítások</td>
                            <td>1 év</td>
                        </tr>
                        <tr>
                            <td>googtrans</td>
                            <td>Funkcionális</td>
                            <td>Nyelvi beállítás megjegyzése</td>
                            <td>Munkamenet</td>
                        </tr>
                        <tr>
                            <td>va_geo_done</td>
                            <td>Funkcionális</td>
                            <td>IP-alapú ország-detektálás egyszeri futtatása</td>
                            <td>1 nap</td>
                        </tr>
                        <tr>
                            <td>_ga, _gid</td>
                            <td>Analitikai</td>
                            <td>Google Analytics látogatói statisztikák</td>
                            <td>2 év / 24 óra</td>
                        </tr>
                    </tbody>
                </table>
                <p style="margin-top:10px;font-size:13px;color:rgba(255,255,255,.7);">
                    A szükséges sütik törlése befolyásolhatja az oldal működését. Az analitikai sütik hozzájárulás alapján kerülnek elhelyezésre.
                </p>
            </article>

            <!-- 9. Adatbiztonság -->
            <article class="va-help-section">
                <h2>9. Adatbiztonság</h2>
                <ul>
                    <li>HTTPS (TLS) titkosítás minden adatátvitelen</li>
                    <li>Jelszavak erős hash-eléssel tárolva (bcrypt)</li>
                    <li>Rendszeres biztonsági mentések, korlátozott hozzáférés</li>
                    <li>Adatvédelmi incidens esetén 72 órán belül értesítjük a NAIH-ot</li>
                </ul>
            </article>

            <!-- 10. Tájékoztató módosítása -->
            <article class="va-help-section">
                <h2>10. A tájékoztató módosítása</h2>
                <p>Fenntartjuk a jogot e tájékoztató módosítására. A változásokról az oldalon tájékoztatjuk látogatóinkat.
                   A tájékoztató mindig a weingartnerauto.hu/adatkezeles/ oldalon érhető el.</p>
                <p style="margin-top:8px;font-size:13px;color:rgba(255,255,255,.62);">
                    Hatályos: <?php echo date( 'Y. F j.' ); ?>
                </p>
            </article>

        </div>

        <hr class="va-help-divider">

        <div class="va-help-mini">
            <div class="va-help-mini-box">
                <strong>Adatkezelési kérelem</strong>
                Törlési, helyesbítési vagy hozzáférési kérelmet a Kapcsolat oldalon keresztül küldhet.
            </div>
            <div class="va-help-mini-box">
                <strong>Válaszidő</strong>
                Kérelmére 30 napon belül (indokolt esetben 90 napon belül) reagálunk.
            </div>
            <div class="va-help-mini-box">
                <strong>NAIH panasz</strong>
                Jogai érvényesítéséhez közvetlenül is fordulhat a NAIH felügyeleti hatósághoz.
            </div>
        </div>

        <div class="va-help-callout">
            Kérdése van az adatkezeléssel kapcsolatban? Vegye fel velünk a kapcsolatot – minden megkeresésre
            válaszolunk, és adatvédelmi jogait maradéktalanul biztosítjuk.
            <div class="va-help-link-row">
                <a href="<?php echo esc_url( home_url( '/kapcsolat' ) ); ?>">Kapcsolatfelvétel</a>
                <a href="<?php echo esc_url( home_url( '/sugo' ) ); ?>">Súgó</a>
            </div>
        </div>
    </div>
</section>

<?php get_footer();
