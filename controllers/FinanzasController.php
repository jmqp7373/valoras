<?php
require_once __DIR__ . '/../models/Finanzas.php';

class FinanzasController {
    private $finanzasModel;
    
    public function __construct() {
        $this->finanzasModel = new Finanzas();
    }
    
    /**
     * Registra un nuevo movimiento financiero
     * Valida los datos y guarda en la base de datos
     * 
     * @return array Resultado con success y message
     */
    public function registrarMovimiento() {
        // Iniciar sesión si no está activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Validar que sea POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return [
                'success' => false,
                'message' => 'Método no permitido'
            ];
        }
        
        // Validar campos obligatorios
        $fecha = $_POST['fecha'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $categoria = $_POST['categoria'] ?? '';
        $monto = $_POST['monto'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        
        // Validaciones
        $errores = [];
        
        if (empty($fecha)) {
            $errores[] = 'La fecha es obligatoria';
        }
        
        if (empty($tipo) || !in_array($tipo, ['Ingreso', 'Gasto'])) {
            $errores[] = 'El tipo debe ser Ingreso o Gasto';
        }
        
        if (empty($categoria)) {
            $errores[] = 'La categoría es obligatoria';
        }
        
        if (empty($monto) || !is_numeric($monto) || floatval($monto) <= 0) {
            $errores[] = 'El monto debe ser mayor a 0';
        }
        
        if (empty($descripcion)) {
            $errores[] = 'La descripción es obligatoria';
        }
        
        // Si hay errores, retornar
        if (!empty($errores)) {
            $_SESSION['error_finanzas'] = implode('. ', $errores);
            return [
                'success' => false,
                'message' => implode('. ', $errores)
            ];
        }
        
        // Sanitizar datos
        $data = [
            'fecha' => htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8'),
            'tipo' => htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'),
            'categoria' => htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8'),
            'monto' => floatval($monto),
            'descripcion' => htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8')
        ];
        
        // Guardar movimiento
        $resultado = $this->finanzasModel->guardarMovimiento($data);
        
        if ($resultado) {
            $_SESSION['success_finanzas'] = 'Movimiento registrado exitosamente';
            return [
                'success' => true,
                'message' => 'Movimiento registrado exitosamente'
            ];
        } else {
            $_SESSION['error_finanzas'] = 'Error al registrar el movimiento';
            return [
                'success' => false,
                'message' => 'Error al registrar el movimiento'
            ];
        }
    }
    
    /**
     * Lista todos los movimientos financieros
     * 
     * @return array Lista de movimientos
     */
    public function listarMovimientos() {
        return $this->finanzasModel->obtenerMovimientos();
    }
    
    /**
     * Calcula los totales financieros
     * 
     * @return array Array con total_ingresos, total_gastos, balance, ultima_actualizacion
     */
    public function calcularTotales() {
        return $this->finanzasModel->obtenerTotales();
    }
}
