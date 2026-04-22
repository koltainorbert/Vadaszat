$file = "d:\Vadaszat2026\wp-plugin\vadaszapro-core\admin\class-settings-page.php"
$c = [System.IO.File]::ReadAllText($file, [System.Text.Encoding]::UTF8)

# Magyar fordítások
$translations = @{
    'Hasznalati logika'               = 'Használati logika'
    'Badge szin'                      = 'Badge szín'
    'Megjelenes'                      = 'Megjelenés'
    'Ez hatarozza meg a badge vizualis karakteret es az adminban, frontendben latszo rovid szoveget.' = 'Ez határozza meg a badge vizuális karakterét és az adminban, frontendben látszó rövid szöveget.'
    'Megnevezes'                      = 'Megnevezés'
    'Hatter RGBA'                     = 'Háttér RGBA'
    'Leiras'                          = 'Leírás'
    'Hirdetesi limit'                 = 'Hirdetési limit'
    'Itt dol el, hogy egyszerre aktiv darabszamot vagy havi feladast szamoljon a rendszer.' = 'Azt határozza meg, hogy egyszerre aktív darabszámot vagy havi feladást számláljon a rendszer.'
    'Szamlalas modja'                 = 'Számlálás módja'
    'aktiv vagy havi'                 = 'aktív vagy havi'
    'Aktiv hirdetesek egyszerre'      = 'Aktív hirdetések egyszerre'
    'Havi feladott hirdetesek'        = 'Havi feladott hirdetések'
    'Boost / Kiemeles'                = 'Boost / Kiemelés'
    'A cooldown csomag-specifikus. A globalis badge es kapcsolo a kulon panelen allithato.' = 'A cooldown csomag-specifikus. A globális badge és kapcsoló a külön panelen állítható.'
    'napokban merve'                  = 'napokban mérve'
    'Ar es marketing'                 = 'Ár és marketing'
    'Nem a fizetesi logikat kezeli, hanem a kommunikacios szovegeket es badge tartalmakat.' = 'Nem a fizetési logikát kezeli, hanem a kommunikációs szövegeket és badge tartalmakat.'
    'Havi ar'                         = 'Havi ár'
    'Eves ar'                         = 'Éves ár'
    'Promo badge szoveg'              = 'Promo badge szöveg'
    'opcionalis kiemeles, pl. Legjobb ar' = 'opcionális kiemelés, pl. Legjobb ár'
    'Alapertekek visszaallitasa'      = 'Alapértékek visszaállítása'
    'A visszaallitas csak a jelenlegi panel mezoihez nyul. Mentes utan irjuk felul az adatbazist.' = 'A visszaállítás csak a jelenlegi panel mezőihez nyúl. Mentés után írjuk felül az adatbázist.'
    'Globalis boost beallitasok'      = 'Globális boost beállítások'
    'Itt tudod vezerezni a badge lathatosagat, a rendszer be-kikapcsolasat es a kozos feliratot.' = 'A badge láthatóságát, a rendszer be-/kikapcsolását és a közös feliratot itt vezérelheted.'
    'Kozös boost logika'              = 'Közös boost logika'
    'A csomagszintu cooldown mellett ez szabja meg, hogy a badge meddig latszik es egyaltalan aktiv-e a szolgaltatas.' = 'A csomagszintű cooldown mellett ez szabja meg, hogy a badge meddig látszik és egyáltalán aktív-e a szolgáltatás.'
    'Badge lathatosag'                = 'Badge láthatóság'
    'napban megadva'                  = 'napban megadva'
    'Badge szoveg'                    = 'Badge szöveg'
    'pl. Elore teve'                  = 'pl. Előre téve'
    'Boost rendszer'                  = 'Boost rendszer'
    ' cooldown'                       = ' várakozás'
    'Aktiv hirdetesek'                = 'Aktív hirdetések'
    'pl. 990 Ft/ho'                   = 'pl. 990 Ft/hó'
    'pl. 9900 Ft/ev'                  = 'pl. 9900 Ft/év'
}

foreach ($old in $translations.Keys) {
    $c = $c.Replace($old, $translations[$old])
}

[System.IO.File]::WriteAllText($file, $c, [System.Text.Encoding]::UTF8)
Write-Host "Kész - szövegek magyarítva"
