# Módulo de Administración de Credenciales - Valora.vip

## 📋 Descripción

Módulo de administración centralizada de credenciales de modelos para plataformas de transmisión en vivo (Chaturbate, Stripchat, etc.). Permite gestionar miles de registros con filtros avanzados y paginación server-side.

## ✅ Estado de Implementación

**Completado el: 15 de noviembre de 2025**

### Archivos Creados

1. **Controlador**: `controllers/CredencialesController.php`
   - Maneja la lógica de negocio
   - Endpoint AJAX para listado filtrado y paginado
   - Métodos para cargar datos de filtros (páginas, estudios, casas, cuentas)

2. **Vista**: `views/credenciales/credenciales_index.php`
   - Interfaz de usuario con filtros avanzados
   - Tabla responsive con Bootstrap
   - Modal para detalle de credenciales
   - Integración con layout master del proyecto

3. **JavaScript**: `assets/js/credenciales.js`
   - Carga dinámica de datos vía AJAX
   - Sistema de filtros con debounce
   - Paginación interactiva
   - Toggle de visualización de contraseñas
   - Manejo de errores

4. **Migraciones SQL**:
   - `database/migrations/add_credenciales_indexes.sql` - Índices de optimización
   - `database/migrations/add_credenciales_module.sql` - Registro del módulo

## 🔧 Características Implementadas

### Filtros Disponibles
- ✅ Búsqueda de modelo (nombre, apellidos, usuario de plataforma) con debounce
- ✅ Filtro por plataforma (Chaturbate, Stripchat, etc.)
- ✅ Filtro por casa de estudio
- ✅ Filtro por estudio
- ✅ Filtro por cuenta de estudio
- ✅ Filtro por estado (Activas/Eliminadas/Todas)

### Tabla de Datos
- ✅ Columnas: Modelo, Plataforma, Usuario, Password, Email, Cuenta Estudio, Estudio, Casa, Fecha Creación, Estado, Acciones
- ✅ Visualización segura de contraseñas (ocultas por defecto con toggle)
- ✅ Badges de color por plataforma
- ✅ Badges de estado (Activa/Eliminada)
- ✅ Paginación server-side (50 registros por página)
- ✅ Contador de total de registros

### Optimización
- ✅ Índices en base de datos:
  - `idx_id_usuario` - Credenciales por usuario
  - `idx_id_pagina` - Credenciales por plataforma
  - `idx_id_cuenta_estudio` - Credenciales por cuenta
  - `idx_eliminado` - Filtrado por estado
  - `idx_usuario` - Búsqueda por usuario de plataforma
  - `idx_nombres` - Búsqueda por nombre de modelo
  - `idx_apellidos` - Búsqueda por apellido de modelo
  - `idx_filtros_combinados` - Índice compuesto para queries complejos

### Seguridad y Permisos
- ✅ Integración con sistema de permisos por rol
- ✅ Permisos asignados a Superadmin (completo) y Admin (solo lectura)
- ✅ Verificación de autenticación
- ✅ Protección contra XSS en el frontend

## 📊 Relaciones de Base de Datos

```
credenciales
├── id_usuario → usuarios.id_usuario
├── id_pagina → paginas.id_pagina
└── id_cuenta_estudio → cuentas_estudios.id_cuenta_estudio
    └── id_estudio → estudios.id_estudio
        └── id_estudio_casa → estudio_casas.id_estudio_casa
```

## 🚀 Acceso al Módulo

### URL
```
http://localhost/valora.vip/controllers/CredencialesController.php
```

### Desde el menú del sistema
El módulo aparece automáticamente en el menú desplegable del header bajo la categoría "Admin" para usuarios con permisos.

## 🔒 Permisos Configurados

| Rol | Ver | Editar | Eliminar |
|-----|-----|--------|----------|
| Superadmin | ✅ | ✅ | ✅ |
| Admin | ✅ | ❌ | ❌ |

## 📝 Próximas Mejoras Sugeridas

1. **Funcionalidad de Detalle**
   - Implementar vista completa de credencial en modal
   - Historial de cambios

2. **Acciones CRUD**
   - Crear nueva credencial
   - Editar credencial existente
   - Eliminar (soft delete)
   - Restaurar credenciales eliminadas

3. **Exportación**
   - Exportar a Excel/CSV
   - Exportar a PDF con filtros aplicados

4. **Seguridad Avanzada**
   - Cifrado de contraseñas en base de datos
   - Logs de acceso a credenciales
   - Notificaciones de cambios

5. **Análisis**
   - Dashboard de credenciales por plataforma
   - Estadísticas de uso
   - Credenciales inactivas o duplicadas

## 🧪 Testing

### Verificación Local
1. Acceder a la URL del módulo
2. Verificar que se carguen los filtros correctamente
3. Probar cada filtro individualmente
4. Probar combinación de filtros
5. Verificar paginación
6. Verificar toggle de contraseñas
7. Verificar responsive design

### Performance
- Con 18,000 registros, la carga inicial debe ser < 2 segundos
- Filtrado debe responder en < 1 segundo
- Cambio de página debe ser instantáneo

## 📦 Dependencias

- PHP 7.4+
- MySQL/MariaDB con soporte para índices
- Bootstrap 5 (ya incluido en el proyecto)
- Bootstrap Icons (ya incluido en el proyecto)

## 🛠️ Troubleshooting

### La tabla no carga datos
1. Verificar que existan credenciales en la BD
2. Revisar consola del navegador (F12) para errores JS
3. Verificar que el usuario tenga permisos

### Filtros no funcionan
1. Limpiar caché del navegador
2. Verificar que los selectores tengan datos
3. Revisar logs de PHP para errores del controlador

### Performance lenta
1. Verificar que los índices estén creados
2. Ejecutar `EXPLAIN` en las queries del controlador
3. Considerar aumentar `innodb_buffer_pool_size`

## 👨‍💻 Autor

Sistema Valora - Implementado el 15 de noviembre de 2025

## 📜 Licencia

Propiedad de Valora.vip - Todos los derechos reservados
