<?php
require_once __DIR__ . '/../models/Finanzas.php';

// Manejar petición AJAX para totales JSON
if (isset($_GET['action']) && $_GET['action'] === 'totales_json') {
    header('Content-Type: application/json');
    $controller = new FinanzasController();
    $totales = $controller->calcularTotales();
    
    echo json_encode([
        'ingresos' => $totales['total_ingresos'],
        'gastos' => $totales['total_gastos'],
        'balance' => $totales['balance'],
        'ultima_actualizacion' => $totales['ultima_actualizacion']
    ]);
    exit();
}

// Manejar petición AJAX para listar movimientos filtrados
if (isset($_GET['action']) && $_GET['action'] === 'listar') {
    $controller = new FinanzasController();
    
    // Obtener filtros desde GET
    $filtros = [
        'fechaInicio' => $_GET['fechaInicio'] ?? '',
        'fechaFin' => $_GET['fechaFin'] ?? '',
        'categoria' => $_GET['categoria'] ?? ''
    ];
    
    $movimientos = $controller->listarMovimientos($filtros);
    
    // Generar HTML de la tabla
    if (empty($movimientos)) {
        echo '<div class="alert alert-info" style="background: #d1ecf1; color: #0c5460; padding: 1.5rem; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bee5eb; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                <h3 style="margin-bottom: 0.5rem; font-family: \'Poppins\', sans-serif;">No se encontraron movimientos</h3>
                <p style="margin: 0; font-size: 1rem;">Intenta ajustar los filtros o limpiarlos para ver todos los registros.</p>
              </div>';
    } else {
        echo '<div class="table-responsive">
                <table class="tabla-movimientos">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Categoría</th>
                            <th>Monto</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($movimientos as $mov) {
            $fecha = date('d/m/Y', strtotime($mov['fecha']));
            $tipo = htmlspecialchars($mov['tipo']);
            $categoria = htmlspecialchars($mov['categoria']);
            $monto = number_format($mov['monto'], 2, ',', '.');
            $descripcion = htmlspecialchars($mov['descripcion']);
            $badgeClass = strtolower($tipo);
            $montoClass = strtolower($tipo);
            $icono = $tipo === 'Ingreso' ? '💰' : '💸';
            
            echo "<tr>
                    <td>{$fecha}</td>
                    <td>
                        <span class='badge-tipo {$badgeClass}'>
                            {$icono} {$tipo}
                        </span>
                    </td>
                    <td>{$categoria}</td>
                    <td class='monto-{$montoClass}'>
                        \${$monto}
                    </td>
                    <td>{$descripcion}</td>
                  </tr>";
        }
        
        echo '    </tbody>
                </table>
              </div>';
    }
    exit();
}

class FinanzasController {
    private $finanzasModel;
    
    public function __construct() {
        $this->finanzasModel = new Finanzas();
        $this->insertarDatosEjemplo();
    }
    
    /**
     * Inserta movimientos de ejemplo si la tabla está vacía
     */
    private function insertarDatosEjemplo() {
        try {
            $movimientos = $this->finanzasModel->obtenerMovimientos();
            
            // Si no hay movimientos, insertar datos de ejemplo
            if (empty($movimientos)) {
                $datosEjemplo = [
                    // GASTOS (Total: $11,730,000)
                    ['fecha' => '2025-11-01', 'tipo' => 'Gasto', 'categoria' => 'Arriendo', 'monto' => 4500000, 'descripcion' => 'Pago Arriendo Estudio Diamante'],
                    ['fecha' => '2025-11-02', 'tipo' => 'Gasto', 'categoria' => 'Servicios', 'monto' => 980000, 'descripcion' => 'Servicio EPM y Agua'],
                    ['fecha' => '2025-11-03', 'tipo' => 'Gasto', 'categoria' => 'Nómina', 'monto' => 5200000, 'descripcion' => 'Pago Modelos Semana 44'],
                    ['fecha' => '2025-11-04', 'tipo' => 'Gasto', 'categoria' => 'Personal', 'monto' => 800000, 'descripcion' => 'Administración Apto Castelmola'],
                    ['fecha' => '2025-11-06', 'tipo' => 'Gasto', 'categoria' => 'Servicios', 'monto' => 250000, 'descripcion' => 'Pago Internet Claro y Telefonía'],
                    
                    // INGRESOS (Total: $18,500,000) - Balance Positivo: +$6,770,000
                    ['fecha' => '2025-11-05', 'tipo' => 'Ingreso', 'categoria' => 'Otro', 'monto' => 8000000, 'descripcion' => 'Ingresos Estudio Fotográfico Noviembre'],
                    ['fecha' => '2025-11-07', 'tipo' => 'Ingreso', 'categoria' => 'Otro', 'monto' => 6500000, 'descripcion' => 'Pago Colaboraciones Empresariales'],
                    ['fecha' => '2025-11-08', 'tipo' => 'Ingreso', 'categoria' => 'Otro', 'monto' => 3000000, 'descripcion' => 'Transferencia Servicios Creativos'],
                    ['fecha' => '2025-11-08', 'tipo' => 'Ingreso', 'categoria' => 'Otro', 'monto' => 1000000, 'descripcion' => 'Bonificación Proyecto Especial']
                ];
                
                foreach ($datosEjemplo as $dato) {
                    $this->finanzasModel->guardarMovimiento($dato);
                }
            }
        } catch (Exception $e) {
            error_log("Error al insertar datos de ejemplo: " . $e->getMessage());
        }
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
     * Soporta filtros opcionales por fecha y categoría
     * 
     * @param array $filtros Array con fechaInicio, fechaFin, categoria
     * @return array Lista de movimientos
     */
    public function listarMovimientos($filtros = []) {
        return $this->finanzasModel->obtenerMovimientos($filtros);
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
