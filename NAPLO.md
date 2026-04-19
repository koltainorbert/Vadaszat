# Fejlesztesi Naplo

---

## 2026. 04. 19. – Session #112 (Auto-update, Child theme, Rate limiting, Email rendszer)

### Elvégzett feladatok

#### Auto-update rendszer (GitHub Releases alapján)
- **`includes/class-updater.php`** – új fájl, független, 3rd party library nélkül
  - GitHub Releases API polling (6 óránkénti transient cache)
  - WP natív `pre_set_site_transient_update_plugins` + `plugins_api` hook
  - Zip letöltés: Release asset `.zip` → fallback GitHub auto-zip
  - `fix_folder_name()`: GitHub archive mappa → `vadaszapro-core/` átnevezés
- **`vadaszapro-core.php`** bővítve:
  - `VA_GITHUB_REPO` és `VA_GITHUB_TOKEN` konstansok (wp-config.php-ban állítható)
  - `require_once class-updater.php` + `VA_Updater::init()` a boot blokkban

#### Child theme
- **`wp-theme/vadaszapro-child/style.css`** – `Template: vadaszapro-theme` fejléccel
- **`wp-theme/vadaszapro-child/functions.php`** – szülő CSS enqueue, placeholder hook-okhoz

#### Rate limiting (publikus AJAX)
- **`class-ajax.php`**: `is_rate_limited()` privát helper – IP-alapú, transient-tel
  - `live_search`: 60 kérés/perc/IP limit
  - `filter_listings`: 60 kérés/perc/IP limit
  - 429-es JSON hibaválasz túllépés esetén

#### Email rendszer (előző session, most lezárva)
- `VA_Mailer` HTML email osztály – branded template, inline CSS
- 4 rendszer email (regisztráció, listing published, listing deleted, account deleted)
- Admin-editable sablonok: `vadaszapro-emails` panel
- Aukció emailek szintén admin-szerkeszthetők
- Sidebar fix: Aukció beállítások + Email sablonok menüpontok látszanak

### Állapot
- Auto-update: ✅ kész + deployed
- Child theme: ✅ kész (telepítés: WP admin → Megjelenés → Témák → Aktiválás)
- Rate limiting: ✅ kész + deployed
- Email rendszer: ✅ kész + deployed

### Beállítandó (deploy után)
- `VA_GITHUB_REPO` konstans: `wp-config.php`-ba → `define('VA_GITHUB_REPO', 'felhasznalonev/vadaszapro-core');`
- Child theme aktiválása a WP adminban

### Következő session lehetséges feladatok
- [ ] Child theme LocalWP-be deploy + aktiválás
- [ ] GitHub repo létrehozása az auto-update teszteléséhez
- [ ] Rate limiting finomhangolás (objektum cache ha elérhető)

---

## 2026. 04. 19. – Session #111 (Quill editor bugfix + képtörlés cleanup)

### Elvégzett feladatok
- **Admin editor mentés/betöltés fix**: `dangerouslyPasteHTML` → `root.innerHTML` (képméret megmarad)
- **Admin "Mentés vázlatként" gomb fix**: `form.submit()` bypass → `vaAdminDoSubmit()` (Quill tartalom most már mentődik)
- **Admin kép upload fix**: DOM-módosítás helyett HTML string-csere (MutationObserver bug kikerülve)
- **Admin törlés fix**: `wp_trash_post` → `wp_delete_post(true)` (valódi törlés, képek is törlődnek)
- **`before_delete_post` hook bővítés**: editor képek + galéria képek + kiemelt kép törlése listing törlésekor
- **Frontend törlés fix**: `delete_listing_with_images` bővítve – `post_content` img URL-ek regex-szel kinyerve, `attachment_url_to_postid()` alapján törölve (post_parent=0 esetén is működik)

### Állapot
- Admin Quill editor: ✅ teljesen kész
- Frontend Quill editor: ✅ teljesen kész
- Képtörlés (listing törléskor): ✅ minden ág lefedve (editor, galéria, borítókép, post_parent=0)

### Következő session lehetséges feladatok
- [ ] Email HTML support (wp_mail HTML headers)
- [ ] Auto-update rendszer GitHub-ról (YahnisElsts/plugin-update-checker)
- [ ] Child theme



### Allapot
- **Git HEAD:** `Auto_2026.04.19_14.50` — ez az utolsó stabil állapot, TinyMCE még nincs benne
- **Következő feladat:** TinyMCE rich editor bevezetése (erről most nem lett kész)
- Előző session (109) bugok javítva: enforce_plan_limits, dashboard UI, auto-visszaállítás
- TinyMCE implementáció közben adatvesztés történt (leírás eltűnt, 5 hirdetés privát lett)
- `va_recover.php` script segítségével az adatok visszaállítva
- Git reset → `Auto_2026.04.19_14.50`-re visszaálltunk, deploy kész

### Következő session teendői
- [ ] TinyMCE editor implementáció ÓVATOSAN:
  - `submit-form.php`: textarea → `wp_editor()` sötét skinnel
  - `class-listing-edit.php`: textarea → `wp_editor()` admin stílusban
  - `class-ajax.php`: `va_submit_listing` handler: `sanitize_textarea_field` → `wp_kses_post`
  - `class-ajax.php`: `va_update_listing` handler: ellenőrzés/javítás ugyanígy
- [ ] Tesztelés: hirdetés feladás + szerkesztés + mentés után leírás megmarad-e

### Tanulság
- TinyMCE bevezetésekor a szerkesztő `post_content`-et írja (WP default mező)
- Az AJAX handler ha `sanitize_textarea_field`-et használ, a HTML formázást levágja → leírás "eltűnik"
- Mindig `wp_kses_post`-ot kell használni leíráshoz ha rich editor van

---

## 2026. 04. 19. – Session #107 (Rang-alapu csomagvasarlas UI + teljes vasarlasi flow)

### Mit csinaltunk [x]
- [x] `wp-plugin/vadaszapro-core/includes/class-shortcodes.php` – `render_buy_credits()` teljesen ujraepitve
  - rang-alapu (Basic / Silver / Gold / Platinum) kartyas csomagvalaszto
  - kedvezmeny toggle es egyedi mennyiseg blokk kiveve
  - `va_return` kezeles (`buy` / `submit`) es ennek tovabbitasa checkoutra
- [x] `wp-plugin/vadaszapro-core/frontend/css/frontend.css` – uj premium pricing design rendszer
  - dot-grid + glow hero, modern kartyak, rank temak, responsive racs
- [x] `wp-plugin/vadaszapro-core/includes/class-ajax.php`
  - `buy_credits()` bovites: `return_to` fogadas + tokenben tarolas
  - callback iranyitas: siker/cancel utan megfelelo oldalra vissza (`submit` vagy csomag oldal)
  - uj helper-ek: buy page URL + redirect
- [x] `wp-theme/vadaszapro-theme/header.php` – uj `Vásárlás` header gomb
  - bejelentkezett user: direkt csomagoldal
  - vendeg user: loginra iranyit, majd csomagoldal
- [x] `wp-plugin/vadaszapro-core/frontend/templates/listing/submit-form.php`
  - csomagvasarlas link dinamikussa teve
  - hirdetes-feladasi flow `va_return=submit` paramrel a csomagoldalra visz
