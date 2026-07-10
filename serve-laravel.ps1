$ErrorActionPreference = 'Stop'

$php = 'C:\xampp\php\php.exe'
$app = Join-Path $PSScriptRoot 'laravel'

if (-not (Test-Path -LiteralPath $php)) {
    throw "PHP XAMPP tidak ditemukan di $php"
}

Set-Location -LiteralPath $app
& $php artisan serve --host=127.0.0.1 --port=8081
