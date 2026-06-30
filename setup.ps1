# setup.ps1 — A executer apres git clone ou git pull
# Remplace 'postgres' par ton mot de passe sans toucher a l'encodage des fichiers

$pass = Read-Host "Entre ton mot de passe PostgreSQL"

# --- Creer config.php ---
$config = "<?php`r`ndefine('DB_HOST', 'localhost');`r`ndefine('DB_PORT', '5432');`r`ndefine('DB_NAME', 'base_inondation');`r`ndefine('DB_USER', 'postgres');`r`ndefine('DB_PASS', '$pass');`r`ndefine('DB_DSN',  'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME);`r`n"
[System.IO.File]::WriteAllText("$PSScriptRoot\config.php", $config, [System.Text.UTF8Encoding]::new($false))
Write-Host "config.php cree."

# --- Remplacer le mot de passe dans les fichiers PHP en preservant l'encodage ---
$files = Get-ChildItem "$PSScriptRoot" -Recurse -Filter "*.php" | Where-Object {
    $_.Name -notmatch "config\.php|config\.example\.php|gen_hash\.php"
}

$escaped = [regex]::Escape($pass)
$count = 0

foreach ($f in $files) {
    # Lire les octets bruts pour detecter le BOM
    $bytes   = [System.IO.File]::ReadAllBytes($f.FullName)
    $hasBOM  = $bytes.Length -ge 3 -and $bytes[0] -eq 0xEF -and $bytes[1] -eq 0xBB -and $bytes[2] -eq 0xBF
    $enc     = [System.Text.UTF8Encoding]::new($hasBOM)
    $content = $enc.GetString($bytes)

    $new = $content `
        -replace "'postgres','postgres'",            "'postgres','$pass'" `
        -replace "'postgres', 'postgres'",           "'postgres', '$pass'" `
        -replace "define\('DB_PASS', 'postgres'\)",  "define('DB_PASS', '$pass')" `
        -replace "(\`$pass\s*=\s*)'postgres'",       "`$1'$pass'" `
        -replace "(\`$password\s*=\s*)'postgres'",   "`$1'$pass'"

    if ($new -ne $content) {
        # Ecrire avec exactement le meme encodage que l'original
        [System.IO.File]::WriteAllBytes($f.FullName, $enc.GetBytes($new))
        $count++
    }
}

Write-Host "$count fichiers PHP mis a jour."
Write-Host "Configuration terminee !"
