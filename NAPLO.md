# Fejlesztesi Naplo

---

## 2026. 04. 18. – Session #36 (Visszanullázódó mentés végleges ok + javítás)

### Mit csináltunk [x]
- [x] Azonosítva: a `render_design()` és `render_header_footer()` mentési groupja fel volt cserélve
- [x] Javítva: Design oldal újra `va_design_settings` csoportot ment
- [x] Javítva: Fejléc + Lábléc oldal újra `va_header_footer_settings` csoportot ment
- [x] A fejléc/lábléc alapszín mezők visszakerültek a Fejléc + Lábléc oldalra
- [x] Következmény: a hero méret mezők mentéskor már nem nullázódnak vissza
- [x] Hibavizsgálat: módosított fájl hibamentes

---

## 2026. 04. 18. – Session #35 (Footer logó adminból)

### Mit csináltunk [x]
- [x] Új opciók a Fejléc + Lábléc oldalon: lábléc logó URL + lábléc logó magasság
- [x] A logó média pickerrel választható (`field_media`)
- [x] A lábléc első oszlopában megjelenik az adminban beállított logó
- [x] Biztonságos fallback: ha nincs megadva logó, a layout nem törik
- [x] Új CSS osztály a lábléc logóhoz (`.va-footer__brand-logo`)
- [x] Hibavizsgálat: módosított fájlok hibamentesek

---

## 2026. 04. 18. – Session #34 (Footer szétesés vizsgálat + fallback javítás)

### Mit csináltunk [x]
- [x] Footer vizsgálat lefuttatva (markup + dinamikus CSS + hibavizsgálat)
- [x] Azonosított kockázat: üresre mentett opciók esetén a lábléc feliratok eltűnhetnek
- [x] `footer.php` megerősítve: minden opció-vezérelt footer címke/szöveg kötelező fallbacket kap
- [x] Így a lábléc feliratok nem tudnak üresen maradni hibás mentés után sem
- [x] Hibavizsgálat: `footer.php` hibamentes
- [x] Deploy: `Deploy Theme` lefutott

---

## 2026. 04. 18. – Session #33 (Hero vs Header mentési konfliktus javítás)

### Mit csináltunk [x]
- [x] Az ok azonosítva: hero/design és fejléc/lábléc mezők ugyanabban a settings groupban voltak
- [x] Külön settings group létrehozva a fejléc/lábléc oldalhoz: `va_header_footer_settings`
- [x] A fejléc/lábléc opciók átemelve dedikált regisztrációba, így mentéskor nem nullázza a másik oldal mezőit
- [x] A Design oldalról kikerültek a fejléc/lábléc mezők, hogy ne legyen keveredés
- [x] A Fejléc + Lábléc oldal saját `settings_fields` blokkot kapott
- [x] Hibavizsgálat: módosított fájl hibamentes

---

## 2026. 04. 18. – Session #32 (10 db egykattintásos fejléc/lábléc preset)

### Mit csináltunk [x]
- [x] Fejléc + Lábléc oldalon új preset blokk: 10 db egykattintásos modern paletta
- [x] Új backend action: `va_apply_hf_preset` nonce + jogosultság ellenőrzéssel
- [x] Minden preset egyszerre állítja a header/footer gradient, border, shadow, glow és link hover opciókat
- [x] Preset nevek: Carbon Red, Steel Ember, Night Copper, Midnight Ice, Forest Glass, Obsidian Gold, Graphite Rose, Arctic Mint, Royal Plum, Desert Sand
- [x] Siker/hiba admin visszajelzés beépítve preset alkalmazás után
- [x] Hibavizsgálat: módosított fájl hibamentes

---

## 2026. 04. 18. – Session #31 (Full export/import + modern fejléc/lábléc paletta)

### Mit csináltunk [x]
- [x] Export/Import oldal bővítve teljes migráció opciókkal
- [x] Export: opcionálisan taxonómiák (`va_category`, `va_county`, `va_condition`) és fix oldalak tartalma is mehet a JSON-ba
- [x] Import: opcionálisan taxonómiák és oldalak visszaállítása/upsertje is lefut
- [x] Import visszajelzés bővítve: opciók + taxonómiák + oldalak darabszáma
- [x] Fejléc + Lábléc admin oldal modern színpaletta mezőkkel bővítve
- [x] Új fejléc vezérlés: gradient alapszínek, border szín, shadow szín, glow szín, kereső glow, CTA glow
- [x] Új lábléc vezérlés: gradient alapszínek, border szín, shadow/glow, link hover szín
- [x] Frontend dinamikus CSS bekötve az új paletta/árnyék opciókra
- [x] Hibavizsgálat: módosított fájlok hibamentesek

