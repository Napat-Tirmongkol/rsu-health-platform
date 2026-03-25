# fix_deprecated_db.ps1
$baseDir = "c:\xampp\htdocs\e-campaignv2\deprecated\e_Borrow"

$files = Get-ChildItem -Path $baseDir -Recurse -Filter *.php

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw
    $newContent = $content
    
    # Calculate depth relative to deprecated/e_Borrow
    $relPath = $file.FullName.Substring($baseDir.Length + 1)
    $depth = $relPath.Split('\').Count - 1
    
    # Base prefix to reach project root from deprecated/e_Borrow/subdir...
    # deprecated/e_Borrow is depth 0 from its own sub-root.
    # Level 1 (e.g. index.php) -> ../../config/db_connect.php
    # Level 2 (e.g. admin/index.php) -> ../../../config/db_connect.php
    $prefix = "../" * ($depth + 2)
    $configPath = $prefix + "config/db_connect.php"
    
    # Replace the local include with the global config
    $newContent = $newContent -replace "require_once\(.*?db_connect\.php.*?\)", "require_once(__DIR__ . '/$configPath')"
    $newContent = $newContent -replace "include\(.*?db_connect\.php.*?\)", "require_once(__DIR__ . '/$configPath')"
    
    if ($newContent -ne $content) {
        Set-Content $file.FullName $newContent -NoNewline -Encoding UTF8
        Write-Host "Fixed DB link in: $relPath (Depth: $depth)"
    }
}
