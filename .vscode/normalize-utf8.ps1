$file = "d:\Vadaszat2026\wp-plugin\vadaszapro-core\admin\class-settings-page.php"
$raw = [System.IO.File]::ReadAllText($file, [System.Text.Encoding]::UTF8)

# Normalize NFD -> NFC
$nfc = $raw.Normalize([System.Text.NormalizationForm]::FormC)

[System.IO.File]::WriteAllText($file, $nfc, (New-Object System.Text.UTF8Encoding $false))
Write-Host "NFC normalization done"
