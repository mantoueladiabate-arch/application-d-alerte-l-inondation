# setup.ps1 — A executer apres un git clone ou quand le mot de passe change
# 1. Cree config.php avec ton mot de passe local
# 2. Met a jour le mot de passe dans tous les fichiers PHP qui ont encore des credentials en dur

$pass = Read-Host "Entre ton mot de passe PostgreSQL"

# --- Creer config.php ---
$config = @"
<?php
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'base_inondation');
define('DB_USER', 'postgres');
define('DB_PASS', '$pass');
define('DB_DSN',  'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME);
"@
Set-Content -Path "$PSScriptRoot\config.php" -Value $config -Encoding UTF8
Write-Host "config.php cree."

# --- Remplacer les credentials en dur dans les fichiers PHP ---
$files = Get-ChildItem "$PSScriptRoot" -Recurse -Filter "*.php" | Where-Object {
    $_.Name -notmatch "config\.php|config\.example\.php|gen_hash\.php"
}

$count = 0
foreach ($f in $files) {
    $content = Get-Content $f.FullName -Raw -Encoding UTF8
    $new = $content `
        -replace "'postgres','postgres'",   "'postgres','$pass'" `
        -replace "'postgres', 'postgres'",  "'postgres', '$pass'" `
        -replace "define\('DB_PASS', 'postgres'\)", "define('DB_PASS', '$pass')" `
        -replace "(\`$pass\s*=\s*)'postgres'",      "`$1'$pass'" `
        -replace "(\`$password\s*=\s*)'postgres'",  "`$1'$pass'"
    if ($new -ne $content) {
        Set-Content $f.FullName $new -Encoding UTF8 -NoNewline
        $count++
    }
}

Write-Host "$count fichiers PHP mis a jour."
Write-Host "Configuration terminee !"
