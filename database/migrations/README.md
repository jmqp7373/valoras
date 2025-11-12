# 📊 Migraciones de Base de Datos - Valora.vip

## 📋 Índice de Migraciones

Este directorio contiene todas las migraciones SQL del sistema, organizadas cronológicamente por funcionalidad.

---

## 🔐 Autenticación y Seguridad

### `create_password_first_token_table.sql`
**Propósito:** Crear tabla para códigos de verificación inicial (6 dígitos)  
**Tabla:** `password_first_token`  
**Funcionalidad:** Maneja verificación de celular durante registro  
**Ejecutar:** Una vez en setup inicial  
**Dependencias:** Ninguna

### `update_password_reset_table.sql`
**Propósito:** Actualizar tabla de recuperación de contraseña  
**Tabla:** `password_reset_tokens`  
**Cambios:**
- Agregar columna `method` (sms/email)
- Modificar `token` a VARCHAR(6) para códigos cortos
**Ejecutar:** Una vez en sistema existente  
**Dependencias:** Tabla `password_reset_tokens` debe existir

---

## 👤 Usuarios y Perfiles

### `agregar_campos_perfil.sql`
**Propósito:** Extender información de perfil de usuario  
**Tabla:** `usuarios`  
**Campos agregados:**
- Información personal: `tipo_sangre`, `direccion`, `ciudad`
- Contacto emergencia: `contacto_emergencia_nombre`, `contacto_emergencia_parentesco`, `contacto_emergencia_telefono`
- Salud: `alergias`, `certificado_medico`
- Bancarios: `banco_nombre`, `banco_tipo_cuenta`, `banco_numero_cuenta`
- Días de descanso: `dias_descanso` (JSON)
- Documentos: `rut`, `pasaporte`
- Disponibilidad: `disponibilidad_inicio`, `disponibilidad_fin`

**Ejecutar:** Una vez para habilitar perfiles extendidos  
**Dependencias:** Tabla `usuarios` debe existir

---

## 🔑 Permisos y Roles

### `create_permissions_tables.sql`
**Propósito:** Sistema completo de permisos  
**Tablas creadas:**
- `roles` - Roles del sistema
- `roles_permisos` - Permisos por rol
- `usuarios_permisos` - Permisos individuales

**Ejecutar:** Una vez en setup inicial  
**Dependencias:** Ninguna

### `create_modulos_table.sql`
**Propósito:** Tabla de módulos del sistema  
**Tabla:** `modulos`  
**Funcionalidad:** Mapeo de archivos PHP a módulos con permisos  
**Ejecutar:** Una vez antes de usar panel de permisos  
**Dependencias:** Ninguna

### `add_eliminado_to_modulos.sql`
**Propósito:** Agregar control de eliminación lógica  
**Tabla:** `modulos`  
**Cambios:**
- Agregar columna `eliminado` (TINYINT)
- Agregar índice `idx_eliminado`

**Ejecutar:** Una vez en sistema con módulos existentes  
**Dependencias:** Tabla `modulos` debe existir

---

## 💰 Ventas

### `create_ventas_table.sql`
**Propósito:** Sistema de gestión de ventas  
**Tabla:** `ventas`  
**Funcionalidad:** Registro y seguimiento de ventas  
**Ejecutar:** Una vez en setup inicial  
**Dependencias:** Tabla `usuarios` debe existir

### `rename_ventas_columns.sql`
**Propósito:** Renombrar columnas para consistencia  
**Tabla:** `ventas`  
**Cambios:** Estandarización de nombres de columnas  
**Ejecutar:** Una vez en sistema con tabla ventas antigua  
**Dependencias:** Tabla `ventas` debe existir

### `rename_ventas_columns_fix.sql`
**Propósito:** Corrección de migración anterior  
**Tabla:** `ventas`  
**Ejecutar:** Solo si `rename_ventas_columns.sql` falló  
**Dependencias:** Tabla `ventas` debe existir

### `deploy_ventas_production.sql`
**Propósito:** Script de despliegue completo para producción  
**Funcionalidad:** Crear tabla ventas con estructura final  
**Ejecutar:** Una vez en servidor de producción  
**Dependencias:** Ninguna

---

## 📝 Orden de Ejecución Recomendado

Para una instalación nueva:

1. **Autenticación:**
   ```sql
   create_password_first_token_table.sql
   ```

2. **Permisos y Roles:**
   ```sql
   create_permissions_tables.sql
   create_modulos_table.sql
   add_eliminado_to_modulos.sql
   ```

3. **Usuarios:**
   ```sql
   agregar_campos_perfil.sql
   ```

4. **Ventas:**
   ```sql
   create_ventas_table.sql
   ```

Para actualizar sistema existente:
- Ejecutar solo las migraciones que agreguen nuevas funcionalidades
- Verificar dependencias antes de ejecutar
- Hacer backup antes de ejecutar migraciones en producción

---

## ⚠️ Notas Importantes

1. **Backup:** Siempre hacer backup antes de ejecutar migraciones
2. **Entorno:** Probar primero en desarrollo, luego en producción
3. **Reversión:** Algunas migraciones no tienen script de reversión
4. **Logs:** Revisar logs después de ejecutar cada migración

---

## 🔄 Historial de Cambios

- **2025-11-11:** Organización inicial de migraciones
- **2025-11-11:** Agregada migración `add_eliminado_to_modulos.sql`
- **2025-11-09:** Sistema de permisos y módulos
- **2025-11-08:** Sistema de ventas

---

## 📞 Soporte

Para dudas sobre migraciones:
- Autor: Jorge Mauricio Quiñónez Pérez
- Email: jmqp7373@gmail.com
- Proyecto: Valora.vip