- [x] Hibavizsgalat: erintett fajlok hibamentesek
- [x] Deploy All: kesz ✅

### Eredmeny
- Headerbol a `Vásárlás` gomb vegre tenylegesen a csomagvalaszto oldalra visz.
- A csomagoldalon rangok szerinti modern UI-bol indul a bankkartyas fizetes.
- Ha a hirdetes feladas kozben kell csomag, ugyaninnen valaszt/fizet a user, es fizetes utan visszakerul a feladashoz.

---

## 2026. 04. 19. – Session #106 (Csomag beallitasok admin UI teljes ujrarendezes)

### Mit csinaltunk [x]
- [x] `wp-plugin/vadaszapro-core/admin/class-settings-page.php` – a `render_plans()` teljesen ujraepitve
- [x] a bent ragadt, duplikalt regi csomagbeallito markup teljes torlese
- [x] uj admin informacios architektura: bal oldali csomag navigacio + jobb oldali reszletes szerkeszto panel
- [x] uj osszefoglalo blokkok: limit, basis, cooldown, badge szin gyors attekintessel
- [x] panelenkenti elo badge preview es azonnali sidebar/meta frissites input valtozasra
- [x] kulon globalis panel a boost badge rendszerkozpontu beallitasaihoz
- [x] hibavizsgalat: `class-settings-page.php` hibamentes
- [x] Deploy Plugin: kesz ✅

### Eredmeny
- A csomagkezelo oldal mar nem szeteso kartyahalom, hanem egy attekintheto, professzionalis admin szerkeszto felulet.
- Gyorsabb lett a csomagok kozotti valtas, jobban elkulonul a megjelenes, a limitlogika, a boost es a marketing adat.
- A korabbi duplikalt markup kikerult, igy a `render_plans()` szerkezete is tiszta maradt.

---

## 2026. 04. 20. – Session #105 (Felhasználói terv rendszer + Boost kiemelés)

### Mit csinaltunk [x]
- [x] `includes/class-user-roles.php` – ÚJ FÁJL: VA_User_Roles class létrehozva
  - 4 csomag (Basic / Silver / Gold / Platinum) PLANS konstanssal
  - `get_user_plan()`, `get_plan_config()`, `can_post_listing()` hirdetési limit logika
  - `can_boost()`, `do_boost()`, `is_boosted()` kiemelés rendszer
  - `filter_posts_clauses()` – boost sort a listákban (wp_postmeta LEFT JOIN + ORDER BY boost_time)
  - `ajax_admin_set_plan()` – admin AJAX plan váltás
  - `ajax_boost_listing()` – felhasználói AJAX boost
- [x] `vadaszapro-core.php` – bekötve: require + VA_User_Roles::init()
- [x] `includes/class-ajax.php` – submit_listing: kredit alapú limit → plan alapú limit csere
- [x] `admin/class-settings-page.php` – render_users() teljes újraírás:
  - Plan összefoglaló kártyák (4 db felhasználószámmal)
  - User tábla: avatar, plan badge, boost cooldown, havi progress bar
  - Inline plan editor (AJAX, platinum extra mezők: custom limit/cooldown/note)
  - Lapozó (40/oldal), keresés + plan szűrő
- [x] `admin/class-admin.php` – Social Media sidebar elem hozzáadva
- [x] `frontend/templates/user/dashboard.php` – Plan badge a navban + boost gomb per-hirdetés
- [x] `frontend/templates/listing/card.php` – boost badge (⚡ Előre téve, 14 napos ablak)
- [x] `frontend/css/frontend.css` – .va-card__badge--boost CSS hozzáadva
- [x] Deploy Plugin: kész ✅

### Eredmeny
- 4 tervszint (Basic/Silver/Gold/Platinum) teljes backend + admin + frontend integráció
- Admin bármely felhasználó csomagját módosíthatja (Platinum: egyedi limit + cooldown + note)
- Felhasználók a dashboardon ⚡ Előre gombbal kiemelhetik saját hirdetéseiket
- Kiemelési cooldown csomagonként: Basic=7n, Silver=5n, Gold/Platinum=3n
- Kiemelt hirdetések a kategória-listában elöl jelennek meg (boost_time ORDER BY)

 (Vadásznaptár desktop: alsó fekete csúszka + felső hónap-indikátor eltávolítás)

### Mit csinaltunk [x]
- [x] `wp-theme/vadaszapro-theme/index.php` – nyitooldali vadásznaptárból kiveve a felső, scroll közbeni hónap-indikátor (`va-hn-month-ind`)
- [x] a hozzá tartozó JS logika törölve (`updateHnMonthIndicator` és hívásai)
- [x] vízszintes chart csúszka desktop stílus finomítva: fekete alsó scrollbar track + sötét thumb (`.va-hnaptar__scroll`)
- [x] `wp-theme/vadaszapro-theme/page-vadasz-naptar.php` – külön vadásznaptár oldalon is kiveve a felső hónap-indikátor (`vn-chart-month-ind`)
- [x] a hozzá tartozó JS logika törölve (`updateVnMonthIndicator` és hívásai)
- [x] vízszintes chart csúszka desktop stílus finomítva: fekete alsó scrollbar track + sötét thumb (`.vn-chart-scroll`)
- [x] Hibavizsgálat: módosított fájlok hibamentesek
- [x] Deploy Theme: kész ✅

### Eredmeny
- Desktopon a vadásznaptár vízszintes csúszkája sötét/fekete megjelenésű az alsó sávban.
- A naptár tetején oldalra húzáskor már nem jelenik meg hónapnév-kijelzés.

---

## 2026. 04. 19. – Session #103 (Termékoldal Designer admin menü + wireframe presetek + frontend paraméterezés)

### Mit csinaltunk [x]
- [x] `wp-plugin/vadaszapro-core/admin/class-admin.php` – új admin menüpont: `Termékoldal` (`vadaszapro-single-designer`)
- [x] topbar cím map frissítve: `Termékoldal Designer`
- [x] sidebarban új elem: `🧱 Termékoldal`
- [x] `wp-plugin/vadaszapro-core/admin/class-settings-page.php` – új settings csoport: `va_single_settings` (layout, galéria, tipó, gombok, színek, viewer)
- [x] új preset action hook: `admin_post_va_apply_single_preset`
- [x] új preset handler: `handle_apply_single_preset()`
- [x] új preset gyűjtemény: `get_single_presets()` (Cinematic Hero, Compact Trade, Editorial Stack)
- [x] új render oldal: `render_single_designer()`
- [x] grafikus wireframe előnézet élő frissítéssel (input/change → preview CSS változók)
- [x] `wp-theme/vadaszapro-theme/single-va_listing.php` – `va_single_*` opciók beolvasása és validálása
- [x] dinamikus inline CSS: max szélesség, oszlopok, gap, fő kép arány/fit, thumb méret, card radius/padding, cím/ár/meta méret, gomb méret, share méret, accent/glass/border, viewer háttér
- [x] layout osztály kapcsolás: `sl--layout-split` / `sl--layout-stacked`
- [x] Hibavizsgalat: modosított fájlok hibamentesek

