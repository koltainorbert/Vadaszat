$b = [System.IO.File]::ReadAllBytes("D:\Vadaszat2026\wp-plugin\vadaszapro-core\admin\admin.js")
$c = [System.Text.Encoding]::Unicode.GetString($b)

$anchor = "        });" + "`r`n`r`n" + "        /* " + [char]0x2500 + [char]0x2500 + " Toast stack"

$videoBlock = "        });" + "`r`n`r`n" + `
"        /* " + [char]0x2500 + [char]0x2500 + " Video media picker " + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + " */" + "`r`n" + `
"        " + [char]36 + "(document).on(" + [char]34 + "click" + [char]34 + ", " + [char]34 + ".va-media-video-btn" + [char]34 + ", function (e) {" + "`r`n" + `
"            e.preventDefault();" + "`r`n" + `
"            var btn    = " + [char]36 + "(this);" + "`r`n" + `
"            var target = btn.data(" + [char]34 + "target" + [char]34 + ");" + "`r`n" + `
"            var frame  = wp.media({" + "`r`n" + `
"                title:    " + [char]34 + "Videó kiválasztása" + [char]34 + "," + "`r`n" + `
"                button:   { text: " + [char]34 + "Kiválaszt" + [char]34 + " }," + "`r`n" + `
"                multiple: false," + "`r`n" + `
"                library:  { type: " + [char]34 + "video" + [char]34 + " }" + "`r`n" + `
"            });" + "`r`n" + `
"            frame.on(" + [char]34 + "select" + [char]34 + ", function () {" + "`r`n" + `
"                var att = frame.state().get(" + [char]34 + "selection" + [char]34 + ").first().toJSON();" + "`r`n" + `
"                " + [char]36 + "(" + [char]34 + "#" + [char]34 + " + target).val(att.url).trigger(" + [char]34 + "change" + [char]34 + ");" + "`r`n" + `
"            });" + "`r`n" + `
"            frame.open();" + "`r`n" + `
"        });" + "`r`n`r`n" + `
"        /* " + [char]0x2500 + [char]0x2500 + " Video media clear " + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + " */" + "`r`n" + `
"        " + [char]36 + "(document).on(" + [char]34 + "click" + [char]34 + ", " + [char]34 + ".va-media-video-clear" + [char]34 + ", function (e) {" + "`r`n" + `
"            e.preventDefault();" + "`r`n" + `
"            var target = " + [char]36 + "(this).data(" + [char]34 + "target" + [char]34 + ");" + "`r`n" + `
"            " + [char]36 + "(" + [char]34 + "#" + [char]34 + " + target).val(" + [char]34 + [char]34 + ").trigger(" + [char]34 + "change" + [char]34 + ");" + "`r`n" + `
"        });" + "`r`n`r`n" + `
"        /* " + [char]0x2500 + [char]0x2500 + " Toast stack"

$idx = $c.IndexOf($anchor)
if ($idx -ge 0) {
    $c = $c.Replace($anchor, $videoBlock)
    [System.IO.File]::WriteAllBytes("D:\Vadaszat2026\wp-plugin\vadaszapro-core\admin\admin.js", [System.Text.Encoding]::Unicode.GetBytes($c))
    Write-Host "OK - video handler inserted"
} else {
    Write-Host "NOT FOUND - anchor not found"
    $i2 = $c.IndexOf("Toast stack")
    Write-Host "Toast stack at: $i2"
    Write-Host $c.Substring([Math]::Max(0,$i2-200), 300)
}
