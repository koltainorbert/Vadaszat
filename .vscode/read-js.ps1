$b=[System.IO.File]::ReadAllBytes("D:\Vadaszat2026\wp-plugin\vadaszapro-core\admin\admin.js")
$c=[System.Text.Encoding]::Unicode.GetString($b)
$i=$c.IndexOf("vaInitColorPickers")
Write-Output $c.Substring($i,2000)
