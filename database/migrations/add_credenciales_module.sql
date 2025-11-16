-- ============================================
-- Migración: Registrar módulo de Credenciales en la BD
-- Proyecto: Valora.vip
-- Fecha: 2025-11-15
-- Descripción: Agregar el módulo de administración de credenciales
--              al sistema de módulos y permisos
-- ============================================

-- Insertar el módulo de credenciales si no existe
INSERT INTO modulos (clave, titulo, subtitulo, categoria, ruta_completa, icono, activo, exento)
SELECT 
    'credenciales_admin',
    'Administración de Credenciales',
    'Gestiona las credenciales de modelos en todas las plataformas',
    'admin',
    'views\\credenciales\\credenciales_index.php',
    '🔐',
    1,
    0
WHERE NOT EXISTS (
    SELECT 1 FROM modulos WHERE clave = 'credenciales_admin'
);

-- Si el módulo ya existe, actualizar solo el icono
UPDATE modulos 
SET icono = '🔐'
WHERE clave = 'credenciales_admin';

-- Verificar inserción
SELECT 
    'Módulo registrado correctamente' AS Estado,
    id,
    clave,
    titulo,
    categoria,
    ruta_completa,
    icono
FROM modulos 
WHERE clave = 'credenciales_admin';

-- Obtener el ID del módulo recién insertado
SET @modulo_id := (SELECT id FROM modulos WHERE clave = 'credenciales_admin' LIMIT 1);

-- Asignar permisos completos al rol Superadmin (id = 26, nivel_orden = 1)
INSERT INTO roles_permisos (id_rol, modulo, puede_ver, puede_editar, puede_eliminar)
SELECT 
    26,
    'credenciales_admin',
    1,
    1,
    1
WHERE NOT EXISTS (
    SELECT 1 FROM roles_permisos 
    WHERE id_rol = 26 AND modulo = 'credenciales_admin'
);

-- Asignar permisos de visualización al rol Administrador (id = 2, nivel_orden = 2)
INSERT INTO roles_permisos (id_rol, modulo, puede_ver, puede_editar, puede_eliminar)
SELECT 
    2,
    'credenciales_admin',
    1,
    0,
    0
WHERE NOT EXISTS (
    SELECT 1 FROM roles_permisos 
    WHERE id_rol = 2 AND modulo = 'credenciales_admin'
);

-- Verificar permisos asignados
SELECT 
    'Permisos asignados:' AS Info;

SELECT 
    r.nombre AS Rol,
    rp.modulo AS Modulo,
    rp.puede_ver AS Ver,
    rp.puede_editar AS Editar,
    rp.puede_eliminar AS Eliminar
FROM roles_permisos rp
INNER JOIN roles r ON r.id = rp.id_rol
WHERE rp.modulo = 'credenciales_admin'
ORDER BY r.nivel_orden;

SELECT '✓ Módulo de credenciales configurado exitosamente' AS Estado_Final;
