# ============================================
# Script para ejecutar migraciones en Docker
# ============================================

Write-Host "🗄️ Ejecutando migraciones de base de datos..." -ForegroundColor Cyan

docker-compose exec app php artisan migrate

Write-Host ""
Write-Host "✅ Migraciones completadas" -ForegroundColor Green
Write-Host ""
Write-Host "💡 Comandos adicionales útiles:" -ForegroundColor Cyan
Write-Host "   Revertir última migración: docker-compose exec app php artisan migrate:rollback" -ForegroundColor White
Write-Host "   Refrescar DB (peligro):    docker-compose exec app php artisan migrate:fresh" -ForegroundColor White
Write-Host "   Ver estado:                docker-compose exec app php artisan migrate:status" -ForegroundColor White
Write-Host ""