---

## 2026. 04. 18. – Session #30 (Admin Export/Import + Alaphelyzet)

### Mit csináltunk [x]
- [x] Új admin almenü: `Export / Import` a VadászApró menü alatt
- [x] Export funkció: teljes `va_*` opciókészlet JSON fájlba mentése
- [x] Import funkció: JSON visszatöltés, összes `va_*` opció frissítése
- [x] Alaphelyzet funkció: összes `va_*` beállítás törlése (kivéve védett kulcsok), majd defaultok újraépítése
- [x] Biztonság: jogosultság-ellenőrzés + nonce minden műveletnél
- [x] Admin visszajelzés: siker/hiba üzenetek import és reset után
- [x] Hibavizsgálat: módosított admin fájlok hibamentesek

---

## 2026. 04. 18. – Session #29 (Külön Fejléc + Lábléc admin menü, teljes paraméterezés)

### Mit csináltunk [x]
- [x] Új admin almenü: `Fejléc + Lábléc` a VadászApró menü alatt
- [x] Új, részletes fejléc opciók: magasság, belső spacing, üveg-hatás opacitás/blur, shadow
- [x] Új, részletes kereső opciók: szélesség, magasság, radius, border/bg alpha, ikonméret, ikon háttér
- [x] Új fejléc gomb opciók: radius, padding, glow, user gomb border/bg alpha
- [x] Új mobil kapcsolók: kereső és piros CTA gomb mobil láthatóság
- [x] Új fejléc szöveg opciók: kereső placeholder, login/register/submit feliratok
- [x] Új lábléc layout opciók: padding, grid gap, min oszlopszélesség, border alpha, max width
- [x] Új lábléc szöveg opciók: oszlopcímek, jogi link feliratok, copyright sor
- [x] Frontend bekötés kész: `functions.php` dinamikus CSS now kezeli az új fejléc/lábléc mezőket
- [x] `header.php` és `footer.php` opció-vezérelt szövegezést kapott
- [x] Hibavizsgálat: módosított fájlok hibamentesek

---

## 2026. 04. 18. – Session #28 (Repo rendrakás + kanonikus források)

### Mit csináltunk [x]
- [x] A keresőgomb aktív theme-ben fehér nagyító ikont kapott
- [x] Az egyértelműen nem használt, gyökérszintű duplikált theme/plugin forrásfájlok eltávolítva
- [x] A kiürült duplikált mappák törölve (`admin`, `frontend`, `includes`, `vadaszapro-theme` a repo gyökerében)
- [x] A repo hivatalos forrásai rögzítve:
  - `wp-plugin/vadaszapro-core`
  - `wp-theme/vadaszapro-theme`
- [x] `TELEPITES.md` frissítve, hogy később se keveredjen vissza a többforrásos állapot

---

## 2026. 04. 18. – Session #27 (Kereső belső keret törlés + hover-only külső neon)

### Mit csináltunk [x]
- [x] A kereső belső input-kontúr/fókuszkeret teljesen nullázva lett
- [x] A külső pontozott piros keret megszüntetve alapállapotban
- [x] Helyette csak hover/focus alatt jelenik meg finom külső neon piros kiemelés
- [x] A fix rásegítő override bekerült mindhárom theme `style.css` példányba
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #26 (Kereső végső override + duplikált theme szinkron)

### Mit csináltunk [x]
- [x] A kereső végső override stílusa ráírva a duplikált theme `style.css` fájlok végére is
- [x] A wp-theme példány is pontozott neon külső keretre lett állítva
- [x] Az input saját fókusz-border/outline/box-shadow teljesen nullázva lett
- [x] Cél: sehol ne maradjon belső piros kijelölés, csak a külső keret maradjon hangsúlyos
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #25 (Kereső nagyító láthatóság + piros belső kiemelés törlése)

