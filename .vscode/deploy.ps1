. "$PSScriptRoot\local-config.ps1"

Copy-Item "$PSScriptRoot\..\wp-plugin\vadaszapro-core\*" $LOCAL_WP_PLUGIN -Recurse -Force
Copy-Item "$PSScriptRoot\..\wp-theme\vadaszapro-theme\*" $LOCAL_WP_THEME -Recurse -Force
if ($LOCAL_WP_CHILD) {
    if (-not (Test-Path $LOCAL_WP_CHILD)) { New-Item -ItemType Directory -Path $LOCAL_WP_CHILD -Force | Out-Null }
    Copy-Item "$PSScriptRoot\..\wp-theme\vadaszapro-child\*" $LOCAL_WP_CHILD -Recurse -Force
    Write-Host 'Child tema is deployolva!'
}
Write-Host 'Deploy kesz!'
