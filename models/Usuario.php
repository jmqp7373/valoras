<?php
require_once __DIR__ . '/../config/database.php';

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    // Propiedades del usuario basadas en la tabla usuarios
    public $id_usuario;
    public $numero_de_cedula;
    public $usuario;
    public $disponibilidad;
    public $id_referente;
    public $nombres;
    public $apellidos;
    public $password;
    public $codigo_pais;
    public $celular;
    public $cedula;
    public $fecha_de_nacimiento;
    public $email;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para autenticar usuario
    public function login($numero_cedula, $password) {
        $query = "SELECT u.*, ui.* 
                  FROM " . $this->table_name . " u
                  LEFT JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario
                  WHERE ui.cedula = :cedula LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cedula', $numero_cedula);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar la contraseña
            if(password_verify($password, $row['password'])) {
                return $row;
            }
        }
        
        return false;
    }
    
    // Método para autenticar usuario por cualquier identificador (cédula, usuario o celular)
    public function loginByIdentifier($identificador, $password, $tipo = 'cedula') {
        // Construir query según el tipo de identificador
        // Seleccionar explícitamente los campos necesarios para evitar duplicados
        switch($tipo) {
            case 'username':
                $query = "SELECT 
                    u.id_usuario,
                    u.usuario,
                    u.nombres,
                    u.apellidos,
                    u.password,
                    u.estado,
                    u.id_rol,
                    ui.cedula,
                    ui.celular,
                    ui.email,
                    ui.codigo_pais
                FROM " . $this->table_name . " u 
                LEFT JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario 
                WHERE u.usuario = :identificador LIMIT 1";
                break;
            case 'celular':
                $query = "SELECT 
                    u.id_usuario,
                    u.usuario,
                    u.nombres,
                    u.apellidos,
                    u.password,
                    u.estado,
                    u.id_rol,
                    ui.cedula,
                    ui.celular,
                    ui.email,
                    ui.codigo_pais
                FROM " . $this->table_name . " u 
                LEFT JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario 
                WHERE ui.celular = :identificador LIMIT 1";
                break;
            case 'cedula':
            default:
                $query = "SELECT 
                    u.id_usuario,
                    u.usuario,
                    u.nombres,
                    u.apellidos,
                    u.password,
                    u.estado,
                    u.id_rol,
                    ui.cedula,
                    ui.celular,
                    ui.email,
                    ui.codigo_pais
                FROM " . $this->table_name . " u 
                LEFT JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario 
                WHERE ui.cedula = :identificador LIMIT 1";
                break;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':identificador', $identificador);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verificar la contraseña
            if($row['password'] !== null && password_verify($password, $row['password'])) {
                return $row;
            }
        }
        
        return false;
    }

    // Método para verificar si un usuario existe por cédula
    public function existsByCedula($cedula) {
        $query = "SELECT u.id_usuario FROM " . $this->table_name . " u 
                  INNER JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario 
                  WHERE ui.cedula = :cedula LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Método para verificar si un usuario existe por email
    public function existsByEmail($email) {
        // Si el email está vacío, no verificar duplicados
        if (empty($email)) {
            return false;
        }
        
        $query = "SELECT u.id_usuario FROM " . $this->table_name . " u 
                  INNER JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario 
                  WHERE ui.email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Método para verificar si un usuario existe por celular
    public function existsByCelular($celular) {
        $query = "SELECT u.id_usuario FROM " . $this->table_name . " u 
                  INNER JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario 
                  WHERE ui.celular = :celular LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':celular', $celular);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Método para crear un nuevo usuario
    public function create() {
        // Verificar duplicados antes de insertar
        $duplicateCheck = $this->checkForDuplicates();
        if (!$duplicateCheck['success']) {
            return $duplicateCheck;
        }

        $query = "INSERT INTO " . $this->table_name . " 
                 (cedula, nombres, apellidos, usuario, password, codigo_pais, celular, email, 
                  id_estudio, id_referente, estado, inmune_asistencia, nivel_orden) 
                 VALUES 
                 (:cedula, :nombres, :apellidos, :usuario, :password, :codigo_pais, :celular, :email,
                  :id_estudio, :id_referente, :estado, :inmune_asistencia, :nivel_orden)";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->cedula = htmlspecialchars(strip_tags($this->cedula));
        $this->nombres = htmlspecialchars(strip_tags($this->nombres));
        $this->apellidos = htmlspecialchars(strip_tags($this->apellidos));
        $this->usuario = htmlspecialchars(strip_tags($this->usuario));
        
        // Solo hashear password si no es NULL (permitir usuarios sin password inicial)
        $password_to_save = $this->password !== null ? password_hash($this->password, PASSWORD_DEFAULT) : null;
        
        $this->codigo_pais = htmlspecialchars(strip_tags($this->codigo_pais));
        $this->celular = htmlspecialchars(strip_tags($this->celular));
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Valores por defecto para campos obligatorios
        $id_estudio = 1; // Valor por defecto para estudios
        $id_referente = 0; // Sin referente por defecto  
        $estado = 1; // Usuario activo
        $inmune_asistencia = 0; // Sin inmunidad por defecto
        $nivel_orden = 1; // Nivel básico por defecto

        // Bind valores
        $stmt->bindParam(':cedula', $this->cedula);
        $stmt->bindParam(':nombres', $this->nombres);
        $stmt->bindParam(':apellidos', $this->apellidos);
        $stmt->bindParam(':usuario', $this->usuario);
        $stmt->bindParam(':password', $password_to_save);
        $stmt->bindParam(':codigo_pais', $this->codigo_pais);
        $stmt->bindParam(':celular', $this->celular);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id_estudio', $id_estudio);
        $stmt->bindParam(':id_referente', $id_referente);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':inmune_asistencia', $inmune_asistencia);
        $stmt->bindParam(':nivel_orden', $nivel_orden);

        try {
            if($stmt->execute()) {
                return ['success' => true, 'message' => 'Usuario creado exitosamente'];
            }
            return ['success' => false, 'message' => 'Error al crear el usuario'];
        } catch(PDOException $e) {
            // Manejar errores específicos de base de datos
            if($e->getCode() == 23000) { // Violation de integridad/duplicado
                if(strpos($e->getMessage(), 'cedula') !== false) {
                    return ['success' => false, 'message' => 'Ya existe una cuenta con esta cédula'];
                } elseif(strpos($e->getMessage(), 'celular') !== false) {
                    return ['success' => false, 'message' => 'Ya existe una cuenta con este número de celular'];
                } elseif(strpos($e->getMessage(), 'email') !== false) {
                    return ['success' => false, 'message' => 'Ya existe una cuenta con este email'];
                } else {
                    return ['success' => false, 'message' => 'Ya existe una cuenta con estos datos'];
                }
            }
            return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    // Método para verificar duplicados antes de insertar
    public function checkForDuplicates() {
        // Verificar cédula duplicada
        if ($this->existsByCedula($this->cedula)) {
            return ['success' => false, 'message' => 'Ya existe una cuenta registrada con esta cédula'];
        }

        // Verificar celular duplicado
        if ($this->existsByCelular($this->celular)) {
            return ['success' => false, 'message' => 'Ya existe una cuenta registrada con este número de celular'];
        }

        // Verificar email duplicado
        if ($this->existsByEmail($this->email)) {
            return ['success' => false, 'message' => 'Ya existe una cuenta registrada con este email'];
        }

        return ['success' => true];
    }

    // Método para obtener información del usuario por ID
    public function getById($id) {
        $query = "SELECT u.*, ui.* 
                  FROM " . $this->table_name . " u
                  LEFT JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario
                  WHERE u.id_usuario = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return false;
    }
    
    /**
     * Actualizar datos de contacto del usuario
     * 
     * @param string $cedula Número de cédula del usuario
     * @param string $telefono Nuevo número de teléfono
     * @param string $email Nuevo correo electrónico
     * @return bool
     */
    public function updateContactData($cedula, $telefono, $email) {
        try {
            // Primero obtener id_usuario
            $query_id = "SELECT u.id_usuario FROM usuarios u 
                         INNER JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario 
                         WHERE ui.cedula = :cedula LIMIT 1";
            $stmt_id = $this->conn->prepare($query_id);
            $stmt_id->bindParam(':cedula', $cedula);
            $stmt_id->execute();
            
            if ($stmt_id->rowCount() === 0) {
                error_log("Usuario no encontrado con cédula: {$cedula}");
                return false;
            }
            
            $row = $stmt_id->fetch(PDO::FETCH_ASSOC);
            $id_usuario = $row['id_usuario'];
            
            // Actualizar en usuarios_info
            $query = "UPDATE usuarios_info SET celular = :telefono, email = :email WHERE id_usuario = :id_usuario";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id_usuario', $id_usuario);
            
            if ($stmt->execute()) {
                error_log("Usuario actualizado/verificado: Cédula {$cedula}, Rows affected: " . $stmt->rowCount());
                return true;
            }
            error_log("Error al ejecutar UPDATE para cédula: {$cedula}");
            return false;
        } catch (PDOException $e) {
            error_log("Error al actualizar datos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar perfil completo del usuario
     * 
     * @param int $id_usuario ID del usuario
     * @param array $datos Datos del perfil a actualizar
     * @return array Respuesta con success y message
     */
    public function actualizarPerfil($id_usuario, $datos) {
        try {
            // Separar campos entre usuarios y usuarios_info
            $campos_usuarios = ['nombres', 'apellidos'];
            $campos_usuarios_info = [
                'celular', 'email', 'fecha_de_nacimiento', 'cedula',
                'tipo_sangre', 'direccion', 'ciudad', 'codigo_pais',
                'contacto_emergencia_nombre', 'contacto_emergencia_parentesco', 'contacto_emergencia_telefono',
                'alergias', 'certificado_medico',
                'id_banco', 'banco_tipo_cuenta', 'banco_numero_cuenta',
                'dias_descanso', 'id_estudio', 'id_referente', 'notas',
                'foto_perfil', 'foto_con_cedula', 'foto_cedula_frente', 'foto_cedula_reverso',
                'disponibilidad', 'nivel_orden', 'inmune_asistencia', 'url_entrevista',
                'ref1_nombre', 'ref1_parentesco', 'ref1_celular', 'info_medica'
            ];

            // UPDATE para tabla usuarios
            $set_usuarios = [];
            $valores_usuarios = [];
            foreach ($campos_usuarios as $campo) {
                if (array_key_exists($campo, $datos)) {
                    $set_usuarios[] = "$campo = :$campo";
                    $valores_usuarios[$campo] = $datos[$campo];
                }
            }

            if (!empty($set_usuarios)) {
                $query_usuarios = "UPDATE " . $this->table_name . " SET " . 
                                 implode(', ', $set_usuarios) . 
                                 " WHERE id_usuario = :id_usuario";
                $stmt_usuarios = $this->conn->prepare($query_usuarios);
                foreach ($valores_usuarios as $key => $value) {
                    $stmt_usuarios->bindValue(":$key", $value);
                }
                $stmt_usuarios->bindValue(':id_usuario', $id_usuario);
                $stmt_usuarios->execute();
            }

            // UPDATE para tabla usuarios_info
            $set_info = [];
            $valores_info = [];
            foreach ($campos_usuarios_info as $campo) {
                if (array_key_exists($campo, $datos)) {
                    $set_info[] = "$campo = :$campo";
                    $valores_info[$campo] = $datos[$campo];
                }
            }

            // Calcular y agregar progreso
            $progreso = $this->calcularProgresoPerfil($id_usuario, $datos);
            $set_info[] = "progreso_perfil = :progreso_perfil";
            $valores_info['progreso_perfil'] = $progreso;

            if (!empty($set_info)) {
                $query_info = "UPDATE usuarios_info SET " . 
                             implode(', ', $set_info) . 
                             " WHERE id_usuario = :id_usuario";
                $stmt_info = $this->conn->prepare($query_info);
                foreach ($valores_info as $key => $value) {
                    $stmt_info->bindValue(":$key", $value);
                }
                $stmt_info->bindValue(':id_usuario', $id_usuario);
                
                if ($stmt_info->execute()) {
                    return [
                        'success' => true, 
                        'message' => 'Perfil actualizado exitosamente',
                        'progreso' => $progreso
                    ];
                }
            }

            return ['success' => false, 'message' => 'No hay datos para actualizar'];

        } catch (PDOException $e) {
            error_log("Error al actualizar perfil: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Obtener datos completos del perfil del usuario
     * 
     * @param int $id_usuario ID del usuario
     * @return array|false Datos del perfil o false si no existe
     */
    public function obtenerPerfil($id_usuario) {
        try {
            $query = "SELECT u.*, ui.*, 
                             ub.nombre_banco, ub.codigo_abreviado as banco_codigo, 
                             ub.color_banco, ub.tipo_banco,
                             uf.FotoDePerfil as foto_perfil,
                             uf.FotoConCedulaEnMano as foto_con_cedula,
                             uf.CedulaLadoFrontal as foto_cedula_frente,
                             uf.CedulaLadoReverso as foto_cedula_reverso,
                             uf.certificado_medico
                      FROM " . $this->table_name . " u
                      LEFT JOIN usuarios_info ui ON u.id_usuario = ui.id_usuario
                      LEFT JOIN usuarios_bancos ub ON ui.id_banco = ub.id_banco
                      LEFT JOIN usuarios_fotos uf ON u.id_usuario = uf.id_usuario
                      WHERE u.id_usuario = :id_usuario LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $perfil = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Decodificar JSON de días de descanso
                if (isset($perfil['dias_descanso']) && !empty($perfil['dias_descanso'])) {
                    $perfil['dias_descanso'] = json_decode($perfil['dias_descanso'], true);
                } else {
                    $perfil['dias_descanso'] = [];
                }

                return $perfil;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Error al obtener perfil: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Calcular progreso del perfil (0-100%)
     * 
     * @param int $id_usuario ID del usuario
     * @param array $datos_nuevos Datos adicionales a considerar (opcional)
     * @return int Porcentaje de completitud
     */
    public function calcularProgresoPerfil($id_usuario, $datos_nuevos = []) {
        // Obtener datos actuales si no se pasan datos nuevos
        if (empty($datos_nuevos)) {
            $perfil = $this->obtenerPerfil($id_usuario);
        } else {
            // Mezclar datos actuales con nuevos
            $perfil_actual = $this->obtenerPerfil($id_usuario);
            $perfil = array_merge($perfil_actual ?: [], $datos_nuevos);
        }

        if (!$perfil) {
            return 0;
        }

        // Campos obligatorios (peso: 60%)
        $campos_obligatorios = [
            'nombres', 'apellidos', 'cedula', 'celular', 'email',
            'foto_perfil', 'foto_con_cedula', 'foto_cedula_frente', 'foto_cedula_reverso',
            'contacto_emergencia_nombre', 'contacto_emergencia_telefono',
            'id_banco', 'banco_numero_cuenta'
        ];

        // Campos opcionales (peso: 40%)
        $campos_opcionales = [
            'fecha_de_nacimiento', 'ciudad', 'direccion', 'tipo_sangre',
            'contacto_emergencia_parentesco', 'alergias', 'banco_tipo_cuenta',
            'dias_descanso', 'id_estudio', 'notas'
        ];

        $completados_obligatorios = 0;
        foreach ($campos_obligatorios as $campo) {
            if (!empty($perfil[$campo])) {
                $completados_obligatorios++;
            }
        }

        $completados_opcionales = 0;
        foreach ($campos_opcionales as $campo) {
            if (!empty($perfil[$campo])) {
                $completados_opcionales++;
            }
        }

        // Calcular porcentaje ponderado
        $total_obligatorios = count($campos_obligatorios);
        $total_opcionales = count($campos_opcionales);

        $peso_obligatorios = ($completados_obligatorios / $total_obligatorios) * 60;
        $peso_opcionales = ($completados_opcionales / $total_opcionales) * 40;

        return round($peso_obligatorios + $peso_opcionales);
    }

    /**
     * Actualizar ruta de foto en el perfil
     * 
     * @param int $id_usuario ID del usuario
     * @param string $tipo_foto Tipo de foto (foto_perfil, foto_con_cedula, etc)
     * @param string $ruta Ruta del archivo
     * @return bool
     */
    public function actualizarFoto($id_usuario, $tipo_foto, $ruta) {
        try {
            $tipos_permitidos = ['foto_perfil', 'foto_con_cedula', 'foto_cedula_frente', 'foto_cedula_reverso', 'certificado_medico'];
            
            if (!in_array($tipo_foto, $tipos_permitidos)) {
                return false;
            }

            $query = "UPDATE " . $this->table_name . " SET $tipo_foto = :ruta WHERE id_usuario = :id_usuario";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':ruta', $ruta);
            $stmt->bindParam(':id_usuario', $id_usuario);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar foto: " . $e->getMessage());
            return false;
        }
    }
}
?>
