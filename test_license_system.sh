#!/bin/bash

# ============================================
# SCRIPT DE TESTING - SISTEMA DE CONTROL
# ============================================
# ⚠️ CONFIDENCIAL - NO COMPARTIR
# ============================================

# CONFIGURACIÓN
BASE_URL="http://localhost:8000/api"
MASTER_KEY="change-this-key"  # Cambiar por tu key real

# Colores
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "════════════════════════════════════════════════════════"
echo "🔒 TESTING SISTEMA DE CONTROL DE ACCESO"
echo "════════════════════════════════════════════════════════"
echo ""

# Función para hacer requests
make_request() {
    local method=$1
    local endpoint=$2
    local key=$3
    
    if [ "$method" = "GET" ]; then
        curl -s -X GET "${BASE_URL}${endpoint}" \
            -H "X-System-Key: ${key}" \
            -H "Accept: application/json"
    else
        curl -s -X POST "${BASE_URL}${endpoint}" \
            -H "X-System-Key: ${key}" \
            -H "Accept: application/json"
    fi
}

# TEST 1: Verificar estado inicial
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "TEST 1: Verificar estado del sistema"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
response=$(make_request "GET" "/sys/hc" "$MASTER_KEY")
echo "Response: $response"
echo ""

# TEST 2: Intentar sin key (debe fallar)
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "TEST 2: Intentar sin Master Key (debe fallar)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
response=$(make_request "GET" "/sys/hc" "wrong-key")
if [[ $response == *"Unauthorized"* ]]; then
    echo -e "${GREEN}✓ PASS: Rechaza key incorrecta${NC}"
else
    echo -e "${RED}✗ FAIL: Acepta key incorrecta${NC}"
fi
echo ""

# TEST 3: Desactivar sistema
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "TEST 3: Desactivar sistema"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
response=$(make_request "POST" "/sys/off" "$MASTER_KEY")
echo "Response: $response"
if [[ $response == *"deactivated"* ]]; then
    echo -e "${GREEN}✓ Sistema desactivado correctamente${NC}"
else
    echo -e "${RED}✗ Error al desactivar${NC}"
fi
echo ""

# TEST 4: Intentar login con sistema desactivado
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "TEST 4: Intentar login con sistema desactivado"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
response=$(curl -s -X POST "${BASE_URL}/auth/login" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"dni":"11111111","password":"admin123"}')
    
if [[ $response == *"SERVICE_UNAVAILABLE"* ]]; then
    echo -e "${GREEN}✓ PASS: Login bloqueado correctamente${NC}"
else
    echo -e "${RED}✗ FAIL: Login NO bloqueado${NC}"
fi
echo "Response: $response"
echo ""

# TEST 5: Reactivar sistema
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "TEST 5: Reactivar sistema"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
response=$(make_request "POST" "/sys/on" "$MASTER_KEY")
echo "Response: $response"
if [[ $response == *"activated"* ]]; then
    echo -e "${GREEN}✓ Sistema reactivado correctamente${NC}"
else
    echo -e "${RED}✗ Error al reactivar${NC}"
fi
echo ""

# TEST 6: Intentar login con sistema activo
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "TEST 6: Intentar login con sistema activo"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
response=$(curl -s -X POST "${BASE_URL}/auth/login" \
    -H "Content-Type: application/json" \
    -H "Accept: application/json" \
    -d '{"dni":"11111111","password":"admin123"}')
    
if [[ $response == *"token"* ]] || [[ $response == *"success"* ]]; then
    echo -e "${GREEN}✓ PASS: Login funciona correctamente${NC}"
else
    echo -e "${YELLOW}⚠ Login response:${NC} $response"
fi
echo ""

# Resumen
echo "════════════════════════════════════════════════════════"
echo "✅ TESTING COMPLETADO"
echo "════════════════════════════════════════════════════════"
echo ""
echo "📋 COMANDOS ÚTILES:"
echo ""
echo "Verificar estado:"
echo "  curl -X GET ${BASE_URL}/sys/hc -H \"X-System-Key: ${MASTER_KEY}\""
echo ""
echo "Desactivar:"
echo "  curl -X POST ${BASE_URL}/sys/off -H \"X-System-Key: ${MASTER_KEY}\""
echo ""
echo "Activar:"
echo "  curl -X POST ${BASE_URL}/sys/on -H \"X-System-Key: ${MASTER_KEY}\""
echo ""
echo "════════════════════════════════════════════════════════"