### Eredmeny
- A termékoldal teljes megjelenése adminból paraméterezhetővé vált, presetekkel és vizuális wireframe előnézettel.
- A mentett beállítások azonnal érvényesülnek a hirdetés részletes oldalon.

---

## 2026. 04. 19. – Session #102 (Naptar UX Pro: scroll hint + sticky oszlop arnyek + mini honap indikator)

### Mit csinaltunk [x]
- [x] `wp-theme/vadaszapro-theme/index.php` – nyitooldali vadasz naptar UX bovites:
- [x] mobil scroll hint: "Húzd oldalra a naptárat"
- [x] mini honap indikator (scroll pozicio alapjan)
- [x] sticky elso oszlop (faj/csoport) vizualis arnyek leválasztassal
- [x] `wp-theme/vadaszapro-theme/page-vadasz-naptar.php` – ugyanazon UX fejlesztesek a kulon naptar oldalon
- [x] JS: overflow allapot figyeles + resize recalculation + scroll allapot markolas
- [x] Deploy Theme: exit 0 ✅

---

## 2026. 04. 19. – Session #101 (Vadasz naptar teljes responsive overhaul: nyitooldal + kulon oldal)

### Mit csinaltunk [x]
- [x] `wp-theme/vadaszapro-theme/index.php` – nyitooldali `va-hnaptar` mobil-first atdolgozas
- [x] fix oszlopszelessegek CSS valtozora emelve (`--va-hn-name-w`)
- [x] chart olvashatosag: fixalt minimum szelessegek viewporttol fuggoen (`980/860/760/680/620`)
- [x] tobb torespont (1024, 760, 560, 420) + tipografia/padding finomitas
- [x] extrém keskeny kijelzon sub sorok tomoritese (sub elrejtese 420px alatt)
- [x] `wp-theme/vadaszapro-theme/page-vadasz-naptar.php` – azonos responsive hardening a kulon naptar oldalon
- [x] oszlopszelesseg valtozo (`--vn-name-w`) + chart min-width lepcsok
- [x] Deploy Theme: exit 0 ✅

### Eredmeny
- A naptar elemei nem tornek szet mobilon, olvashatoak maradnak, a chart konzisztensen vizszintesen gorgetheto.

---

## 2026. 04. 19. – Session #100 (Viewer responsive hardening + touch gesztusok)

### Mit csinaltunk [x]
- [x] `wp-theme/vadaszapro-theme/single-va_listing.php` – erinteses gesztusok a fullscreen viewerben:
- [x] swipe balra/jobbra kepvaltas (zoom=1 allapotban)
- [x] touch drag mozgatás nagyitasnal (zoom>1)
- [x] `wp-theme/vadaszapro-theme/style.css` – safe-area hardening (`env(safe-area-inset-*)`) toolbar/close/nav elemekre
- [x] Extra responsive finomhangolas 420px alatt (kontroll meretek, zoom trigger felirat elrejtese)
- [x] `touch-action: none` a stage-en a stabil mobil interakcioert
- [x] Deploy Theme: exit 0 ✅

---

## 2026. 04. 19. – Session #99 (Viewer lapozas javitas: elozo/kovetkezo kep)

### Mit csinaltunk [x]
- [x] `wp-theme/vadaszapro-theme/single-va_listing.php` – fullscreen viewerbe elozo/kovetkezo gombok (`sl-viewer-prev`, `sl-viewer-next`)
- [x] Billentyuzet navigacio: nyil balra/jobbra kepvaltas, ESC bezaras
- [x] Main kep + thumb aktiv allapot szinkronizalas lapozas kozben
- [x] `wp-theme/vadaszapro-theme/style.css` – viewer nav gomb stilusok (desktop + mobil)
- [x] Deploy Theme: exit 0 ✅

---

## 2026. 04. 19. – Session #98 (Single product full-frame galeria + profi zoom kepnezegeto)

### Mit csinaltunk [x]
- [x] `wp-theme/vadaszapro-theme/style.css` – fo galeria kep frame-kitoltese (`object-fit: cover`) + zoom trigger gomb
- [x] `wp-theme/vadaszapro-theme/single-va_listing.php` – fullscreen kepnezegeto overlay bevezetese
- [x] Viewer funkciok: megnyitas fokeprol, ESC/overlay bezaras, zoom +/-, reset, egergorgos zoom, drag mozgatas nagyitasnal
- [x] Mobil finomhangolas (1:1 fo kep, toolbar/close pozicio)
- [x] Safari kompatibilitas: `-webkit-backdrop-filter`, `-webkit-user-select`
- [x] Deploy Theme: exit 0 ✅

---

## 2026. 04. 19. – Session #97 (Szerkeszteskor ures keppaletta javitas)

### Mit csinaltunk [x]
- [x] `wp-plugin/vadaszapro-core/frontend/templates/listing/submit-form.php` – edit mod kepbetoltes robust fallback logika:
- [x] elso korben `va_gallery_ids` olvasas (uj formatum)
- [x] ha ures, legacy `va_gallery` kezeles (tomb es vesszos string formatum)
- [x] ha csak kiemelt kep van, az is bekerul a palettaba
- [x] ha a kiemelt kep nincs a gallery listaban, automatikusan elore beszurjuk
- [x] Eredmeny: regi es uj hirdeteseknel is megjelennek a mar feltoltott kepek szerkeszteskor
- [x] Deploy Plugin: exit 0 ✅

---

## 2026. 04. 19. – Session #96 (Social ikon minoseg + link masolas javitas + frontend push toast rendszer)

### Mit csinaltunk [x]
- [x] `wp-plugin/vadaszapro-core/includes/helpers.php` – social ikonkeszlet finomitas, hivatalosabb brand karakter (kulon Facebook lettermark)
- [x] `wp-theme/vadaszapro-theme/single-va_listing.php` – megosztas ikonok atallitva kozponti `va_social_svg()` helperre (header/footer/single egyseges)
- [x] `wp-theme/vadaszapro-theme/single-va_listing.php` – link masolas javitas:
- [x] `navigator.clipboard` secure context esetben
- [x] fallback `document.execCommand('copy')` insecure/local kornyezetre
- [x] siker/hiba push visszajelzes masolas utan
- [x] `wp-plugin/vadaszapro-core/frontend/js/frontend.js` – push toast globalizalas (`window.va_toast`), title javitas (`Sikeres` / `Sikertelen`), szerver oldali `va-notice` automatikus push-ra tukrozese
- [x] `wp-plugin/vadaszapro-core/frontend/templates/listing/submit-form.php` – hirdetes feladas/szerkesztes osszes AJAX kimenetere push notifikacio (siker, hiba, kredit/fizetes kovetelmeny, halozati hiba)
- [x] `wp-plugin/vadaszapro-core/frontend/css/frontend.css` – push szinkodok javitasa: siker = zold, hiba = piros
- [x] Deploy All: exit 0 ✅

### Megjegyzes
- A push visszajelzes immar form menteseknel es masolasnal is konzisztensen mukodik.

---

## 2026. 04. 19. – Session #95 (Admin Panel teljes személyre szabás – presetek + live preview + CSS injektálás)

