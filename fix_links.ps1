# fix_links.ps1
$baseDir = "c:\xampp\htdocs\e-campaignv2"
$files = Get-ChildItem -Path $baseDir -Recurse -Filter *.php

foreach ($file in $files) {
    if ($file.Name -eq "fix_links.ps1") { continue }
    $content = [System.IO.File]::ReadAllText($file.FullName)
    $newContent = $content -replace "camp_list\.php", "campaigns.php"
    
    if ($newContent -ne $content) {
        [System.IO.File]::WriteAllText($file.FullName, $newContent)
        Write-Host "Fixed link in: $($file.FullName)"
    }
}
