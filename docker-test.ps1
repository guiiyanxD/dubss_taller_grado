# ============================================
# Script para ejecutar tests en Docker
# ============================================

param(
    [string]$Filter = ""
)

Write-Host "🧪 Ejecutando tests..." -ForegroundColor Cyan

if ($Filter -ne "") {
    Write-Host "   Filtrando por: $Filter" -ForegroundColor Yellow
    docker-compose exec app php artisan test --filter=$Filter
} else {
    docker-compose exec app php artisan test
}

Write-Host ""
Write-Host "💡 Ejemplos de uso:" -ForegroundColor Cyan
Write-Host "   Test específico: .\docker-test.ps1 -Filter 'PostulacionTest'" -ForegroundColor White
Write-Host "   Con cobertura:   docker-compose exec app php artisan test --coverage" -ForegroundColor White
Write-Host ""
