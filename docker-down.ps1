# ============================================
# Script para detener Docker DUBSS
# ============================================

Write-Host "🛑 Deteniendo entorno Docker DUBSS..." -ForegroundColor Yellow

docker-compose down

Write-Host "✅ Contenedores detenidos" -ForegroundColor Green
Write-Host ""
Write-Host "💡 Tip: Los datos de la BD se conservan en volúmenes" -ForegroundColor Cyan
Write-Host "   Para eliminar TODO (incluida la BD): docker-compose down -v" -ForegroundColor White
Write-Host ""