### Mit csináltunk [x]
- [x] A keresőmező belső piros kiemelése eltávolítva
- [x] A jobb oldali ikon fekete nagyítóra állítva
- [x] A nagyító jobb láthatóságot kapott világos kör háttérrel
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #24 (Automatikus cache-bust CSS módosításokhoz)

### Mit csináltunk [x]
- [x] A theme `style.css` verziója most már `filemtime()` alapján töltődik
- [x] A plugin `frontend.css` is automatikus cache-bust verziózást kapott
- [x] Cél: a CSS módosítások biztosan azonnal megjelenjenek, ne ragadjon bent régi stílus
- [x] Hibavizsgálat: `functions.php` hibamentes
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #23 (Keresősáv dinamikus felülírás javítás)

### Mit csináltunk [x]
- [x] Javítva a Design rendszerből érkező dinamikus CSS felülírás
- [x] A keresőgomb kikerült a globális header accent háttérszabályból
- [x] Így az egyszerűsített kis piros nagyító stílus ténylegesen érvényesül
- [x] Hibavizsgálat: `functions.php` hibamentes
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #22 (Header keresősáv egyszerűsítés)

### Mit csináltunk [x]
- [x] Header keresősáv leegyszerűsítve, ugyanakkora hossz/magasság megtartásával
- [x] A jobb oldali nagy piros blokk helyett kis piros nagyító ikon került be
- [x] A nagyító enyhe lebegő animációt kapott
- [x] A mező sima lekerekített, letisztult megjelenést kapott
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #21 (Lebegő vissza-a-tetjére gomb körkörös indikátorral)

### Mit csináltunk [x]
- [x] A felső header progress sáv teljesen eltávolítva
- [x] Helyette lebegő gomb került az oldal aljára
- [x] Körkörös piros indikátor fut körbe görgetés alapján
- [x] Középen animált piros felfelé mutató nyíl került be
- [x] Kattintásra sima visszagörgetés az oldal tetejére
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #20 (Header felső/alsó tér növelése logóhoz)

### Mit csináltunk [x]
- [x] Header magasság növelve (`--nav: 66px`)
- [x] Header belső függőleges padding növelve (`.va-header__inner`: felül/alul több hely)
- [x] Cél: logó ne érjen bele az alsó progress csík/border zónába
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #19 (Hero sorköz magasság állítás)

### Mit csináltunk [x]
- [x] Design oldalon új hero sorköz (line-height) mezők:
  - főoldal cím + alcím
  - kategória cím + alcím
  - alkategória cím + leírás
  - kapcsolat cím + alcím
- [x] Frontenden dinamikus CSS-be bekötve (`line-height` felülírás)
- [x] Új helper a theme-ben: `va_design_float_option()`
- [x] Hibavizsgálat: módosított fájlok hibamentesek
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #18 (Dinamikus header scroll progress csík)

### Mit csináltunk [x]
- [x] A header alján lévő fix piros csík eltávolítva
- [x] Új dinamikus 2px progress csík került be a header aljára
- [x] A csík görgetés alapján töltődik/leürül (le/fel görgetéskor)
- [x] Markup + CSS + JS bekötés (`header.php`, `style.css`, `footer.php`)
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #17 (Főoldali hero logó feljebb igazítás)

### Mit csináltunk [x]
- [x] A főoldali hero logó 15px-el feljebb került (`transform: translateY(-15px)`)
- [x] Deploy: `Deploy All` lefutott
- [x] Ellenőrzés: új hiba nem keletkezett, csak korábbról ismert CSS kompatibilitási warningok maradtak

---

## 2026. 04. 18. – Session #16 (Admin mobil szorzó vezérlés)

### Mit csináltunk [x]
- [x] Design oldalon új mobil skála mezők:
  - `va_mobile_factor_hero`
  - `va_mobile_factor_header`
  - `va_mobile_factor_footer`
- [x] A fluid `clamp()` képletekbe bekötve a mobil szorzók (70–120%)
- [x] Új helper: `va_design_scaled_ratio()`
- [x] Validáció: módosított fájlok hibamentesek
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #15 (Reszponzív fluid méretezés a Design vezérléshez)

### Mit csináltunk [x]
- [x] A Design oldalon állítható méretek kimenete fix px helyett fluid `clamp()` alapú lett
- [x] Hero / fejléc / lábléc szövegméretek mobilra és tabletre automatikusan arányosodnak
- [x] Új helper került be a theme-be: `va_design_fluid_px()`
- [x] Validáció: `functions.php` hibamentes
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #14 (Összes hero + fejléc + lábléc méret/típus vezérlés)

