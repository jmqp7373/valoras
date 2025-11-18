<?php
/**
 * API endpoint para subida de archivos de perfil vía AJAX
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $usuarioModel = new Usuario($db);
    
    $id_usuario = $_SESSION['user_id'];
    $upload_dir_base = __DIR__ . '/../uploads/perfiles/';
    
    // Mapear tipo de archivo a carpeta específica y nombre de columna en BD
    $carpetas_tipo = [
        'foto_perfil' => ['carpeta' => 'FotoDePerfil', 'columna' => 'FotoDePerfil'],
        'foto_con_cedula' => ['carpeta' => 'FotoConCedulaEnMano', 'columna' => 'FotoConCedulaEnMano'],
        'foto_cedula_frente' => ['carpeta' => 'CedulaLadoFrontal', 'columna' => 'CedulaLadoFrontal'],
        'foto_cedula_reverso' => ['carpeta' => 'CedulaLadoReverso', 'columna' => 'CedulaLadoReverso'],
        'certificado_medico' => ['carpeta' => 'certificadoMedico', 'columna' => 'certificado_medico']
    ];
    
    // Extensiones permitidas por tipo
    $extensiones_permitidas = [
        'foto_perfil' => ['jpg', 'jpeg', 'png', 'gif'],
        'foto_con_cedula' => ['jpg', 'jpeg', 'png'],
        'foto_cedula_frente' => ['jpg', 'jpeg', 'png', 'pdf'],
        'foto_cedula_reverso' => ['jpg', 'jpeg', 'png', 'pdf'],
        'certificado_medico' => ['jpg', 'jpeg', 'png', 'pdf']
    ];
    
    // Buscar qué archivo se está subiendo
    $archivo_subido = null;
    $tipo_archivo = null;
    $config_tipo = null;
    
    foreach ($carpetas_tipo as $tipo => $config) {
        if (isset($_FILES[$tipo]) && $_FILES[$tipo]['error'] === UPLOAD_ERR_OK) {
            $archivo_subido = $_FILES[$tipo];
            $tipo_archivo = $tipo;
            $config_tipo = $config;
            break;
        }
    }
    
    if (!$archivo_subido) {
        echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo']);
        exit;
    }
    
    // Validar tamaño (máximo 5MB)
    $max_size = 5 * 1024 * 1024;
    if ($archivo_subido['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => 'El archivo excede el tamaño máximo de 5MB']);
        exit;
    }
    
    // Validar extensión
    $extension = strtolower(pathinfo($archivo_subido['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $extensiones_permitidas[$tipo_archivo])) {
        echo json_encode([
            'success' => false,
            'message' => 'Formato no permitido. Permitidos: ' . implode(', ', $extensiones_permitidas[$tipo_archivo])
        ]);
        exit;
    }
    
    // Obtener subcarpeta
    $subcarpeta = $config_tipo['carpeta'] ?? '';
    
    // Construir ruta completa
    $directorio_destino = $upload_dir_base;
    if ($subcarpeta !== '') {
        $directorio_destino .= $subcarpeta . '/';
        if (!file_exists($directorio_destino)) {
            mkdir($directorio_destino, 0755, true);
        }
    }
    
    // Generar nombre único
    $nombre_archivo = $id_usuario . '_' . $tipo_archivo . '_' . time() . '.' . $extension;
    $ruta_completa = $directorio_destino . $nombre_archivo;
    
    // Ruta relativa para BD
    $ruta_relativa = 'uploads/perfiles/';
    if ($subcarpeta !== '') {
        $ruta_relativa .= $subcarpeta . '/';
    }
    $ruta_relativa .= $nombre_archivo;
    
    // Mover archivo
    if (move_uploaded_file($archivo_subido['tmp_name'], $ruta_completa)) {
        // Actualizar BD - usar tabla usuarios_fotos
        $columna_bd = $config_tipo['columna'];
        
        try {
            // Verificar si ya existe registro para este usuario
            $stmt = $db->prepare("SELECT id_foto FROM usuarios_fotos WHERE id_usuario = ?");
            $stmt->execute([$id_usuario]);
            
            if ($stmt->rowCount() > 0) {
                // UPDATE
                $sql = "UPDATE usuarios_fotos SET $columna_bd = ? WHERE id_usuario = ?";
                $stmt = $db->prepare($sql);
                $stmt->execute([$ruta_relativa, $id_usuario]);
            } else {
                // INSERT
                $sql = "INSERT INTO usuarios_fotos (id_usuario, $columna_bd) VALUES (?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->execute([$id_usuario, $ruta_relativa]);
            }
            
            // Calcular progreso actualizado
            $progreso = $usuarioModel->calcularProgresoPerfil($id_usuario);
            
            echo json_encode([
                'success' => true,
                'message' => 'Archivo subido correctamente',
                'ruta' => $ruta_relativa,
                'progreso' => $progreso
            ]);
        } catch (Exception $e) {
            // Eliminar archivo si falla la BD
            unlink($ruta_completa);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar base de datos: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al mover el archivo']);
    }
    
} catch (Exception $e) {
    error_log("Error en PerfilUploadController: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
