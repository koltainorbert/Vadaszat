$b = [System.IO.File]::ReadAllBytes("D:\Vadaszat2026\wp-plugin\vadaszapro-core\admin\admin.js")
$c = [System.Text.Encoding]::Unicode.GetString($b)

$insertAfter = "frame.open();" + "`r`n" + "        });"
$insertBefore = "`r`n`r`n" + "        /* " + [char]0x2500 + [char]0x2500 + " Toast stack"

$searchStr = $insertAfter + $insertBefore

$clearBlock = "`r`n`r`n" + "        /* " + [char]0x2500 + [char]0x2500 + " Media clear " + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + " */" + "`r`n" + `
"        " + [char]36 + "(document).on(" + [char]34 + "click" + [char]34 + ", " + [char]34 + ".va-media-clear" + [char]34 + ", function (e) {" + "`r`n" + `
"            e.preventDefault();" + "`r`n" + `
"            var btn     = " + [char]36 + "(this);" + "`r`n" + `
"            var target  = btn.data(" + [char]34 + "target" + [char]34 + ");" + "`r`n" + `
"            var preview = btn.data(" + [char]34 + "preview" + [char]34 + ");" + "`r`n" + `
"            " + [char]36 + "(" + [char]34 + "#" + [char]34 + " + target).val(" + [char]34 + [char]34 + ").trigger(" + [char]34 + "change" + [char]34 + ");" + "`r`n" + `
"            if (preview) {" + "`r`n" + `
"                var " + [char]36 + "p = " + [char]36 + "(" + [char]34 + "#" + [char]34 + " + preview);" + "`r`n" + `
"                if (" + [char]36 + "p.is(" + [char]34 + "img" + [char]34 + ")) " + [char]36 + "p.attr(" + [char]34 + "src" + [char]34 + ", " + [char]34 + [char]34 + ").hide();" + "`r`n" + `
"                else " + [char]36 + "p.find(" + [char]34 + "img" + [char]34 + ").attr(" + [char]34 + "src" + [char]34 + ", " + [char]34 + [char]34 + ").hide();" + "`r`n" + `
"            } else {" + "`r`n" + `
"                btn.closest(" + [char]34 + ".va-media-field" + [char]34 + ").find(" + [char]34 + ".va-media-preview" + [char]34 + ").attr(" + [char]34 + "src" + [char]34 + ", " + [char]34 + [char]34 + ").hide();" + "`r`n" + `
"            }" + "`r`n" + `
"        });"

$replaceStr = $insertAfter + $clearBlock + $insertBefore

$idx = $c.IndexOf($searchStr)
if ($idx -ge 0) {
    $c = $c.Replace($searchStr, $replaceStr)
    [System.IO.File]::WriteAllBytes("D:\Vadaszat2026\wp-plugin\vadaszapro-core\admin\admin.js", [System.Text.Encoding]::Unicode.GetBytes($c))
    Write-Host "OK - replaced at index $idx"
} else {
    Write-Host "NOT FOUND - keresett szoveg nincs meg"
    $i2 = $c.IndexOf("frame.open")
    Write-Host "frame.open at: $i2"
    Write-Host $c.Substring($i2, 300)
}
