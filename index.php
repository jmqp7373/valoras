<?php
/**
 * Valora.vip - Dashboard Principal
 * Deploy Test: Nov 5, 2025 - FTP Credentials Updated
 */
require_once 'config/database.php';
require_once 'controllers/FinanzasController.php';
startSessionSafely();

// Obtener datos financieros para el resumen
$finanzasController = new FinanzasController();
$totalesFinanzas = $finanzasController->calcularTotales();

// Verificar si el usuario está logueado
if(!isLoggedIn()) {
    header('Location: views/login/login.php');
    exit();
}

// Obtener información del usuario
$user_nombres = $_SESSION['user_nombres'] ?? '';
$user_apellidos = $_SESSION['user_apellidos'] ?? '';
$user_cedula = $_SESSION['user_cedula'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Valora</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <?php
        // Incluir header con botón de usuario
        $logo_path = 'assets/images/logos/logoValoraHorizontal.png';
        $logout_path = 'controllers/login/logout.php';
        $profile_path = 'views/usuario/miPerfil.php';
        include 'components/header/header.php';
        ?>
        
        <main class="dashboard-main">
            <div class="welcome-section">
                <h1>¡Bienvenido a Valora!</h1>
                <p>Has iniciado sesión exitosamente.</p>
                <div class="user-details">
                    <h3>Información del Usuario:</h3>
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($user_nombres . ' ' . $user_apellidos); ?></p>
                    <p><strong>Cédula:</strong> <?php echo htmlspecialchars($user_cedula); ?></p>
                </div>
                
                <div style="margin-top: 2rem; text-align: center;">
                    <a href="views/tickets/ticketCreate.php" 
                       style="background: linear-gradient(135deg, #882A57, #ee6f92); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; margin: 10px;">
                        🎫 Crear Ticket de Soporte
                    </a>
                    <a href="views/tickets/ticketList.php" 
                       style="background: #17a2b8; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; margin: 10px;">
                        📋 Ver Mis Tickets
                    </a>
                    <a href="views/admin/checksTests/system-check.php" 
                       style="background: #8b5a83; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; margin: 10px;">
                        🔍 Verificación del Sistema
                    </a>
                    <a href="views/finanzas/finanzasDashboard.php" 
                       style="background: #222222; color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; margin: 10px; font-weight: 600;">
                        💰 Gestión de Finanzas Completa
                    </a>
                </div>
            </div>

            <!-- Resumen Financiero -->
            <div class="finanzas-resumen" style="margin-top: 3rem;">
                <h2 style="text-align: center; color: #222222; margin-bottom: 2rem; font-size: 1.8rem;">📊 Resumen Financiero</h2>
                
                <!-- Tarjetas de totales -->
                <div class="finanzas-cards">
                    <div class="finanza-card ingreso">
                        <div class="finanza-icon">💰</div>
                        <div class="finanza-info">
                            <span class="finanza-label">Ingresos Totales</span>
                            <span class="finanza-amount" id="totalIngresos">$<?php echo number_format($totalesFinanzas['total_ingresos'], 2, ',', '.'); ?></span>
                        </div>
                    </div>
                    
                    <div class="finanza-card gasto">
                        <div class="finanza-icon">💸</div>
                        <div class="finanza-info">
                            <span class="finanza-label">Gastos Totales</span>
                            <span class="finanza-amount" id="totalGastos">$<?php echo number_format($totalesFinanzas['total_gastos'], 2, ',', '.'); ?></span>
                        </div>
                    </div>
                    
                    <div class="finanza-card balance">
                        <div class="finanza-icon">⚖️</div>
                        <div class="finanza-info">
                            <span class="finanza-label">Balance General</span>
                            <span class="finanza-amount" id="totalBalance" style="color: <?php echo $totalesFinanzas['balance'] >= 0 ? '#28a745' : '#dc3545'; ?>">
                                $<?php echo number_format($totalesFinanzas['balance'], 2, ',', '.'); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Gráfico circular -->
                <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-top: 2rem; max-width: 500px; margin-left: auto; margin-right: auto;">
                    <canvas id="finanzasChart" width="400" height="400"></canvas>
                    <p id="ultimaActualizacion" style="text-align: center; color: #6c757d; margin-top: 1rem; font-size: 0.9rem;">
                        Última actualización: <strong><?php echo htmlspecialchars($totalesFinanzas['ultima_actualizacion']); ?></strong>
                    </p>
                </div>
            </div>
        </main>
    </div>
    
    <style>
        /* Reset de estilos del body para permitir scroll */
        body {
            display: block !important;
            height: auto !important;
            min-height: 100vh;
            overflow-y: auto !important;
            padding: 0 !important;
        }

        .dashboard-container {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        .dashboard-main {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .welcome-section {
            background-color: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .user-details {
            margin-top: 2rem;
            padding: 1rem;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        /* Estilos para resumen financiero */
        .finanzas-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .finanza-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.3s;
        }

        .finanza-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .finanza-card.ingreso {
            border-left: 5px solid #28a745;
        }

        .finanza-card.gasto {
            border-left: 5px solid #dc3545;
        }

        .finanza-card.balance {
            border-left: 5px solid #222222;
        }

        .finanza-icon {
            font-size: 3rem;
        }

        .finanza-info {
            display: flex;
            flex-direction: column;
        }

        .finanza-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
        }

        .finanza-amount {
            font-size: 1.8rem;
            font-weight: 700;
            color: #222222;
        }

        @media (max-width: 768px) {
            .finanzas-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <script>
        // Variable global para el gráfico
        let miGraficoFinanzas = null;

        // Gráfico de finanzas
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('finanzasChart');
            if (ctx) {
                const ingresos = <?php echo $totalesFinanzas['total_ingresos']; ?>;
                const gastos = <?php echo $totalesFinanzas['total_gastos']; ?>;
                
                // Solo mostrar gráfico si hay datos
                if (ingresos > 0 || gastos > 0) {
                    miGraficoFinanzas = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Ingresos', 'Gastos'],
                            datasets: [{
                                data: [ingresos, gastos],
                                backgroundColor: [
                                    '#28a745',
                                    '#dc3545'
                                ],
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: {
                                            size: 14,
                                            family: "'Poppins', sans-serif"
                                        },
                                        padding: 20
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Distribución de Ingresos y Gastos',
                                    font: {
                                        size: 16,
                                        weight: 'bold',
                                        family: "'Poppins', sans-serif"
                                    },
                                    padding: {
                                        bottom: 20
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            label += '$' + context.parsed.toLocaleString('es-CO', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                            return label;
                                        }
                                    }
                                }
                            }
                        }
                    });
                } else {
                    // Mostrar mensaje si no hay datos
                    ctx.parentElement.innerHTML = '<div style="text-align: center; padding: 2rem; color: #6c757d;"><p style="font-size: 3rem;">📊</p><p style="font-size: 1.1rem;">No hay datos financieros para mostrar</p><p style="color: #999; margin-top: 0.5rem;">Comienza registrando movimientos en el módulo de finanzas</p></div>';
                }
            }

            // Iniciar actualización automática
            actualizarResumenFinanzas();
            setInterval(actualizarResumenFinanzas, 30000); // Cada 30 segundos
        });

        /**
         * Actualiza el resumen financiero sin recargar la página
         */
        async function actualizarResumenFinanzas() {
            try {
                const response = await fetch('controllers/FinanzasController.php?action=totales_json');
                const data = await response.json();

                // Actualizar tarjetas de totales
                const totalIngresosEl = document.getElementById('totalIngresos');
                const totalGastosEl = document.getElementById('totalGastos');
                const totalBalanceEl = document.getElementById('totalBalance');
                const ultimaActualizacionEl = document.getElementById('ultimaActualizacion');

                if (totalIngresosEl) {
                    totalIngresosEl.innerText = '$' + data.ingresos.toLocaleString('es-CO', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }

                if (totalGastosEl) {
                    totalGastosEl.innerText = '$' + data.gastos.toLocaleString('es-CO', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                }

                if (totalBalanceEl) {
                    totalBalanceEl.innerText = '$' + data.balance.toLocaleString('es-CO', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    // Actualizar color del balance
                    totalBalanceEl.style.color = data.balance >= 0 ? '#28a745' : '#dc3545';
                }

                if (ultimaActualizacionEl) {
                    ultimaActualizacionEl.innerHTML = 'Última actualización: <strong>' + data.ultima_actualizacion + '</strong>';
                }

                // Actualizar gráfico Chart.js
                if (miGraficoFinanzas && (data.ingresos > 0 || data.gastos > 0)) {
                    miGraficoFinanzas.data.datasets[0].data = [data.ingresos, data.gastos];
                    miGraficoFinanzas.update();
                } else if (!miGraficoFinanzas && (data.ingresos > 0 || data.gastos > 0)) {
                    // Si no había gráfico pero ahora hay datos, recargar la página
                    location.reload();
                }
            } catch (error) {
                console.error('Error actualizando resumen financiero:', error);
            }
        }

        // Hacer la función global para que pueda ser llamada desde otras páginas
        window.actualizarResumenFinanzas = actualizarResumenFinanzas;
    </script>

    <?php
    $base_path = '';
    include 'components/footer.php';
    ?>
</body>
</html>