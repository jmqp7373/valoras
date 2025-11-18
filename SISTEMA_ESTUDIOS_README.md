# Sistema de Gestión de Estudios - Documentación

## Resumen del Sistema

Sistema completo de gestión de estudios, casas/plataformas, categorías y clases con sistema de auditoría centralizado implementado para Valora VIP.

## Características Implementadas

### 1. Base de Datos
- **Tabla**: `estudios_auditoria`
- **Campos**:
  - `id_auditoria` (PK auto_increment)
  - `tabla_afectada` (ENUM: estudios, estudios_casas, estudios_categorias, estudios_clases)
  - `id_registro` (INT)
  - `accion` (ENUM: INSERT, UPDATE, DELETE)
  - `datos_anteriores` (JSON)
  - `datos_nuevos` (JSON)
  - `id_usuario` (FK a usuarios)
  - `fecha_modificacion` (TIMESTAMP)
  - `ip_usuario` (VARCHAR 45)
  - `descripcion` (VARCHAR 255)
- **Índices**: 4 índices para optimización (tabla+registro, usuario, fecha, acción)
- **Constraints**: FK a usuarios con ON DELETE RESTRICT

### 2. Modelo (`models/Estudios.php`)
#### Métodos de Estudios:
- `obtenerEstudios($id_usuario, $es_admin)` - Lista estudios según permisos
- `obtenerEstudioPorId($id_estudio)` - Obtiene un estudio específico
- `crearEstudio($datos)` - Crea nuevo estudio con auditoría
- `actualizarEstudio($id_estudio, $datos)` - Actualiza con auditoría
- `eliminarEstudio($id_estudio)` - Elimina con validación de dependencias

#### Métodos de Casas:
- `obtenerCasasPorEstudio($id_estudio)` - Lista casas de un estudio
- `obtenerTodasCasas()` - Lista todas las casas con nombre de estudio
- `obtenerCasaPorId($id_casa)` - Obtiene una casa específica
- `crearCasa($datos)` - Crea nueva casa con auditoría
- `actualizarCasa($id_casa, $datos)` - Actualiza con auditoría
- `eliminarCasa($id_casa)` - Elimina con auditoría

#### Métodos de Categorías y Clases:
- Métodos similares CRUD para categorías y clases
- Todos con auditoría automática

#### Sistema de Auditoría:
- `registrarAuditoria()` - Método privado llamado automáticamente
- `obtenerHistorialCambios($tabla, $id_registro, $limit)` - Consulta historial
- Decodificación automática de JSON para facilitar uso

### 3. Controlador (`controllers/EstudiosController.php`)
#### Seguridad:
- Verificación de autenticación obligatoria
- Validación de permisos admin (rol o nivel_orden <= 2)
- Verificación de acceso por estudio (usuarios ven solo su estudio)

#### Endpoints AJAX:
**Estudios**: listar_estudios, obtener_estudio, crear_estudio, actualizar_estudio, eliminar_estudio
**Casas**: listar_casas, obtener_casa, crear_casa, actualizar_casa, eliminar_casa
**Categorías**: listar_categorias, obtener_categoria, crear_categoria, actualizar_categoria, eliminar_categoria
**Clases**: listar_clases, obtener_clase, crear_clase, actualizar_clase, eliminar_clase
**Auditoría**: historial (con filtros por tabla/acción)

#### Validaciones:
- Campos requeridos (nombres no vacíos)
- Permisos antes de operaciones sensibles
- Validación de existencia de registros
- Respuestas JSON estandarizadas

### 4. Vista (`views/admin/estudiosGestion.php`)
#### Estructura:
- **5 Tabs**: Estudios, Casas/Plataformas, Categorías, Clases, Historial
- **DataTables** para búsqueda/filtrado/paginación
- **Modales Bootstrap** para formularios crear/editar
- **Filtros dinámicos** en casas (por estudio) e historial (tabla/acción)

#### Permisos UI:
- Botones crear/editar/eliminar solo para admin
- Usuarios normales solo visualizan

#### Diseño:
- Gradient header (purple-blue)
- Font Awesome icons
- Responsive Bootstrap 5.3
- Custom CSS para badges y JSON diff viewer

### 5. JavaScript (`assets/js/estudios-gestion.js`)
#### Funcionalidad:
- **AJAX completo** para todas las operaciones CRUD
- **SweetAlert2** para confirmaciones y notificaciones
- **Validación** de formularios antes de envío
- **Actualización dinámica** de tablas y selectores

#### Componentes:
- Inicialización DataTables con i18n español
- Gestión de modales Bootstrap
- Funciones reutilizables por entidad
- Historial con formato JSON pretty-print

### 6. Registro del Módulo
#### Base de Datos:
- **Tabla**: `modulos`
- **Clave**: `views_admin_estudiosGestion`
- **Ruta**: `views\admin\estudiosGestion.php`
- **Título**: "Gestión de Estudios"
- **Subtítulo**: "Administrar estudios, casas y categorías"
- **Icono**: `fa-building`
- **Categoría**: `admin`
- **Activo**: 1

#### Permisos:
- **Roles con acceso**: admin (ID 2), superadmin (ID 26)
- **Permisos**: puede_ver=1, puede_editar=1, puede_eliminar=1
- **Tabla**: `roles_permisos`

## Flujo de Trabajo

