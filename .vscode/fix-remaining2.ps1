$file = "d:\Vadaszat2026\wp-plugin\vadaszapro-core\admin\class-settings-page.php"
$lines = [System.IO.File]::ReadAllLines($file, [System.Text.Encoding]::UTF8)

$lines[5195] = '                                        <h2 class="va-pc-card__title">Közös boost logika</h2>'
$lines[5196] = '                                        <p class="va-pc-card__text">A csomagszintű cooldown mellett ez szabja meg, hogy a badge meddig látszik és egyáltalán aktív-e a szolgáltatás.</p>'

[System.IO.File]::WriteAllLines($file, $lines, (New-Object System.Text.UTF8Encoding $false))
Write-Host "Kesz"