### Mit csinaltunk [x]
- [x] `class-settings-page.php` – `render_adminpanel()` megírva (~400 sor): teljes UI szerkesztőoldal
  - 6 szekció: Márka/logó, Háttér rétegek, Szöveg+szegély, Accent szín, Layout méretek, Betűtípus
  - 10 db egy kattintásos preset (Dark Crimson, Midnight Navy, Forest Command, Obsidian Gold, Graphite Purple, Carbon Steel, Copper Dark, Steel Ember, Arctic White, Royal Plum)
  - Beágyazott live preview (topbar + sidebar + KPI kártyák + tábla szkeletonnal)
  - JS: form input → preview CSS változók real-time frissítése
- [x] `class-settings-page.php` – `handle_apply_ap_preset()`: nonce-védett preset alkalmazás, `update_option` loop, redirect `?va_ap_preset=ok/invalid`
- [x] `class-settings-page.php` – `get_adminpanel_presets()`: 10 preset tömb, minden preset tartalmaz bg/bg2/accent/accent2 swatchokat + teljes options[] map-et
- [x] `class-settings-page.php` – `register_settings()`: 19 db `va_ap_*` option regisztrálva (`va_ap_settings` csoport)
  - Branding: panel_name, panel_icon, logo_url, logo_height
  - Színek: color_bg, color_bg2, color_bg3, color_bg4, color_text, color_muted, color_accent, color_accent2, color_border, color_border2
  - Méretek: sidebar_width, topbar_height, radius, radius_sm
  - Font: font (14 lehetséges betűtípus slug)
- [x] `class-settings-page.php` – `init()`: `admin_post_va_apply_ap_preset` hook hozzáadva
- [x] `class-admin.php` – `inject_admin_css()`: dinamikus CSS vars injektálás `<style id="va-admin-theme-vars">:root{...}</style>` formában, admin_head hookra, csak VA oldalakon
  - rgba() értékek NEM esc_attr()-rel, hanem natívan kerülnek ki (komma/zárójel nem escape-elendő CSS-ben)
  - Font slug → teljes CSS font-stack mapping (14 font)
- [x] `class-admin.php` – `init()`: `admin_head` → `inject_admin_css` hozzáadva
- [x] `class-admin.php` – `render_shell()`: dinamikus `$ap_panel_name`, `$ap_panel_icon`, `$ap_logo_url`, `$ap_logo_height` változók, sidebar logo feltételes (kép vs emoji)
- [x] `class-admin.php` – `register_menus()`: `vadaszapro-adminpanel` submenu regisztrálva → `VA_Settings_Page::render_adminpanel`
- [x] `class-admin.php` – sidebar nav: "🖥️ Admin Panel" menüpont hozzáadva Beállítások szekció végére
- [x] `class-admin.php` – titles map: `"vadaszapro-adminpanel" => "Admin Panel beállítások"` bejegyezve
- [x] Deploy Plugin: exit 0 ✅

### Korábbi session (Session #94 – Form Builder: va_admin_listing_edit form)
- [x] `class-form-builder.php` – `va_admin_listing_edit` form hozzáadva 13 default mezővel (va_price, va_price_type, va_brand, va_model, va_caliber, va_year, va_phone, va_location, va_email_show, va_featured, va_verified, va_license_req, va_expires)
- [x] `class-form-builder.php` – `date` típus ikon (📅) hozzáadva, `va_admin_listing_edit` az $allowed listában, tab renderelve "🛠️ Admin hirdetés szerkesztő" névvel
- [x] `class-listing-edit.php` – `render_edit()`: Form Builder config betöltés, mezők ki/be kapcsolható, egyéni label/placeholder szerkeszthető, custom_* mezők "Egyéb mezők" kártyán dinamikusan jelennek meg
- [x] `class-listing-edit.php` – `handle_save()`: custom_* mezők DB mentése va_sync_listing_meta() után
- [x] Deploy Plugin: exit 0 ✅

### Fontos technikai döntések
- rgba() értékek CSS-be: NEM esc_attr() (az nem bántja a zárójeleket), de jobb ha direkten adjuk ki → muted, border, border2 fields text inputon jönnek, color picker helyett
- CSS vars override: admin.css :root{} → inject_admin_css() <style> tag felülírja, sorrend garantált (admin_head jön a CSS enqueue után)
- Font Google Fonts: NEM töltjük be külső forrásból az admin panelen (performance), csak a system-ui alapú stacket adjuk meg

---

## 2026. 04. 18. – Session #93 (Form Builder: fekete szöveg CSS + egyedi mező hozzáadás/törlés)

### Mit csinaltunk [x]
- [x] `class-form-builder.php` teljes CSS újraírva WP admin fehér háttérre (minden szöveg fekete, látható)
- [x] Toggle gombok natív CSS-sel (adminban is látható zöld/piros)
- [x] Mező törlés: custom_* kulcsú sorokon 🗑 gomb → JS confirm + DOM remove
- [x] Egyedi mező hozzáadása panel: label, placeholder, típus választó, + Hozzáad gomb (Enter is)
- [x] handle_save() bővítve: custom_ mezők is mentődnek, típus validáció, üres label skip
- [x] Deploy Plugin OK ✅

---

## 2026. 04. 18. – Session #92 (Vizualis Form Builder admin + dinamikus formok)

### Mit csinaltunk [x]
- [x] Uj admin osztaly: `class-form-builder.php` (VA_Form_Builder)
- [x] Admin menube uj pont: "🧩 Form szerkesztő"
- [x] 3 form szerkesztheto grafikusan: Hirdetes feladas, Regisztracio, Bejelentkezes
- [x] Minden formon mezonkent: felhasznaloi felirat, placeholder, ki/bekapcsolas, kotelezo toggle
- [x] Sor sorrend drag-and-drop (SortableJS CDN + natív HTML5 fallback)
- [x] Config WP options-ban tarolva (`va_form_config_*`), alap visszaallitas gomb
- [x] Submit-form.php teljesen dinamikussá teve: VA_Form_Builder config szerint renderel
- [x] Register-form.php teljesen dinamikussá teve: VA_Form_Builder config szerint renderel
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #91 (Hirdetes admin: fizetesi szolgaltato + szamlazasi beallitasok)

### Mit csinaltunk [x]
- [x] A Hirdetes beallitasok oldal 3 blokkra bontva: Alap dijazas, Fizetesi beallitasok, Szamlazasi beallitasok
- [x] Uj fizetesi mezok: szolgaltato valaszto (none/barion/stripe/simplepay/custom), test/live mod, public key, secret key, webhook secret
- [x] Uj URL mezok: sikeres/megszakitott fizetes URL (opcionalis feluliras)
- [x] Admin segedmezok: automatikus success/cancel callback URL minta megjelenitese
- [x] Uj szamlazasi mezok: kiallito nev/cim/adoszam/email/telefon, szamla prefix, kovetkezo sorszam, szamla labjegyzet
- [x] PDF szamla generator atkotve az uj szamlazasi mezokre (prefix + folyamatos sorszam + kiallito adatok)
- [x] Szamlasorszam auto inkrementalasa `va_invoice_next_number` alapjan
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #90 (PDF szamla szolgaltato nelkul)