### Mit csináltunk [x]
- [x] Design oldalon teljes méretvezérlés az összes hero szövegre:
  - Főoldal hero (badge, cím, alcím, gomb)
  - Kategória hero (badge, cím, alcím, stat szám, stat felirat)
  - Alkategória hero (badge, cím, leírás, találatszám)
  - Kapcsolat hero (badge, cím, alcím)
- [x] Fejléc elemek méret + típus/súly beállítások:
  - brand név méret/súly
  - navigáció méret/súly
  - keresőméret
  - fejléc gombok mérete
- [x] Lábléc elemek méret + típus/súly beállítások:
  - oszlopcím méret/súly
  - link méret/súly
  - alsó sor méret
- [x] Frontend dinamikus CSS kibővítve az új opciók lekövetésére
- [x] Hibavizsgálat: módosított fájlok hibamentesek
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #13 (Kiterjedt betűtípus + színrendszer külön Design oldalon)

### Mit csináltunk [x]
- [x] Új admin aloldal: `Design` (külön menüpont az Általánostól)
- [x] Kiterjedt betűtípus választó bevezetése (20 opció, Google Fonts támogatással)
  - Globális alap betűtípus
  - Címsorok betűtípusa
  - Fejléc/Navigáció betűtípusa
  - Tartalmi szöveg betűtípusa
  - Lábléc betűtípusa
- [x] Színrendszer bevezetése 4 szinten:
  - Globális
  - Fejléc
  - Tartalom
  - Lábléc
- [x] Admin oldalon WP Color Picker bekötve a színmezőkhöz
- [x] Frontenden dinamikus CSS kimenet:
  - globális változók (`--a`, `--t`, `--t2`)
  - célzott felülírások header/content/footer részekre
- [x] Kiválasztott betűk automatikus betöltése Google Fonts-ról (`display=swap`)
- [x] Hibavizsgálat: módosított fájlok hibamentesek
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #12 (Hero elemek igazítása adminból)

### Mit csináltunk [x]
- [x] Hero elemek igazíthatósága (bal/közép/jobb) adminból bevezetve mind a 4 aktív hero blokkra:
  - Főoldal hero
  - Kategória főoldal hero
  - Alkategória hero
  - Kapcsolat hero
- [x] Új beállítások az Általános oldalon:
  - `va_home_hero_align`
  - `va_kategoria_hero_align`
  - `va_tax_hero_align`
  - `va_contact_hero_align`
- [x] Sablonok osztály-alapú igazításra átvezetve
- [x] CSS: bal/közép/jobb variánsok + gombok/lead pozicionálása igazításhoz kötve
- [x] Ellenőrzés: PHP fájlok hibamentesek
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #11 (Minden hero szöveg adminból szerkeszthető)

### Mit csináltunk [x]
- [x] Teljes hero szöveg-admin bevezetés az összes használt hero blokkra:
  - Főoldali hero (`header.php`)
  - Kategória főoldal hero (`page-kategoria.php`)
  - Alkategória hero (`taxonomy-va_category.php`)
  - Kapcsolat hero (`page-kapcsolat.php`)
- [x] Új opciók a `VA_Settings_Page` Általános fülön:
  - badge, cím(ek), alcím, gombszövegek, stat feliratok, találatszám utótag
- [x] Sablonok átvezetve `get_option(...)` használatra fallback alapértékekkel
- [x] Hibavizsgálat: módosított PHP fájlok hibamentesek
- [x] Deploy: `Deploy All` lefutott

---

## 2026. 04. 18. – Session #10 (Hero logó pozíció adminból)

### Mit csináltunk [x]
- [x] Új admin opció: `va_hero_logo_position` (Bal / Közép / Jobb)
- [x] Beállítás mező hozzáadva az Általános oldalra
- [x] Header sablon frissítve: a hero logó osztálya opció alapján vált (`vh__logo--left|center|right`)
- [x] Új CSS igazítás osztályok a hero logóhoz
- [x] Deploy futtatva (`Deploy All`), hibamentes PHP ellenőrzés

---

## 2026. 04. 18. – Session #9 (Logó méretezés adminból + favicon torzulás javítás)

