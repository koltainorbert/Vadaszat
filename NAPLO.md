# Fejlesztesi Naplo

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
