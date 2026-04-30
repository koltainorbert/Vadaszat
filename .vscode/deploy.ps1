# Auto-detect LocalWP plugin + tema utvonal
# Ha van local-config.ps1, azt hasznaljuk; egyebkent auto-detect

$localConfig = "$PSScriptRoot\local-config.ps1"
if (Test-Path $localConfig) {
    . $localConfig
}

if (-not $LOCAL_WP_PLUGIN) {
    $found = Get-ChildItem 'D:\LocalWP' -Recurse -Filter 'vadaszapro-core' -Directory -ErrorAction SilentlyContinue | Select-Object -First 1
    if (-not $found) {
        Write-Error 'Nem talalhato a vadaszapro-core konyvtar a LocalWP alatt!'
        exit 1
    }
    $LOCAL_WP_PLUGIN = $found.FullName
}

if (-not $LOCAL_WP_THEME) {
    $found = Get-ChildItem 'D:\LocalWP' -Recurse -Filter 'vadaszapro-theme' -Directory -ErrorAction SilentlyContinue | Select-Object -First 1
    if (-not $found) {
        Write-Error 'Nem talalhato a vadaszapro-theme konyvtar a LocalWP alatt!'
        exit 1
    }
    $LOCAL_WP_THEME = $found.FullName
}

Write-Host "Plugin -> $LOCAL_WP_PLUGIN" -ForegroundColor Cyan
Write-Host "Tema   -> $LOCAL_WP_THEME" -ForegroundColor Cyan

Copy-Item "$PSScriptRoot\..\wp-plugin\vadaszapro-core\*" $LOCAL_WP_PLUGIN -Recurse -Force
Copy-Item "$PSScriptRoot\..\wp-theme\vadaszapro-theme\*" $LOCAL_WP_THEME -Recurse -Force

if ($LOCAL_WP_CHILD) {
    if (-not (Test-Path $LOCAL_WP_CHILD)) { New-Item -ItemType Directory -Path $LOCAL_WP_CHILD -Force | Out-Null }
    Copy-Item "$PSScriptRoot\..\wp-theme\vadaszapro-child\*" $LOCAL_WP_CHILD -Recurse -Force
    Write-Host 'Child tema is deployolva!'
}

Write-Host 'Deploy kesz!' -ForegroundColor Green
