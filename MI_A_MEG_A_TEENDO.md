# Mi a még a teendő

Ez a projekt "szentgrál" státuszlista. Cél: reggel / induláskor ebből dolgozni.

## Kész (pirossal áthúzva)

- <span style="color:#ff0000;"><s>Core plugin + theme architektúra felépítve</s></span>
- <span style="color:#ff0000;"><s>Jármű kategória adatmodell (VA_Vehicle_Catalog) bevezetve</s></span>
- <span style="color:#ff0000;"><s>Kategória szinkron dataset verzióval megoldva</s></span>
- <span style="color:#ff0000;"><s>Kereső alap + részletes szűrők nagyrészt implementálva</s></span>
- <span style="color:#ff0000;"><s>Ár szerinti rendezés viselkedése javítva</s></span>
- <span style="color:#ff0000;"><s>Submit form jelentősen bővítve jármű mezőkkel</s></span>
- <span style="color:#ff0000;"><s>Single listing részletek kártya és scroll UX finomítva</s></span>
- <span style="color:#ff0000;"><s>Deploy flow működik (Deploy Plugin/Theme/All)</s></span>
- <span style="color:#ff0000;"><s>Session naplózás rendszeresen vezetve</s></span>
- <span style="color:#ff0000;"><s>Fresh install fallback ellenőrizve (factory defaults guard)</s></span>

## Még teendő (100% profi / ThemeForest szint)

1. Teljes clean-install smoke teszt
- Üres WordPress példányon nulláról aktiválás
- Oldalak, kategóriák, alap beállítások és dizájn ellenőrzése

2. QA mátrix (desktop/mobil/tablet + böngészők)
- Chrome, Firefox, Safari, Edge
- Formok, validációk, feltöltések, edge case-ek

3. Security hardening audit
- Nonce/capability check minden admin/AJAX útvonalon
- Sanitization + escaping teljes áttekintés

4. Performance és CWV csomag
- CSS/JS optimalizálás, képoptimalizálás
- Largest Contentful Paint és Interaction to Next Paint mérés/javítás

5. ThemeForest kompatibilitási csomag
- Theme vs plugin felelősség teljes szétválasztása
- Child theme csomag véglegesítése
- Demo import (one-click) és stabil demo adat

6. Dokumentáció és support readiness
- Telepítés, beállítás, hibaelhárítás lépésenként
- Changelog + verziózási policy

7. Jogi/licenc megfelelés
- Minden asset/font/licenc ellenőrzése
- Eredeti design identitás finomítása (klón érzet csökkentése)

## Aktuális státusz

- Készültség: kb. 70-80% termékérettség
- ThemeForest beadási készültség: közepes, még compliance fókusz kell

## Napi használat (induláskori rutin)

1. Nyisd meg ezt a fájlt: MI_A_MEG_A_TEENDO.md
2. Ellenőrizd a "Még teendő" pontokból az aznapi fókuszt
3. Amit elkészítettél, tedd át a "Kész" blokkba piros áthúzással
4. Session végén frissítsd a NAPLO.md fájlt a konkrét változásokkal
