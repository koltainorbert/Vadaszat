# Auto-detect LocalWP plugin utvonal
$localConfig = "$PSScriptRoot\local-config.ps1"
if (Test-Path $localConfig) { . $localConfig }

if (-not $LOCAL_WP_PLUGIN) {
    $found = Get-ChildItem 'D:\LocalWP' -Recurse -Filter 'vadaszapro-core' -Directory -ErrorAction SilentlyContinue | Select-Object -First 1
    if (-not $found) { Write-Error 'Nem talalhato a vadaszapro-core!'; exit 1 }
    $LOCAL_WP_PLUGIN = $found.FullName
}

Write-Host "Plugin -> $LOCAL_WP_PLUGIN" -ForegroundColor Cyan
Copy-Item "$PSScriptRoot\..\wp-plugin\vadaszapro-core\*" $LOCAL_WP_PLUGIN -Recurse -Force
Write-Host 'Plugin deploy kesz!' -ForegroundColor Green