### Mit csinaltunk [x]
- [x] A sikeres fizetes utani szamla TXT helyett mostantol PDF fajlba generalodik
- [x] Kulso library nelkul, belso minimal PDF builder kerult be (`Helvetica`, egy oldalas szamla layout)
- [x] Biztonsag: szovegek tisztitasa + ekezetek ASCII-ra konvertalasa PDF kompatibilitas miatt
- [x] A PDF fajl tovabbra is `uploads/va-invoices/` mappaba kerul, URL mentessel (`va_invoice_url`)
- [x] Celfunkcio: szolgaltato nelkul is valos, letoltheto PDF szamla keszuljon fizetesi siker callbacknel
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #89 (Fizetesi fallback + fizetes utani aktivalas + szamla)

### Mit csinaltunk [x]
- [x] Hirdetes feladas form fallback javitas: explicit `method="post"` es `action=admin-ajax.php`, hogy ne GET-es ures oldal legyen
- [x] Fizetos limitnel a hirdetes mar nem vesz el: draftkent mentjuk (`va_listing`) fizetes elott
- [x] Fizetesi allapot meta mezok bevezetve a hirdeteshez:
- [x] `va_payment_required`, `va_payment_status`, `va_payment_amount`, `va_payment_token`
- [x] Fizetesi URL most tokenes callback parameterekkel epul (`va_payment=success|cancel`, `token`)
- [x] Uj callback feldolgozas `template_redirect` alatt:
- [x] sikeres fizetesnel automatikus status valtas `publish/pending` allapotba
- [x] megszakitott fizetesnel hirdetes draftban marad
- [x] Flash uzenetekkel egyertelmu UX visszajelzes a feladas oldalon
- [x] Szamla generalas fizetes utan:
- [x] szamlaszam (`va_invoice_no`) + osszeg + datum meta mentes
- [x] letoltetheto TXT szamla file generalas `uploads/va-invoices/` konyvtarba (`va_invoice_url`)
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #88 (Admin mentes push notifikacio)

### Mit csinaltunk [x]
- [x] Uj admin push toast dizajn bevezetve (jobb felso stack, lekerekitett kartya, animacio, glow)
- [x] Menteskor azonnali informacios toast: `Mentes folyamatban...`
- [x] Ujratoltes utan a WP notice uzenetek push toastban is megjelennek (siker/hiba)
- [x] Fallback: `settings-updated=true` esetben automatikus siker toast
- [x] Cel: barmely admin mentes utan azonnali, latvanyos visszajelzes mint a kedvenceknel
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #87 (1 ingyenes hirdetes, utana bankkartyas fizetes)

### Mit csinaltunk [x]
- [x] Uj hirdetes beallitasok:
- [x] `va_listing_price_after_free` (ingyenes limit utani hirdetes ara)
- [x] `va_listing_payment_url` (bankkartyas checkout URL)
- [x] Hirdetes feladas backend logika bovites:
- [x] Felhasznalo aktiv/folyamatban levo hirdetesszam ellenorzese
- [x] Ingyenes limit utan a feladas blokkolasa, fizetesi URL visszaadasa
- [x] Frontend submit form frissites:
- [x] Ingyenes maradek / fizetos dij informacios sor
- [x] Fizetes kotelezo esetben hiba helyett bankkartyas fizetes CTA gomb megjelenitese
- [x] Celfunkcio: 1 ingyenes hirdetes utan tovabbi feladas csak fizetesi lepessel
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #86 (Nem-admin WP tiltás + szerepkör kezelés)

### Mit csinaltunk [x]
- [x] Nem-admin felhasznaloknal a WordPress admin bar elrejtve (`show_admin_bar` szuro)
- [x] Nem-admin user `wp-admin` hozzaferese tiltva es fooldalra iranyitva (`admin_init`)
- [x] Uj egyedi szerepkorok letrehozva: `Maganszemely` (`va_maganszemely`) es `Ceg` (`va_ceg`)
- [x] Regisztraciokor account type alapjan automatikus role kiosztas
- [x] Admin `Felhasznalok` oldalon szerepkor oszlop + role valaszto + mentes gomb minden userhez
- [x] Szerepkor modositas csak nem-admin roleokra engedett (administrator kizarva)
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #85 (Elfelejtett jelszo frontendben marad)

### Mit csinaltunk [x]
- [x] `wp_lostpassword_url()` atiranyitva a sajat frontend login oldalra (`?action=lostpassword`)
- [x] A `wp-login.php?action=lostpassword|retrievepassword` keresei automatikusan a frontend oldalra redirectelnek
- [x] A `wp-login.php?action=rp|resetpass` reset link is frontend oldalra redirectel (`?action=resetpass&key=...&login=...`)
- [x] Bejelentkezes template bovitve ket uj nezetre:
- [x] Elfelejtett jelszo (email kuldes)
- [x] Uj jelszo beallitasa (key/login alapu reset)
- [x] Backend feldolgozas hozzaadva:
- [x] `va_action=lostpassword` -> `retrieve_password()`
- [x] `va_action=resetpass` -> `check_password_reset_key()` + `reset_password()`
- [x] Eredmeny: az ugyfel nem esik ki a WordPress default login feluletre
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #84 (Toggle esztetikai ujrarendezes: 1 sor, 1 magassag)

### Mit csinaltunk [x]
- [x] Account-type sor ujratervezve `inline-flex` elrendezessel (stabil 1 sor)
- [x] Egységes vizualis sor-magassag bevezetve (`--va-account-row-h`)
- [x] Label elemek es kapcsolo kozeppontra igazítva, harmonikusabb aranyokkal
- [x] Toggle meretek ujrakalibralva (42x24, 18px knob), nem dominans de jol olvashato
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #83 (Toggle magassag a szoveg ala)

### Mit csinaltunk [x]
- [x] A regisztracios account-type kapcsolo merete em-alapu skalarol mukodik a labelszoveghez kotve
- [x] A kapcsolo magassaga csokkentve, hogy vizualisan biztosan ne legyen magasabb a feliratnal
- [x] A kapcsolo gomb pozicioja/atmenete ujramertezve az uj kompakt magassaghoz
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #82 (Account type toggle meret/pozicio finomitas)

### Mit csinaltunk [x]
- [x] A regisztracios toggle kapcsolo merete csokkentve (kompaktabb switch)
- [x] A feliratok betumerete csokkentve, aranyosabb a kapcsolohoz
- [x] A teljes toggle szekcio balra igazitva (nem kozepre)
- [x] Kompaktabb container padding/gap beallitasok
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #81 (Ceges mezo tisztitas + toggle szimmetria)

### Mit csinaltunk [x]
- [x] Ceges regisztraciobol a `Szemelynev` mezo eltavolitva (nem kotelezo tobbe)
- [x] Backend validacio frissitve: ceges modban csak `Cegnev + Adoszam + Szekhely` kotelezo
- [x] Kontakt nev meta mentes/torles logika eltavolitva
- [x] Account type toggle ujrarendezve szimmetrikus gridre (kozepre zarva)
- [x] Bal/jobb label igazitas kulon finomitva, hogy vizualisan ne csusszon el
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #80 (Login/Regisztracio kulon ki-be kapcsolhato)

