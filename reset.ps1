# reset.ps1 — A executer AVANT git push
# Remet le mot de passe a 'postgres' sans toucher a l'encodage des fichiers

$root = $PSScriptRoot
$files = Get-ChildItem $root -Recurse -Filter "*.php" | Where-Object {
    $_.Name -notmatch "config\.php|config\.example\.php|gen_hash\.php"
}

# Detecter le mot de passe actuel dans les fichiers
$currentPass = $null
foreach ($f in $files) {
    $bytes   = [System.IO.File]::ReadAllBytes($f.FullName)
    $hasBOM  = $bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF
    $enc     = [System.Text.UTF8Encoding]::new($hasBOM)
    $content = $enc.GetString($bytes)

    if ($content -match "'postgres','([^']+)'" -and $Matches[1] -ne 'postgres') {
        $currentPass = $Matches[1]; break
    }
    if ($content -match "'postgres', '([^']+)'" -and $Matches[1] -ne 'postgres') {
        $currentPass = $Matches[1]; break
    }
    if ($content -match "define\('DB_PASS', '([^']+)'\)" -and $Matches[1] -ne 'postgres') {
        $currentPass = $Matches[1]; break
    }
}

if (-not $currentPass) {
    Write-Host "Mot de passe deja a 'postgres'. Rien a faire."
    exit
}

$escaped = [regex]::Escape($currentPass)
$count   = 0

foreach ($f in $files) {
    $bytes   = [System.IO.File]::ReadAllBytes($f.FullName)
    $hasBOM  = $bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF
    $enc     = [System.Text.UTF8Encoding]::new($hasBOM)
    $content = $enc.GetString($bytes)

    $new = $content `
        -replace "'postgres','$escaped'",            "'postgres','postgres'" `
        -replace "'postgres', '$escaped'",           "'postgres', 'postgres'" `
        -replace "define\('DB_PASS', '$escaped'\)",  "define('DB_PASS', 'postgres')" `
        -replace "(\`$pass\s*=\s*)'$escaped'",       "`$1'postgres'" `
        -replace "(\`$password\s*=\s*)'$escaped'",   "`$1'postgres'"

    if ($new -ne $content) {
        [System.IO.File]::WriteAllBytes($f.FullName, $enc.GetBytes($new))
        $count++
    }
}

Write-Host "$count fichiers remis a 'postgres'."
Write-Host "Tu peux maintenant git add + git commit + git push."
Write-Host "Apres le push, relance .\setup.ps1 pour restaurer ton mot de passe."
