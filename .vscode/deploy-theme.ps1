. "$PSScriptRoot\local-config.ps1"

$themeSource = "$PSScriptRoot\..\wp-theme\vadaszapro-theme\*"
$themeTargets = @()

if ($LOCAL_WP_THEME) {
    if (-not (Test-Path $LOCAL_WP_THEME)) {
        New-Item -ItemType Directory -Path $LOCAL_WP_THEME -Force | Out-Null
    }
    $themeTargets += $LOCAL_WP_THEME

    $themesRoot = Split-Path -Path $LOCAL_WP_THEME -Parent
    if (Test-Path $themesRoot) {
        $variants = Get-ChildItem -Path $themesRoot -Directory -ErrorAction SilentlyContinue |
            Where-Object { $_.Name -like 'vadaszapro-theme*' }
        foreach ($v in $variants) {
            if ($themeTargets -notcontains $v.FullName) {
                $themeTargets += $v.FullName
            }
        }
    }
}

foreach ($target in $themeTargets) {
    Copy-Item $themeSource $target -Recurse -Force
    Write-Host ("Tema deploy: " + $target)
}

if ($LOCAL_WP_CHILD) {
    if (-not (Test-Path $LOCAL_WP_CHILD)) { New-Item -ItemType Directory -Path $LOCAL_WP_CHILD -Force | Out-Null }
    Copy-Item "$PSScriptRoot\..\wp-theme\vadaszapro-child\*" $LOCAL_WP_CHILD -Recurse -Force
    Write-Host 'Child tema is deployolva!'
}
Write-Host 'Tema deploy kesz!'