### Mit csinaltunk [x]
- [x] Uj altalanos admin kapcsolok: `va_enable_login`, `va_enable_register`
- [x] Az `Általános` beallitas oldalon kulon toggle-kent kezelhetok
- [x] Headerben a Bejelentkezes/Regisztracio gombok csak akkor jelennek meg, ha engedelyezettek
- [x] Footer `Fiok` oszlopban a megfelelo linkek szinten feltetelesek
- [x] Login/Register frontend oldalak tiltott allapotban urlap helyett figyelmeztetest mutatnak
- [x] Backend oldali vedelem: login/register POST feldolgozas tiltva, ha az adott funkcio ki van kapcsolva
- [x] Aukcio oldali vendeg licit gomb is figyeli a login tiltast (figyelmeztetesre valt)
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #79 (Interaktiv regisztracio + ceg/maganszemely toggle)

### Mit csinaltunk [x]
- [x] Regisztracio urlap interaktivva teve: typing placeholder effekt tobb mezone
- [x] Submit allapot javitas: betoltes jelzes (`Regisztracio folyamatban...`) + loading animacio
- [x] Uj account type kapcsolo: Maganszemely / Ceg (toggle switch)
- [x] Ceges adatblokkok dinamikus megjelenitese/elrejtese JS-bol
- [x] Ceges kotelezo mezok: Cegnev, Adoszam, Szekhely, Szemelynev
- [x] Backend validacio es mentes bovites a ceges adatokra
- [x] Uj user meta kulcsok: `va_account_type`, `va_company_name`, `va_company_tax`, `va_company_seat`, `va_contact_name`
- [x] Biztonsag: ASZF checkbox szerveroldali kotelezo ellenorzese
- [x] Mobil fallback megtartva (1 oszlop)
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #78 (Regisztracio rovidebb/szelesebb layout)

### Mit csinaltunk [x]
- [x] Regisztracios oldal szelesitese kulon wrapperrel (`va-auth-wrap--register`)
- [x] Desktopon 2 oszlopos regisztracios grid bevezetve, hogy kevesebb gorgetes kelljen
- [x] Checkbox + submit teljes szelessegben maradt (egyertelmu UX)
- [x] Mobil fallback: 1 oszlop 860px alatt
- [x] Inline seged szoveg stilus kiszervezve CSS osztalyba (`va-register-help`)
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #77 (Kedvenceim menu sziv piros)

### Mit csinaltunk [x]
- [x] A dashboard `Kedvenceim` menupont sziv ikonja fix piros lett
- [x] Hover es aktiv allapotban is piros marad
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #76 (Dashboard menu teljes egyvonalas igazitas)

### Mit csinaltunk [x]
- [x] A dashboard menupontok icon + label kulon elemekre bontva
- [x] Fix ikon oszlop bevezetve (`grid-template-columns: 20px 1fr`), hogy minden sor tokeletesen egyvonalban legyen
- [x] A kijelentkezes sor is ugyanabba a strukturaba kerult (inline style eltavolitva)
- [x] Label oldal overflow-biztos lett (`text-overflow: ellipsis`)
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #75 (Profil dashboard menupont UX fix)

### Mit csinaltunk [x]
- [x] A profil/dashboard bal menu elemei egysegesen 1 sorosak lettek (`white-space: nowrap`)
- [x] Minden menupont kez kurzort kapott (`cursor: pointer`), nem csak a kijelentkezes
- [x] A menupontok ikon + szoveg elrendezese egységesitve (`display:flex`, `align-items:center`, `gap`)
- [x] Tab kezelo JS szukitve csak a `data-tab` elemekre, igy a kijelentkezes linket mar nem fogja meg a tab script
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #74 (Kartya hover border sarok simitas)

### Mit csinaltunk [x]
- [x] A termekkartya hover border nem a fo borderen valtozik mar, hanem kulon radius-oroklo overlayen
- [x] Ez megszunteti a reces/tort sarokhatast hover allapotban
- [x] Hozzaadva `focus-within` allapot is az egyseges kiemeleshez
- [x] Finom GPU simitas: `translateZ(0)` + `backface-visibility: hidden`
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #73 (Kedvencek stabilizalas + 5s push toast)

### Mit csinaltunk [x]
- [x] A `va-frontend` JS globalisan enqueue-olva a theme-bol, hogy minden kartya nezetben biztosan aktiv legyen
- [x] Globalis `VA_Data` lokalizacio hozzaadva (ajax_url, nonce, post_id)
- [x] Kedvencek kattintasnal robosztus hibakezeles bevezetve (missing adat, backend hiba, halozati hiba)
- [x] Dupla kattintas vedelme (`busy` flag)
- [x] Uj jobb felso push ertesites design (lekerekitett kartya, glow, elegans be/ki animacio)
- [x] Toast eletciklus: 5 masodperc
- [x] Cel: azonnali vizualis visszajelzes + megbizhato kedvencek mentes
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #72 (Kedvencek mentes hibajavitas)

### Mit csinaltunk [x]
- [x] A kedvencek gomb mukodese fuggetlenitve a globalis `VA_Data` objektumtol
- [x] A gomb most sajat `data-nonce` es `data-ajax-url` adatokat kap
- [x] A frontend JS watchlist handler ezeket hasznalja, fallbackkel
- [x] `stopPropagation()` hozzaadva, hogy ne zavarja kartya-link interakcio
- [x] Cel: kattintasra biztos kedvencekbe mentes + profilban megjelenes
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #71 (Kedvencek sziv az ar melle helyezve)

### Mit csinaltunk [x]
- [x] A kartyan a kedvencek sziv mar nem a kepre van pozicionalva
- [x] A sziv fizikailag az ar soraba kerult (ar melle)
- [x] A gomb emiatt nem takarja a kepet es stabilan kattinthato marad
- [x] A kartya cim linkes maradt, a kedvencek gomb kulon, tiszta interakcios zonat kapott
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #70 (Kartya kedvencek szivecske redesign)

### Mit csinaltunk [x]
- [x] A kartya kedvencek gomb karakteres `♥` ikonja lecserelve valodi SVG szivre
- [x] A szivecske fix pirosra allitva minden allapotban
- [x] Vizualis finomitas: kor alakú gomb, piros keret, enyhe glow, elegans hover animacio
- [x] Cel: markans, szep, azonnal felismerheto kedvencek ikon
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #69 (Layout Allito grafikus abra blokkok)

### Mit csinaltunk [x]
- [x] A Layout Allito tetejere vizualis magyarazo kartyak kerultek (nem steril tabla)
- [x] Grafikus blokkok: kontener + oldalpárna, responsive toréspont flow, kartya anatomia
- [x] Kulon admin CSS keszult a diagram elemekhez (dark panel, piros kiemeles, responsziv admin elrendezes)
- [x] Cel: gyorsabb ertelmezes, egyertelmu "mi mit csinal" UX
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #68 (Mobil-first responzivitas + Breakpoint Preview)

### Mit csinaltunk [x]
- [x] A Layout Allitoba bekerultek a toréspont vezerlok (desktop→tablet, tablet→mobil, oldalsav rejtese)
- [x] A dinamikus frontend CSS most ezeket a toréspontokat hasznalja
- [x] Felső admin savba uj menu: `VA Breakpoint Preview`
- [x] Preset preview szelessegek: 1440, 1280, 1024, 820, 480, 390, 375, 320 px
- [x] Bricks-szeru egyedi kezi szelesseg megadasa prompttal (`Egyedi szelesseg (px)…`)
- [x] Preview modban a teljes oldal a valasztott szelessegre van constrainelve + jobb also jelzes mutatja az aktualis px-et
- [x] Cel: maximalis mobil-ellenorizhetoseg valos torespontokkal
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #67 (Divi/Porto mintaju Layout Allito)

