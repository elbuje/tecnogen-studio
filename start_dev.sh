#!/bin/bash

# ==============================================================================
# start_dev.sh — Inicio de Servidores de Desarrollo para TecnoGen Studio
# ==============================================================================

echo "🚀 Iniciando TecnoGen Studio en entorno de Desarrollo..."

# Matar procesos previos en puertos 8018 y 5192
fuser -k 8018/tcp 2>/dev/null
fuser -k 5192/tcp 2>/dev/null

# 1. Iniciar Backend FastAPI en :8018
echo "📦 Iniciando Backend FastAPI en http://127.0.0.1:8018..."
PYTHONPATH=backend ./backend/venv/bin/uvicorn app.main:app --host 0.0.0.0 --port 8018 --reload &
BACKEND_PID=$!

# 2. Iniciar Frontend Vite en :5192
echo "🎨 Iniciando Frontend Vite en http://127.0.0.1:5192..."
cd frontend && npm run dev &
FRONTEND_PID=$!

echo ""
echo "✅ Servicios activos:"
echo "   - Frontend: http://localhost:5192"
echo "   - Backend:  http://localhost:8018/docs"
echo ""
echo "Presione Ctrl+C para detener ambos servicios."

trap "kill $BACKEND_PID $FRONTEND_PID 2>/dev/null" EXIT
wait