### Mit csináltunk [x]
- [x] Új admin méret mezők:
  - `va_header_logo_height` (Fejléc logó magasság px)
  - `va_hero_logo_height` (Hero logó magasság px)
- [x] Header és hero logó magasság beállítás opció alapján renderelve
- [x] Header logó vizuális tisztítás:
  - háttér/keret/saroklekerekítés/árnyék eltávolítva
  - `object-fit: contain` (ne torzuljon)
- [x] Hero logó megtartva + külön méret opció adminból
- [x] Favicon torzulás ellen automata négyzetes generálás az ikon attachmentből:
  - `32x32` és `180x180` PNG favicon fájlok készülnek (`/uploads/va-favicons/`)
  - head linkek ezeket használják
  - `get_site_icon_url` fallback is ezekre mutat
- [x] Deploy futtatva (`Deploy All`), PHP hibák nélkül

---

## 2026. 04. 18. – Session #8 (Médiatáras logó/ikon + favicon torzulás javítás)

### Mit csináltunk [x]
- [x] Adminban új, médiatárból tallózható képes mezők:
  - `Ikon (automata favicon)`
  - `Fejléc logó`
  - `Hero logó (főoldal)`
- [x] Új admin JS: WordPress Media Library picker (`Tallózás` / `Törlés` + preview)
- [x] `class-admin.php`: `wp_enqueue_media()` bekötve
- [x] Header logó kirajzolás logika:
  - elsődleges: `va_header_logo_url`
  - fallback: `va_brand_icon_url`
  - végső fallback: 🦌 ikon
- [x] Hero logó hely hozzáadva a főoldali hero blokkba (`va_hero_logo_url`, fallback: fejléc logó)
- [x] Logó torzulás javítás:
  - `object-fit: contain`
  - külön osztályok: `.va-logo__img--header` és `.va-logo__img--icon`
- [x] Favicon link output javítva: nem erőltetett `type=image/png`, így SVG/WEBP esetén sem torz jellegű fallback
- [x] Deploy futtatva (`Deploy All`)

---

## 2026. 04. 18. – Session #7 (Header név + ikon + automata favicon)

### Mit csináltunk [x]
- [x] Új admin mező: `va_brand_icon_url` (Fejléc ikon URL)
- [x] Header logó átállítva opció alapra:
  - név: `va_site_name`
  - ikon: `va_brand_icon_url`
  - fallback ikon: 🦌 ha URL üres
- [x] Automata favicon: a beállított ikon URL-ből kerül ki a `head`-be
  - `rel="icon"`
  - `rel="shortcut icon"`
  - `rel="apple-touch-icon"`
- [x] `get_site_icon_url` filter: ha WP Site Icon nincs külön beállítva, az admin ikon URL szolgál faviconként
- [x] Header ikon kép stílus (`.va-logo__img`) hozzáadva
- [x] Deploy futtatva (`Deploy All`), hibamentes PHP ellenőrzés

---

## 2026. 04. 18. – Session #6 (Hero badge piros pulzáló pont)

### Mit csináltunk [x]
- [x] Főoldali hero badge-be visszakerült a piros pulzáló pont
- [x] `header.php`: `vh__badge` elé `vcp-hero__badge-dot` elem beszúrva
- [x] `style.css`: `vh__badge` inline-flex + gap, hogy a pont és a szöveg egy sorban jól látszódjon
- [x] `style.css`: hiányzó `@keyframes dotBlink` definíció hozzáadva
- [x] Deploy futtatva (`Deploy All`)

---

## 2026. 04. 18. – Session #5 (Aukció globális kikapcsolás)

### Mit csináltunk [x]
- [x] Új globális kapcsoló: `va_enable_auctions` (adminban: **Aukció funkció engedélyezése**)
- [x] Kikapcsolás esetén az aukció CPT nem regisztrálódik (`va_auction`)
- [x] Taxonómiák csak `va_listing` post type-ra kötődnek, ha az aukció tiltott
- [x] Aukciós cron + AJAX licit rendszer tiltása kikapcsolt állapotban
- [x] Aukció oldalak tiltása: `va-aukciok`, `post_type=va_auction`, archive/single -> átirányítás a hirdetés oldalra
- [x] Frontendből eltüntetve az aukció menüpont és főoldali „Futó aukciók” blokk
- [x] Live search és listázó AJAX csak hirdetésekre keres, ha aukció ki van kapcsolva
- [x] Dashboardból eltüntetve a `Licitjeim` tab
- [x] Admin menüből eltüntetve az `Aukciók` beállítás almenü kikapcsolt módban
- [x] Admin statisztikák/felhasználó lista aukciós számai feltételessé téve
- [x] Toggle mezők javítása: hidden `0` érték hozzáadva, így OFF állapot biztosan menthető
- [x] Deploy futtatva (`Deploy All`), szintaxis hibák ellenőrizve (hibamentes)

