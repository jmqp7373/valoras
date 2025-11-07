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
        $query = "SELECT * FROM " . $this->table_name . " WHERE cedula = :cedula LIMIT 1";
        
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
        switch($tipo) {
            case 'username':
                $query = "SELECT * FROM " . $this->table_name . " WHERE usuario = :identificador LIMIT 1";
                break;
            case 'celular':
                $query = "SELECT * FROM " . $this->table_name . " WHERE celular = :identificador LIMIT 1";
                break;
            case 'cedula':
            default:
                $query = "SELECT * FROM " . $this->table_name . " WHERE cedula = :identificador LIMIT 1";
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
        $query = "SELECT id_usuario FROM " . $this->table_name . " WHERE cedula = :cedula LIMIT 1";
        
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
        
        $query = "SELECT id_usuario FROM " . $this->table_name . " WHERE email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Método para verificar si un usuario existe por celular
    public function existsByCelular($celular) {
        $query = "SELECT id_usuario FROM " . $this->table_name . " WHERE celular = :celular LIMIT 1";
        
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
                  id_estudio, id_referente, estado, inmune_asistencia, nivel) 
                 VALUES 
                 (:cedula, :nombres, :apellidos, :usuario, :password, :codigo_pais, :celular, :email,
                  :id_estudio, :id_referente, :estado, :inmune_asistencia, :nivel)";

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
        $nivel = 1; // Nivel básico por defecto

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
        $stmt->bindParam(':nivel', $nivel);

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
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_usuario = :id LIMIT 1";
        
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
     * @param string $cedula N�mero de c�dula del usuario
     * @param string $telefono Nuevo n�mero de tel�fono
     * @param string $email Nuevo correo electr�nico
     * @return bool
     */
    public function updateContactData($cedula, $telefono, $email) {
        try {
            $query = "UPDATE " . $this->table_name . " SET celular = :telefono, email = :email WHERE cedula = :cedula";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':cedula', $cedula);
            
            // Si execute() fue exitoso, considerarlo como éxito aunque no cambie filas
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
}
?>
