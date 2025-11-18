<?php
/**
 * Valora.vip - Perfil Controller
 * Manejo de actualización de perfil de usuario
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

class PerfilController {
    private $db;
    private $usuarioModel;
    private $upload_dir_base = __DIR__ . '/../uploads/perfiles/';

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->usuarioModel = new Usuario($this->db);

        // Crear directorio de uploads si no existe
        if (!file_exists($this->upload_dir_base)) {
            mkdir($this->upload_dir_base, 0755, true);
        }
    }

    /**
     * Actualizar perfil del usuario
     * Soporta guardado parcial - solo actualiza campos enviados
     */
    public function actualizarPerfil() {
        startSessionSafely();

        if (!isLoggedIn()) {
            return ['success' => false, 'message' => 'No estás autenticado'];
        }

        $id_usuario = $_SESSION['user_id'];
        $datos = [];

        // Campos de texto simples
        $campos_texto = [
            'nombres', 'apellidos', 'celular', 'email', 'fecha_de_nacimiento',
            'tipo_sangre', 'direccion', 'ciudad',
            'contacto_emergencia_nombre', 'contacto_emergencia_parentesco', 'contacto_emergencia_telefono',
            'alergias', 'id_banco', 'banco_tipo_cuenta', 'banco_numero_cuenta',
            'notas', 'id_estudio'
        ];

        foreach ($campos_texto as $campo) {
            if (isset($_POST[$campo])) {
                $datos[$campo] = trim($_POST[$campo]);
            }
        }

        // Días de descanso (checkboxes - guardar como JSON)
        if (isset($_POST['dias_descanso']) && is_array($_POST['dias_descanso'])) {
            $datos['dias_descanso'] = json_encode($_POST['dias_descanso']);
        }

        // Procesar archivos subidos
        $archivos_procesados = $this->procesarArchivosSubidos($id_usuario);
        
        if ($archivos_procesados['success']) {
            // Mezclar rutas de archivos con datos
            $datos = array_merge($datos, $archivos_procesados['rutas']);
        } else {
            return $archivos_procesados; // Retornar error si falló la subida
        }

        // Actualizar perfil
        $resultado = $this->usuarioModel->actualizarPerfil($id_usuario, $datos);

        if ($resultado['success']) {
            // Actualizar variables de sesión
            $_SESSION['user_nombres'] = $datos['nombres'] ?? $_SESSION['user_nombres'];
            $_SESSION['user_apellidos'] = $datos['apellidos'] ?? $_SESSION['user_apellidos'];
            $_SESSION['user_email'] = $datos['email'] ?? $_SESSION['user_email'];
        }

        return $resultado;
    }

    /**
     * Procesar archivos subidos (fotos y certificados)
     */
    private function procesarArchivosSubidos($id_usuario) {
        $rutas = [];
        $tipos_archivo = [
            'foto_perfil' => ['jpg', 'jpeg', 'png', 'gif'],
            'foto_con_cedula' => ['jpg', 'jpeg', 'png'],
            'foto_cedula_frente' => ['jpg', 'jpeg', 'png', 'pdf'],
            'foto_cedula_reverso' => ['jpg', 'jpeg', 'png', 'pdf'],
            'certificado_medico' => ['jpg', 'jpeg', 'png', 'pdf']
        ];

        foreach ($tipos_archivo as $tipo => $extensiones_permitidas) {
            if (isset($_FILES[$tipo]) && $_FILES[$tipo]['error'] === UPLOAD_ERR_OK) {
                $resultado = $this->subirArchivo($_FILES[$tipo], $tipo, $id_usuario, $extensiones_permitidas);
                
                if ($resultado['success']) {
                    $rutas[$tipo] = $resultado['ruta'];
                } else {
                    return $resultado; // Retornar error inmediatamente
                }
            }
        }

        return ['success' => true, 'rutas' => $rutas];
    }

    /**
     * Subir un archivo individual al servidor
     */
    private function subirArchivo($archivo, $tipo, $id_usuario, $extensiones_permitidas) {
        // Validar tamaño (máximo 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($archivo['size'] > $max_size) {
            return ['success' => false, 'message' => "El archivo $tipo excede el tamaño máximo de 5MB"];
        }

        // Validar extensión
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $extensiones_permitidas)) {
            return [
                'success' => false, 
                'message' => "Formato de archivo no permitido para $tipo. Permitidos: " . implode(', ', $extensiones_permitidas)
            ];
        }

        // Mapear tipo de archivo a carpeta específica
        $carpetas_tipo = [
            'foto_perfil' => 'FotoDePerfil',
            'foto_con_cedula' => 'FotoConCedulaEnMano',
            'foto_cedula_frente' => 'CedulaLadoFrontal',
            'foto_cedula_reverso' => 'CedulaLadoReverso',
            'certificado_medico' => '' // Se mantiene en la raíz de perfiles
        ];

        // Obtener la subcarpeta correspondiente
        $subcarpeta = $carpetas_tipo[$tipo] ?? '';
        
        // Construir ruta completa con subcarpeta
        $directorio_destino = $this->upload_dir_base;
        if ($subcarpeta !== '') {
            $directorio_destino .= $subcarpeta . '/';
            // Crear directorio si no existe
            if (!file_exists($directorio_destino)) {
                mkdir($directorio_destino, 0755, true);
            }
        }

        // Generar nombre único
        $nombre_archivo = $id_usuario . '_' . $tipo . '_' . time() . '.' . $extension;
        $ruta_completa = $directorio_destino . $nombre_archivo;
        
        // Ruta relativa para guardar en BD
        $ruta_relativa = 'uploads/perfiles/';
        if ($subcarpeta !== '') {
            $ruta_relativa .= $subcarpeta . '/';
        }
        $ruta_relativa .= $nombre_archivo;

        // Mover archivo
        if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            return ['success' => true, 'ruta' => $ruta_relativa];
        }

        return ['success' => false, 'message' => "Error al subir el archivo $tipo"];
    }

    /**
     * Obtener datos del perfil actual
     */
    public function obtenerPerfil() {
        startSessionSafely();

        if (!isLoggedIn()) {
            return false;
        }

        $id_usuario = $_SESSION['user_id'];
        return $this->usuarioModel->obtenerPerfil($id_usuario);
    }

    /**
     * Obtener lista de estudios para el selector
     */
    public function obtenerEstudios() {
        try {
            $query = "SELECT id_estudio, nombre FROM estudios ORDER BY nombre ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener estudios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener lista de bancos activos para el selector
     */
    public function obtenerBancos() {
        try {
            $query = "SELECT id_banco, nombre_banco, tipo_banco, codigo_abreviado, color_banco 
                      FROM usuarios_bancos 
                      WHERE estado = 1 
                      ORDER BY 
                        CASE tipo_banco 
                          WHEN 'Banco' THEN 1 
                          WHEN 'Neobanco' THEN 2 
                          WHEN 'Cooperativa' THEN 3 
                        END,
                        nombre_banco ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener bancos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cambiar contraseña del usuario
     */
    public function cambiarPassword() {
        startSessionSafely();

        if (!isLoggedIn()) {
            return ['success' => false, 'message' => 'No estás autenticado'];
        }

        // Validar campos requeridos
        if (empty($_POST['password_actual']) || empty($_POST['password_nueva']) || empty($_POST['password_confirmar'])) {
            return ['success' => false, 'message' => 'Todos los campos son obligatorios'];
        }

        $id_usuario = $_SESSION['user_id'];
        $password_actual = $_POST['password_actual'];
        $password_nueva = $_POST['password_nueva'];
        $password_confirmar = $_POST['password_confirmar'];

        // Validar que las contraseñas nuevas coincidan
        if ($password_nueva !== $password_confirmar) {
            return ['success' => false, 'message' => 'Las contraseñas nuevas no coinciden'];
        }

        // Validar longitud mínima
        if (strlen($password_nueva) < 8) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres'];
        }

        try {
            // Obtener contraseña actual de la base de datos
            $query = "SELECT password FROM usuarios WHERE id_usuario = :id_usuario LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Usuario no encontrado'];
            }

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar contraseña actual
            if (!password_verify($password_actual, $usuario['password'])) {
                return ['success' => false, 'message' => 'La contraseña actual es incorrecta'];
            }

            // Hashear nueva contraseña
            $password_hash = password_hash($password_nueva, PASSWORD_DEFAULT);

            // Actualizar contraseña en la base de datos
            $query = "UPDATE usuarios SET password = :password WHERE id_usuario = :id_usuario";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':password', $password_hash);
            $stmt->bindParam(':id_usuario', $id_usuario);

            if ($stmt->execute()) {
                // Destruir la sesión actual para forzar nuevo login
                session_destroy();
                
                return [
                    'success' => true, 
                    'message' => 'Contraseña actualizada exitosamente. Por seguridad, se cerrarán todas las sesiones.',
                    'logout' => true // Indicar al frontend que debe redirigir al login
                ];
            }

            return ['success' => false, 'message' => 'Error al actualizar la contraseña'];

        } catch (PDOException $e) {
            error_log("Error al cambiar contraseña: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos'];
        }
    }
}

// Manejo de peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $controller = new PerfilController();
    
    switch ($_POST['action']) {
        case 'actualizar_perfil':
            $resultado = $controller->actualizarPerfil();
            header('Content-Type: application/json');
            echo json_encode($resultado);
            exit();
            
        case 'cambiar_password':
            $resultado = $controller->cambiarPassword();
            header('Content-Type: application/json');
            echo json_encode($resultado);
            exit();
            
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            exit();
    }
}
?>