### Mit csinaltunk [x]
- [x] Uj admin menupont: `Layout Allito`
- [x] Kulon settings csoport letrehozva: `va_layout_settings`
- [x] Sok parameteres layout panel bevezetve (preset, kontener, tartalom, oldalsav, grid, kartya, widget, hover/arnyek)
- [x] Preset modok: Porto / Divi / Custom
- [x] Frontend dinamkus CSS bekotve a layout opciokra (kontener szelesseg, padding, grid oszlopszam, gap, kartya radius, kep arany, meta meret, oldalsav szelesseg/sticky stb.)
- [x] Alapelv: nagylepteku, hirdetes-fokuszu testreszabhatósag egy helyen
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #66 (Favicon bal sarok utanjavitas)

### Mit csinaltunk [x]
- [x] Favicon biztonsagi margoja tovabb novelve (14%)
- [x] Fajlvariant frissitve `safe3`-ra, hogy garantaltan ujrageneraljon
- [x] Celzott javitas: bal sarok lecsapas megszuntetese
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #65 (Favicon bal oldal lecsapas javitas)

### Mit csinaltunk [x]
- [x] Favicon generalas javitva biztonsagi belso margoval (8%)
- [x] A kesz ikon PNG atlatszo canvason keszul, hogy a jel ne erjen a szelere
- [x] Versionalt favicon fajlnev bevezetve cache-bustinghoz (`safe2`)
- [x] Fallback megtartva: GD hianyaban WP image editor
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #64 (Kartya meta egy sor alap + migracio)

### Mit csinaltunk [x]
- [x] A kartya meta alapertelmezett sor-szama 1 sorra allitva
- [x] A hely + datum alapertelmezett sorhoz rendelese 1. sorra allitva
- [x] Biztonsagos migracio bevezetve: ha a korabbi alap 2 soros hely+datum konfiguracio van, automatikusan 1 sorra valt
- [x] Az admin testreszabhatosag teljesen megmaradt
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #63 (Kartya meta teljes admin vezerles)

### Mit csinaltunk [x]
- [x] A kartya meta megjelenites teljesen adminbol allithato lett
- [x] Minden meta parameter kulon kapcsolhato: kategoria, megye, telepules/hely, megtekintes, felado, datum
- [x] Minden parameter kulon sorhoz rendelheto (1-3. sor)
- [x] Layout parameterek is adminbol allithatok: sorok szama, oszlopkoz, soron beluli sorkoz, sorblokkok kozti tavolsag
- [x] Alapertelmezett beallitas a kert igeny szerint: csak hely + datum
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #62 (Kartya meta 2 soros elrendezes)

### Mit csinaltunk [x]
- [x] A kartya meta blokk 3 sorrol 2 sorra atalakitva
- [x] 1. sor: kategoria + megye + telepules
- [x] 2. sor: megtekintes + felado + datum
- [x] Cel: tomorebb, egysegesebb sorstruktura
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #61 (Meta elemek egymas melle igazitas)

### Mit csinaltunk [x]
- [x] Kartya meta sorok igazitasanak modositasa balra zart elrendezesre
- [x] A `space-between` eltavolitva, hogy ne legyenek indokolatlanul nagy kozok
- [x] Soron beluli elemek kozti tavolsag fixalt (column-gap/row-gap)
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #60 (Kartya meta sorkoz tomorites)

### Mit csinaltunk [x]
- [x] A kartya meta sorok sortavja egységesitve es csokkentve
- [x] Sorok kozti tavolsag tomoritve (top/middle/bottom fix spacing)
- [x] Cel: kompaktabb, egysegesebb kartyamegjelenes
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #59 (Kartya meta sorstruktura fix)

### Mit csinaltunk [x]
- [x] A listing kartya meta adatai fix 3 sorra bontva
- [x] Felso sor: kategoria + megye
- [x] Kozepso sor: telepules + megtekintes
- [x] Also sor: felado + datum
- [x] Cel: nyitolapi kartya-elrendezes igazitas a kért 2. referencia kephez
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #58 (Nyitolap kartyakep fallback javitas)

### Mit csinaltunk [x]
- [x] A listing kartya kepforrasa kiegeszitve fallbackkel
- [x] Ha nincs kiemelt kep, az elso feltoltott hirdeteskep jelenik meg
- [x] Igy a nyitolap / archiv / kategoria listak kepmegjelenese egyezik a hirdetes aloldal kepforrasaival
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #57 (Idojaras 7 nap holnaptol indul)

### Mit csinaltunk [x]
- [x] A 7 napos elorejelzesbol a mai nap kiszurve
- [x] A lista mindig holnaptol indul es 7 jovobeli napot mutat
- [x] API keret 8 napra emelve, hogy a mai nap kihagyasa utan is maradjon 7 elem
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #56 (Idojaras: MA jeloles es datum egyeztetes)

### Mit csinaltunk [x]
- [x] A 7 napos sorban a mai nap mar egyertelmuen `MA` jelolest kap
- [x] A mai nap felismerese Budapest idozonara allitott datum alapjan tortenik
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #55 (Idojaras widget mukodesi hiba javitas)

### Mit csinaltunk [x]
- [x] Javitas: render sorrendhiba miatt a napi adatok valtozoja hasznalat elott nem volt inicializalva
- [x] A napi adatok deklaracioja a megfelelo helyre kerult
- [x] Eredmeny: az idojaras feldolgozas nem omlik ossze, adatok megjelennek
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #54 (Idojaras 7 napos panel osszecsukhato + datum mindenhol)

### Mit csinaltunk [x]
- [x] A 7 napos elorejelzes alapbol osszecsukott, gombbal lenyithato
- [x] Nagyobb, kontrasztosabb (feher) betuk az idojaras widgetben
- [x] Datum megjelenites bovites: aktualis idopont + napi sorokban konkret datum
- [x] Hibavizsgalat: modosított fajlok rendben

---

## 2026. 04. 18. – Session #53 (Geolokalizalt idojaras widget a Hold ala)

### Mit csinaltunk [x]
- [x] Uj idojaras widget kerult a Hold widget ala a fooldali sidebarban
- [x] Geolokalizacio: eloszor browser helymeghatarozas, fallback IP alapu helyzet
- [x] Aktualis adatok: homerseklet, hoerzet, para, szel, csapadek, allapot
- [x] Elorejelzes: 7 nap (min/max, csapadek valoszinuseg, csapadek osszeg, max szel)
- [x] Uj admin toggle: `va_show_weather_widget` (kulon ki/be kapcsolhato)
- [x] Hibavizsgalat: modosított fajlok rendben

---

## 2026. 04. 18. – Session #52 (Fooldali vadaszati naptar panel kulon kapcsolo)

### Mit csinaltunk [x]
- [x] Uj admin toggle: `va_show_home_hunting_calendar`
- [x] A nagy fooldali "Vadaszati idenyek 2026" panel (HTML + CSS + JS) opciohoz kotve
- [x] Igy kulon kikapcsolhato a panel, mikozben a tobbi tartalom marad
- [x] Hibavizsgalat: modosított fajlok rendben

