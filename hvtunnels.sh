#!/bin/bash

# ==============================================================================
# hvtunnels - Túneles SSH para TecnoGen Studio hacia Hostinger VPS (72.62.107.109)
# ==============================================================================

echo "🌉 Abriendo túneles SSH hacia Servidor Dev Hostinger (72.62.107.109)..."
echo ""
echo "  🎨 TecnoGen Studio:"
echo "     - Frontend (Vite):          http://localhost:5192"
echo "     - Backend (FastAPI Swagger):http://localhost:8018/docs"
echo "     - MetaWiki Grafo:           http://localhost:5190"
echo ""

# Matar túneles previos colgados en estos puertos
pkill -f "ssh -f -N.*8018:localhost:8018" 2>/dev/null

ssh -f -N \
  -L 5192:localhost:5192 \
  -L 8018:localhost:8018 \
  -L 5190:localhost:5190 \
  mfmujic@72.62.107.109

if [ $? -eq 0 ]; then
    echo "✅ Túneles activos:"
    echo "👉 Abrí en tu navegador:"
    echo "   - Frontend: http://localhost:5192"
    echo "   - Backend:  http://localhost:8018/docs"
    echo "📝 Para cerrar túneles: pkill -f 'ssh -f -N.*8018:localhost:8018'"
else
    echo "❌ Error al crear los túneles SSH."
fi