### Crear Estudio (Admin):
1. Click "Nuevo Estudio" → Abre modal
2. Llenar nombre y descripción → Click "Guardar"
3. JavaScript valida → AJAX POST a EstudiosController
4. Controller valida permisos → Llama modelo
5. Modelo inicia transacción → INSERT estudio
6. Registra auditoría (acción=INSERT, datos_nuevos)
7. Commit → Respuesta JSON success
8. JavaScript muestra SweetAlert success → Recarga tabla

### Editar Casa (Usuario Normal):
1. Usuario solo ve casas de su estudio (filtrado en modelo)
2. Click editar → AJAX GET obtener_casa
3. Controller verifica `puedeAccederEstudio()` 
4. Si match con su estudio → Modal con datos
5. Edita y guarda → Controller valida acceso nuevamente
6. Modelo captura datos_anteriores → UPDATE
7. Registra auditoría (acción=UPDATE, ambos JSON)
8. Commit → Actualización en tabla

### Ver Historial:
1. Tab Historial → JavaScript llama cargarHistorial()
2. AJAX GET con filtros opcionales (tabla, acción)
3. Controller llama obtenerHistorialCambios()
4. Modelo query con JOIN a usuarios
5. Decodifica JSON automáticamente
6. Retorna array con nombres completos de usuarios
7. JavaScript renderiza timeline con badges coloreados
8. JSON diff viewer para comparar estados

## Archivos Creados/Modificados

### Nuevos Archivos:
1. `models/Estudios.php` - 650+ líneas
2. `controllers/EstudiosController.php` - 650+ líneas
3. `views/admin/estudiosGestion.php` - 450+ líneas
4. `assets/js/estudios-gestion.js` - 750+ líneas

### Migración Base de Datos:
```sql
CREATE TABLE estudios_auditoria (...)
```

### Registros Base de Datos:
```sql
-- Módulo
UPDATE modulos SET titulo='Gestión de Estudios', icono='fa-building' 
WHERE clave='views_admin_estudiosGestion';

-- Permisos
INSERT INTO roles_permisos (id_rol=2, modulo='views_admin_estudiosGestion', ...)
INSERT INTO roles_permisos (id_rol=26, modulo='views_admin_estudiosGestion', ...)
```

## Acceso al Sistema

### URL:
`https://valora.vip/views/admin/estudiosGestion.php`

### Permisos Requeridos:
- Rol: admin o superadmin
- O nivel_orden <= 2 en usuarios_info

### Funcionalidad por Rol:
**Admin/Superadmin**:
- Ver todos los estudios
- Crear/editar/eliminar estudios
- Crear/editar/eliminar categorías y clases
- Gestionar casas de cualquier estudio
- Ver historial completo

**Usuario Normal**:
- Ver solo su estudio asignado
- Gestionar casas de su estudio únicamente
- Ver categorías y clases (solo lectura)
- Ver historial relacionado a su estudio

## Sistema de Auditoría

### Qué se Registra:
- **Quién**: id_usuario, nombres, apellidos
- **Qué**: tabla_afectada, accion (INSERT/UPDATE/DELETE)
- **Cuándo**: fecha_modificacion (timestamp automático)
- **Dónde**: ip_usuario (REMOTE_ADDR)
- **Cómo**: datos_anteriores y datos_nuevos en JSON

### Formato JSON:
```json
{
  "nombre": "Estudio ABC",
  "descripcion": "Descripción del estudio"
}
```

### Consulta de Ejemplo:
```php
$historial = $estudios->obtenerHistorialCambios('estudios', 5, 50);
// Retorna últimas 50 modificaciones del estudio ID 5
```

## Características Técnicas

### Seguridad:
- Prepared statements (PDO)
- Transacciones para integridad
- Validación permisos en controlador
- CSRF protection (session_start)
- Sanitización de inputs

### Performance:
- Índices en campos de búsqueda
- DataTables con paginación del lado cliente
- Lazy loading de historial
- JSON storage eficiente

### Mantenibilidad:
- Código modular y reutilizable
- Comentarios descriptivos
- Convenciones consistentes
- Separación de responsabilidades (MVC)

### UX/UI:
- Notificaciones amigables (SweetAlert2)
- Confirmaciones para acciones destructivas
- Feedback visual inmediato
- Diseño responsive

## Próximos Pasos (Opcional)

1. **Exportación de Historial**: Botón para exportar a CSV/Excel
2. **Filtros Avanzados**: Rango de fechas, búsqueda de usuario
3. **Gráficas**: Dashboard con estadísticas de actividad
4. **Notificaciones**: Email cuando se modifica estudio
5. **Restauración**: Botón para revertir cambios desde historial
6. **Logs Detallados**: Integrar con sistema de logs PHP

## Notas de Implementación

- ✅ Tabla auditoría creada exitosamente
- ✅ Modelo con CRUD completo y auditoría automática
- ✅ Controlador con validación de permisos robusta
- ✅ Vista con 5 tabs funcionales y modales
- ✅ JavaScript con AJAX completo y manejo de errores
- ✅ Módulo registrado en base de datos
- ✅ Permisos asignados a admin y superadmin

**Fecha de Implementación**: 2025-01-XX  
**Desarrollado por**: GitHub Copilot (Claude Sonnet 4.5)  
**Estado**: ✅ COMPLETADO - Listo para producción
