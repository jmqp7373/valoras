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
$logo_path = '../../assets/images/logos/logoValoraHorizontal.png';
$home_path = '../../index.php';
$profile_path = '../usuario/miPerfil.php';
$settings_path = '../usuario/configuracion.php';
$logout_path = '../../controllers/login/logout.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventas por Modelo - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        body {
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        
        .ventas-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .ventas-header {
            background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .ventas-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .ventas-header p {
            margin: 8px 0 0 0;
            opacity: 0.95;
            font-size: 14px;
        }
        
        .filter-section {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .filter-section label {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: block;
            font-size: 15px;
        }
        
        .filter-section select {
            width: 100%;
            max-width: 500px;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            background-color: #fafafa;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .filter-section select:focus {
            outline: none;
            border-color: #882A57;
            box-shadow: 0 0 0 3px rgba(136, 42, 87, 0.1);
            background-color: white;
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
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 5px solid #882A57;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .modelo-info h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .modelo-info p {
            margin: 8px 0;
            color: #666;
            font-size: 14px;
        }
        
        .total-badge {
            background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 18px;
            display: inline-block;
            margin-top: 15px;
            box-shadow: 0 3px 10px rgba(136, 42, 87, 0.3);
        }
        
        .ventas-table-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        
        .ventas-table-container h3 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 18px;
            font-weight: 600;
        }
        
        .ventas-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .ventas-table thead {
            background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
            color: white;
        }
        
        .ventas-table th {
            padding: 16px 18px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .ventas-table th:first-child {
            border-top-left-radius: 8px;
        }
        
        .ventas-table th:last-child {
            border-top-right-radius: 8px;
        }
        
        .ventas-table td {
            padding: 16px 18px;
            border-bottom: 1px solid #f0f0f0;
            color: #555;
            font-size: 14px;
        }
        
        .ventas-table tbody tr {
            transition: background-color 0.2s ease;
        }
        
        .ventas-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .ventas-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .amount {
            font-weight: 700;
            color: #28a745;
            font-size: 16px;
        }
        
        .alert {
            padding: 18px 24px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        
        .alert-warning {
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
        
        .alert-info {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            color: #0c5460;
        }
        
        .no-data-message {
            text-align: center;
            padding: 50px 20px;
            color: #999;
            font-size: 16px;
        }
        
        .back-button {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 28px;
            background: white;
            color: #882A57;
            border: 2px solid #882A57;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 15px;
        }
        
        .back-button:hover {
            background: linear-gradient(135deg, #6A1B1B 0%, #882A57 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(136, 42, 87, 0.3);
        }
        
        .btn-importar {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-left: 15px;
        }
        
        .btn-importar:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        
        .btn-importar:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        #resultadoImportacion {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include '../../components/header/header.php'; ?>
    
    <div class="ventas-container">
        <div class="ventas-header">
            <h1>📊 Ventas por Modelo</h1>
            <p>Consulta los ingresos registrados de cada modelo por plataforma</p>
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
                
                <?php if ($usuario_id): ?>
                    <button type="button" id="importarBtn" class="btn-importar">
                        📥 Importar desde Stripchat
                    </button>
                <?php endif; ?>
            </form>
            
            <!-- Resultado de la importación -->
            <div id="resultadoImportacion"></div>
        </div>

        <?php if ($usuario_id && $usuarioInfo): ?>
            <!-- Información del modelo seleccionado -->
            <div class="modelo-info">
                <h3>👤 <?= htmlspecialchars($usuarioInfo['nombres'] . ' ' . $usuarioInfo['apellidos']) ?></h3>
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
    
    <!-- Script para importar datos desde Stripchat -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const importarBtn = document.getElementById('importarBtn');
        const resultadoDiv = document.getElementById('resultadoImportacion');

        if (importarBtn) {
            importarBtn.addEventListener('click', async () => {
                // Deshabilitar botón y mostrar estado de carga
                importarBtn.disabled = true;
                importarBtn.textContent = "⏳ Importando datos...";
                resultadoDiv.innerHTML = '<div style="padding: 15px; background: #e7f3ff; border: 1px solid #b8daff; border-radius: 8px; color: #004085; margin-top: 15px;">⏳ Conectando con la API de Stripchat...</div>';

                try {
                    const usuario_id = <?= $usuario_id ?? 0 ?>;
                    const response = await fetch(`../../controllers/VentasController.php?action=importarDesdeAPI&usuario_id=${usuario_id}`);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    // Obtener el texto de respuesta primero para debug
                    const responseText = await response.text();
                    console.log('Respuesta del servidor:', responseText);
                    
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (parseError) {
                        throw new Error(`Unexpected token '<', "${responseText.substring(0, 50)}..." is not valid JSON`);
                    }

                    if (data.success) {
                        resultadoDiv.innerHTML = `
                            <div class="alert" style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 8px; margin-top: 15px;">
                                <strong>✅ Importación completada exitosamente</strong><br>
                                Se agregaron/actualizaron <strong>${data.registros}</strong> registro(s) de ventas.<br>
                                <small>La página se recargará en 2 segundos...</small>
                            </div>
                        `;
                        
                        // Recargar la página después de 2 segundos
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        resultadoDiv.innerHTML = `
                            <div class="alert alert-warning" style="background-color: #fff3cd; border: 1px solid #ffc107; color: #856404; padding: 15px; border-radius: 8px; margin-top: 15px;">
                                <strong>⚠️ No se pudieron importar los datos</strong><br>
                                ${data.message || 'Error desconocido'}
                            </div>
                        `;
                        
                        // Restaurar botón
                        importarBtn.disabled = false;
                        importarBtn.textContent = "📥 Importar desde Stripchat";
                    }
                } catch (error) {
                    console.error('Error en la importación:', error);
                    resultadoDiv.innerHTML = `
                        <div class="alert alert-danger" style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 8px; margin-top: 15px;">
                            <strong>❌ Error en la solicitud</strong><br>
                            ${error.message || 'No se pudo conectar con el servidor'}
                        </div>
                    `;
                    
                    // Restaurar botón
                    importarBtn.disabled = false;
                    importarBtn.textContent = "📥 Importar desde Stripchat";
                }
            });
        }
    });
    </script>
</body>
</html>
