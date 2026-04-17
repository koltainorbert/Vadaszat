. "$PSScriptRoot\local-config.ps1"

Copy-Item "$PSScriptRoot\..\wp-plugin\vadaszapro-core\*" $LOCAL_WP_PLUGIN -Recurse -Force
Copy-Item "$PSScriptRoot\..\wp-theme\vadaszapro-theme\*" $LOCAL_WP_THEME -Recurse -Force
Write-Host 'Deploy kesz!'