---

## 2026. 04. 18. – Session #51 (Hold/Ideny/Naptar kapcsolhatosag adminbol)

### Mit csinaltunk [x]
- [x] Uj admin togglek az Altalanos beallitasokban:
- [x] `va_show_hunting_season_widget` (Vadaszati ideny widget)
- [x] `va_show_moon_widget` (Hold widget)
- [x] `va_enable_hunting_calendar_page` (Vadaszati naptar oldal)
- [x] A fooldali `index.php` mar feltetelhez kotve rendereli a ket widgetet es a kapcsolodo JS-eket
- [x] A `page-vadasz-naptar.php` oldal teljes tartalma adminbol letilthato
- [x] Hibavizsgalat: modosított fajlok rendben

---

## 2026. 04. 18. – Session #50 (Hold widget datum stilus vissza az elozore)

### Mit csinaltunk [x]
- [x] A datum/idopont megjelenes visszaallitva az elozo egyszeru stilusra
- [x] Csak a szin maradt allitva: feher (`#ffffff`)
- [x] Hibavizsgalat: modosított fajl ellenorizve

---

## 2026. 04. 18. – Session #49 (Hold widget datum lathatosag javitas)

### Mit csinaltunk [x]
- [x] A Holdnaptar datum/idopont badge kontrasztja jelentosen erosítve
- [x] A datum nagyobb betumeretet, keretet es finom hatteret kapott
- [x] Hibavizsgalat: modosított fajl ellenorizve

---

## 2026. 04. 18. – Session #48 (Ideiglenes hold szimulacio eltavolitasa, elesites)

### Mit csinaltunk [x]
- [x] A fooldali Holdnaptar ideiglenes Valos / Telik / Fogy demo vezerloi eltavolitva
- [x] A kezicsuszka es a szimulacios allapotok kiszedve a frontendbol
- [x] Az eles, valos idon alapulo hold widget maradt meg automatikus frissitessel
- [x] Hibavizsgalat: modosított fajlok rendben

---

## 2026. 04. 18. – Session #47 (Hold peremfeny visszaallitasa egy lepessel)

### Mit csinaltunk [x]
- [x] Az elozo lepes visszavonva: a finom belso peremfeny visszakerult
- [x] Csak a legutobbi hold-rajz modositas lett visszaallitva
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #46 (Hold korbefuto gyuru teljes eltavolitasa)

### Mit csinaltunk [x]
- [x] A holdon maradt korbefuto belso peremfeny gyuru eltavolitva
- [x] Igy a hold korul mar nem fut teljes koros fenyes stroke
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #45 (Valos telihold tonus + 50 eves holdnaptar nezet)

### Mit csinaltunk [x]
- [x] A fooldali hold widget melegebb, valosabb szintonusokat kap telihold kozeleben
- [x] Teliholdnal a hold kep mar nem csak szurke: mehet elefantcsont, sargas vagy vorosesebb tone-ba
- [x] A vadasz-naptar oldalon uj honap/ev valaszto kerult be
- [x] Uj gyorsgombok: Mai honap es +50 ev
- [x] Az oldal az aktualis evtol 50 evre elore enged navigalni a holdnaptarban

---

## 2026. 04. 18. – Session #44 (Hold kulso kor eltavolitasa)

### Mit csinaltunk [x]
- [x] A hold canvas vegso kulso korvonala eltavolitva
- [x] A belso megvilagitott perem megmaradt, csak a kulso gyuru tunik el
- [x] Hibavizsgalat: modosított fajl hibamentes

---

## 2026. 04. 18. – Session #43 (Hold peremfeny + ideiglenes fazis szimulacio)

### Mit csinaltunk [x]
- [x] A megvilagitott holdperem visszakapta a jobban lathato fenyes szegelyt
- [x] Ideiglenes szimulacio vezerlok bekerultek: Valos / Telik / Fogy + kezicsuszka
- [x] A szimulacios mod sajat allapotszoveget kap, hogy egyertelmu legyen a demo nezet
- [x] Hibavizsgalat: modosított JS/PHP fajl hibamentes

---

## 2026. 04. 18. – Session #42 (Hold szazalek sav lathatosag javitas)

### Mit csinaltunk [x]
- [x] A hold megvilagitottsag szazalek badge eros kontrasztot kapott
- [x] Uj vizualis sav kerult a hold ala, ami a megvilagitott resz %-at mutatja
- [x] A sav JS-bol frissul az aktualis holdfazis alapjan
- [x] Hibavizsgalat: modosított fajlok hibamentesek

---

## 2026. 04. 18. – Session #41 (Valodi hold skin bekotese)

### Mit csinaltunk [x]
- [x] A Holdnaptar render motor valodi holdfoto texturat kapott (kulso forras)
- [x] Kep betoltesre automatikus redraw kerult be, nem kell varni 1 percet
- [x] Ha a kulso kep nem erheto el, fallback marad a proceduralis textura
- [x] Hibavizsgalat: modosított fajl hibamentes
- [x] Deploy: `Deploy Theme` lefutott

---

## 2026. 04. 18. – Session #40 (Holdnaptar valosaghubb holdfelszin + kraterek)

### Mit csinaltunk [x]
- [x] A Holdnaptar canvas rajzolo logika teljesen frissitve valosaghubb felszinre
- [x] Proceduralis texturagenerator kerult be: tobb retegu zaj, limb darkening, mare foltok
- [x] Krater modellezes javitva: perem + melyedes (pit) a termeszetesebb felszinert
- [x] Terminator feny/arnyek atmenet finomitva a fazis fuggvenyeben
- [x] Ujhold kozeli earthshine visszaverodes finoman megtartva
- [x] Hibavizsgalat: modosított fajl hibamentes
- [x] Deploy: `Deploy Theme` lefutott

---

## 2026. 04. 18. – Session #39 (Minden admin mező érték láthatóság fix)

### Mit csináltunk [x]
- [x] Bevezetve központi `get_display_option()` fallback a settings oldalon
- [x] Ha egy opció üres/hiányzik, a mező most a regisztrált default értéket mutatja
- [x] Kiterjesztve az összes helper mezőre: text/email/url/media/number/decimal/select/color/toggle
- [x] Eredmény: nem maradnak "vak" üres mezők, mindenhol látható aktuális vagy default érték
- [x] Hibavizsgálat: módosított fájl hibamentes

---

## 2026. 04. 18. – Session #38 (Admin mezők láthatósági fix)

### Mit csináltunk [x]
- [x] Javítva az admin beállítás oldalak input láthatósági hibája
- [x] Minden settings mezőre kontrasztos stílus: fehér háttér + sötét szöveg + fókusz keret
- [x] Kiterjesztve text/email/url/number/select/textarea mezőkre
- [x] Eredmény: a beírt és mentett értékek minden admin oldalon olvashatóak

---

## 2026. 04. 18. – Session #37 (Lábléc logó pozíció finomítás)

### Mit csináltunk [x]
- [x] A lábléc első oszlopában a logó markup áthelyezve a cím alá
- [x] Eredmény: a logó a vonal alá kerül, nem a szöveg/cím fölé

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
