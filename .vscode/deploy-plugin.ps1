. "$PSScriptRoot\local-config.ps1"
Copy-Item "$PSScriptRoot\..\wp-plugin\vadaszapro-core\*" $LOCAL_WP_PLUGIN -Recurse -Force
Write-Host 'Plugin deploy kesz!'
