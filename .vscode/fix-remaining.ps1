$file = "d:\Vadaszat2026\wp-plugin\vadaszapro-core\admin\class-settings-page.php"
$lines = [System.IO.File]::ReadAllLines($file, [System.Text.Encoding]::UTF8)

# 5196 -> 5195 (0-indexed)
$lines[5195] = "                                        <h2 class=`"va-pc-card__title`">K`u00f6z`u00f6s boost logika</h2>"
$lines[5195] = $lines[5195] -replace '`u00f6', [char]0x00f6

# 5197 -> 5196
$lines[5196] = "                                        <p class=`"va-pc-card__text`">A csomagszint`u0151 cooldown mellett ez szabja meg, hogy a badge meddig l`u00e1tszik `u00e9s egy`u00e1ltal`u00e1n akt`u00edv-e a szolg`u00e1ltat`u00e1s.</p>"
$lines[5196] = $lines[5196] -replace '`u0151', [char]0x0151 -replace '`u00e1', [char]0x00e1 -replace '`u00e9', [char]0x00e9 -replace '`u00ed', [char]0x00ed

[System.IO.File]::WriteAllLines($file, $lines, (New-Object System.Text.UTF8Encoding $false))
Write-Host "Kesz"
