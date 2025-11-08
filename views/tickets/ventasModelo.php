<?php
/**
 * Vista: Ventas por Modelo
 * 
 * Muestra las ganancias registradas de cada modelo consultando la tabla ventas
 */

// Habilitar errores para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/VentasController.php';
startSessionSafely();

// Verificar autenticación
if(!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';

// Inicializar controlador
try {
    $db = getDBConnection();
    $ventasController = new VentasController($db);
} catch (Exception $e) {
    die('Error al conectar con la base de datos: ' . $e->getMessage());
}

// Si hay un modelo seleccionado, se filtra por su ID
$usuario_id = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : null;
$ventas = [];
$usuarioInfo = null;
$totalVentas = 0;

if ($usuario_id) {
    $ventas = $ventasController->getVentasByUsuario($usuario_id);
    $usuarioInfo = $ventasController->getUsuarioInfo($usuario_id);
    $totalVentas = $ventasController->getTotalVentasUsuario($usuario_id);
}

// Obtener lista de todos los usuarios para el select
$usuarios = $ventasController->getAllUsuarios();

// Variables para header
$home_path = '../../index.php';
$settings_path = '../usuario/configuracion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas por Modelo - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        .ventas-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .ventas-header {
            background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .ventas-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .filter-section label {
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }
        
        .filter-section select {
            width: 100%;
            max-width: 400px;
            padding: 12px 16px;
            border: 1px solid #ee6f92;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            background-color: #fafafa;
            margin-bottom: 15px;
        }
        
        .filter-section select:focus {
            outline: none;
            border-color: #d63384;
            box-shadow: 0 0 0 3px rgba(238, 111, 146, 0.1);
        }
        
        .btn-consultar {
            background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-consultar:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(136, 42, 87, 0.3);
        }
        
        .modelo-info {
            background: linear-gradient(135deg, #e7f3ff 0%, #f0e7ff 100%);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 4px solid #882A57;
        }
        
        .modelo-info h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 20px;
        }
        
        .modelo-info p {
            margin: 5px 0;
            color: #666;
        }
        
        .total-badge {
            background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 18px;
            display: inline-block;
            margin-top: 10px;
        }
        
        .ventas-table-container {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        
        .ventas-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .ventas-table thead {
            background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
            color: white;
        }
        
        .ventas-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .ventas-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        
        .ventas-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .amount {
            font-weight: 600;
            color: #28a745;
            font-size: 16px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #17a2b8;
            color: #0c5460;
        }
        
        .no-data-message {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 24px;
            background: white;
            color: #882A57;
            border: 2px solid #882A57;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            background: #882A57;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include '../../components/header/header.php'; ?>
    
    <div class="ventas-container">
        <div class="ventas-header">
            <h1>📊 Ventas por Modelo</h1>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Consulta los ingresos registrados de cada modelo por plataforma</p>
        </div>

        <!-- Filtro por modelo -->
        <div class="filter-section">
            <form method="GET">
                <label for="usuario_id">Selecciona un modelo:</label>
                <select name="usuario_id" id="usuario_id" required>
                    <option value="">-- Selecciona un modelo --</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <option value="<?= $usuario['id_usuario'] ?>" 
                                <?= ($usuario_id == $usuario['id_usuario']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']) ?> 
                            (Cédula: <?= htmlspecialchars($usuario['cedula']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <br>
                <button type="submit" class="btn-consultar">🔍 Consultar Ventas</button>
            </form>
        </div>

        <?php if ($usuario_id && $usuarioInfo): ?>
            <!-- Información del modelo seleccionado -->
            <div class="modelo-info">
                <h3>👤 Modelo: <?= htmlspecialchars($usuarioInfo['nombres'] . ' ' . $usuarioInfo['apellidos']) ?></h3>
                <p><strong>Cédula:</strong> <?= htmlspecialchars($usuarioInfo['cedula']) ?></p>
                <?php if (!empty($usuarioInfo['email'])): ?>
                    <p><strong>Email:</strong> <?= htmlspecialchars($usuarioInfo['email']) ?></p>
                <?php endif; ?>
                <div class="total-badge">
                    💰 Total Acumulado: $<?= number_format($totalVentas, 2) ?> USD
                </div>
            </div>

            <?php if (!empty($ventas)): ?>
                <!-- Tabla de ventas -->
                <div class="ventas-table-container">
                    <h3 style="margin-top: 0; color: #333;">Registro de Ventas</h3>
                    <table class="ventas-table">
                        <thead>
                            <tr>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Plataforma</th>
                                <th>Email/Usuario</th>
                                <th>Total Ganado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $venta): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($venta['period_start'])) ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($venta['period_end'])) ?></td>
                                    <td><?= htmlspecialchars($venta['credencial_usuario']) ?></td>
                                    <td><?= htmlspecialchars($venta['credencial_email']) ?></td>
                                    <td class="amount">$<?= number_format($venta['total_earnings'], 2) ?> USD</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <strong>Total de registros:</strong> <?= count($ventas) ?> ventas
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    ⚠️ No se encontraron registros de ventas para este modelo.
                </div>
            <?php endif; ?>
            
        <?php elseif ($usuario_id): ?>
            <div class="alert alert-warning">
                ⚠️ No se encontró información del modelo seleccionado.
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                ℹ️ Selecciona un modelo en el filtro superior para ver sus ventas registradas.
            </div>
        <?php endif; ?>

        <a href="../../index.php" class="back-button">← Volver al Dashboard</a>
    </div>

    <?php include '../../components/footer.php'; ?>
</body>
</html>
