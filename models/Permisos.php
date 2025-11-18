<?php
/**
 * Modelo de Permisos
 * Proyecto: Valora.vip
 * Autor: Jorge Mauricio Quiñónez Pérez
 * Fecha: 2025-11-08
 * 
 * Gestiona los permisos de acceso por rol y por usuario individual
 */

class Permisos {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todos los roles disponibles
     */
    public function obtenerRoles() {
        try {
            $sql = "SELECT id, nombre, descripcion FROM roles ORDER BY nivel_orden ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerRoles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todos los módulos del sistema escaneando archivos reales en /views/
     * Sincroniza automáticamente con la tabla modulos
     */
    public function obtenerModulos() {
        $modulos = [];
        $viewsPath = __DIR__ . '/../views/';
        
        // Verificar que el directorio views existe
        if (!is_dir($viewsPath)) {
            error_log("ERROR: No se encuentra el directorio views en: " . $viewsPath);
            return $modulos;
        }
        
        // Carpetas a escanear
        $carpetas = ['admin', 'checksTests', 'login', 'finanzas', 'ventas', 'tickets', 'usuario'];
        
        foreach ($carpetas as $carpeta) {
            $carpetaPath = $viewsPath . $carpeta;
            
            if (is_dir($carpetaPath)) {
                // Escanear archivos PHP en la carpeta principal
                $this->escanearArchivosPhp($carpetaPath, $carpeta, $modulos);
                
                // Escanear subcarpetas
                $subcarpetas = @glob($carpetaPath . '/*', GLOB_ONLYDIR);
                if ($subcarpetas) {
                    foreach ($subcarpetas as $subcarpeta) {
                        $nombreSubcarpeta = basename($subcarpeta);
                        $this->escanearArchivosPhp($subcarpeta, $carpeta . '/' . $nombreSubcarpeta, $modulos);
                    }
                }
            }
        }
        
        // Sincronizar con la base de datos
        if (!empty($modulos)) {
            $this->sincronizarModulos($modulos);
        }
        
        return $modulos;
    }
    
    /**
     * Sincronizar módulos detectados con la tabla modulos
     */
    private function sincronizarModulos($modulosDetectados) {
        try {
            foreach ($modulosDetectados as $clave => $rutaCompleta) {
                // Extraer categoría correctamente
                // Ejemplos:
                // views\admin\permissionsPanel.php -> admin
                // views\admin\credenciales\credencialesGestion.php -> credenciales
                // views\login\login.php -> login
                
                $categoria = 'sistema';
                
                // Dividir la ruta por backslashes
                $partes = explode('\\', $rutaCompleta);
                
                // Buscar el índice de 'views'
                $indexViews = array_search('views', $partes);
                
                if ($indexViews !== false) {
                    // Si hay al menos 3 niveles después de views (views\carpeta1\carpeta2\archivo.php)
                    // usar la segunda carpeta (carpeta2)
                    if (isset($partes[$indexViews + 2]) && strpos($partes[$indexViews + 2], '.php') === false) {
                        $categoria = $partes[$indexViews + 2];
                    }
                    // Si solo hay 2 niveles (views\carpeta\archivo.php)
                    // usar la primera carpeta
                    elseif (isset($partes[$indexViews + 1])) {
                        $categoria = $partes[$indexViews + 1];
                    }
                }
                
                // Insertar o actualizar módulo
                $sql = "INSERT INTO modulos (clave, ruta_completa, categoria, activo) 
                        VALUES (?, ?, ?, 1)
                        ON DUPLICATE KEY UPDATE 
                            ruta_completa = VALUES(ruta_completa),
                            categoria = VALUES(categoria),
                            activo = 1,
                            fecha_actualizacion = CURRENT_TIMESTAMP";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$clave, $rutaCompleta, $categoria]);
            }
        } catch (Exception $e) {
            error_log("Error sincronizando módulos: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener módulos con nombres descriptivos de la BD
     * Incluye detección de archivos renombrados/eliminados
     * Ordenado alfabéticamente por carpeta y nombre de archivo
     */
    public function obtenerModulosConNombres() {
        try {
            // Primero escanear archivos físicos para sincronizar
            $modulosArchivos = $this->obtenerModulos();
            
            // Corrección única: Arreglar categoría del módulo de credenciales
            $this->conn->exec("UPDATE modulos SET categoria = 'credenciales' WHERE clave = 'views_admin_credenciales_credencialesGestion.php' AND categoria = 'admin'");
            
            // Obtener TODOS los módulos de la BD ordenados alfabéticamente
            $sql = "SELECT clave, ruta_completa, titulo, categoria, exento, icono 
                    FROM modulos 
                    WHERE activo = 1
                    ORDER BY categoria ASC, ruta_completa ASC";
            $stmt = $this->conn->query($sql);
            $modulosDB = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $resultado = [];
            
            // Procesar cada módulo de la BD
            foreach ($modulosDB as $moduloDB) {
                $clave = $moduloDB['clave'];
                $rutaDB = $moduloDB['ruta_completa'];
                $titulo = $moduloDB['titulo'];
                $exento = (int)$moduloDB['exento'];
                $icono = $moduloDB['icono'] ?? null;
                
                // Verificar si el archivo físico existe
                $rutaFisica = __DIR__ . '/../' . str_replace('\\', '/', $rutaDB);
                $archivoExiste = @file_exists($rutaFisica);
                
                // Agregar módulo con información completa
                $resultado[$clave] = [
                    'ruta' => $rutaDB,
                    'titulo' => $titulo,
                    'archivo_existe' => $archivoExiste,
                    'exento' => $exento,
                    'icono' => $icono
                ];
            }
            
            return $resultado;
        } catch (Exception $e) {
            error_log("Error en obtenerModulosConNombres: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Actualizar título de un módulo
     */
    public function actualizarNombreDescriptivo($clave, $nombreDescriptivo) {
        try {
            $sql = "UPDATE modulos SET titulo = ? WHERE clave = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$nombreDescriptivo, $clave]);
        } catch (Exception $e) {
            error_log("Error actualizando título: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar un módulo permanentemente
     */
    public function marcarComoEliminado($clave) {
        try {
            $sql = "DELETE FROM modulos WHERE clave = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$clave]);
        } catch (Exception $e) {
            error_log("Error eliminando módulo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Toggle estado exento de un módulo
     */
    public function toggleExento($clave, $exento) {
        try {
            $sql = "UPDATE modulos SET exento = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE clave = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$exento, $clave]);
        } catch (Exception $e) {
            error_log("Error actualizando estado exento: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Escanear archivos PHP en una carpeta específica
     */
    private function escanearArchivosPhp($path, $categoria, &$modulos) {
        $archivos = @glob($path . '/*.php');
        
        if (!$archivos) {
            return;
        }
        
        foreach ($archivos as $archivo) {
            $nombreArchivo = basename($archivo);
            
            // Excluir archivos README y otros no relevantes
            if (strpos($nombreArchivo, 'README') === false) {
                $rutaRelativa = 'views\\' . str_replace('/', '\\', $categoria) . '\\' . $nombreArchivo;
                $key = str_replace(['/', '\\', '.php'], ['_', '_', ''], $rutaRelativa);
                $modulos[$key] = $rutaRelativa;
            }
        }
    }

    /**
     * Obtener permisos de un rol específico
     * @param int $idRol ID del rol
     * @return array Permisos del rol organizados por módulo
     */
    public function obtenerPermisosPorRol($idRol) {
        $modulos = $this->obtenerModulos();
        $permisos = [];

        // Inicializar con permisos vacíos
        foreach ($modulos as $key => $nombre) {
            $permisos[$key] = [
                'nombre' => $nombre,
                'puede_ver' => 0,
                'puede_editar' => 0,
                'puede_eliminar' => 0
            ];
        }

        // Obtener permisos existentes de la BD
        $sql = "SELECT modulo, puede_ver, puede_editar, puede_eliminar 
                FROM roles_permisos 
                WHERE id_rol = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idRol]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Actualizar con permisos reales
        foreach ($resultados as $permiso) {
            if (isset($permisos[$permiso['modulo']])) {
                $permisos[$permiso['modulo']]['puede_ver'] = $permiso['puede_ver'];
                $permisos[$permiso['modulo']]['puede_editar'] = $permiso['puede_editar'];
                $permisos[$permiso['modulo']]['puede_eliminar'] = $permiso['puede_eliminar'];
            }
        }

        return $permisos;
    }

    /**
     * Obtener permisos de un usuario específico
     * @param int $idUsuario ID del usuario
     * @return array Permisos del usuario organizados por módulo
     */
    public function obtenerPermisosPorUsuario($idUsuario) {
        $modulos = $this->obtenerModulos();
        $permisos = [];

        // Inicializar con permisos vacíos
        foreach ($modulos as $key => $nombre) {
            $permisos[$key] = [
                'nombre' => $nombre,
                'puede_ver' => 0,
                'puede_editar' => 0,
                'puede_eliminar' => 0
            ];
        }

        // Obtener permisos existentes de la BD
        $sql = "SELECT modulo, puede_ver, puede_editar, puede_eliminar 
                FROM usuarios_permisos 
                WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idUsuario]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Actualizar con permisos reales
        foreach ($resultados as $permiso) {
            if (isset($permisos[$permiso['modulo']])) {
                $permisos[$permiso['modulo']]['puede_ver'] = $permiso['puede_ver'];
                $permisos[$permiso['modulo']]['puede_editar'] = $permiso['puede_editar'];
                $permisos[$permiso['modulo']]['puede_eliminar'] = $permiso['puede_eliminar'];
            }
        }

        return $permisos;
    }

    /**
     * Actualizar permiso de un rol
     */
    public function actualizarPermisoRol($idRol, $modulo, $puedeVer, $puedeEditar, $puedeEliminar) {
        try {
            $sql = "INSERT INTO roles_permisos (id_rol, modulo, puede_ver, puede_editar, puede_eliminar)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        puede_ver = VALUES(puede_ver),
                        puede_editar = VALUES(puede_editar),
                        puede_eliminar = VALUES(puede_eliminar)";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $idRol, 
                $modulo, 
                $puedeVer ? 1 : 0, 
                $puedeEditar ? 1 : 0, 
                $puedeEliminar ? 1 : 0
            ]);
        } catch (Exception $e) {
            error_log("Error actualizando permiso de rol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar permiso de un usuario
     */
    public function actualizarPermisoUsuario($idUsuario, $modulo, $puedeVer, $puedeEditar, $puedeEliminar) {
        try {
            $sql = "INSERT INTO usuarios_permisos (id_usuario, modulo, puede_ver, puede_editar, puede_eliminar)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        puede_ver = VALUES(puede_ver),
                        puede_editar = VALUES(puede_editar),
                        puede_eliminar = VALUES(puede_eliminar)";
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([
                $idUsuario, 
                $modulo, 
                $puedeVer ? 1 : 0, 
                $puedeEditar ? 1 : 0, 
                $puedeEliminar ? 1 : 0
            ]);
        } catch (Exception $e) {
            error_log("Error actualizando permiso de usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los usuarios con sus roles
     */
    public function obtenerUsuariosConRoles() {
        $sql = "SELECT 
                    u.id_usuario,
                    u.nombres,
                    u.apellidos,
                    u.cedula,
                    u.email,
                    r.nombre as rol_nombre
                FROM usuarios u
                LEFT JOIN roles r ON u.id_rol = r.id
                ORDER BY u.nombres ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verificar si un usuario tiene permiso para un módulo
     * @param int $idUsuario ID del usuario
     * @param string $modulo Nombre del módulo
     * @param string $accion 'ver', 'editar', 'eliminar'
     * @return bool
     */
    public function tienePermiso($idUsuario, $modulo, $accion = 'ver') {
        // Primero verificar permisos individuales del usuario
        $sql = "SELECT puede_ver, puede_editar, puede_eliminar 
                FROM usuarios_permisos 
                WHERE id_usuario = ? AND modulo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idUsuario, $modulo]);
        $permisoUsuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($permisoUsuario) {
            $campo = "puede_" . $accion;
            return isset($permisoUsuario[$campo]) && $permisoUsuario[$campo] == 1;
        }

        // Si no hay permiso individual, verificar por rol
        $sql = "SELECT rp.puede_ver, rp.puede_editar, rp.puede_eliminar
                FROM usuarios u
                INNER JOIN roles_permisos rp ON u.id_rol = rp.id_rol
                WHERE u.id_usuario = ? AND rp.modulo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idUsuario, $modulo]);
        $permisoRol = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($permisoRol) {
            $campo = "puede_" . $accion;
            return isset($permisoRol[$campo]) && $permisoRol[$campo] == 1;
        }

        return false;
    }

    /**
     * Verificar si un usuario es admin o superadmin
     */
    public function esAdmin($idUsuario) {
        try {
            $sql = "SELECT r.nombre 
                    FROM usuarios u
                    INNER JOIN roles r ON u.id_rol = r.id
                    WHERE u.id_usuario = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuario]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado && in_array($resultado['nombre'], ['admin', 'superadmin']);
        } catch (Exception $e) {
            error_log("Error en esAdmin: " . $e->getMessage());
            return false;
        }
    }
}
