. "$PSScriptRoot\local-config.ps1"
Copy-Item "$PSScriptRoot\..\wp-theme\vadaszapro-theme\*" $LOCAL_WP_THEME -Recurse -Force
Write-Host 'Tema deploy kesz!'
