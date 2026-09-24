#!/bin/bash

# ============================================================
# Script de instalación automática de Supervisor
# Sistema de Inventario - Gobernación Yaracuy
# ============================================================

# NO usar 'set -e' para evitar que se detenga en comandos no críticos
# set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  INSTALADOR DE SUPERVISOR - INVENTARIO${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""

# 1. Detectar el directorio del proyecto
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
echo -e "${YELLOW}📁 Directorio del proyecto:${NC} $PROJECT_DIR"
echo ""

# 2. Detectar el usuario actual (para el worker)
CURRENT_USER="$(whoami)"
echo -e "${YELLOW}👤 Usuario actual:${NC} $CURRENT_USER"
echo ""

# 3. Verificar que PHP está instalado
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP no está instalado. Instálalo primero:${NC}"
    echo "   sudo apt install php php-cli php-fpm php-pgsql php-mbstring php-xml php-curl php-zip php-imap"
    exit 1
fi
PHP_PATH="$(which php)"
echo -e "${GREEN}✅ PHP encontrado:${NC} $PHP_PATH"
echo ""

# 4. Verificar que el proyecto tiene artisan
if [ ! -f "$PROJECT_DIR/artisan" ]; then
    echo -e "${RED}❌ No se encontró artisan en $PROJECT_DIR${NC}"
    echo -e "${RED}   Asegúrate de ejecutar este script desde la raíz del proyecto${NC}"
    exit 1
fi
echo -e "${GREEN}✅ artisan encontrado${NC}"
echo ""

# 5. Verificar que existe la carpeta storage
if [ ! -d "$PROJECT_DIR/storage" ]; then
    echo -e "${RED}❌ No existe la carpeta storage${NC}"
    exit 1
fi

# 6. Instalar Supervisor si no está
if ! command -v supervisorctl &> /dev/null; then
    echo -e "${YELLOW}📦 Supervisor no está instalado. Instalando...${NC}"
    sudo apt update
    sudo apt install -y supervisor
    echo -e "${GREEN}✅ Supervisor instalado${NC}"
else
    echo -e "${GREEN}✅ Supervisor ya está instalado${NC}"
fi
echo ""

# 7. Crear carpeta de logs
mkdir -p "$PROJECT_DIR/storage/logs"
echo -e "${GREEN}✅ Carpeta de logs lista${NC}"
echo ""

# 8. Crear archivo del worker
echo -e "${YELLOW}📝 Creando configuración del worker...${NC}"
sudo tee /etc/supervisor/conf.d/inventario-worker.conf > /dev/null <<EOF
[program:inventario-worker]
process_name=%(program_name)s_%(process_num)02d
command=$PHP_PATH $PROJECT_DIR/artisan queue:work --queue=notifications,default --tries=3 --timeout=180 --sleep=3 --max-time=3600
directory=$PROJECT_DIR
autostart=true
autorestart=true
startsecs=5
startretries=10
user=$CURRENT_USER
numprocs=1
redirect_stderr=true
stdout_logfile=$PROJECT_DIR/storage/logs/worker.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=3600
stopasgroup=true
killasgroup=true
EOF
echo -e "${GREEN}✅ Worker configurado${NC}"
echo ""

# 9. Crear archivo del scheduler
echo -e "${YELLOW}📝 Creando configuración del scheduler...${NC}"
sudo tee /etc/supervisor/conf.d/inventario-scheduler.conf > /dev/null <<EOF
[program:inventario-scheduler]
process_name=%(program_name)s_%(process_num)02d
command=$PHP_PATH $PROJECT_DIR/artisan schedule:work
directory=$PROJECT_DIR
autostart=true
autorestart=true
startsecs=5
startretries=10
user=$CURRENT_USER
numprocs=1
redirect_stderr=true
stdout_logfile=$PROJECT_DIR/storage/logs/scheduler.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=5
stopwaitsecs=60
stopasgroup=true
killasgroup=true
EOF
echo -e "${GREEN}✅ Scheduler configurado${NC}"
echo ""

# 10. Asegurar que Supervisor esté corriendo
echo -e "${YELLOW}🔄 Iniciando Supervisor...${NC}"
sudo systemctl enable supervisor 2>/dev/null || true
sudo systemctl start supervisor 2>/dev/null || true
sleep 2

# 11. Recargar configuración
echo -e "${YELLOW}🔄 Recargando configuración de Supervisor...${NC}"
sudo supervisorctl reread
sudo supervisorctl update
echo ""

# 12. Arrancar los programas
echo -e "${YELLOW}🚀 Arrancando programas...${NC}"
sudo supervisorctl start inventario-worker:* 2>/dev/null || true
sudo supervisorctl start inventario-scheduler:* 2>/dev/null || true
echo ""

# 13. Esperar unos segundos para que arranquen
sleep 3

# 14. Mostrar estado
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  ✅ INSTALACIÓN COMPLETADA${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${YELLOW}📊 Estado de los procesos:${NC}"
sudo supervisorctl status
echo ""

echo -e "${BLUE}📄 Verificando logs:${NC}"
if [ -f "$PROJECT_DIR/storage/logs/worker.log" ]; then
    echo "   Últimas 3 líneas del worker.log:"
    tail -n 3 "$PROJECT_DIR/storage/logs/worker.log" | sed 's/^/   /'
else
    echo "   ⚠️  worker.log aún no existe (se creará al procesar el primer job)"
fi
echo ""

echo -e "${GREEN}💡 Comandos útiles:${NC}"
echo "   Ver estado:      sudo supervisorctl status"
echo "   Reiniciar todo:  sudo supervisorctl restart all"
echo "   Detener todo:    sudo supervisorctl stop all"
echo "   Ver logs worker: tail -f $PROJECT_DIR/storage/logs/worker.log"
echo "   Ver logs sched:  tail -f $PROJECT_DIR/storage/logs/scheduler.log"
echo ""
echo -e "${GREEN}✅ Listo. El sistema arrancará automáticamente al iniciar Linux.${NC}"
echo ""