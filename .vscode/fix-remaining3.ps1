$file = "d:\Vadaszat2026\wp-plugin\vadaszapro-core\admin\class-settings-page.php"
$lines = [System.IO.File]::ReadAllLines($file, [System.Text.Encoding]::UTF8)

$o = [char]0x00F6  # o umlaut
$O1 = [char]0x0151 # o double acute (o")
$a = [char]0x00E1  # a acute
$e = [char]0x00E9  # e acute
$i = [char]0x00ED  # i acute

$title = "                                        <h2 class=""va-pc-card__title"">K${o}z${o}s boost logika</h2>"
$desc  = "                                        <p class=""va-pc-card__text"">A csomagszint${O1} cooldown mellett ez szabja meg, hogy a badge meddig l${a}tszik ${e}s egy${a}ltal${a}n akt${i}v-e a szolg${a}ltat${a}s.</p>"

$lines[5195] = $title
$lines[5196] = $desc

[System.IO.File]::WriteAllLines($file, $lines, (New-Object System.Text.UTF8Encoding $false))
Write-Host "Kesz"