### Eredmény
- Egyetlen admin kapcsolóval az aukció funkció teljes frontend/admin jelenléte kikapcsolható.
- A „csak apróhirdetés” üzemmód külön opciós oldalként tisztán használható.

---

## 2026. 04. 18. – Session #4 (kártya egységesítés)

### Mit csináltunk [x]
- [x] Javítva: a `Hirdetések` oldalon széteső kártya layout
- [x] Ok azonosítva: archív oldalon nem töltődött be a plugin egységes `va-frontend` CSS, ezért a téma régi `.va-card` szabályai felülírták a kívánt megjelenést
- [x] `wp-theme/vadaszapro-theme/functions.php` módosítva:
  - a `va-theme` mellé globálisan betöltjük a plugin `frontend/css/frontend.css` fájlját (`va-frontend` handle)
  - dependency: `[ 'va-theme' ]`, verzió: `VA_VERSION`
- [x] Deploy futtatva (`Deploy All`) és hibavizsgálat lefuttatva (`functions.php` hibamentes)

### Eredmény
- A kártyák megjelenése egységes lett az egész oldalon (kereső, archívum, kategória): arányos kép + rendezett cím/ár/meta blokk.

### Hotfix (ugyanebben a sessionben)
- [x] Hiba: `Hirdetések` oldalon a találatszám látszott, de kártyák nem
- [x] Ok: a kártyák `va-animate` class miatt CSS-ben alapból rejtve voltak (`opacity:0`), de ezen az oldalon nem futott mindig a láthatóvá tevő JS
- [x] Javítás:
  - `frontend.css`: `va-animate` alapállapot látható
  - csak JS jelenlét esetén legyen rejtett (`html.va-js .va-animate`)
  - `frontend.js`: `document.documentElement.classList.add('va-js')`
- [x] Deploy: `Deploy All` kész

### Admin funkció bővítés
- [x] Adminból duplikálható hirdetések (`va_listing`) támogatás
- [x] Lista művelet: `Duplikálás` link a hirdetés sorában
- [x] Biztonság: jogosultság + nonce ellenőrzés
- [x] Duplikálás tartalma: cím/tartalom/kivonat + taxonómiák + post meta (lock mezők kihagyva)
- [x] Új bejegyzés státusz: `draft` (`(Másolat)` utótaggal)
- [x] Sikeres duplikálás után automatikus átirányítás az új piszkozat szerkesztőjére

### Kapcsolat oldal
- [x] Új egyedi kapcsolati oldal sablon: `page-kapcsolat.php`
- [x] Kizárólag e-mailes kapcsolatfelvétel támogatás
- [x] Backend küldés `wp_mail()`-lel, WP Mail SMTP kompatibilisen
- [x] Védelem: nonce + honeypot mező + szerveroldali validáció
- [x] Automatikus `kapcsolat` oldal létrehozás hozzáadva a theme oldal-generáláshoz
- [x] Header menü link a `/kapcsolat` oldalra megerősítve
- [x] Deploy: `Deploy All` kész

### Hero videó csere
- [x] Főoldali header hero videó fallback URL cserélve új offroad videóra
- [x] Új videó: `/wp-content/uploads/2026/04/0_Offroad_4x4_1920x1080.mp4`

### Kapcsolat űrlap UX
- [x] Telefonszám mező hozzáadva a kapcsolati űrlaphoz
- [x] Gépelős placeholder effekt a Kapcsolat oldali mezőkben
- [x] A typewriter placeholder nem ír bele a valódi input értékbe, csak a placeholdert animálja

