$file = "d:\Vadaszat2026\wp-plugin\vadaszapro-core\admin\class-settings-page.php"
$lines = [System.IO.File]::ReadAllLines($file, [System.Text.Encoding]::UTF8)

$lines[1149] = "                    <?php self::field_color( 'va_color_hero_badge_bg',         'Hatter szin' ); ?>"
$lines[1150] = "                    <?php self::field_color( 'va_color_hero_badge_border',      'Keret szin' ); ?>"
$lines[1158] = "                    <?php self::field_color( 'va_color_hero_sub',               'Alcim szin' ); ?>"
$lines[1164] = "                    <?php self::field_color( 'va_color_hero_btn_primary_hover', 'Hover hatter' ); ?>"
$lines[1167] = "                    <?php self::field_color( 'va_color_hero_btn_primary_glow',  'Glow szin' ); ?>"
$lines[1172] = "                    <?php self::field_color( 'va_color_hero_btn_ghost_bg',      'Hatter' ); ?>"
$lines[1173] = "                    <?php self::field_color( 'va_color_hero_btn_ghost_border',  'Keret szin' ); ?>"
$lines[1174] = "                    <?php self::field_color( 'va_color_hero_btn_ghost_hover',   'Hover hatter' ); ?>"
$lines[1262] = "                                <?php self::field_color( 'va_color_header_bg',     'Hatter' ); ?>"
$lines[1335] = "                                <?php self::field_color( 'va_hf_header_shadow_color',      'Fo arnyak szin' ); ?>"
$lines[1336] = "                                <?php self::field_color( 'va_hf_header_glow_color',        'Fejlec neon glow' ); ?>"
$lines[1337] = "                                <?php self::field_color( 'va_hf_header_search_glow_color', 'Kereso glow szin' ); ?>"
$lines[1338] = "                                <?php self::field_color( 'va_hf_header_btn_glow_color',    'CTA gomb glow szin' ); ?>"
$lines[1489] = "                                <?php self::field_color( 'va_color_footer_text',     'Szoveg szin' ); ?>"

[System.IO.File]::WriteAllLines($file, $lines, [System.Text.Encoding]::UTF8)
Write-Host "Kesz - 14 sor javitva"
