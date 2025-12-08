# ============================================
# Script de inicio Docker para DUBSS
# ============================================

Write-Host "🐳 Iniciando entorno Docker DUBSS..." -ForegroundColor Cyan

# Verificar si Docker está corriendo
$dockerStatus = docker info 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Docker no está corriendo. Por favor, inicia Docker Desktop." -ForegroundColor Red
    exit 1
}

Write-Host "✅ Docker está corriendo" -ForegroundColor Green

# Construir imágenes
Write-Host "📦 Construyendo imágenes Docker..." -ForegroundColor Yellow
docker-compose build

# Levantar contenedores
Write-Host "🚀 Levantando contenedores..." -ForegroundColor Yellow
docker-compose up -d

# Esperar a que PostgreSQL esté listo
Write-Host "⏳ Esperando a que PostgreSQL esté listo..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

# Instalar dependencias Composer si es necesario
Write-Host "📚 Verificando dependencias PHP..." -ForegroundColor Yellow
docker-compose exec app composer install --no-interaction

# Generar APP_KEY si no existe
$envContent = Get-Content .env -Raw
if ($envContent -match "APP_KEY=\s*$") {
    Write-Host "🔑 Generando APP_KEY..." -ForegroundColor Yellow
    docker-compose exec app php artisan key:generate
}

# Ejecutar migraciones
Write-Host "🗄️ ¿Ejecutar migraciones? (S/N)" -ForegroundColor Yellow
$runMigrations = Read-Host
if ($runMigrations -eq "S" -or $runMigrations -eq "s") {
    docker-compose exec app php artisan migrate
}

Write-Host ""
Write-Host "✅ ¡Entorno Docker iniciado correctamente!" -ForegroundColor Green
Write-Host ""
Write-Host "📍 URLs disponibles:" -ForegroundColor Cyan
Write-Host "   🌐 Aplicación:    http://localhost:8000" -ForegroundColor White
Write-Host "   📧 Mailpit:       http://localhost:8025" -ForegroundColor White
Write-Host "   🔥 Vite (dev):    http://localhost:5173" -ForegroundColor White
Write-Host "   🐘 PostgreSQL:    localhost:5432" -ForegroundColor White
Write-Host ""
Write-Host "🛠️ Comandos útiles:" -ForegroundColor Cyan
Write-Host "   Ver logs:         docker-compose logs -f" -ForegroundColor White
Write-Host "   Detener:          docker-compose down" -ForegroundColor White
Write-Host "   Reiniciar:        docker-compose restart" -ForegroundColor White
Write-Host "   Ejecutar Artisan: docker-compose exec app php artisan [comando]" -ForegroundColor White
Write-Host ""