### Videók adminból
- [x] Főoldal hero videó URL admin beállításból vezérelhető
- [x] Kapcsolat oldal videó URL admin beállításból vezérelhető
- [x] Kategóriák alatti videó URL admin beállításból vezérelhető
- [x] Új mezők: Általános beállítások oldalon (`va_home_hero_video_url`, `va_contact_hero_video_url`, `va_category_video_url`)
- [x] Sablonok átvezetve opció alapú URL-re fallbackkel

---

## 2026. 04. 17. – Session #3 (délelőtt + délután)

### Mit csináltunk [x]
- [x] `single-va_listing.php` teljes újraírás — sérült UTF-8 fájl törölve, HTML entitásokkal újraírva (encoding-biztos megoldás)
  - Minden magyar szöveg HTML entitásként: `R&eacute;szletek`, `Felad&oacute;` stb.
  - JS watchlist szöveg: `\u0151` Unicode escape
  - 2-column layout megőrizve (`.sl__` prefix)
- [x] Videó HERO szekció hozzáadva a főoldalra (`header.php`)
  - Teljes viewport (`100vh`) autoplay/muted/loop mp4 háttér
  - Rétegzett overlay: teteje átlátszó, alja belefut `rgb(6,6,6)`-ba
  - Bal oldali piros accent vonal
  - CTA gombok: Hirdetés feladása + böngészés
  - Stats sor: élő adatok (hirdetések, aukciók, felhasználók száma)
  - Animált scroll jelzés
  - Videó URL: `va_hero_video_url` WP opcióból szerkeszthető
- [x] Header / Navbar teljes 2026 modernizálás
  - `position: fixed` + **scroll-aware glass effect** (40px után aktiválódik)
  - Logo: octagon piros ikon + `vadaszapro.net` subtitle
  - Nav link hover: piros alulvonás slide-animáció (nem background highlight)
  - CTA gomb: corner-cut szögletes design + erős red glow
  - Hamburger: 3 vonal → X CSS animáció, body scroll lock mobilon
  - Aktív nav item URL alapú automatikus jelölés
  - Belső oldalak: `padding-top: var(--nav)` (`.va-site-wrap--inner`)
  - Főoldalon: hero videó tölti a 100vh-t
- [x] Reklámzónák eltávolítva fejlécből (`header_top`, `header_bottom` — ki van szedve)
- [x] Minden git-be pusholva (origin/main = HEAD)

### Hol tartunk
A téma vizuálisan teljesen megújult. A főoldalon video hero van, a header modern glass-effect navbarral rendelkezik. Az egész encoding-biztos (HTML entitások mindenhol).

**Deploy path:**
- Plugin: `D:\LocalWP\apr-vadsz\app\public\wp-content\plugins\vadaszapro-core`
- Téma: `D:\LocalWP\apr-vadsz\app\public\wp-content\themes\vadaszapro-theme`

### Nyitott TODO-k
- [ ] Főoldal hirdetés grid / category kártyák szekció a hero alá
- [ ] `archive.php` grid kártya dizájn egységesítés
- [ ] Keresési oldal (`[va_listing_search]`) vizuális refresh
- [ ] Hirdetés feladás form UX javítás
- [ ] Mobilon hero videó tesztelés (poster kép beállítás iOS-re)
- [ ] `va_hero_video_url` beállítás WP adminba bevezetni (Settings oldalra)

---



