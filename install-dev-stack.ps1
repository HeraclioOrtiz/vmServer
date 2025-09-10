# Script de instalación automática para desarrollo local
# Ejecutar como Administrador

Write-Host "🚀 Instalando stack de desarrollo..." -ForegroundColor Green

# Verificar si es administrador
if (-NOT ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Host "❌ Ejecutar como Administrador" -ForegroundColor Red
    exit 1
}

# Instalar Chocolatey si no existe
if (!(Get-Command choco -ErrorAction SilentlyContinue)) {
    Write-Host "📦 Instalando Chocolatey..." -ForegroundColor Yellow
    Set-ExecutionPolicy Bypass -Scope Process -Force
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
    iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
}

# Instalar componentes
Write-Host "🐘 Instalando PHP..." -ForegroundColor Yellow
choco install php -y

Write-Host "🎵 Instalando Composer..." -ForegroundColor Yellow
choco install composer -y

Write-Host "🗄️ Instalando MySQL..." -ForegroundColor Yellow
choco install mysql -y

Write-Host "🌐 Instalando Apache..." -ForegroundColor Yellow
choco install apache-httpd -y

# Refrescar PATH
$env:Path = [System.Environment]::GetEnvironmentVariable("Path","Machine") + ";" + [System.Environment]::GetEnvironmentVariable("Path","User")

Write-Host "✅ Instalación completada!" -ForegroundColor Green
Write-Host "🔄 Reinicia PowerShell y ejecuta: php --version" -ForegroundColor Cyan
