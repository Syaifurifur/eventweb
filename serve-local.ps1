Set-Location $PSScriptRoot
& 'C:\xampp\php\php.exe' -d extension_dir=C:\xampp\php\ext -d extension=intl -S 127.0.0.1:8080 -t public server.php
