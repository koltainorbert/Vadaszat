# Fejlesztesi Naplo

---

## 2026. 04. 17. – Session #1

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
