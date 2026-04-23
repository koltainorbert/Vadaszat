$b = [System.IO.File]::ReadAllBytes("D:\Vadaszat2026\wp-plugin\vadaszapro-core\admin\admin.js")
$c = [System.Text.Encoding]::Unicode.GetString($b)

$old = "frame.open();" + [char]13 + [char]10 + "        });" + [char]13 + [char]10 + [char]13 + [char]10 + "        /* " + [char]0x2500 + [char]0x2500 + " Toast stack"

$new = "frame.open();" + [char]13 + [char]10 + "        });" + [char]13 + [char]10 + [char]13 + [char]10 + "        /* " + [char]0x2500 + [char]0x2500 + " Media clear " + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + [char]0x2500 + " */" + [char]13 + [char]10 + "        $(document).on(" + [char]34 + "click" + [char]34 + ", " + [char]34 + ".va-media-clear" + [char]34 + ", function (e) {" + [char]13 + [char]10 + "            e.preventDefault();" + [char]13 + [char]10 + "            var btn     = $(this);" + [char]13 + [char]10 + "            var target  = btn.data(" + [char]34 + "target" + [char]34 + ");" + [char]13 + [char]10 + "            var preview = btn.data(" + [char]34 + "preview" + [char]34 + ");" + [char]13 + [char]10 + "            $(" + [char]34 + "#" + [char]34 + " + target).val(" + [char]34 + [char]34 + ").trigger(" + [char]34 + "change" + [char]34 + ");" + [char]13 + [char]10 + "            if (preview) {" + [char]13 + [char]10 + "                var $p = $(" + [char]34 + "#" + [char]34 + " + preview);" + [char]13 + [char]10 + "                if ($p.is(" + [char]34 + "img" + [char]34 + ")) $p.attr(" + [char]34 + "src" + [char]34 + ", " + [char]34 + [char]34 + ").hide();" + [char]13 + [char]10 + "                else $p.find(" + [char]34 + "img" + [char]34 + ").attr(" + [char]34 + "src" + [char]34 + ", " + [char]34 + [char]34 + ").hide();" + [char]13 + [char]10 + "            } else {" + [char]13 + [char]10 + "                btn.closest(" + [char]34 + ".va-media-field" + [char]34 + ").find(" + [char]34 + ".va-media-preview" + [char]34 + ").attr(" + [char]34 + "src" + [char]34 + ", " + [char]34 + [char]34 + ").hide();" + [char]13 + [char]10 + "            }" + [char]13 + [char]10 + "        });" + [char]13 + [char]10 + [char]13 + [char]10 + "        /* " + [char]0x2500 + [char]0x2500 + " Toast stack"

$idx = $c.IndexOf($old)
if ($idx -ge 0) {
    $c = $c.Replace($old, $new)
    [System.IO.File]::WriteAllBytes("D:\Vadaszat2026\wp-plugin\vadaszapro-core\admin\admin.js", [System.Text.Encoding]::Unicode.GetBytes($c))
    Write-Host "OK - replaced at $idx"
} else {
    Write-Host "NOT FOUND"
    $i2 = $c.IndexOf("frame.open")
    Write-Host "frame.open at: $i2"
    Write-Host $c.Substring($i2, 200)
}
