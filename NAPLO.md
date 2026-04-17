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
