# reset.ps1 — A executer AVANT git push
# Remet tous les fichiers PHP a l'etat d'origine (password = 'postgres')

$root = $PSScriptRoot
$files = Get-ChildItem $root -Recurse -Filter "*.php" | Where-Object {
    $_.Name -notmatch "config\.php|config\.example\.php|gen_hash\.php"
}

# Detecter le mot de passe actuel en scannant les fichiers
$currentPass = $null
foreach ($f in $files) {
    $content = Get-Content $f.FullName -Raw -Encoding UTF8
    if ($content -match "'postgres','([^']+)'") {
        $candidate = $Matches[1]
        if ($candidate -ne 'postgres') { $currentPass = $candidate; break }
    }
    if ($content -match "'postgres', '([^']+)'") {
        $candidate = $Matches[1]
        if ($candidate -ne 'postgres') { $currentPass = $candidate; break }
    }
    if ($content -match "define\('DB_PASS', '([^']+)'\)") {
        $candidate = $Matches[1]
        if ($candidate -ne 'postgres') { $currentPass = $candidate; break }
    }
}

if (-not $currentPass) {
    Write-Host "Tous les fichiers sont deja a 'postgres', rien a faire."
    exit
}

Write-Host "Mot de passe detecte. Remise a zero en cours..."

$count = 0
$escaped = [regex]::Escape($currentPass)
foreach ($f in $files) {
    $content = Get-Content $f.FullName -Raw -Encoding UTF8
    $new = $content `
        -replace "'postgres','$escaped'",            "'postgres','postgres'" `
        -replace "'postgres', '$escaped'",           "'postgres', 'postgres'" `
        -replace "define\('DB_PASS', '$escaped'\)",  "define('DB_PASS', 'postgres')" `
        -replace "(\`$pass\s*=\s*)'$escaped'",       "`$1'postgres'" `
        -replace "(\`$password\s*=\s*)'$escaped'",   "`$1'postgres'"
    if ($new -ne $content) {
        Set-Content $f.FullName $new -Encoding UTF8 -NoNewline
        $count++
    }
}

# Remettre config.php a postgres aussi
$configPath = "$root\config.php"
if (Test-Path $configPath) {
    $c = Get-Content $configPath -Raw -Encoding UTF8
    Set-Content $configPath ($c -replace "define\('DB_PASS', '$escaped'\)", "define('DB_PASS', 'postgres')") -Encoding UTF8 -NoNewline
}

Write-Host "$count fichiers remis a 'postgres'."
Write-Host "Tu peux maintenant faire git push."
Write-Host "Apres le push, relance .\setup.ps1 pour restaurer ton mot de passe."