### Mit csináltunk [x]
- [x] Projekt architektúra megtervezve (WordPress plugin + téma)
- [x] `vadaszapro-core` WordPress plugin teljes struktúra létrehozva
- [x] Custom Post Types: `va_listing` (hirdetés), `va_auction` (aukció)
- [x] Taxonómiák: kategória (fa struktúra, előre feltöltve), megye (20 db), állapot
- [x] Meta mezők admin metabox-szal (ár, márka, modell, kaliber, telefon stb.)
- [x] Felhasználói rendszer: regisztráció, bejelentkezés, logout, profil szerkesztés (custom WP-oldalakon)
- [x] Aukció rendszer: AJAX licit leadás, real-time visszaszámlálás, e-mail értesítők, nyertes meghatározás (hourly cron)
- [x] 6 reklámzóna: header_top/bottom, sidebar_left/right, content_top, footer_top – mind HTML-alapú, backendből szerkeszthető
- [x] Backend Settings oldal (6 fül): Általános, Reklámzónák, Hirdetések, Aukciók, Felhasználók, Statisztika
- [x] Frontend shortcode-ok: `[va_listing_search]`, `[va_submit_listing]`, `[va_auction_list]`, `[va_login_form]`, `[va_register_form]`, `[va_user_dashboard]`
- [x] AJAX hirdetésszűrő: kulcsszó, kategória, megye, állapot, ár range, rendezés
- [x] Dashboard: hirdetések kezelése, licitek, watchlist/kedvencek, profil szerkesztés
- [x] `vadaszapro-theme` WordPress téma: SDH design (sötét, #ff0000, dot grid)
- [x] Header: sticky, logo, navigáció, reklám sáv, kategória gyorsmenü
- [x] Layout: 3 oszlop (bal sáv | tartalom | jobb sáv) – responsive
- [x] Főoldal: hero szekció, stat számok, legújabb/kiemelt hirdetések + aukciók
- [x] `single-va_listing.php`: galéria, paraméterek, telefonszám reveal, watchlist
- [x] `single-va_auction.php`: aukció box, visszaszámlálás, licit form, bid history
- [x] Aktiváláskor: adatbázis táblák, alap WordPress oldalak automatikus létrehozása
- [x] `TELEPITES.md` dokumentáció

### Hol tartunk
A teljes WordPress plugin + téma alap struktúra elkészült. A projekt felépítése:
```
D:\Vadaszat2026\
├── wp-plugin\vadaszapro-core\   ← Plugin (→ wp-content/plugins/)
├── wp-theme\vadaszapro-theme\   ← Téma (→ wp-content/themes/)
├── TELEPITES.md                 ← Részletes telepítési útmutató
└── NAPLO.md
```

### Nyitott TODO-k
- [ ] WordPress szerver beállítás (localhost WAMP/XAMPP vagy tárhely)
- [ ] Plugin + téma átmásolása a WordPress könyvtárba
- [ ] Tesztelés, hibakeresés élesben
- [ ] Képgaléria lightbox JS
- [ ] Adatvédelmi + ÁSZF oldalak szövege
- [ ] WooCommerce integráció (kiemelés fizetős)
- [ ] Üzenetküldő rendszer hirdetők között
- [ ] Schema.org SEO markup
- [ ] Google Maps helyszín térkép

---

## 2026. 04. – Session: Dizájn teljes újraírás (dark theme)

### Mit csináltunk [x]
- [x] Referencia design lekérve: `https://github.com/koltainorbert/tt1/vadasz-apro/public/index.html`
- [x] `style.css` v3.0.0 – teljes újraírás a referencia alapján:
  - Háttér: `rgb(6,6,6)` + pont rács (`radial-gradient`, 28px)
  - Akcentszín: `#ff0000` / `#ff4444`
  - Fehér szöveg (`#fff`) mindenhol
  - Header: sticky, sötét, piros `border-bottom`, `backdrop-filter: blur`
  - Cat-bar: sticky, vízszintes pill-stílusú kategória gombok emoji ikonokkal
  - Kártyák: `rgba(255,255,255,.025)` háttér, `rgba(255,255,255,.07)` keret
  - Hover: `translateY(-3px)` + piros ragyogás
  - Footer: `rgb(12,10,10)` háttér
  - Form inputok: sötét háttér, fehér szöveg, piros focus
  - Scrollbar: piros
- [x] `functions.php` – `va_category_icon()` átírva: SVG ikonok → emoji ikonok
  - slug + név alapú keresés (pl. `golyos-puska` → `🎯`, `trofea` → `🦌`)
  - 34 kategória slug leképezve
- [x] `header.php` – 🦌 emoji hozzáadva a logó mellé; bejelentkezés gomb stílus javítva
- [x] Összes fájl másolva `D:\LocalWP\apr-vadsz\app\public\...`

### Hol tartunk
WordPress theme v3.0 deployed a LocalWP-be. A dizájn sötét (dark), piros akcenttel,
emoji kategória ikonokkal – pontosan a referencia (vadasz-apro/public/index.html) stílusában.

### TODO
- [ ] Böngészőben megnézni az eredményt (localhost), esetleges apró igazítások
- [ ] Plugin card template (`va_template('listing/card')`) osztályok ellenőrzése (`.va-card__img` stb.)
- [ ] Hirdetés felvétel tesztelés
- [ ] Push (Ctrl+Shift+B)

---
