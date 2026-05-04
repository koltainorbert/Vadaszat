<?php
/**
 * page-etika.php
 * Etika és Üzleti Magatartási Kódex – /etika/ URL-en tölti be a téma.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<section class="va-help-page va-etika-page" aria-labelledby="va-etika-title">
<style>
.va-etika-page {
    padding: calc(var(--nav) + 26px) 18px 56px;
    color: #fff;
}
.va-etika-shell {
    max-width: 900px;
    margin: 0 auto;
    border: 1px solid rgba(255,0,0,.24);
    border-radius: 26px;
    background:
        radial-gradient(circle at 1px 1px, rgba(255,255,255,.05) 1px, transparent 0) 0 0 / 15px 15px,
        linear-gradient(180deg, rgba(12,12,12,.98), rgba(6,6,6,.99));
    box-shadow: 0 26px 70px rgba(0,0,0,.55);
    overflow: hidden;
}
.va-etika-hero {
    position: relative;
    padding: 34px 30px 24px;
    border-bottom: 1px solid rgba(255,255,255,.09);
    background:
        linear-gradient(120deg, rgba(255,0,0,.18), rgba(255,0,0,.03) 42%, transparent 80%),
        linear-gradient(180deg, rgba(16,16,16,.95), rgba(10,10,10,.92));
}
.va-etika-kicker {
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
.va-etika-hero h1 {
    margin: 0;
    font-size: clamp(1.6rem, 3.5vw, 2.4rem);
    line-height: 1.12;
    letter-spacing: .01em;
    color: #fff;
}
.va-etika-hero p {
    margin-top: 10px;
    color: rgba(255,255,255,.75);
    font-size: 0.95rem;
}
.va-etika-body {
    padding: 28px 30px 32px;
}
.va-etika-body p {
    color: rgba(255,255,255,.88);
    line-height: 1.8;
    font-size: 0.96rem;
    margin: 0 0 10px;
}
.va-etika-body p strong, .va-etika-body b { color: #fff; }
.va-etika-body ul {
    margin: 8px 0 14px 20px;
    padding: 0;
}
.va-etika-body li {
    color: rgba(255,255,255,.85);
    line-height: 1.75;
    font-size: 0.95rem;
    margin-bottom: 5px;
}
.va-etika-body h2 {
    color: #fff;
    font-size: 1.05rem;
    margin: 20px 0 8px;
}
.va-etika-section-title {
    font-size: 0.75rem;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: #ff0000;
    font-weight: 700;
    margin: 24px 0 10px;
    padding-bottom: 7px;
    border-bottom: 1px solid rgba(255,0,0,.22);
}
.va-etika-pdf-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-top: 24px;
    padding: 11px 22px;
    background: rgba(255,0,0,.18);
    border: 1px solid rgba(255,0,0,.45);
    border-radius: 10px;
    color: #fff;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    transition: .18s ease;
    cursor: pointer;
    font-family: inherit;
}
.va-etika-pdf-btn:hover {
    background: rgba(255,0,0,.32);
    transform: translateY(-1px);
    color: #fff;
}
@media print {
    .va-header, .va-footer, #va-scroll-ring, .va-etika-pdf-btn,
    .va-etika-kicker, .va-footer__bottom, .va-sidebar { display: none !important; }
    .va-etika-page { padding: 0 !important; }
    .va-etika-shell {
        border: none !important;
        box-shadow: none !important;
        background: #fff !important;
        border-radius: 0 !important;
        max-width: 100% !important;
    }
    .va-etika-hero {
        background: #fff !important;
        border-bottom: 1px solid #ddd !important;
        padding: 18px 0 14px !important;
    }
    .va-etika-hero h1 { color: #111 !important; font-size: 18pt !important; }
    .va-etika-hero p { color: #333 !important; }
    .va-etika-body p, .va-etika-body li { color: #222 !important; }
    .va-etika-body p strong, .va-etika-body b { color: #000 !important; }
    .va-etika-section-title { color: #111 !important; border-color: #ccc !important; }
    .va-etika-body { padding: 20px 0 !important; }
    body { background: #fff !important; }
    * { -webkit-print-color-adjust: exact; }
}
@media (max-width: 640px) {
    .va-etika-hero { padding: 22px 18px 18px; }
    .va-etika-body { padding: 20px 18px 24px; }
}
</style>

<div class="va-etika-shell">

    <div class="va-etika-hero">
        <div class="va-etika-kicker">&#9878; Etika &amp; Megfelelés</div>
        <h1 id="va-etika-title" style="color:#fff">WEINGARTNER GYÖRGY EGYÉNI VÁLLALKOZÓ<br>ETIKAI ÉS ÜZLETI MAGATARTÁSI KÓDEX</h1>
        <p>Weingartner György üzenete | weingartnerauto.hu | weingartnerauto@gmail.com</p>
    </div>

    <div class="va-etika-body">

<p><strong>Weingartner György üzenete</strong></p>
<p>Az etikus magatartás iránti elkötelezettségünk és a szilárd etikai alapok Weingartner György E.V. működésének egyik legfontosabb elemei. Elkötelezettek vagyunk a korrekt, etikai kultúrán és megfelelésen alapuló üzleti működés mellett. Hosszú távon csak úgy nézhetünk szembe a versenypiac kihívásaival, ha mind személyesen, mind vállalatként erkölcsi felelősséget vállalunk elveinkért. Munkatársaink tevékenységük során mindig kötelesek törvényesen, etikusan és a Weingartner György E.V. érdekeinek megfelelően eljárni.</p>
<p>Köszönetet mondunk értékeink megtartásáért és annak támogatásáért, hogy helyesen működjünk. Ami nem csak azt jelenti, hogy gondosan megalkotott, tisztességesen árazott és magas minőségű termékeket és szolgáltatásokat kínálunk, hanem azt is, hogy mindig szem előtt tartjuk a tisztességességet és becsületességet. Csak olyan szállítótól szerzünk be anyagokat, akinek kifogástalan az emberi jogok és megfelelőségi szabályok tiszteletben tartásával kapcsolatos háttere, biztosítjuk ellátási láncunk tisztaságát, és figyelemmel kísérjük, hogy teljes működésünk megfeleljen a Kódexünknek.</p>
<p>Weingartner György<br>Egyéni Vállalkozó</p>

<div class="va-etika-section-title">1. Bevezetés</div>
<p>Ez a WEINGARTNER GYÖRGY EGYÉNI VÁLLALKOZÓ ETIKAI ÉS ÜZLETI MAGATARTÁSI KÓDEXE, amely tartalmazza etikai elkötelezettségünket, és útmutatóul szolgál valamennyi érintettük számára a megfelelő üzleti magatartás kialakításához. Mi, a Weingartner György E.V.-nál elkötelezettek vagyunk a törvényes, etikus és átlátható üzletmenet iránt.</p>
<p>Ez a dokumentum érvényes a teljes személyzetre aki a Weingartner György E.V. számára dolgozik (beleértve a vezető tisztségviselőket, igazgatókat, menedzsereket, vezetőket, munkavállalókat, ideiglenes, bérelt, gyakornok, alvállalkozó és tanácsadó munkaerőt is), valamint azokra a szervezetekre, akikkel üzleti kapcsolatba kerülünk.</p>
<p>Weingartner György E.V. elvárja a személyzettől, hogy a munkával összefüggő minden ügyben pártatlan és őszinte legyen. A teljes személyzet felel azért, hogy általánosan jóhiszeműen járjon el, és hogy ne tegyen semmi olyat, ami a munkakapcsolathoz szükséges bizalmat rombolja.</p>
<p>Vállalkozásunk sikere a munkavállalóinktól, vevőinktől kapott bizalmon alapul. Az által nyerünk hitelességet, hogy ragaszkodunk a tisztességesség melletti elkötelezettségünkhöz, és hogy kizárólag etikus módon érjük el céljainkat. A teljes személyzettől elvárt, hogy tartsa magát jelen Kódexhez mind szakmai, mind személyes magatartása során, valamint kezeljen mindenkit tisztelettel, őszintén és tisztességesen.</p>
<p>Weingartner György E.V. folyamatosan nyitott minden kérdésre, és nem tűr semmilyen büntetést vagy megtorlást azzal szemben, aki jóhiszeműen jelent egy nem megfelelő viselkedést. Az egyéni vállalkozónak, mint vállalkozásvezetőnek kiemelt szerepe van abban, hogy saját magatartásával is kifejezze a cégkódex fontosságát. Rá hárul a felelősség, hogy minden etikai kérdést vagy aggodalmat időben kezeljen. Fontos, hogy az egyéni vállalkozó/vezető azonnal foglalkozzon minden felmerült etikai problémával, és a munkavállalókat is arra ösztönözze, hogy együttműködjenek minden lehetséges vagy feltételezett etikai visszaélés kivizsgálásában.</p>
<p>A Kódexnek nem megfelelő magatartás olyan vétségnek tekinthető, amely alapján fegyelmi (hátrányos jogkövetkezmény alkalmazására irányuló) eljárásnak lehet helye, és megérdemelt esetekben a jogviszony megszüntetését is eredményezheti.</p>
<p>Elkötelezettek vagyunk azon erőfeszítések mellett, hogy értékeinket és normáinkat beszállítóink, alvállalkozóink, szolgáltatóink és partnereink teljes ellátási láncára alkalmazzuk.</p>

<div class="va-etika-section-title">2. Etikai Alapelvek</div>
<p>A Weingartner György E.V. központi értékei:</p>
<p>Őszinteség &bull; Tisztességesség &bull; Szavahihetőség &bull; Mások tisztelete &bull; Felelősség &bull; Számon kérhetőség &bull; Megbízhatóság &bull; Törvénytisztelet &bull; Következetesség &bull; Empátia &bull; Szolidaritás &bull; Innováció &bull; Környezettudatosság &bull; Képzés és fejlődés</p>

<div class="va-etika-section-title">3. Etikai döntéshozatal</div>
<p>Az etikus viselkedés értékvezérelt döntéshozatalt jelent. Néhány kulcskérdés segíthet egy-egy etikátlan, helytelen vagy törvénytelen helyzet azonosításában. Kérdezze meg magától:</p>
<p>Amit teszek legális? &bull; Összhangban van a vállalat értékeivel, etikájával? &bull; Megfelel a Kódexnek és más szabálynak/szabályzatnak? &bull; Tiszteletben tartom mások jogait? &bull; Hogy festene, ha a címlapokra kerülne? &bull; Hű vagyok ezzel a családomhoz, vállalatomhoz és magamhoz? &bull; Ez a helyes? &bull; Mit mondanék a gyermekemnek, mit tegyen ugyanebben a helyzetben? &bull; Megkértek olyasmire, hogy másként tüntessek fel valamilyen tényt, vagy hogy eltérjek a rendes folyamattól?</p>

<div class="va-etika-section-title">4. Jogszabályoknak való megfelelés</div>
<p>Tisztességesség melletti elkötelezettségünk a törvények és egyéb jogszabályok betartásával kezdődik. Ismerjük és betartjuk a törvényes üzletvitelhez szükséges jogszabályokat és szabályozásokat.</p>
<p>Betartjuk valamennyi érvényesen vállalt szerződéses kötelezettségünket, és nem élünk vissza jogainkkal.</p>
<p>Személyzetünknek mindig be kell tartani minden jogszabályt és szabályzatot, beleértve a Kódexet, és biztosítaniuk kell az ennek megfelelő működést.</p>

<div class="va-etika-section-title">5. Fenntarthatóság: Emberek + Profit + Föld</div>
<p>Elkötelezettek vagyunk aziránt, hogy jelenlegi szükségleteinket a következő generációk lehetőségeinek veszélyeztetése nélkül elégítsük ki. Ezért a gazdasági, környezeti és társadalmi tényezőket együttesen vesszük figyelembe működésünk és üzleti döntéseink során.</p>

<div class="va-etika-section-title">6. Emberi jogok</div>
<p>Elkötelezettek vagyunk minden ember és közösség méltósága és emberi jogainak tiszteletben tartása mellett, akikkel kapcsolatba kerülünk munkánk során. Semmilyen formában nem okozunk, illetve járulunk hozzá emberi jogi jogsértésekhez. Személyzetünk köteles mindenkit méltósággal, tisztelettel és törődéssel kezelni és megtartani emberi jogait.</p>

<div class="va-etika-section-title">7. Tisztességes foglalkoztatás és munkakörülmények</div>
<p>Elkötelezettek vagyunk a munkahelyi egyenlőség előmozdítása, valamint a jogszerű és tisztességes foglalkoztatási és javadalmazási gyakorlat megvalósítása iránt. Határozottan ellenezzük a gyermek, rabszolga vagy bármilyen formában kényszer-, kötelező, illetve megkötött munka mind közvetlen, mind közvetett alkalmazását. Elítéljük a jogellenes, tisztességtelen vagy etikátlan foglalkoztatás valamennyi formáját, amely kihasználja a munkaerőt, rombolja a társadalombiztosítási rendszert, vagy adóelkerülésre szolgál, így pl. a bejelentés nélküli vagy „szürke" munkát, illetve fizetések visszatartását.</p>
<p>Személyzetünk köteles tisztességes magatartást tanúsítani, kollégáikat és másokat teljes tisztelettel kezelni.</p>

<div class="va-etika-section-title">8. Diszkrimináció és zaklatás</div>
<p>Egyenlő esélyeket biztosítunk a foglalkoztatás során és nem tűrjük a diszkrimináció, a zaklatás vagy durva bánásmód bármely formáját. Bármilyen szakmai szempontból lényegtelen tulajdonságra vagy körülményre alapozott, így pl. nem, családi állapot, kor, nemzeti vagy társadalmi vagy etnikai hovatartozás, szín, vallási vagy politikai vélemény, fogyatékosság, szexuális irányultság, érdekképviseleti tagság, vagyoni, születési vagy más helyzet miatt közvetlen vagy közvetett megkülönböztetés nem megengedett. Bármilyen diszkriminatív magatartás, zaklatás, megfélemlítés vagy zsarnokoskodás tilos.</p>
<p>A teljes személyzettől elvárt, hogy a legmagasabb szintű, kölcsönös tiszteleten alapuló viselkedési formákhoz tartsák magukat minden szóbeli és írásbeli kommunikációjuk során, és tartózkodjanak minden zaklatástól, rágalmazástól vagy bármilyen olyan magatartástól, amit mások erőszakosnak, megfélemlítőnek, megalázónak vagy sértőnek tekinthetnek.</p>

<div class="va-etika-section-title">9. Egészség, Biztonság és környezetvédelem</div>
<p>Tiszta, biztonságos és egészséges munkakörnyezetet biztosítunk, és elkötelezettek vagyunk az egészséges környezet fenntartása iránt. Célunk a tevékenységünk természetes környezetre gyakorolt hatásaink lehető legkisebb mértékűre csökkentése. Erőfeszítéseket teszünk, hogy csökkentsük a véges erőforrások, mint az energia és a víz felhasználását, valamin a káros anyagok, mint például a hulladék kibocsátását.</p>
<p>Valamennyi személyzetnek mindig be kell tartania minden vonatkozó egészség, biztonság és környezetvédelmi jogszabályt, szabályzatot és szabályt.</p>

<div class="va-etika-section-title">10. Tisztességes verseny és üzleti működés</div>
<p>Partnereinkkel való együttműködésünk bizalomra és a versenyjognak megfelelő kölcsönös előnyökre épül. Elkötelezettek vagyunk az etikus és tisztességes verseny iránt, miként termékeinket és szolgáltatásainkat minőségük, alkalmasságuk és versenyképes áruk alapján értékesítjük. Önálló árképzési és értékesítési döntéseket hozunk, és tiltott módon nem működünk együtt, illetve nem hangoljuk össze a működésünket versenytársakkal. Tartózkodunk a versenyt vagy partnereink jó hírét, illetve a versenytársaink hitelességét sértő magatartásoktól.</p>
<p>Nem kínálunk, és nem kérünk jogellenes kifizetéseket vagy szívességeket, nem veszünk részt olyan jogellenes megállapodásokban, amely bizonyos vevők kizárására irányul. Elkötelezettek vagyunk viszont valamennyi alkalmazandó kereskedelmi szabályozás, korlátozás, szankció és import-export embargó betartása mellett.</p>
<p>Nem engedjük a tisztességes versenyt sértő magatartásokat a versenytárgyalások, tenderek során.</p>
<p>Nem tartjuk vissza rosszhiszeműen, jogellenesen vagy indokolatlanul a partnereinknek járó kifizetéseket, és az ilyen gyakorlatot nem engedjük ellátási láncunkban, küzdünk a „lánctartozások" etikátlan gyakorlata ellen.</p>
<p>Személyzetünk felelős a tisztességes üzleti gyakorlat biztosításáért munkája során, és azért hogy betartson minden versenyjogi, fogyasztóvédelmi és reklám szabályt. A vevőket és üzleti partnereket minden esetben tisztességesen és egyenlően kell kezelni, a termékeket és szolgáltatásokat tisztességes és pontos tájékoztatással kell megjeleníteni (tisztességes marketing és reklám), és valamennyi lényeges információt meg kell osztani.</p>

<div class="va-etika-section-title">11. Anti-korrupció</div>
<p>Határozottan elítéljük, és nem tűrjük a korrupció minden formáját. Bármilyen üzletszerzési célból tilos közvetlenül vagy közvetetten ajánlani, ígérni, adni, kérni vagy elfogadni bármilyen tisztességtelen előnyt vagy juttatást. A tisztességtelen előny vagy juttatás lehet akár pénz, készpénz helyettesesítő (pl. utalvány), ajándék, hitelkeret, árengedmény, utazás, személyes előny, szállás vagy szolgáltatás. Rendes eljárások biztosítása vagy felgyorsítása érdekében nem engedjük csúszópénzek (vagy „kenőpénz") juttatását sem hivatalos személyeknek sem gazdasági szereplők alkalmazottainak. A korrupció magában foglalja a befolyással üzérkedést is, ha valaki azt a látszatot kelti, hogy egy döntéshozót tisztességtelenül befolyásol.</p>
<p>A korrupció akár üzletszerzési célból, akár más gazdasági előny megszerzése céljából súlyos visszaélésnek minősül. Ugyanígy a vesztegetés elfogadása vagy annak megengedése, hogy más vesztegetést fogadjon el, súlyos vétség. Személyzetünknek el kell tudnia számolni minden előnnyel, amit az üzleti tevékenység során szerzett, és tilos bármilyen vesztegetést adnia vagy elfogadnia, vagy bármely más korrupt módon viselkednie.</p>

<div class="va-etika-section-title">12. Ajándékok és vendéglátás</div>
<p>El kell kerülni minden olyan magatartást, ami azt a látszatot keltheti, hogy személyes előnyökért cserébe kivételes elbánást keresünk, kapunk vagy adunk.</p>
<p>Üzleti udvariasságok vagy kedvességek lehetnek ajándékok, szívességek, étkezések, italok, szórakozás vagy más előnyök egy személytől vagy vállalattól, akivel üzleti kapcsolatban állunk vagy ezt állhatunk. Nem adunk, és nem fogadunk el olyasmit, ami tisztességtelen üzleti ösztönzőnek minősül, vagy ésszerűen ilyennek látszik, vagy valamely jogszabályba, szabályzatba vagy elvbe ütközik, vagy egyébként zavarba ejtő/kellemetlen helyzetet eredményez. Személyzetünk soha nem használhat személyes forrásokat olyasmire, amit vállalati forrásokból sem lehetne megtenni.</p>
<p>Szokásos és a piac ésszerű etikai elveinek megfelelő, alkalmi ajándékokat vagy vendéglátást felajánlhatunk és elfogadhatunk, ha nem eltúlzott, nem gyakori, illetve nem mutat gyakoriságot, és nem kelti azt a benyomást, hogy üzleti döntések befolyásolására szolgál. Csak alacsony értékű, jelentéktelen ajándékokat lehet elfogadni. Minden más ajándékot udvariasan vissza kell utasítani, vagy ha küldeményként érkezett, vissza kell küldeni. Ha a visszaküldés nem lehetséges, jótékonysági vagy közösségi célra kell felajánlani. Az ajándékozó, illetve a megajándékozott személy felelőssége mérlegelni, hogy egy ajándék megfelelő-e.</p>

<div class="va-etika-section-title">13. Vagyonvédelem és vállalati eszközök megfelelő használata</div>
<p>Felelősek vagyunk a vállalat erőforrásainak biztonságáért, védelméért és gazdaságos használatáért. Erőforrásaink, így az idő, anyagok, felszerelések és információ, csak jogszerű üzleti célra használható. Alkalomszerű magánhasználat megengedhető, ha az nem jogellenes, és ha nem befolyásolja a teljesítményt vagy rombolja a munkamorált.</p>
<p>Valamennyi személyzet köteles betartani a biztonsági intézkedéseket, és mind a materiális, mind az immateriális vállalati vagyont tisztelettel kezelni, azzal nem élhet vissza, és nem kezelheti hanyagul.</p>

<div class="va-etika-section-title">14. Bizalmasság, információ biztonság, üzleti titok és szellemi tulajdon védelem</div>
<p>Elkötelezettek vagyunk az üzleti információk teljességének, bizalmasságának és hozzáférhetőségének biztosítása iránt, ezért megfelelő technikai biztonsági megoldásokat alkalmazunk, aminek fenntartása minden személyzet kötelessége. Az üzleti titok magában foglal minden olyan információt, ami még nem került nyilvánosságra, és káros lenne a vállalat vagy vevői, üzleti partnerei számára, ha illetéktelenek számára hozzáférhetővé válna. Minden személyzet köteles az ilyen információt bizalmasan kezelni. Ez magában foglalja azt is, hogy senki sem kereskedhet egy értékpapírral, amíg olyan nem publikus információ birtokában van, ami hatással lehet az értékpapír árfolyamára, illetve ilyen információt nem oszthat meg másokkal. Valamennyi információbiztonságot szolgáló szabályt mindig be kell tartani.</p>
<p>Tiszteletben tartjuk mások szellemi tulajdonát. Helytelen eszközökkel nem szerzünk, meg és nem törekszünk megszerezni üzleti titkokat vagy más védett vagy bizalmas információt. Nem veszünk részt védett szellemi tulajdon engedély nélküli használatában, másolásában, terjesztésben vagy megváltoztatásában.</p>

<div class="va-etika-section-title">15. Számvitel, valós beszámolás és pénzügyi integritás</div>
<p>Könyvelésünket, nyilvántartásainkat, számláinkat és pénzügyi jelentéseinket kellő részletességgel, valósághűen és tranzakcióinkat megfelelően tükröző módon vezetjük, illetve állítjuk össze. Elítéljük a pénzmosás valamennyi formáját, így elkötelezettek vagyunk az iránt, hogy csak olyan partnerekkel lépjünk gazdasági kapcsolatba, akik törvényes forrásokból jogszerű üzleti tevékenységet végeznek.</p>
<p>Elkötelezzük magunkat a tisztességes adózás mellett, és tartózkodunk minden adóelkerülő gyakorlattól, mint pl. a nyugta- vagy számlaadási kötelezettség elmulasztásától vagy valótlan községszámlák elszámolásától.</p>
<p>Valamennyi személyzet köteles betartani minden számviteli eljárást, és biztosítani gazdasági események megfelelő rögzítését és dokumentálását, valamint gondoskodni arról, hogy a közölt pénzügyi beszámolók teljesek, őszinték, pontosak, időszerűek és érthetőek. Tilos a könyvvizsgálat vagy bármely számviteli ellenőrzés tisztességtelen befolyásolása, manipulálása vagy félrevezetése.</p>

<div class="va-etika-section-title">16. Csalás megelőzés</div>
<p>A csalás vagy csalárd – azaz becsapásra, lopásra, megtévesztésre vagy hazugságra irányuló – magatartás etikátlan és az esetek többségében büntetendő. A csalás minden formája (ideértve pl. valótlan községelszámolás, tanúsítványok vagy pénzügyi iratok hamisítása vagy megváltoztatása, vállalati eszközökkel visszaélés vagy eszközök eltulajdonítása, valótlan bejegyzés tétele pénzügyi vagy nem pénzügyi nyilvántartásban vagy jelentésben) tilos.</p>

<div class="va-etika-section-title">17. Összeférhetetlenség</div>
<p>Döntéseinknek objektív és tisztességes mérlegelésen kell alapulnia, és el kell kerülni a tisztességtelen befolyásolás lehetőségét. Az „összeférhetetlenség" akkor alakulhat ki, ha egy munkavállaló személyes érdeke (ami lehet pl. baráti vagy családi kapcsolatokkal, egy vevővel, versenytárssal, beszállítóval vagy alvállalkozóval fennálló viszonnyal összefüggő) összeütközésbe kerül, vagy potenciálisan összeütközésbe kerülhet a Weingartner György E.V. érdekeivel. Annak megítélése, hogy fennáll-e összeférhetetlenség, sokszor nem könnyű, ezért mindenkinek, akinek összeférhetetlenséggel kapcsolatos kérdése merül fel, konzultálnia kell a vezetőséggel.</p>
<p>Összeférhetetlenség eredhet például az alábbi helyzetekből:</p>
<ul>
<li>Munkaviszony (saját másodállás vagy családtag munkaviszonya) vagy gazdasági kapcsolat egy meglévő vagy potenciális vevővel, versenytárssal, beszállítóval vagy alvállalkozóval.</li>
<li>Családtagok vagy közeli kapcsolatban álló személyek alkalmazása vagy felügyelete. Igazgatósági vagy más testületi tagság másik gazdasági társaságnál vagy egyéb szervezetnél.</li>
<li>Jelentős befektetés vagy érdekeltség vevő, versenytárs, beszállító vagy alvállalkozó vállalkozásában.</li>
<li>Személyes érdekeltség, juttatás vagy potenciális személyes haszon egy vállalati tranzakcióhoz kapcsolódóan.</li>
</ul>
<p>Ha munkatársak személyes kapcsolatba kerülnek egymással, a magasabb beosztású munkatárs felelőssége, hogy ezt a vezetője tudomására hozza, és hogy meggyőződjön róla, hogy nem áll fenn összeférhetetlenség.</p>

<div class="va-etika-section-title">18. Adatvédelem, személyes adatok védelme</div>
<p>Tiszteletben tartjuk mindenki személyiségi jogait és elismerjük vevőink, munkavállalóink és egyéb természetes személyek azon igényét, hogy biztosak lehessenek abban, hogy személyes adataikat megfelelően, kizárólag jogszerű üzleti célból kezelik. Elkötelezettek vagyunk az adatvédelmi jogszabályoknak való megfelelés iránt. Kizárólag olyan személyes adatokat szerzünk meg és kezelünk, ami szükséges, és megfelelő tájékoztatást adunk az érintetteknek e tevékenységekről. Megfelelő információbiztonsági intézkedésekkel biztosítjuk a személyes adatok bizalmasságát, teljességét és hozzáférhetőségét.</p>
<p>Személyzetünk köteles követni a vonatkozó jogszabályi elvárásokat, megfelelő gyakorlatokat alkalmazni, valamint betartani az adatkezelés és feldolgozás törvényességét biztosító eljárásokat.</p>

        <button class="va-etika-pdf-btn" onclick="window.print()" type="button">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
            Üzleti Magatartási Kódex letöltése (PDF)
        </button>

    </div>
</div>
</section>

<?php get_footer(); ?>
