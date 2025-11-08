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
            'alergias', 'banco_nombre', 'banco_tipo_cuenta', 'banco_numero_cuenta',
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

        // Generar nombre único
        $nombre_archivo = $id_usuario . '_' . $tipo . '_' . time() . '.' . $extension;
        $ruta_completa = $this->upload_dir_base . $nombre_archivo;
        $ruta_relativa = 'uploads/perfiles/' . $nombre_archivo;

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
            
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            exit();
    }
}
?>
