<?php
/**
 * Valora.vip - Dashboard de Finanzas
 * Módulo de gestión financiera: ingresos, gastos y análisis
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/FinanzasController.php';

startSessionSafely();

// Verificar si el usuario está logueado
if(!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Procesar registro de movimiento
$finanzasController = new FinanzasController();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_movimiento'])) {
    $finanzasController->registrarMovimiento();
    header('Location: finanzasDashboard.php');
    exit();
}

// Obtener datos
$movimientos = $finanzasController->listarMovimientos();
$totales = $finanzasController->calcularTotales();

// Obtener información del usuario
$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Finanzas - Valora</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <header class="dashboard-header">
            <img src="../../assets/images/logos/logoValoraHorizontal.png" class="logo" alt="Valora Logo">
            <div class="user-info">
                <span>Bienvenido, <?php echo htmlspecialchars($user_nombres . ' ' . $user_apellidos); ?></span>
                <a href="../../controllers/login/logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </header>
        
        <main class="dashboard-main">
            <!-- Título y botón de regreso -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="font-family: 'Poppins', sans-serif; color: #222222; margin: 0;">💰 Gestión de Finanzas</h1>
                <a href="../../index.php" style="background: #6A1B1B; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-family: 'Poppins', sans-serif;">
                    ← Volver al Dashboard
                </a>
            </div>

            <!-- Mensajes de éxito/error -->
            <?php if (isset($_SESSION['success_finanzas'])): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    <?php 
                    echo htmlspecialchars($_SESSION['success_finanzas']); 
                    unset($_SESSION['success_finanzas']);
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_finanzas'])): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                    <?php 
                    echo htmlspecialchars($_SESSION['error_finanzas']); 
                    unset($_SESSION['error_finanzas']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de registro de movimiento -->
            <div class="card-finanzas">
                <h2 style="color: #1B263B; font-family: 'Poppins', sans-serif; margin-bottom: 1.5rem;">📝 Registrar Nuevo Movimiento</h2>
                <form method="POST" action="" id="formMovimiento">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="fecha">Fecha *</label>
                            <input type="date" id="fecha" name="fecha" required max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="tipo">Tipo *</label>
                            <select id="tipo" name="tipo" required>
                                <option value="">Seleccione...</option>
                                <option value="Ingreso">💰 Ingreso</option>
                                <option value="Gasto">💸 Gasto</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="categoria">Categoría *</label>
                            <select id="categoria" name="categoria" required>
                                <option value="">Seleccione...</option>
                                <option value="Arriendo">🏠 Arriendo</option>
                                <option value="Nómina">👥 Nómina</option>
                                <option value="Servicios">⚡ Servicios</option>
                                <option value="Personal">👤 Personal</option>
                                <option value="Otro">📌 Otro</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="monto">Monto *</label>
                            <input type="number" id="monto" name="monto" step="0.01" min="0.01" required placeholder="0.00">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion">Descripción *</label>
                        <textarea id="descripcion" name="descripcion" rows="3" required placeholder="Describe el movimiento..."></textarea>
                    </div>
                    
                    <button type="submit" name="registrar_movimiento" class="btn-registrar">
                        ✅ Registrar Movimiento
                    </button>
                </form>
            </div>

            <!-- Resumen de totales -->
            <div class="totales-grid">
                <div class="total-card ingreso">
                    <div class="total-icon">💰</div>
                    <div class="total-info">
                        <span class="total-label">Ingresos Totales</span>
                        <span class="total-amount">$<?php echo number_format($totales['total_ingresos'], 2, ',', '.'); ?></span>
                    </div>
                </div>
                
                <div class="total-card gasto">
                    <div class="total-icon">💸</div>
                    <div class="total-info">
                        <span class="total-label">Gastos Totales</span>
                        <span class="total-amount">$<?php echo number_format($totales['total_gastos'], 2, ',', '.'); ?></span>
                    </div>
                </div>
                
                <div class="total-card balance">
                    <div class="total-icon">⚖️</div>
                    <div class="total-info">
                        <span class="total-label">Balance General</span>
                        <span class="total-amount" style="color: <?php echo $totales['balance'] >= 0 ? '#28a745' : '#dc3545'; ?>">
                            $<?php echo number_format($totales['balance'], 2, ',', '.'); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tabla de movimientos -->
            <div class="card-finanzas" style="margin-top: 2rem;">
                <h2 style="color: #1B263B; font-family: 'Poppins', sans-serif; margin-bottom: 1.5rem;">📊 Historial de Movimientos</h2>
                
                <?php if (empty($movimientos)): ?>
                    <div style="text-align: center; padding: 3rem; color: #6c757d;">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
                        <p style="font-size: 1.1rem; font-family: 'Poppins', sans-serif;">No hay movimientos registrados</p>
                        <p style="color: #999;">Comienza registrando tu primer movimiento financiero</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
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
                            <tbody>
                                <?php foreach ($movimientos as $mov): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y', strtotime($mov['fecha'])); ?></td>
                                        <td>
                                            <span class="badge-tipo <?php echo strtolower($mov['tipo']); ?>">
                                                <?php echo $mov['tipo'] === 'Ingreso' ? '💰' : '💸'; ?>
                                                <?php echo htmlspecialchars($mov['tipo']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($mov['categoria']); ?></td>
                                        <td class="monto-<?php echo strtolower($mov['tipo']); ?>">
                                            $<?php echo number_format($mov['monto'], 2, ',', '.'); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($mov['descripcion']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .dashboard-container {
            min-height: 100vh;
        }
        
        .dashboard-header {
            background-color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dashboard-header .logo {
            height: 40px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-family: 'Poppins', sans-serif;
        }
        
        .logout-btn {
            background-color: #ee6f92;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s;
        }
        
        .logout-btn:hover {
            background-color: #d63384;
        }
        
        .dashboard-main {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .card-finanzas {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 500;
            color: #222222;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 0.75rem;
            border: 1px solid #E5E5E5;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #6A1B1B;
        }

        .btn-registrar {
            background: linear-gradient(135deg, #6A1B1B, #882A57);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
            width: 100%;
            margin-top: 1rem;
        }

        .btn-registrar:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(106, 27, 27, 0.3);
        }

        .totales-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .total-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .total-card.ingreso {
            border-left: 5px solid #28a745;
        }

        .total-card.gasto {
            border-left: 5px solid #dc3545;
        }

        .total-card.balance {
            border-left: 5px solid #222222;
        }

        .total-icon {
            font-size: 3rem;
        }

        .total-info {
            display: flex;
            flex-direction: column;
        }

        .total-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
        }

        .total-amount {
            font-size: 1.8rem;
            font-weight: 700;
            color: #222222;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .tabla-movimientos {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        .tabla-movimientos thead {
            background-color: #1B263B;
            color: white;
        }

        .tabla-movimientos th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        .tabla-movimientos td {
            padding: 1rem;
            border-bottom: 1px solid #E5E5E5;
        }

        .tabla-movimientos tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge-tipo {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        .badge-tipo.ingreso {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-tipo.gasto {
            background-color: #f8d7da;
            color: #721c24;
        }

        .monto-ingreso {
            color: #28a745;
            font-weight: 600;
        }

        .monto-gasto {
            color: #dc3545;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-main {
                padding: 1rem;
            }

            .totales-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        // Validación del formulario
        document.getElementById('formMovimiento').addEventListener('submit', function(e) {
            const monto = document.getElementById('monto').value;
            
            if (parseFloat(monto) <= 0) {
                e.preventDefault();
                alert('El monto debe ser mayor a 0');
                return false;
            }
        });

        // Establecer fecha actual por defecto
        document.getElementById('fecha').value = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
