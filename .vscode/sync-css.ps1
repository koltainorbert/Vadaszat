# Szinkronizacio: root frontend/css/ -> plugin es tema
# Hasznalas: . .vscode/sync-css.ps1

$root = "$PSScriptRoot\.."
$sources = @(
    "frontend/css/frontend.css",
    "frontend/css/frontend.min.css"
)

$targets = @(
    "$root\wp-plugin\vadaszapro-core\frontend\css\",
    "$root\wp-theme\vadaszapro-theme\frontend\css\"
)

Write-Host "CSS Szinkronizacio: root -> plugin/tema" -ForegroundColor Cyan

foreach ($target in $targets) {
    if (-not (Test-Path $target)) {
        New-Item -ItemType Directory -Path $target -Force | Out-Null
        Write-Host "✓ Keszult: $target" -ForegroundColor Green
    }
    
    foreach ($source in $sources) {
        $srcPath = "$root\$source"
        $fileName = Split-Path $source -Leaf
        $dstPath = "$target$fileName"
        
        if (Test-Path $srcPath) {
            Copy-Item $srcPath $dstPath -Force
            Write-Host "✓ Szinkronizalt: $fileName -> $(Split-Path $target -Leaf)" -ForegroundColor Green
        } else {
            Write-Host "✗ Forras nem talalhato: $source" -ForegroundColor Red
        }
    }
}

Write-Host "Kesz!" -ForegroundColor Green
