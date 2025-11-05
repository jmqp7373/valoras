#!/bin/bash
# Script de instalación para Valora.vip
# Configura automáticamente los archivos necesarios

echo "🚀 Configurando Valora.vip..."

# Verificar si config/config.php existe
if [ ! -f "config/config.php" ]; then
    echo "📝 Creando config/config.php desde plantilla..."
    cp config/config.example.php config/config.php
    echo "✅ config/config.php creado"
    echo ""
    echo "⚠️  IMPORTANTE: Edita config/config.php y agrega tu API Key de OpenAI"
    echo "   Línea a modificar: define('OPENAI_API_KEY', 'tu-api-key-aqui');"
    echo ""
else
    echo "✅ config/config.php ya existe"
fi

# Verificar permisos de directorios
echo "🔍 Verificando permisos..."

if [ -d "views/login" ]; then
    echo "✅ Directorio views/login existe"
else
    echo "📁 Creando directorio views/login..."
    mkdir -p views/login
fi

# Verificar archivos críticos
echo "🔍 Verificando archivos del sistema IA..."

files_to_check=(
    "controllers/login/usernameGenerator.php"
    "views/login/registranteUserAvailavilitySelect.php"
    "config/config.example.php"
)

for file in "${files_to_check[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file"
    else
        echo "❌ FALTA: $file"
    fi
done

echo ""
echo "🎉 Configuración completada!"
echo ""
echo "📋 PRÓXIMOS PASOS:"
echo "1. Editar config/config.php con tu API Key de OpenAI"
echo "2. Probar la funcionalidad en: views/login/registranteUserAvailavilitySelect.php"
echo "3. Integrar con el registro en: views/register.php"
echo ""
echo "🌐 URLs disponibles:"
echo "   - Registro: /views/register.php"
echo "   - Generador IA: /views/login/registranteUserAvailavilitySelect.php"
echo "   - Verificación: /views/admin/checksTests/system-check.php"