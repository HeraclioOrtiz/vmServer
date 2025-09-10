# Setup PHP portable sin permisos de administrador
Write-Host "🚀 Configurando PHP portable..." -ForegroundColor Green

$phpDir = "C:\php-portable"
$composerDir = "C:\composer-portable"

# Crear directorios
New-Item -ItemType Directory -Force -Path $phpDir
New-Item -ItemType Directory -Force -Path $composerDir

# Descargar PHP
Write-Host "📥 Descargando PHP 8.2..." -ForegroundColor Yellow
$phpUrl = "https://windows.php.net/downloads/releases/php-8.2.24-Win32-vs16-x64.zip"
$phpZip = "$env:TEMP\php.zip"
Invoke-WebRequest -Uri $phpUrl -OutFile $phpZip

# Extraer PHP
Write-Host "📦 Extrayendo PHP..." -ForegroundColor Yellow
Expand-Archive -Path $phpZip -DestinationPath $phpDir -Force

# Configurar PHP
$phpIni = @"
extension_dir = "ext"
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=zip
extension=json
extension=xml
"@

$phpIni | Out-File -FilePath "$phpDir\php.ini" -Encoding UTF8

# Descargar Composer
Write-Host "📥 Descargando Composer..." -ForegroundColor Yellow
$composerUrl = "https://getcomposer.org/download/latest-stable/composer.phar"
Invoke-WebRequest -Uri $composerUrl -OutFile "$composerDir\composer.phar"

# Crear batch para Composer
$composerBat = '@echo off' + "`r`n" + 'php "' + $composerDir + '\composer.phar" %*'
$composerBat | Out-File -FilePath "$composerDir\composer.bat" -Encoding ASCII

# Agregar al PATH de la sesión actual
$env:PATH = "$phpDir;$composerDir;" + $env:PATH

Write-Host "✅ PHP portable configurado!" -ForegroundColor Green
Write-Host "📍 PHP instalado en: $phpDir" -ForegroundColor Cyan
Write-Host "📍 Composer instalado en: $composerDir" -ForegroundColor Cyan
Write-Host "" -ForegroundColor White
Write-Host "🔧 Para usar en esta sesión:" -ForegroundColor Yellow
Write-Host "`$env:PATH = `"$phpDir;$composerDir;`" + `$env:PATH" -ForegroundColor White
Write-Host "" -ForegroundColor White
Write-Host "🧪 Verificar instalación:" -ForegroundColor Yellow
Write-Host "php --version" -ForegroundColor White
Write-Host "composer --version" -ForegroundColor White
