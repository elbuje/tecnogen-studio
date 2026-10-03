#!/bin/bash
# ==============================================================================
# hvtunnels - Script de Túneles SSH para Mac hacia Hostinger VPS (72.62.107.109)
# Mantener siempre actualizado con TODOS los servicios del ecosistema.
# ==============================================================================

echo "🌉 Abriendo túneles SSH hacia Servidor Central Hostinger (72.62.107.109)..."
echo ""
echo "  🚀 SERVICIOS ACTIVOS Y LEVANTADOS:"
echo "     - TecnoGen Studio (Laravel):     http://localhost:8018"
echo "     - TecnoGen Studio (Vite):        http://localhost:5192"
echo "     - TecnoGen Web (tecnogen.ar):    http://localhost:5193 | Preview: http://localhost:8019"
echo "     - MetaWiki Grafo 2D/3D:          http://localhost:5190"
echo "     - Tecnobrain Web:                http://localhost:5177"
echo "     - Klyma:                         http://localhost:5191 | API: http://localhost:8010"
echo "     - IASA Tablero:                  http://localhost:8017"
echo "     - Fede Nowback:                  http://localhost:8015"
echo "     - Admin Financiera:              http://localhost:8012"
echo "     - Registro Emprendedor:          http://localhost:8013"
echo "     - Campus Tecnobrain:             http://localhost:8011"
echo "     - Oráculo Marketing:             http://localhost:5189 | API: http://localhost:8009"
echo "     - Impulso Empresarial:           http://localhost:5188"
echo "     - Sargon CRM:                    http://localhost:5182 | API: http://localhost:8008"
echo "     - Lana GPT:                      http://localhost:5181"
echo "     - Astros y Tarot:                http://localhost:5180"
echo "     - Nippur SEO:                    http://localhost:5179"
echo "     - Gestionservploi:               http://localhost:5178"
echo "     - Agenda Odonto:                 http://localhost:5176 | Preview: http://localhost:8016 | API: http://localhost:8002"
echo "     - ur.nippur.cloud:               http://localhost:5175 | API: http://localhost:8003"
echo "     - Cencherle Vendedor Digital:    http://localhost:5174 | API: http://localhost:8001"
echo "     - TuOrden App:                   http://localhost:5173 | API: http://localhost:8000"
echo "     - Operaciones Tecnobrain:        http://localhost:5184 | API: http://localhost:8014"
echo "     - Tecnobrain Social / Agentes:   http://localhost:5185 | Hub: http://localhost:8182"
echo "     - Tablero Cencherle:             http://localhost:8004"
echo "     - Zigurat CMG:                   http://localhost:8005"
echo "     - TecnoAgente:                   http://localhost:8181"
echo "     - MySQL Remoto (Local 3307):     localhost:3307"
echo ""

# Matar túneles previos que puedan estar colgados
pkill -f "ssh -f -N.*72.62.107.109" 2>/dev/null

ssh -f -N \
  -L 8018:localhost:8018 \
  -L 5192:localhost:5192 \
  -L 5193:localhost:5193 \
  -L 8019:localhost:8019 \
  -L 5190:localhost:5190 \
  -L 8017:localhost:8017 \
  -L 8015:localhost:8015 \
  -L 8014:localhost:8014 \
  -L 8013:localhost:8013 \
  -L 8012:localhost:8012 \
  -L 8011:localhost:8011 \
  -L 8010:localhost:8010 \
  -L 8009:localhost:8009 \
  -L 8008:localhost:8008 \
  -L 8007:localhost:8007 \
  -L 8005:localhost:8005 \
  -L 8004:localhost:8004 \
  -L 8003:localhost:8003 \
  -L 8002:localhost:8002 \
  -L 8001:localhost:8001 \
  -L 8000:localhost:8000 \
  -L 8016:localhost:8016 \
  -L 8181:localhost:8181 \
  -L 8182:localhost:8182 \
  -L 5191:localhost:5191 \
  -L 5189:localhost:5189 \
  -L 5188:localhost:5188 \
  -L 5185:localhost:5185 \
  -L 5184:localhost:5184 \
  -L 5182:localhost:5182 \
  -L 5181:localhost:5181 \
  -L 5180:localhost:5180 \
  -L 5179:localhost:5179 \
  -L 5178:localhost:5178 \
  -L 5177:localhost:5177 \
  -L 5176:localhost:5176 \
  -L 5175:localhost:5175 \
  -L 5174:localhost:5174 \
  -L 5173:localhost:5173 \
  -L 3307:localhost:3306 \
  mfmujic@72.62.107.109

if [ $? -eq 0 ]; then
    echo "✅ Túneles activos en segundo plano desde tu Mac."
    echo ""
    echo "👉 TecnoGen Studio: http://localhost:8018"
    echo "👉 MetaWiki Grafo:  http://localhost:5190"
    echo "📝 Para cerrar túneles: pkill -f 'ssh -f -N.*72.62.107.109'"
else
    echo "❌ Error al crear los túneles SSH."
fi
